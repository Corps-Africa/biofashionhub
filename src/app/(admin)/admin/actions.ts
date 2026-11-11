"use server";
import { createClient } from "@supabase/supabase-js";
import { redirect } from "next/navigation";
import { adminOnly, bool, done, int, removeImages, serviceClient, str, uniqueSlug } from "@/lib/admin";
import { SETTING_FIELDS } from "@/lib/settingFields";
import { SITE_URL, SUPABASE_ANON_KEY, SUPABASE_URL } from "@/lib/supabase/env";
import { serverClient } from "@/lib/supabase/server";
import { waNumber } from "@/lib/util";

export type FormState = { error?: string; info?: string };

/* ───────────── Auth ───────────── */

export async function signIn(_: FormState, fd: FormData): Promise<FormState> {
  const sb = await serverClient();
  const { error } = await sb.auth.signInWithPassword({ email: str(fd, "email", 160), password: String(fd.get("password") ?? "") });
  if (error) return { error: error.status === 429 ? "Too many attempts. Please wait a few minutes and try again." : "Wrong email or password." };
  redirect("/admin");
}

export async function signOut() {
  const sb = await serverClient();
  await sb.auth.signOut();
  redirect("/admin/login");
}

export async function sendReset(_: FormState, fd: FormData): Promise<FormState> {
  const email = str(fd, "email", 160);
  if (!/^[^@\s]+@[^@\s]+\.[^@\s]+$/.test(email)) return { error: "Please enter a valid email address." };
  const sb = await serverClient();
  await sb.auth.resetPasswordForEmail(email, { redirectTo: `${SITE_URL}/auth/confirm?next=/admin/reset` });
  // Same reply whether or not the account exists, so the form can't be used to discover emails.
  return { info: "If that email belongs to an admin, a reset link is on its way. Check your inbox (and spam folder)." };
}

export async function setNewPassword(_: FormState, fd: FormData): Promise<FormState> {
  const pw = String(fd.get("password") ?? "");
  if (pw.length < 10) return { error: "Password must be at least 10 characters." };
  if (pw !== String(fd.get("password2") ?? "")) return { error: "The two passwords do not match." };
  const sb = await serverClient();
  const { error } = await sb.auth.updateUser({ password: pw });
  if (error) return { error: "Could not change the password: " + error.message };
  done("/admin", "Your new password is saved.");
}

/* ───────────── Products ───────────── */

export async function saveProduct(_: FormState, fd: FormData): Promise<FormState> {
  const { sb } = await adminOnly();
  const id = int(fd, "id");
  const name = str(fd, "name", 140);
  const priceRaw = str(fd, "price", 20).replace(/[^0-9.]/g, "");
  if (!name) return { error: "Please give the product a name." };
  if (priceRaw && Number.isNaN(Number(priceRaw))) return { error: "Price should be a number, e.g. 350 — or leave it empty for “Price on request”." };
  const row = {
    name,
    category_id: int(fd, "category_id") || null,
    price: priceRaw === "" ? null : Number(priceRaw),
    description: str(fd, "description", 4000),
    details: str(fd, "details", 2000),
    image: str(fd, "image", 300),
    image2: str(fd, "image2", 300),
    featured: bool(fd, "featured"),
    in_stock: bool(fd, "in_stock"),
    published: bool(fd, "published"),
    sort: int(fd, "sort"),
  };
  if (id) {
    const { data: old } = await sb.from("products").select("name, slug, image, image2").eq("id", id).single();
    if (!old) return { error: "That product no longer exists." };
    const slug = old.name === name ? old.slug : await uniqueSlug(sb, "products", name, id);
    const { error } = await sb.from("products").update({ ...row, slug }).eq("id", id);
    if (error) return { error: error.message };
    await removeImages(sb, old.image !== row.image ? old.image : null, old.image2 !== row.image2 ? old.image2 : null);
  } else {
    const { error } = await sb.from("products").insert({ ...row, slug: await uniqueSlug(sb, "products", name) });
    if (error) return { error: error.message };
  }
  done("/admin/products", `Saved “${name}”.`);
}

