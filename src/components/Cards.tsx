import Link from "next/link";
import Icon from "./Icon";
import type { Post, Testimonial } from "@/lib/types";
import { formatDate, imgUrl } from "@/lib/util";

export function Quote({ t }: { t: Testimonial }) {
  const stars = Math.max(0, Math.min(5, t.rating));
  return (
    <figure className="quote">
      {/* eslint-disable-next-line @next/next/no-img-element */}
      {t.image && <img className="quote-photo" src={imgUrl(t.image)} alt="" loading="lazy" />}
      <div className="stars" aria-label={`${stars} out of 5 stars`}>{Array.from({ length: stars }, (_, i) => <Icon key={i} name="star" />)}</div>
      <blockquote>“{t.quote}”</blockquote>
      <figcaption><strong>{t.name}</strong>{t.role && ` · ${t.role}`}</figcaption>
    </figure>
  );
}

export function PostCard({ post, headingLevel = 3 }: { post: Post; headingLevel?: 2 | 3 }) {
  const href = `/blog/${post.slug}`;
  const H = headingLevel === 2 ? "h2" : "h3";
  return (
    <article className="card post-card">
      {/* eslint-disable-next-line @next/next/no-img-element */}
      <Link className="card-media" href={href}><img src={imgUrl(post.image)} alt="" loading="lazy" /></Link>
      <div className="card-body">
        <p className="eyebrow">{formatDate(post.published_at)}</p>
        <H className="card-title"><Link href={href}>{post.title}</Link></H>
        <p>{post.excerpt}</p>
        {headingLevel === 2 && <Link className="link-arrow" href={href}>Read more <Icon name="arrow" /></Link>}
      </div>
    </article>
  );
}
