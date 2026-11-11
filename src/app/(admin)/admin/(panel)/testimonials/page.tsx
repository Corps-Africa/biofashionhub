import Link from "next/link";
import { ActionForm, ConfirmButton, SubmitButton } from "@/components/admin/ActionForm";
import ImageField from "@/components/admin/ImageField";
import { requireAdmin } from "@/lib/admin";
import type { Testimonial } from "@/lib/types";
import { saveTestimonial, testimonialQuick } from "../../actions";

export const metadata = { title: "Testimonials" };

export default async function TestimonialsAdmin({ searchParams }: { searchParams: Promise<{ edit?: string }> }) {
  const { sb } = await requireAdmin();
  const editId = Number((await searchParams).edit ?? 0);
  const items = ((await sb.from("testimonials").select("*").order("sort").order("id")).data ?? []) as Testimonial[];
  const t: Partial<Testimonial> = items.find((i) => i.id === editId) ?? { rating: 5, published: true };

  return (
    <>
      <h1>Testimonials</h1>
      <p className="a-help">Only share words customers have agreed to let you publish. A good way: screenshot a happy WhatsApp message and ask, “May we share this on our website?”</p>
      <ul className="a-items" style={{ marginTop: "1rem" }}>
        {items.map((i) => (
          <li key={i.id} className={`a-item${i.published ? "" : " is-hidden"}`}>
            <div className="a-item-main">
              <span className="a-item-title">{i.name} <span className="a-sub">{i.role}</span></span>
              <span className="a-sub">“{i.quote.length > 140 ? i.quote.slice(0, 140) + "…" : i.quote}”</span>
              <span className="a-badges">
                {i.published ? <span className="a-tag">On website</span> : <span className="a-tag a-tag-muted">Hidden</span>}
                {i.name.startsWith("SAMPLE") && <span className="a-tag a-tag-warn">Sample — replace</span>}
              </span>
            </div>
            <div className="a-item-actions">
              <Link className="a-btn a-btn-sm" href={`/admin/testimonials?edit=${i.id}#form`}>Edit</Link>
              <form action={testimonialQuick}><input type="hidden" name="id" value={i.id} /><input type="hidden" name="action" value="toggle" /><button className="a-btn a-btn-ghost a-btn-sm">{i.published ? "Hide" : "Show"}</button></form>
              <form action={testimonialQuick}><input type="hidden" name="id" value={i.id} /><input type="hidden" name="action" value="delete" /><ConfirmButton message="Delete this testimonial?">Delete</ConfirmButton></form>
            </div>
          </li>
        ))}
      </ul>
      <div id="form">
        <ActionForm key={t.id ?? "new"} action={saveTestimonial} className="a-card a-form">
          <h2>{t.id ? "Edit testimonial" : "Add a testimonial"}</h2>
          <input type="hidden" name="id" value={t.id ?? 0} />
          <div className="a-grid-2">
            <div className="a-field"><label htmlFor="t-name">Customer name *</label><input id="t-name" name="name" required defaultValue={t.name} /></div>
            <div className="a-field"><label htmlFor="t-role">About them (optional)</label><input id="t-role" name="role" placeholder="e.g. Groom, Tamale" defaultValue={t.role} /></div>
          </div>
          <div className="a-field"><label htmlFor="t-quote">What they said *</label><textarea id="t-quote" name="quote" rows={4} required defaultValue={t.quote} /></div>
          <div className="a-grid-2">
            <div className="a-field"><label htmlFor="t-rating">Stars</label>
              <select id="t-rating" name="rating" defaultValue={t.rating}>{[5, 4, 3, 2, 1].map((n) => <option key={n} value={n}>{n} star{n > 1 ? "s" : ""}</option>)}</select></div>
            <ImageField name="image" label="Customer photo (optional)" value={t.image} folder="testimonials" />
          </div>
          <label className="a-check"><input type="checkbox" name="published" defaultChecked={t.published} /> Show on website</label>
          <div className="a-actions"><SubmitButton>Save testimonial</SubmitButton>{t.id && <Link className="a-btn a-btn-ghost" href="/admin/testimonials">Cancel</Link>}</div>
        </ActionForm>
      </div>
    </>
  );
}