export async function productQuick(fd: FormData) {
  const { sb } = await adminOnly();
  const id = int(fd, "id");
  const { data: p } = await sb.from("products").select("*").eq("id", id).single();
  if (!p) done("/admin/products", "That product no longer exists.");
  switch (str(fd, "action")) {
    case "delete":
      await sb.from("products").delete().eq("id", id);
      await removeImages(sb, p.image, p.image2);
      done("/admin/products", `“${p.name}” was deleted.`);
    case "toggle_published":
      await sb.from("products").update({ published: !p.published }).eq("id", id);
      done("/admin/products", `“${p.name}” is now ${p.published ? "hidden" : "visible"} on the website.`);
    case "toggle_stock":
      await sb.from("products").update({ in_stock: !p.in_stock }).eq("id", id);
      done("/admin/products", `“${p.name}” marked as ${p.in_stock ? "made to order" : "available now"}.`);
  }
  done("/admin/products", "Nothing changed.");
}

/* ───────────── Categories ───────────── */

export async function saveCategory(_: FormState, fd: FormData): Promise<FormState> {
  const { sb } = await adminOnly();
  const id = int(fd, "id");
  const name = str(fd, "name", 80);
  if (!name) return { error: "Category name is required." };
  const icons = ["kaftan", "fabric", "hat", "watch", "scissors", "bag", "star"];
  const row = { name, tagline: str(fd, "tagline", 200), icon: icons.includes(str(fd, "icon")) ? str(fd, "icon") : "kaftan", sort: int(fd, "sort") };
  if (id) {
    const { data: old } = await sb.from("categories").select("name, slug").eq("id", id).single();
    if (!old) return { error: "That category no longer exists." };
    const slug = old.name === name ? old.slug : await uniqueSlug(sb, "categories", name, id);
    const { error } = await sb.from("categories").update({ ...row, slug }).eq("id", id);
    if (error) return { error: error.message };
  } else {
    const { error } = await sb.from("categories").insert({ ...row, slug: await uniqueSlug(sb, "categories", name) });
    if (error) return { error: error.message };
  }
  done("/admin/categories", `Saved “${name}”.`);
}

export async function deleteCategory(fd: FormData) {
  const { sb } = await adminOnly();
  await sb.from("categories").delete().eq("id", int(fd, "id"));
  done("/admin/categories", "Category deleted. Its products were kept with no category.");
}

/* ───────────── Enquiries ───────────── */

export async function enquiryAction(fd: FormData) {
  const { sb } = await adminOnly();
  const id = int(fd, "id");
  switch (str(fd, "action")) {
    case "delete": await sb.from("enquiries").delete().eq("id", id); done("/admin/enquiries", "Enquiry deleted.");
    case "unread": await sb.from("enquiries").update({ is_read: false }).eq("id", id); done("/admin/enquiries", "Marked as unread.");
    case "read_all": await sb.from("enquiries").update({ is_read: true }).eq("is_read", false); done("/admin/enquiries", "All marked as read.");
  }
  done("/admin/enquiries", "Nothing changed.");
}

/* ───────────── Lookbook ───────────── */

export async function addGalleryPhotos(paths: string[], caption: string): Promise<FormState> {
  const { sb } = await adminOnly();
  const clean = paths.filter((p) => /^gallery\/[\w\-/.]+$/.test(p)).slice(0, 50);
  if (!clean.length) return { error: "No photos were uploaded." };
  const { data: first } = await sb.from("gallery").select("sort").order("sort").limit(1);
  const start = (first?.[0]?.sort ?? 0) - clean.length;
  const { error } = await sb.from("gallery").insert(clean.map((image, i) => ({ image, caption: caption.slice(0, 160), sort: start + i })));
  if (error) return { error: error.message };
  done("/admin/gallery", `Added ${clean.length} photo${clean.length > 1 ? "s" : ""} to the lookbook.`);
}

export async function saveGallery(fd: FormData) {
  const { sb } = await adminOnly();
  const ids = fd.getAll("id").map((v) => Number(v));
  await Promise.all(ids.map((id) => sb.from("gallery").update({ caption: str(fd, `caption_${id}`, 160), sort: int(fd, `sort_${id}`) }).eq("id", id)));
  done("/admin/gallery", "Captions and order saved.");
}

