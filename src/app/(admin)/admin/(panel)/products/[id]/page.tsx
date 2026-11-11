import Link from "next/link";
import { notFound } from "next/navigation";
import { ActionForm, SubmitButton } from "@/components/admin/ActionForm";
import ImageField from "@/components/admin/ImageField";
import { requireAdmin } from "@/lib/admin";
import type { Category, Product } from "@/lib/types";
import { saveProduct } from "../../../actions";

export const metadata = { title: "Edit product" };

export default async function ProductEdit({ params }: { params: Promise<{ id: string }> }) {
  const { id } = await params;
  const { sb } = await requireAdmin();
  const isNew = id === "new";
  const [{ data: cats }, { data }] = await Promise.all([
    sb.from("categories").select("*").order("sort"),
    isNew ? Promise.resolve({ data: null }) : sb.from("products").select("*").eq("id", Number(id)).maybeSingle(),
  ]);
  if (!isNew && !data) notFound();
  const p: Partial<Product> = data ?? { published: true, in_stock: true, featured: false, sort: 0 };

  return (
    <>
      <h1>{isNew ? "Add product" : "Edit product"}</h1>
      <ActionForm action={saveProduct} className="a-form">
        <input type="hidden" name="id" value={p.id ?? 0} />
        <div className="a-card">
          <div className="a-field"><label htmlFor="name">Product name *</label><input id="name" name="name" required maxLength={140} defaultValue={p.name} /></div>
          <div className="a-grid-2">
            <div className="a-field"><label htmlFor="category_id">Category</label>
              <select id="category_id" name="category_id" defaultValue={p.category_id ?? ""}>
                <option value="">— None —</option>
                {((cats ?? []) as Category[]).map((c) => <option key={c.id} value={c.id}>{c.name}</option>)}
              </select></div>
            <div className="a-field"><label htmlFor="price">Price (GH₵)</label><input id="price" name="price" inputMode="decimal" placeholder="Leave empty for “Price on request”" defaultValue={p.price ?? ""} /></div>
          </div>
          <div className="a-field"><label htmlFor="description">Description</label><textarea id="description" name="description" rows={5} defaultValue={p.description} />
            <p className="a-help">Leave an empty line between paragraphs. Wrap words in **double stars** to make them bold.</p></div>
          <div className="a-field"><label htmlFor="details">Details (one per line)</label><textarea id="details" name="details" rows={4} placeholder={"Colour: Navy\nSizes: S – XXL"} defaultValue={p.details} />
            <p className="a-help">Write “Label: value” on each line, e.g. <em>Sizes: S – XXL</em>.</p></div>
        </div>
        <div className="a-card">
          <h2>Photos</h2>
          <div className="a-grid-2">
            <ImageField name="image" label="Main photo" value={p.image} folder="products" />
            <ImageField name="image2" label="Second photo (optional)" value={p.image2} folder="products" />
          </div>
        </div>
        <div className="a-card">
          <h2>Display</h2>
          <label className="a-check"><input type="checkbox" name="published" defaultChecked={p.published} /> Show on website</label>
          <label className="a-check"><input type="checkbox" name="featured" defaultChecked={p.featured} /> Feature on home page</label>
          <label className="a-check"><input type="checkbox" name="in_stock" defaultChecked={p.in_stock} /> Available now (untick for “Made to order”)</label>
          <div className="a-field a-narrow"><label htmlFor="sort">Order</label><input id="sort" name="sort" type="number" defaultValue={p.sort} /><p className="a-help">Lower numbers appear first.</p></div>
        </div>
        <div className="a-actions">
          <SubmitButton>Save product</SubmitButton>
          <Link className="a-btn a-btn-ghost" href="/admin/products">Cancel</Link>
          {p.slug && p.published && <a className="a-link" href={`/product/${p.slug}`} target="_blank" rel="noopener">View on website ↗</a>}
        </div>
      </ActionForm>
    </>
  );
}
