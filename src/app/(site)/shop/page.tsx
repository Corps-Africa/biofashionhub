import type { Metadata } from "next";
import Link from "next/link";
import Icon from "@/components/Icon";
import PageHero from "@/components/PageHero";
import ProductCard from "@/components/ProductCard";
import { getSettings } from "@/lib/data";
import { publicClient } from "@/lib/supabase/public";
import type { Category, Product } from "@/lib/types";
import { waLink } from "@/lib/util";

type Props = { searchParams: Promise<{ category?: string; q?: string }> };

export async function generateMetadata({ searchParams }: Props): Promise<Metadata> {
  const { category } = await searchParams;
  if (!category) return { title: "Shop", description: "Shop kaftans, quality fabrics, Hausa caps, accessories and wristwatches. Order on WhatsApp." };
  const { data } = await publicClient().from("categories").select("name, tagline").eq("slug", category).maybeSingle();
  return data ? { title: data.name, description: data.tagline } : { title: "Shop" };
}

export default async function ShopPage({ searchParams }: Props) {
  const { category = "", q = "" } = await searchParams;
  const search = q.trim().slice(0, 60);
  const sb = publicClient();
  const [st, catRes] = await Promise.all([getSettings(), sb.from("categories").select("*").order("sort").order("name")]);
  const categories = (catRes.data ?? []) as Category[];
  const current = categories.find((c) => c.slug === category) ?? null;

  let query = sb.from("products").select("*, categories(name, slug)").eq("published", true);
  if (current) query = query.eq("category_id", current.id);
  if (search) {
    const term = search.replace(/[%_,()*\\]/g, " ");
    query = query.or(`name.ilike.%${term}%,description.ilike.%${term}%`);
  }
  const products = ((await query.order("sort").order("id", { ascending: false })).data ?? []) as Product[];

  return (
    <>
      <PageHero eyebrow="Shop" title={current ? current.name : "The collection"}
        text={current ? current.tagline : "Tap “Order on WhatsApp” on any item and we’ll confirm size, price and delivery with you."} />
      <section className="section section-tight">
        <div className="wrap">
          <div className="shop-bar">
            <nav className="chips" aria-label="Categories">
              <Link className="chip" href="/shop" aria-current={!current ? "page" : undefined}>All</Link>
              {categories.map((c) => (
                <Link key={c.id} className="chip" href={`/shop?category=${encodeURIComponent(c.slug)}`} aria-current={current?.id === c.id ? "page" : undefined}>{c.name}</Link>
              ))}
            </nav>
            <form className="search" action="/shop" method="get" role="search">
              {current && <input type="hidden" name="category" value={current.slug} />}
              <label className="sr-only" htmlFor="q">Search products</label>
              <input id="q" name="q" type="search" placeholder="Search products" defaultValue={search} />
              <button className="btn btn-navy btn-sm" type="submit">Search</button>
            </form>
          </div>
          {products.length ? (
            <div className="product-grid">{products.map((p) => <ProductCard key={p.id} p={p} settings={st} />)}</div>
          ) : (
            <div className="empty">
              <p>No products found{search && ` for “${search}”`}.</p>
              <p>Can’t see what you want? We can source it or make it for you.</p>
              <a className="btn btn-wa" href={waLink(st, `Hello Bio Fashion Hub, I am looking for ${search || "something specific"}.`)} target="_blank" rel="noopener"><Icon name="whatsapp" /> Ask us on WhatsApp</a>
            </div>
          )}
        </div>
      </section>
    </>
  );
}
