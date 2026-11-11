import type { Metadata } from "next";
import Link from "next/link";
import { notFound } from "next/navigation";
import { cache } from "react";
import Icon from "@/components/Icon";
import Paras from "@/components/Paras";
import ProductCard from "@/components/ProductCard";
import ProductPhotos from "@/components/ProductPhotos";
import { getSettings } from "@/lib/data";
import { publicClient } from "@/lib/supabase/public";
import type { Product } from "@/lib/types";
import { imgUrl, money, productOrderMessage, waLink } from "@/lib/util";

export const revalidate = 300;
type Props = { params: Promise<{ slug: string }> };

const getProduct = cache(async (slug: string) => {
  const { data } = await publicClient().from("products").select("*, categories(name, slug)").eq("slug", slug).eq("published", true).maybeSingle();
  return data as Product | null;
});

export async function generateMetadata({ params }: Props): Promise<Metadata> {
  const p = await getProduct((await params).slug);
  if (!p) return { title: "Not found" };
  return { title: p.name, description: p.description.slice(0, 160), openGraph: { images: [imgUrl(p.image)] } };
}

export default async function ProductPage({ params }: Props) {
  const p = await getProduct((await params).slug);
  if (!p) notFound();
  const st = await getSettings();
  let rel = publicClient().from("products").select("*, categories(name, slug)").eq("published", true).neq("id", p.id);
  rel = p.category_id ? rel.eq("category_id", p.category_id) : rel.is("category_id", null);
  const related = ((await rel.order("sort").limit(4)).data ?? []) as Product[];
  const details = p.details.split(/\r?\n/).map((l) => l.trim()).filter(Boolean);

  return (
    <>
      <section className="section">
        <div className="wrap">
          <nav className="crumbs" aria-label="Breadcrumb">
            <Link href="/shop">Shop</Link>
            {p.categories && <> / <Link href={`/shop?category=${encodeURIComponent(p.categories.slug)}`}>{p.categories.name}</Link></>}
          </nav>
          <div className="product-detail">
            <ProductPhotos images={[p.image, p.image2].filter(Boolean).map((i) => imgUrl(i))} alt={p.name} />
            <div className="pd-info">
              {p.categories && <p className="eyebrow">{p.categories.name}</p>}
              <h1>{p.name}</h1>
              <p className="price price-lg">{money(p.price)}</p>
              <p className={`stock ${p.in_stock ? "in" : "out"}`}>
                {p.in_stock ? <><Icon name="check" /> Available now</> : <><Icon name="clock" /> Made to order — ask for timing</>}
              </p>
              <Paras className="prose" text={p.description} />
              {details.length > 0 && (
                <ul className="details">
                  {details.map((line, i) => {
                    const at = line.indexOf(":");
                    return <li key={i}>{at > 0 ? <><span>{line.slice(0, at).trim()}</span>{line.slice(at + 1).trim()}</> : line}</li>;
                  })}
                </ul>
              )}
              <a className="btn btn-wa btn-lg btn-block" href={waLink(st, productOrderMessage(p))} target="_blank" rel="noopener"><Icon name="whatsapp" /> Order on WhatsApp</a>
              <p className="small muted">Your item is added to the message automatically. We’ll reply to confirm size, colour, price and delivery.</p>
              <Link className="btn btn-outline btn-block" href={`/custom-orders?item=${encodeURIComponent(p.name)}`}><Icon name="ruler" /> Need it in your size or fabric?</Link>
            </div>
          </div>
        </div>
      </section>
      {related.length > 0 && (
        <section className="section section-tint">
          <div className="wrap">
            <div className="section-head"><p className="eyebrow">You may also like</p><h2>More from {p.categories?.name ?? "the shop"}</h2></div>
            <div className="product-grid">{related.map((r) => <ProductCard key={r.id} p={r} settings={st} />)}</div>
          </div>
        </section>
      )}
    </>
  );
}