export async function deleteGalleryPhoto(fd: FormData) {
  const { sb } = await adminOnly();
  const { data } = await sb.from("gallery").delete().eq("id", int(fd, "id")).select("image").single();
  await removeImages(sb, data?.image);
  done("/admin/gallery", "Photo removed.");
}

/* ───────────── Testimonials ───────────── */

export async function saveTestimonial(_: FormState, fd: FormData): Promise<FormState> {
  const { sb } = await adminOnly();
  const id = int(fd, "id");
  const row = {
    name: str(fd, "name", 100), role: str(fd, "role", 100), quote: str(fd, "quote", 800),
    rating: Math.max(1, Math.min(5, int(fd, "rating") || 5)), image: str(fd, "image", 300), published: bool(fd, "published"),
  };
  if (!row.name || !row.quote) return { error: "Name and the customer’s words are both required." };
  if (id) {
    const { data: old } = await sb.from("testimonials").select("image").eq("id", id).single();
    const { error } = await sb.from("testimonials").update(row).eq("id", id);
    if (error) return { error: error.message };
    if (old && old.image !== row.image) await removeImages(sb, old.image);
  } else {
    const { data: last } = await sb.from("testimonials").select("sort").order("sort", { ascending: false }).limit(1);
    const { error } = await sb.from("testimonials").insert({ ...row, sort: (last?.[0]?.sort ?? 0) + 1 });
    if (error) return { error: error.message };
  }
  done("/admin/testimonials", "Testimonial saved.");
}

export async function testimonialQuick(fd: FormData) {
  const { sb } = await adminOnly();
  const id = int(fd, "id");
  const { data: t } = await sb.from("testimonials").select("*").eq("id", id).single();
  if (!t) done("/admin/testimonials", "That testimonial no longer exists.");
  if (str(fd, "action") === "delete") {
    await sb.from("testimonials").delete().eq("id", id);
    await removeImages(sb, t.image);
    done("/admin/testimonials", "Testimonial deleted.");
  }
  await sb.from("testimonials").update({ published: !t.published }).eq("id", id);
  done("/admin/testimonials", t.published ? "Hidden from the website." : "Now showing on the website.");
}

/* ───────────── Posts ───────────── */

export async function savePost(_: FormState, fd: FormData): Promise<FormState> {
  const { sb } = await adminOnly();
  const id = int(fd, "id");
  const title = str(fd, "title", 160);
  if (!title) return { error: "Please add a title." };
  const date = str(fd, "published_date", 10);
  const row = {
    title, excerpt: str(fd, "excerpt", 300), body: str(fd, "body", 30000), image: str(fd, "image", 300), published: bool(fd, "published"),
    ...(/^\d{4}-\d{2}-\d{2}$/.test(date) ? { published_at: `${date}T09:00:00+00:00` } : {}),
  };
  if (id) {
    const { data: old } = await sb.from("posts").select("title, slug, image").eq("id", id).single();
    if (!old) return { error: "That article no longer exists." };
    const slug = old.title === title ? old.slug : await uniqueSlug(sb, "posts", title, id);
    const { error } = await sb.from("posts").update({ ...row, slug }).eq("id", id);
    if (error) return { error: error.message };
    if (old.image !== row.image) await removeImages(sb, old.image);
  } else {
    const { error } = await sb.from("posts").insert({ ...row, slug: await uniqueSlug(sb, "posts", title) });
    if (error) return { error: error.message };
  }
  done("/admin/posts", `Saved “${title}”.`);
}

export async function deletePost(fd: FormData) {
  const { sb } = await adminOnly();
  const { data } = await sb.from("posts").delete().eq("id", int(fd, "id")).select("title, image").single();
  await removeImages(sb, data?.image);
  done("/admin/posts", data ? `Deleted “${data.title}”.` : "Deleted.");
}

/* ───────────── Settings ───────────── */

