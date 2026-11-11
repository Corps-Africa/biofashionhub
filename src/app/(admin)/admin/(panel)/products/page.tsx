import Link from "next/link";
import { ConfirmButton } from "@/components/admin/ActionForm";
import { requireAdmin } from "@/lib/admin";
import type { Category, Product } from "@/lib/types";
import { imgUrl, money } from "@/lib/util";
import { productQuick } from "../../actions";

export const metadata = { title: "Products" };

export default async function ProductsAdmin({ searchParams }: { searchParams: Promise<{ category?: string }> }) {
  const { sb } = await requireAdmin();
  const filter = Number((await searchParams).category ?? 0);
  let q = sb.from("products").select("*, categories(name, slug)");
  if (filter) q = q.eq("category_id", filter);
  const [{ data: cats }, { data }] = await Promise.all([sb.from("categories").select("*").order("sort"), q.order("sort").order("id", { ascending: false })]);
  const products = (data ?? []) as Product[];

  return (
    <>
      <h1>Products</h1>
      <div className="a-toolbar">
        <Link className="a-btn" href="/admin/products/new">+ Add product</Link>
        <nav className="a-tabs" aria-label="Filter by category">
          <Link href="/admin/products" aria-current={!filter ? "page" : undefined}>All</Link>
          {((cats ?? []) as Category[]).map((c) => <Link key={c.id} href={`/admin/products?category=${c.id}`} aria-current={filter === c.id ? "page" : undefined}>{c.name}</Link>)}
        </nav>
      </div>
      {!products.length ? (
        <div className="a-card"><p>No products yet. <Link href="/admin/products/new">Add your first product</Link>.</p></div>
      ) : (
        <ul className="a-items">
          {products.map((p) => (
            <li key={p.id} className={`a-item${p.published ? "" : " is-hidden"}`}>
              {/* eslint-disable-next-line @next/next/no-img-element */}
              <img src={imgUrl(p.image)} alt="" />
              <div className="a-item-main">
                <Link className="a-item-title" href={`/admin/products/${p.id}`}>{p.name}</Link>
                <span className="a-sub">{p.categories?.name ?? "No category"} · {money(p.price)}</span>
                <span className="a-badges">
                  {!p.published && <span className="a-tag a-tag-muted">Hidden</span>}
                  {p.featured && <span className="a-tag a-tag-gold">Featured</span>}
                  <span className="a-tag">{p.in_stock ? "Available" : "Made to order"}</span>
                  {p.image.startsWith("/placeholder/") && <span className="a-tag a-tag-warn">Sample photo</span>}
                </span>
              </div>
              <div className="a-item-actions">
                <Link className="a-btn a-btn-sm" href={`/admin/products/${p.id}`}>Edit</Link>
                <form action={productQuick}><input type="hidden" name="id" value={p.id} /><input type="hidden" name="action" value="toggle_stock" />
                  <button className="a-btn a-btn-ghost a-btn-sm">{p.in_stock ? "Mark made-to-order" : "Mark available"}</button></form>
                <form action={productQuick}><input type="hidden" name="id" value={p.id} /><input type="hidden" name="action" value="toggle_published" />
                  <button className="a-btn a-btn-ghost a-btn-sm">{p.published ? "Hide" : "Show"}</button></form>
                <form action={productQuick}><input type="hidden" name="id" value={p.id} /><input type="hidden" name="action" value="delete" />
                  <ConfirmButton message={`Delete “${p.name}”? This cannot be undone.`}>Delete</ConfirmButton></form>
              </div>
            </li>
          ))}
        </ul>
      )}
    </>
  );
}
