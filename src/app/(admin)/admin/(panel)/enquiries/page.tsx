import Link from "next/link";
import { ConfirmButton } from "@/components/admin/ActionForm";
import Icon from "@/components/Icon";
import { requireAdmin } from "@/lib/admin";
import type { Enquiry } from "@/lib/types";
import { formatDate, telHref, waNumber } from "@/lib/util";
import { enquiryAction } from "../../actions";

export const metadata = { title: "Enquiries" };
type Props = { searchParams: Promise<{ view?: string; type?: string }> };

export default async function EnquiriesAdmin({ searchParams }: Props) {
  const { sb } = await requireAdmin();
  const sp = await searchParams;
  const view = Number(sp.view ?? 0);

  if (view) {
    const { data } = await sb.from("enquiries").select("*").eq("id", view).maybeSingle();
    const q = data as Enquiry | null;
    if (!q) return <><h1>Enquiries</h1><p>That enquiry no longer exists. <Link href="/admin/enquiries">Back</Link></p></>;
    if (!q.is_read) await sb.from("enquiries").update({ is_read: true }).eq("id", q.id);
    const num = waNumber(q.phone);
    const reply = `Hello ${q.name}, thank you for contacting Bio Fashion Hub. `;
    return (
      <>
        <h1>Enquiry</h1>
        <p><Link href="/admin/enquiries">← All enquiries</Link></p>
        <div className="a-card">
          <div className="a-card-head"><h2>{q.name}</h2><span className="a-tag">{q.type === "custom" ? "Custom order" : "Contact"}</span></div>
          <dl className="a-dl">
            <dt>Received</dt><dd>{formatDate(q.created_at, { weekday: "long", day: "numeric", month: "long", year: "numeric", hour: "numeric", minute: "2-digit" })}</dd>
            {q.phone && <><dt>Phone</dt><dd><a href={telHref(q.phone)}>{q.phone}</a></dd></>}
            {q.email && <><dt>Email</dt><dd><a href={`mailto:${q.email}`}>{q.email}</a></dd></>}
            {q.occasion && <><dt>Occasion</dt><dd>{q.occasion}</dd></>}
            {q.preferred_date && <><dt>Preferred date</dt><dd>{formatDate(q.preferred_date, { weekday: "long", day: "numeric", month: "long", year: "numeric" })}</dd></>}
          </dl>
          <div className="a-message" style={{ whiteSpace: "pre-line" }}>{q.message}</div>
          <div className="a-actions">
            {num.length >= 11 && <a className="a-btn a-btn-wa" href={`https://wa.me/${num}?text=${encodeURIComponent(reply)}`} target="_blank" rel="noopener"><Icon name="whatsapp" /> Reply on WhatsApp</a>}
            {q.email && <a className="a-btn a-btn-ghost" href={`mailto:${q.email}?subject=${encodeURIComponent("Your enquiry with Bio Fashion Hub")}`}>Reply by email</a>}
            <form action={enquiryAction}><input type="hidden" name="id" value={q.id} /><input type="hidden" name="action" value="unread" /><button className="a-btn a-btn-ghost">Mark unread</button></form>
            <form action={enquiryAction}><input type="hidden" name="id" value={q.id} /><input type="hidden" name="action" value="delete" /><ConfirmButton className="a-btn a-btn-danger" message="Delete this enquiry?">Delete</ConfirmButton></form>
          </div>
        </div>
      </>
    );
  }

  const type = sp.type === "custom" || sp.type === "contact" ? sp.type : "";
  let query = sb.from("enquiries").select("*");
  if (type) query = query.eq("type", type);
  const items = ((await query.order("id", { ascending: false }).limit(300)).data ?? []) as Enquiry[];
  return (
    <>
      <h1>Enquiries</h1>
      <div className="a-toolbar">
        <nav className="a-tabs">
          <Link href="/admin/enquiries" aria-current={!type ? "page" : undefined}>All</Link>
          <Link href="/admin/enquiries?type=custom" aria-current={type === "custom" ? "page" : undefined}>Custom orders</Link>
          <Link href="/admin/enquiries?type=contact" aria-current={type === "contact" ? "page" : undefined}>Contact</Link>
        </nav>
        <form action={enquiryAction}><input type="hidden" name="action" value="read_all" /><button className="a-btn a-btn-ghost a-btn-sm">Mark all read</button></form>
      </div>
      {items.length ? (
        <ul className="a-list a-card">
          {items.map((q) => (
            <li key={q.id} className={q.is_read ? "" : "unread"}>
              <Link href={`/admin/enquiries?view=${q.id}`}>
                <span><strong>{q.name}</strong> <span className="a-tag">{q.type === "custom" ? "Custom order" : "Contact"}</span>{!q.is_read && <> <span className="a-tag a-tag-gold">New</span></>}</span>
                <span className="a-sub">{q.message.length > 110 ? q.message.slice(0, 110) + "…" : q.message}</span>
                <span className="a-sub">{q.phone || q.email} · {formatDate(q.created_at, { day: "numeric", month: "short", hour: "numeric", minute: "2-digit" })}</span>
              </Link>
            </li>
          ))}
        </ul>
      ) : <div className="a-card"><p className="a-help">No enquiries yet.</p></div>}
    </>
  );
}