export async function saveSettings(_: FormState, fd: FormData): Promise<FormState> {
  const { sb } = await adminOnly();
  const { data: current } = await sb.from("settings").select("key, value");
  const before = Object.fromEntries((current ?? []).map((r) => [r.key, r.value]));
  const rows: { key: string; value: string }[] = [];
  const replaced: string[] = [];
  for (const f of SETTING_FIELDS) {
    let v = f.type === "checkbox" ? (bool(fd, f.key) ? "1" : "0") : str(fd, f.key, f.type === "textarea" ? 3000 : 300);
    if (f.type === "email" && v && !/^[^@\s]+@[^@\s]+\.[^@\s]+$/.test(v)) return { error: `${f.label} is not a valid email address.` };
    if (f.type === "url" && v) {
      if (!/^https?:\/\//i.test(v)) v = "https://" + v;
      try { new URL(v); } catch { return { error: `${f.label} is not a valid link.` }; }
    }
    if (f.key === "tiktok_username") v = v.replace(/^@/, "").replace(/[^\w.]/g, "");
    if (f.key === "whatsapp_number" && waNumber(v).length < 11) return { error: "Please enter a full WhatsApp number, e.g. 054 847 3309." };
    if (f.type === "image" && before[f.key] && before[f.key] !== v) replaced.push(before[f.key]);
    rows.push({ key: f.key, value: v });
  }
  const { error } = await sb.from("settings").upsert(rows);
  if (error) return { error: error.message };
  await removeImages(sb, ...replaced);
  done("/admin/settings", "Settings saved. Changes are live on the website.");
}

/* ───────────── Account ───────────── */

export async function updateName(fd: FormData) {
  const { sb, user } = await adminOnly();
  await sb.from("admins").update({ display_name: str(fd, "display_name", 80) }).eq("user_id", user.id);
  done("/admin/account", "Name updated.");
}

export async function changePassword(_: FormState, fd: FormData): Promise<FormState> {
  const { sb, user } = await adminOnly();
  const pw = String(fd.get("new") ?? "");
  // Confirm the current password with a throwaway client so the live session isn't touched.
  const check = createClient(SUPABASE_URL, SUPABASE_ANON_KEY, { auth: { persistSession: false, autoRefreshToken: false } });
  const { error: wrong } = await check.auth.signInWithPassword({ email: user.email!, password: String(fd.get("current") ?? "") });
  if (wrong) return { error: "Your current password is wrong." };
  if (pw.length < 10) return { error: "New password must be at least 10 characters." };
  if (pw !== String(fd.get("new2") ?? "")) return { error: "The new passwords do not match." };
  const { error } = await sb.auth.updateUser({ password: pw });
  if (error) return { error: error.message };
  done("/admin/account", "Password changed.");
}

export async function addAdmin(_: FormState, fd: FormData): Promise<FormState> {
  await adminOnly();
  const svc = serviceClient();
  if (!svc) return { error: "Adding admins needs SUPABASE_SERVICE_ROLE_KEY set in Netlify. See the README." };
  const email = str(fd, "email", 160).toLowerCase();
  const pw = String(fd.get("password") ?? "");
  if (!/^[^@\s]+@[^@\s]+\.[^@\s]+$/.test(email)) return { error: "Please enter a valid email address." };
  if (pw.length < 10) return { error: "Temporary password must be at least 10 characters." };
  const { data, error } = await svc.auth.admin.createUser({ email, password: pw, email_confirm: true });
  if (error) return { error: error.message.includes("already") ? "That email already has an account." : error.message };
  const { error: e2 } = await svc.from("admins").insert({ user_id: data.user.id, display_name: str(fd, "display_name", 80) });
  if (e2) return { error: e2.message };
  done("/admin/account", `Added ${email}. Share the temporary password with them privately.`);
}

export async function removeAdmin(fd: FormData) {
  const { user } = await adminOnly();
  const svc = serviceClient();
  const id = str(fd, "user_id", 60);
  if (!svc) done("/admin/account", "Removing admins needs SUPABASE_SERVICE_ROLE_KEY. See the README.");
  if (id === user.id) done("/admin/account", "You can’t remove yourself.");
  await svc.from("admins").delete().eq("user_id", id);
  await svc.auth.admin.deleteUser(id);
  done("/admin/account", "Admin removed.");
}
