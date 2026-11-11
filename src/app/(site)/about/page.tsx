import type { Metadata } from "next";
import Link from "next/link";
import Icon from "@/components/Icon";
import PageHero from "@/components/PageHero";
import Paras from "@/components/Paras";
import { getSettings } from "@/lib/data";
import { imgUrl, initials, s, waLink } from "@/lib/util";

export const revalidate = 300;
export const metadata: Metadata = { title: "About Us", description: "Bio Fashion Hub is a fashion and lifestyle brand in Tamale providing quality, stylish and affordable clothing and accessories." };

export default async function AboutPage() {
  const st = await getSettings();
  return (
    <>
      <PageHero eyebrow="About us" title="Look good. Feel confident." text={s(st, "about_intro")} />
      <section className="section">
        <div className="wrap about-grid">
          {/* eslint-disable-next-line @next/next/no-img-element */}
          <div className="about-media"><img src={imgUrl(s(st, "about_image"))} alt="Bio Fashion Hub kaftan" loading="lazy" /></div>
          <div>
            <p className="eyebrow">Our story</p>
            <h2>Fashion that builds confidence</h2>
            <Paras className="prose" text={s(st, "about_story")} />
            <div className="mission"><p className="eyebrow eyebrow-gold">Our mission</p><p>{s(st, "about_mission")}</p></div>
          </div>
        </div>
      </section>
      <section className="section section-tint">
        <div className="wrap">
          <div className="section-head center"><p className="eyebrow">What we stand for</p><h2>Our promise to you</h2></div>
          <div className="values">
            <div><span className="offer-icon"><Icon name="fabric" /></span><h3>Quality</h3><p>Carefully selected fabrics and finishing that last.</p></div>
            <div><span className="offer-icon"><Icon name="kaftan" /></span><h3>Style</h3><p>Classic and modern looks for every occasion.</p></div>
            <div><span className="offer-icon"><Icon name="check" /></span><h3>Affordability</h3><p>Elegant fashion at prices that make sense.</p></div>
            <div><span className="offer-icon"><Icon name="scissors" /></span><h3>Personal</h3><p>Custom outfits made to fit you and your moment.</p></div>
          </div>
        </div>
      </section>
      <section className="section">
        <div className="wrap founder">
          {s(st, "ceo_photo")
            // eslint-disable-next-line @next/next/no-img-element
            ? <img className="founder-photo" src={imgUrl(s(st, "ceo_photo"))} alt={s(st, "ceo_name")} loading="lazy" />
            : <span className="founder-photo founder-initials" aria-hidden="true">{initials(s(st, "ceo_name"))}</span>}
          <div>
            <p className="eyebrow">Meet the founder</p>
            <blockquote className="founder-quote">“{s(st, "ceo_bio")}”</blockquote>
            <p><strong>{s(st, "ceo_name")}</strong> · {s(st, "ceo_role")}</p>
          </div>
        </div>
      </section>
      <section className="band band-slim">
        <div className="wrap band-cta">
          <h2>Ready to find your look?</h2>
          <div className="btn-row">
            <Link className="btn btn-gold" href="/shop">Shop now</Link>
            <a className="btn btn-outline-light" href={waLink(st, "Hello Bio Fashion Hub!")} target="_blank" rel="noopener"><Icon name="whatsapp" /> Chat with us</a>
          </div>
        </div>
      </section>
    </>
  );
}
