import type { Metadata } from "next";
import Lightbox from "@/components/Lightbox";
import PageHero from "@/components/PageHero";
import { getSettings } from "@/lib/data";
import { publicClient } from "@/lib/supabase/public";
import type { GalleryItem } from "@/lib/types";
import { imgUrl, waLink } from "@/lib/util";

export const revalidate = 300;
export const metadata: Metadata = { title: "Lookbook", description: "A look at kaftans, custom outfits and accessories styled by Bio Fashion Hub." };

export default async function LookbookPage() {
  const [st, res] = await Promise.all([getSettings(), publicClient().from("gallery").select("*").order("sort").order("id", { ascending: false })]);
  const items = ((res.data ?? []) as GalleryItem[]).map((g) => ({ src: imgUrl(g.image), caption: g.caption }));
  return (
    <>
      <PageHero eyebrow="Lookbook" title="Our work" text="Outfits we’ve made and styled. See something you like? Tap a photo and ask us about it." />
      <section className="section section-tight">
        <div className="wrap">
          {items.length ? <Lightbox items={items} waBase={waLink(st)} /> : <p className="empty">New photos coming soon.</p>}
        </div>
      </section>
    </>
  );
}
