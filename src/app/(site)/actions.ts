"use server";
import { getSettings } from "@/lib/data";
import { SITE_URL } from "@/lib/supabase/env";
import { publicClient } from "@/lib/supabase/public";
import { s, waLink } from "@/lib/util";

export type EnquiryState = {
  ok?: boolean;
  wa?: string;
  errors?: Partial<Record<"name" | "phone" | "email" | "message" | "form", string>>;
  values?: Record<string, string>;
};

const str = (fd: FormData, k: string, max: number) => String(fd.get(k) ?? "").trim().slice(0, max);

export async function submitEnquiry(_prev: EnquiryState, fd: FormData): Promise<EnquiryState> {
  const type = fd.get("type") === "custom" ? "custom" : "contact";
  const v = {
    name: str(fd, "name", 120),
    phone: str(fd, "phone", 40),
    email: str(fd, "email", 160),
    occasion: str(fd, "occasion", 120),
    preferred_date: str(fd, "preferred_date", 10),
    message: str(fd, "message", 3000),
  };
  const errors: EnquiryState["errors"] = {};
  if (!v.name) errors.name = "Please tell us your name.";
  if (!v.phone && !v.email) errors.phone = "Please give a phone number or email so we can reply.";
  if (v.email && !/^[^@\s]+@[^@\s]+\.[^@\s]+$/.test(v.email)) errors.email = "That email address doesn’t look right.";
  if (v.phone && !/^[0-9+\s()\-]{9,20}$/.test(v.phone)) errors.phone = "Please enter a valid phone number.";
  if (!v.message) errors.message = "Please add a short message.";
  if (!/^\d{4}-\d{2}-\d{2}$/.test(v.preferred_date)) v.preferred_date = "";
  if (Object.keys(errors).length) return { errors, values: v };

  const st = await getSettings();
  const biz = s(st, "business_name");

  // Spam traps: the hidden field must stay empty and real people take more than 3 seconds.
  // Bots get a normal-looking success and nothing is saved.
  const started = Number(fd.get("ts") || 0);
  if (String(fd.get("website") ?? "") !== "" || (started && Date.now() - started < 3000)) {
    return { ok: true, wa: waLink(st) };
  }

  const { error } = await publicClient().rpc("submit_enquiry", {
    p_type: type, p_name: v.name, p_phone: v.phone, p_email: v.email,
    p_occasion: v.occasion, p_preferred_date: v.preferred_date || null, p_message: v.message,
  });
  if (error) {
    const limited = error.message.includes("rate limited");
    return {
      values: v,
      errors: { form: limited ? "You’ve sent several messages already — please reach us on WhatsApp instead." : "Sorry, your message could not be sent. Please try again or message us on WhatsApp." },
    };
  }

  await notifyByEmail(s(st, "notify_email"), type, v);

  const intro = `Hello ${biz}, I'm ${v.name}. ` + (type === "custom"
    ? `I just sent a custom order request${v.occasion ? ` for ${v.occasion}` : ""} on your website.`
    : "I just sent a message on your website.");
  return { ok: true, wa: waLink(st, `${intro}\n\n${v.message}`) };
}

/** Optional email alert through Resend. Failures never block the visitor. */
async function notifyByEmail(to: string, type: string, v: Record<string, string>) {
  const key = process.env.RESEND_API_KEY;
  if (!key || !to) return;
  const lines = Object.entries(v).filter(([, val]) => val).map(([k, val]) => `${k[0].toUpperCase()}${k.slice(1).replace("_", " ")}: ${val}`);
  try {
    await fetch("https://api.resend.com/emails", {
      method: "POST",
      headers: { Authorization: `Bearer ${key}`, "Content-Type": "application/json" },
      body: JSON.stringify({
        from: process.env.ENQUIRY_FROM_EMAIL || "Bio Fashion Hub <onboarding@resend.dev>",
        to: [to],
        reply_to: v.email || undefined,
        subject: `New website enquiry: ${type === "custom" ? "custom order" : "contact"} from ${v.name}`,
        text: `New enquiry from the Bio Fashion Hub website\n\n${lines.join("\n")}\n\nView all enquiries: ${SITE_URL}/admin/enquiries\n`,
      }),
      signal: AbortSignal.timeout(5000),
    });
  } catch {
    // Email is a convenience — the enquiry is already saved in the dashboard.
  }
}
