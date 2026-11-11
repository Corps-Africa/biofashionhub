import type { Metadata } from "next";
import EnquiryForm from "@/components/EnquiryForm";
import Icon from "@/components/Icon";
import PageHero from "@/components/PageHero";
import { getSettings } from "@/lib/data";
import { s, telHref, waLink } from "@/lib/util";

export const revalidate = 300;
export const metadata: Metadata = { title: "Contact", description: "Visit Bio Fashion Hub in Tamale, call us or chat with us on WhatsApp." };

export default async function ContactPage() {
  const st = await getSettings();
  const map = encodeURIComponent(s(st, "map_query"));
  return (
    <>
      <PageHero eyebrow="Contact" title="We’d love to dress you" text="Visit the shop, call, or send us a message — the fastest way to reach us is WhatsApp." />
      <section className="section">
        <div className="wrap two-col">
          <div>
            <ul className="contact-list">
              <li><span className="ci"><Icon name="whatsapp" /></span><div><strong>WhatsApp</strong><a href={waLink(st, "Hello Bio Fashion Hub!")} target="_blank" rel="noopener">{s(st, "whatsapp_number")}</a></div></li>
              <li><span className="ci"><Icon name="phone" /></span><div><strong>Call</strong>
                <a href={telHref(s(st, "phone_1"))}>{s(st, "phone_1")}</a>
                {s(st, "phone_2") && <a href={telHref(s(st, "phone_2"))}>{s(st, "phone_2")}</a>}
              </div></li>
              <li><span className="ci"><Icon name="mail" /></span><div><strong>Email</strong><a href={`mailto:${s(st, "email")}`}>{s(st, "email")}</a></div></li>
              <li><span className="ci"><Icon name="pin" /></span><div><strong>Visit</strong><span>{s(st, "address")}</span></div></li>
              <li><span className="ci"><Icon name="clock" /></span><div><strong>Opening hours</strong><span style={{ whiteSpace: "pre-line" }}>{s(st, "opening_hours")}</span></div></li>
            </ul>
            <div className="map">
              <iframe title={`Map showing ${s(st, "business_name")}`} src={`https://maps.google.com/maps?q=${map}&z=15&output=embed`} loading="lazy" referrerPolicy="no-referrer-when-downgrade" />
            </div>
            <a className="link-arrow" href={`https://www.google.com/maps/search/?api=1&query=${map}`} target="_blank" rel="noopener">Get directions <Icon name="arrow" /></a>
          </div>
          <div className="form-card">
            <EnquiryForm type="contact" waHref={waLink(st, "Hello Bio Fashion Hub!")} />
          </div>
        </div>
      </section>
    </>
  );
}
