import type { Metadata } from "next";
import EnquiryForm from "@/components/EnquiryForm";
import Icon from "@/components/Icon";
import PageHero from "@/components/PageHero";
import { getSettings } from "@/lib/data";
import { s, waLink } from "@/lib/util";

export const metadata: Metadata = {
  title: "Custom Orders & Fittings",
  description: "Custom kaftans and outfits for weddings, grooms, Eid and special occasions in Tamale. Book a fitting.",
};

export default async function CustomOrdersPage({ searchParams }: { searchParams: Promise<{ item?: string }> }) {
  const st = await getSettings();
  const item = ((await searchParams).item ?? "").slice(0, 120);
  return (
    <>
      <PageHero eyebrow="Custom orders" title={s(st, "custom_title")} text={s(st, "custom_text")} />
      <section className="section">
        <div className="wrap two-col">
          <div>
            <h2 className="h3">How it works</h2>
            <ol className="numbered">
              <li><strong>Tell us about your occasion.</strong> Fill in the form or message us on WhatsApp.</li>
              <li><strong>Choose your style and fabric.</strong> Bring your own fabric or choose from our selection.</li>
              <li><strong>Get measured.</strong> Visit us in {s(st, "city")} at a time that suits you, or send measurements.</li>
              <li><strong>Fitting &amp; collection.</strong> We fine-tune the fit so you look your best on the day.</li>
            </ol>
            <div className="note-card">
              <h3 className="h4"><Icon name="clock" /> Plan ahead</h3>
              <p>For weddings and group orders, contact us as early as you can so there’s plenty of time for fittings.</p>
            </div>
            <div className="ideal-for">
              <p className="eyebrow">Perfect for</p>
              <ul className="tags"><li>Weddings</li><li>Grooms &amp; groomsmen</li><li>Naming ceremonies</li><li>Eid</li><li>Graduations</li><li>Office &amp; events</li></ul>
            </div>
          </div>
          <div className="form-card">
            <EnquiryForm type="custom" waHref={waLink(st, "Hello Bio Fashion Hub, I would like a custom outfit.")} initialMessage={item ? `I'm interested in: ${item}\n` : ""} />
          </div>
        </div>
      </section>
    </>
  );
}
