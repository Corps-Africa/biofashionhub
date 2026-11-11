import type { Metadata } from "next";
import { Quote } from "@/components/Cards";
import Icon from "@/components/Icon";
import PageHero from "@/components/PageHero";
import { getSettings } from "@/lib/data";
import { publicClient } from "@/lib/supabase/public";
import type { Testimonial } from "@/lib/types";
import { waLink } from "@/lib/util";

export const revalidate = 300;
export const metadata: Metadata = { title: "Testimonials", description: "What customers say about Bio Fashion Hub." };

export default async function TestimonialsPage() {
  const [st, res] = await Promise.all([getSettings(), publicClient().from("testimonials").select("*").eq("published", true).order("sort").order("id")]);
  const items = (res.data ?? []) as Testimonial[];
  return (
    <>
      <PageHero eyebrow="Testimonials" title="Customer stories" text="Real words from the people we’ve dressed." />
      <section className="section section-tight">
        <div className="wrap">
          {items.length
            ? <div className="quote-grid">{items.map((t) => <Quote key={t.id} t={t} />)}</div>
            : <div className="empty"><p>Customer stories are coming soon.</p><p>Bought from us? We’d love to hear how it went.</p></div>}
          <div className="center" style={{ marginTop: "2.5rem" }}>
            <a className="btn btn-wa" href={waLink(st, "Hello Bio Fashion Hub, I would like to share my experience:")} target="_blank" rel="noopener"><Icon name="whatsapp" /> Share your experience</a>
          </div>
        </div>
      </section>
    </>
  );
}
