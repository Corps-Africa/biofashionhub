import type { Metadata } from "next";
import Link from "next/link";
import { notFound } from "next/navigation";
import { cache } from "react";
import Icon from "@/components/Icon";
import Paras from "@/components/Paras";
import { getSettings } from "@/lib/data";
import { publicClient } from "@/lib/supabase/public";
import type { Post } from "@/lib/types";
import { formatDate, imgUrl, waLink } from "@/lib/util";

export const revalidate = 300;
type Props = { params: Promise<{ slug: string }> };

const getPost = cache(async (slug: string) => {
  const { data } = await publicClient().from("posts").select("*").eq("slug", slug).eq("published", true).lte("published_at", new Date().toISOString()).maybeSingle();
  return data as Post | null;
});

export async function generateMetadata({ params }: Props): Promise<Metadata> {
  const p = await getPost((await params).slug);
  if (!p) return { title: "Not found" };
  return { title: p.title, description: p.excerpt, openGraph: { type: "article", images: p.image ? [imgUrl(p.image)] : undefined } };
}

export default async function PostPage({ params }: Props) {
  const post = await getPost((await params).slug);
  if (!post) notFound();
  const st = await getSettings();
  return (
    <article className="section">
      <div className="wrap narrow">
        <nav className="crumbs" aria-label="Breadcrumb"><Link href="/blog">Journal</Link></nav>
        <p className="eyebrow">{formatDate(post.published_at, { day: "numeric", month: "long", year: "numeric" })}</p>
        <h1 className="post-title">{post.title}</h1>
        {post.excerpt && <p className="lead">{post.excerpt}</p>}
        {/* eslint-disable-next-line @next/next/no-img-element */}
        {post.image && <img className="post-cover" src={imgUrl(post.image)} alt="" />}
        <Paras className="prose prose-lg" text={post.body} />
        <div className="post-cta">
          <p><strong>Like this look?</strong> We can help you put it together.</p>
          <a className="btn btn-wa" href={waLink(st, `Hello Bio Fashion Hub, I just read "${post.title}" and would like some help.`)} target="_blank" rel="noopener"><Icon name="whatsapp" /> Chat with us</a>
        </div>
      </div>
    </article>
  );
}
