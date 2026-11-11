import Link from "next/link";
import { requireAdmin } from "@/lib/admin";
import type { Enquiry } from "@/lib/types";
import { formatDate } from "@/lib/util";

export const metadata = { title: "Dashboard" };

export default async function Dashboard() {
  const { sb } = await requireAdmin();
  const count = (q: PromiseLike<{ count: number | null }>) => q.then((r) => r.count ?? 0);
  const [products, unread, photos, live, placeholders, noPrice, settings, recent] = await Promise.all([
    count(sb.from("products").select("id", { count: "exact", head: true })),
    count(sb.from("enquiries").select("id", { count: "exact", head: true }).eq("is_read", false)),
    count(sb.from("gallery").select("id", { count: "exact", head: true })),
    count(sb.from("testimonials").select("id", { count: "exact", head: true }).eq("published", true)),
    count(sb.from("products").select("id", { count: "exact", head: true }).like("image", "/placeholder/%")),
    count(sb.from("products").select("id", { count: "exact", head: true }).is("price", null)),
    sb.from("settings").select("key, value").in("key", ["facebook_url", "tiktok_username", "instagram_url"]),
    sb.from("enquiries").select("*").order("id", { ascending: false }).limit(5),
  ]);
  const hasSocial = (settings.data ?? []).some((r) => r.value);
  const stats: [string, number, string][] = [
    ["Products", products, "/admin/products"], ["Unread enquiries", unread, "/admin/enquiries"],
    ["Lookbook photos", photos, "/admin/gallery"], ["Live testimonials", live, "/admin/testimonials"],
  ];
  const latest = (recent.data ?? []) as Enquiry[];

  return (
    <>
      <h1>Dashboard</h1>
      <div className="a-stats">
        {stats.map(([label, n, href]) => <Link key={href} className="a-stat" href={href}><strong>{n}</strong><span>{label}</span></Link>)}
      </div>
      <div className="a-quick">
        <Link className="a-btn" href="/admin/products/new">+ Add product</Link>
        <Link className="a-btn a-btn-ghost" href="/admin/gallery">+ Add lookbook photos</Link>
        <Link className="a-btn a-btn-ghost" href="/admin/posts/new">+ Write article</Link>
      </div>
      {(placeholders > 0 || noPrice > 0 || !hasSocial || live === 0) && (
        <section className="a-card">
          <h2>Getting ready to launch</h2>
          <ul className="a-todo">
            {placeholders > 0 && <li>{placeholders} product(s) still use sample photos from the flyer. <Link href="/admin/products">Add real photos</Link></li>}
            {noPrice > 0 && <li>{noPrice} product(s) show “Price on request”. Add prices if you want them on the site.</li>}
            {!hasSocial && <li>Add your social media links. <Link href="/admin/settings">Open settings</Link></li>}
            {live === 0 && <li>Add a real customer testimonial. <Link href="/admin/testimonials">Testimonials</Link></li>}
            <li>Check the shop address and map location are exact. <Link href="/admin/settings">Open settings</Link></li>
          </ul>
        </section>
      )}
      <section className="a-card">
        <div className="a-card-head"><h2>Latest enquiries</h2><Link href="/admin/enquiries">See all</Link></div>
        {latest.length ? (
          <ul className="a-list">
            {latest.map((q) => (
              <li key={q.id} className={q.is_read ? "" : "unread"}>
                <Link href={`/admin/enquiries?view=${q.id}`}>
                  <span><strong>{q.name}</strong> <span className="a-tag">{q.type === "custom" ? "Custom order" : "Contact"}</span></span>
                  <span className="a-sub">{q.message.length > 90 ? q.message.slice(0, 90) + "…" : q.message}</span>
                  <span className="a-sub">{formatDate(q.created_at, { day: "numeric", month: "short", hour: "numeric", minute: "2-digit" })}</span>
                </Link>
              </li>
            ))}
          </ul>
        ) : <p className="a-help">No enquiries yet. Messages from the Contact and Custom Orders forms will appear here.</p>}
      </section>
    </>
  );
}
