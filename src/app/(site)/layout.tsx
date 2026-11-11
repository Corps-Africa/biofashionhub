import Link from "next/link";
import "@/styles/site.css";
import Document from "@/components/Document";
import Icon from "@/components/Icon";
import { baseMetadata, baseViewport } from "@/lib/meta";
import SiteNav from "@/components/SiteNav";
import { getSettings } from "@/lib/data";
import { SITE_URL } from "@/lib/supabase/env";
import { imgUrl, s, telHref, waLink } from "@/lib/util";

export const metadata = baseMetadata;
export const viewport = baseViewport;

export default async function SiteLayout({ children }: { children: React.ReactNode }) {
  const st = await getSettings();
  const biz = s(st, "business_name");
  const tiktok = s(st, "tiktok_username").replace(/^@/, "");
  const socials = ([
    ["facebook", s(st, "facebook_url")],
    ["tiktok", tiktok ? `https://www.tiktok.com/@${tiktok}` : ""],
    ["instagram", s(st, "instagram_url")],
  ] as const).filter(([, url]) => url);
  const enquire = waLink(st, `Hello ${biz}, I would like to make an enquiry.`);
  const jsonLd = {
    "@context": "https://schema.org", "@type": "ClothingStore", name: biz, slogan: s(st, "tagline"),
    telephone: s(st, "phone_1"), email: s(st, "email"), url: SITE_URL, image: SITE_URL + imgUrl(s(st, "hero_image")).replace(SITE_URL, ""),
    address: { "@type": "PostalAddress", streetAddress: s(st, "address"), addressLocality: s(st, "city"), addressCountry: "GH" },
  };

  return (
    <Document>
      <a className="skip" href="#main">Skip to content</a>
      <script type="application/ld+json" dangerouslySetInnerHTML={{ __html: JSON.stringify(jsonLd).replace(/</g, "\\u003c") }} />
      <header className="site-header">
        <div className="wrap header-inner">
          <Link className="brand" href="/" aria-label={`${biz} home`}>
            {/* eslint-disable-next-line @next/next/no-img-element */}
            <img src="/logo-mark.png" alt="" width={54} height={57} />
            <span className="brand-word"><span>Bio Fashion</span><em>Hub</em></span>
          </Link>
          <SiteNav waHref={enquire} />
        </div>
      </header>
      <main id="main">{children}</main>
      <footer className="site-footer">
        <div className="wrap footer-grid">
          <div className="footer-brand">
            {/* eslint-disable-next-line @next/next/no-img-element */}
            <img src="/logo-mark.png" alt="" width={64} height={68} />
            <p className="footer-name">Bio Fashion <em>Hub</em></p>
            <p className="tagline-caps">{s(st, "tagline")}</p>
          </div>
          <div>
            <h2 className="footer-h">Visit &amp; call</h2>
            <ul className="footer-list">
              <li><Icon name="pin" /> {s(st, "address")}</li>
              <li><Icon name="phone" /> <a href={telHref(s(st, "phone_1"))}>{s(st, "phone_1")}</a></li>
              {s(st, "phone_2") && <li><Icon name="phone" /> <a href={telHref(s(st, "phone_2"))}>{s(st, "phone_2")}</a></li>}
              <li><Icon name="mail" /> <a href={`mailto:${s(st, "email")}`}>{s(st, "email")}</a></li>
            </ul>
          </div>
          <div>
            <h2 className="footer-h">Explore</h2>
            <ul className="footer-list plain">
              <li><Link href="/shop">Shop all</Link></li>
              <li><Link href="/custom-orders">Custom orders &amp; fittings</Link></li>
              <li><Link href="/lookbook">Lookbook</Link></li>
              <li><Link href="/testimonials">Testimonials</Link></li>
              <li><Link href="/blog">Style journal</Link></li>
            </ul>
          </div>
          <div>
            <h2 className="footer-h">Follow us</h2>
            {socials.length ? (
              <div className="social-row">
                {socials.map(([net, url]) => (
                  <a key={net} href={url} target="_blank" rel="noopener" aria-label={net[0].toUpperCase() + net.slice(1)}><Icon name={net} /></a>
                ))}
              </div>
            ) : <p className="muted-light">Social links coming soon.</p>}
            <a className="btn btn-gold btn-sm" href={waLink(st, `Hello ${biz}!`)} target="_blank" rel="noopener"><Icon name="whatsapp" /> WhatsApp us</a>
          </div>
        </div>
        <div className="wrap footer-base">
          <p>&copy; {new Date().getFullYear()} {biz}. All rights reserved.</p>
          <p><Link href="/admin" prefetch={false}>Staff login</Link></p>
        </div>
      </footer>
      <a className="wa-float" href={enquire} target="_blank" rel="noopener" aria-label="Chat with us on WhatsApp"><Icon name="whatsapp" /><span>Chat with us</span></a>
    </Document>
  );
}
