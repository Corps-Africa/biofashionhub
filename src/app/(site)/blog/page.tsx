import type { Metadata } from "next";
import { PostCard } from "@/components/Cards";
import PageHero from "@/components/PageHero";
import { publicClient } from "@/lib/supabase/public";
import type { Post } from "@/lib/types";

export const revalidate = 300;
export const metadata: Metadata = { title: "Style Journal", description: "Style tips, new arrivals and news from Bio Fashion Hub." };

export default async function BlogPage() {
  const { data } = await publicClient().from("posts").select("*").eq("published", true).lte("published_at", new Date().toISOString()).order("published_at", { ascending: false });
  const posts = (data ?? []) as Post[];
  return (
    <>
      <PageHero eyebrow="Journal" title="Style tips & news" text="Guides, new arrivals and updates from the hub." />
      <section className="section section-tight">
        <div className="wrap">
          {posts.length ? <div className="post-grid">{posts.map((p) => <PostCard key={p.id} post={p} headingLevel={2} />)}</div> : <p className="empty">Articles coming soon.</p>}
        </div>
      </section>
    </>
  );
}
