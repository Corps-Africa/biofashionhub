import Link from "next/link";
import Icon from "@/components/Icon";
import { PostCard, Quote } from "@/components/Cards";
import ProductCard from "@/components/ProductCard";
import { getSettings } from "@/lib/data";
import { publicClient } from "@/lib/supabase/public";
import type { Category, GalleryItem, Post, Product, Testimonial } from "@/lib/types";
import { imgUrl, s, telHref } from "@/lib/util";

export const revalidate = 300;

export default async function HomePage() {
  const sb = publicClient();
  const [st, cats, featured, testimonials, gallery, posts] = await Promise.all([
    getSettings(),
    sb.from("categories").select("*").order("sort").order("name"),
    sb.from("products").select("*, categories(name, slug)").eq("published", true).eq("featured", true).order("sort").order("id", { ascending: false }).limit(8),
    sb.from("testimonials").select("*").eq("published", true).order("sort").order("id").limit(3),
    sb.from("gallery").select("*").order("sort").order("id", { ascending: false }).limit(6),
    sb.from("posts").select("*").eq("published", true).lte("published_at", new Date().toISOString()).order("published_at", { ascending: false }).limit(2),
  ]);
  const categories = (cats.data ?? []) as Category[];
  const products = (featured.data ?? []) as Product[];
  const quotes = (testimonials.data ?? []) as Testimonial[];
  const photos = (gallery.data ?? []) as GalleryItem[];
  const articles = (posts.data ?? []) as Post[];
  const tiktok = s(st, "tiktok_username").replace(/^@/, "");
  const city = s(st, "city");

  return (
    <>
      <section className="hero">
        <div className="wrap hero-grid">
          <div className="hero-copy">
            <p className="eyebrow">{s(st, "hero_eyebrow")}</p>
            <h1 className="display">{s(st, "hero_title")}</h1>
            <p className="tagline-caps">{s(st, "tagline")}</p>
            <span className="rule" aria-hidden="true" />
            <p className="lead">{s(st, "hero_text")}</p>
            <div className="btn-row">
              <Link className="btn btn-navy" href="/shop"><Icon name="bag" /> Shop the collection</Link>
              <Link className="btn btn-outline" href="/custom-orders"><Icon name="scissors" /> Book a custom fitting</Link>
            </div>
          </div>
          <div className="hero-media">
            <span className="hero-arc" aria-hidden="true" />
            {/* eslint-disable-next-line @next/next/no-img-element */}
            <img src={imgUrl(s(st, "hero_image"))} alt="Kaftans from Bio Fashion Hub on display" fetchPriority="high" />
          </div>
        </div>
      </section>

      <section className="section">
        <div className="wrap">
          <div className="section-head center"><p className="eyebrow">What we offer</p><h2>Everything for a complete look</h2></div>
          <div className="offer-grid">
            {categories.map((c) => (
              <Link key={c.id} className="offer" href={`/shop?category=${encodeURIComponent(c.slug)}`}>
                <span className="offer-icon"><Icon name={c.icon} /></span>
                <span><span className="offer-title">{c.name}</span><span className="offer-text">{c.tagline}</span></span>
              </Link>
            ))}
            <Link className="offer" href="/custom-orders">
              <span className="offer-icon"><Icon name="scissors" /></span>
              <span><span className="offer-title">Custom Outfits</span><span className="offer-text">Tailored for you, your groom’s party or your occasion.</span></span>
            </Link>
          </div>
        </div>
      </section>

      {products.length > 0 && (
        <section className="section section-tint">
          <div className="wrap">
            <div className="section-head split">
              <div><p className="eyebrow">Featured pieces</p><h2>Fresh from the hub</h2></div>
              <Link className="link-arrow" href="/shop">View all products <Icon name="arrow" /></Link>
            </div>
            <div className="product-grid">{products.map((p) => <ProductCard key={p.id} p={p} settings={st} />)}</div>
          </div>
        </section>
      )}

      <section className="section">
        <div className="wrap">
          <div className="section-head center"><p className="eyebrow">Easy ordering</p><h2>Order in three simple steps</h2></div>
          <ol className="steps">
            <li><span className="step-icon"><Icon name="bag" /></span><h3>Choose</h3><p>Browse the shop and pick what you love — or tell us what you have in mind.</p></li>
            <li><span className="step-icon"><Icon name="whatsapp" /></span><h3>Chat</h3><p>Tap “Order on WhatsApp”. Your item is already in the message — just hit send.</p></li>
            <li><span className="step-icon"><Icon name="check" /></span><h3>Collect</h3><p>We confirm size, price and payment, then you pick up in {city} or we arrange delivery.</p></li>
          </ol>
        </div>
      </section>

      <section className="band">
        <div className="wrap band-grid">
          <div>
            <p className="eyebrow eyebrow-gold">Custom orders</p>
            <h2>{s(st, "custom_title")}</h2>
            <p>{s(st, "custom_text")}</p>
            <ul className="ticks">
              <li><Icon name="check" /> Grooms &amp; wedding parties</li>
              <li><Icon name="check" /> Matching family &amp; group outfits</li>
              <li><Icon name="check" /> Your fabric or ours</li>
            </ul>
            <Link className="btn btn-gold" href="/custom-orders"><Icon name="ruler" /> Request a fitting</Link>
          </div>
          <div className="band-media">
            {/* eslint-disable-next-line @next/next/no-img-element */}
            <img src={imgUrl(s(st, "custom_image"))} alt="A custom-made outfit" loading="lazy" />
          </div>
        </div>
      </section>

      {photos.length > 0 && (
        <section className="section">
          <div className="wrap">
            <div className="section-head split">
              <div><p className="eyebrow">Lookbook</p><h2>Styled by Bio Fashion Hub</h2></div>
              <Link className="link-arrow" href="/lookbook">See the lookbook <Icon name="arrow" /></Link>
            </div>
            <div className="mosaic">
              {/* eslint-disable-next-line @next/next/no-img-element */}
              {photos.map((g) => <figure key={g.id}><img src={imgUrl(g.image)} alt={g.caption || "Lookbook photo"} loading="lazy" /></figure>)}
            </div>
          </div>
        </section>
      )}

      {quotes.length > 0 && (
        <section className="section section-tint">
          <div className="wrap">
            <div className="section-head center"><p className="eyebrow">Kind words</p><h2>What our customers say</h2></div>
            <div className="quote-grid">{quotes.map((t) => <Quote key={t.id} t={t} />)}</div>
            <p className="center"><Link className="link-arrow" href="/testimonials">Read more stories <Icon name="arrow" /></Link></p>
          </div>
        </section>
      )}

      {tiktok && (
        <section className="section">
          <div className="wrap">
            <div className="section-head center"><p className="eyebrow">On TikTok</p><h2>See the latest styles</h2></div>
            <div className="social-embed">
              <blockquote className="tiktok-embed" cite={`https://www.tiktok.com/@${tiktok}`} data-unique-id={tiktok} data-embed-type="creator" style={{ maxWidth: 780, minWidth: 288 }}>
                <section><a target="_blank" rel="noopener" href={`https://www.tiktok.com/@${tiktok}`}>@{tiktok}</a></section>
              </blockquote>
              <script async src="https://www.tiktok.com/embed.js" />
            </div>
          </div>
        </section>
      )}

      {s(st, "facebook_feed") === "1" && s(st, "facebook_url") && (
        <section className="section">
          <div className="wrap social-embed">
            <iframe title="Facebook updates" width={500} height={600} loading="lazy" style={{ border: "none", overflow: "hidden", maxWidth: "100%" }}
              src={`https://www.facebook.com/plugins/page.php?href=${encodeURIComponent(s(st, "facebook_url"))}&tabs=timeline&width=500&height=600&small_header=true`} />
          </div>
        </section>
      )}

      {articles.length > 0 && (
        <section className="section section-tint">
          <div className="wrap">
            <div className="section-head split">
              <div><p className="eyebrow">Style journal</p><h2>Tips &amp; news</h2></div>
              <Link className="link-arrow" href="/blog">All articles <Icon name="arrow" /></Link>
            </div>
            <div className="post-grid">{articles.map((p) => <PostCard key={p.id} post={p} />)}</div>
          </div>
        </section>
      )}

      <section className="contact-strip">
        <div className="wrap contact-strip-inner">
          <div className="cs-item"><Icon name="pin" /><div><strong>{city.toUpperCase()}</strong><span>{s(st, "address")}</span></div></div>
          <div className="cs-item"><Icon name="phone" /><div><strong><a href={telHref(s(st, "phone_1"))}>{s(st, "phone_1")}</a></strong><span>{s(st, "phone_2")}</span></div></div>
          <div className="cs-item"><Icon name="mail" /><div><strong>Email us</strong><span><a href={`mailto:${s(st, "email")}`}>{s(st, "email")}</a></span></div></div>
          <Link className="btn btn-navy" href="/contact">Find the shop <Icon name="arrow" /></Link>
        </div>
      </section>
    </>
  );
}
