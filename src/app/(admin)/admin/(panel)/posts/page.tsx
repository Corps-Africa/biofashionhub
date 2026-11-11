import Link from "next/link";
import { ConfirmButton } from "@/components/admin/ActionForm";
import { requireAdmin } from "@/lib/admin";
import type { Post } from "@/lib/types";
import { formatDate, imgUrl } from "@/lib/util";
import { deletePost } from "../../actions";

export const metadata = { title: "Journal / Blog" };

export default async function PostsAdmin() {
  const { sb } = await requireAdmin();
  const posts = ((await sb.from("posts").select("*").order("published_at", { ascending: false })).data ?? []) as Post[];
  return (
    <>
      <h1>Journal / Blog</h1>
      <div className="a-toolbar"><Link className="a-btn" href="/admin/posts/new">+ Write article</Link></div>
      {!posts.length ? <div className="a-card"><p>No articles yet.</p></div> : (
        <ul className="a-items">
          {posts.map((p) => (
            <li key={p.id} className={`a-item${p.published ? "" : " is-hidden"}`}>
              {/* eslint-disable-next-line @next/next/no-img-element */}
              <img src={imgUrl(p.image)} alt="" />
              <div className="a-item-main">
                <Link className="a-item-title" href={`/admin/posts/${p.id}`}>{p.title}</Link>
                <span className="a-sub">{formatDate(p.published_at)}</span>
                <span className="a-badges">{p.published ? <span className="a-tag">Published</span> : <span className="a-tag a-tag-muted">Draft</span>}</span>
              </div>
              <div className="a-item-actions">
                <Link className="a-btn a-btn-sm" href={`/admin/posts/${p.id}`}>Edit</Link>
                <form action={deletePost}><input type="hidden" name="id" value={p.id} /><ConfirmButton message={`Delete “${p.title}”?`}>Delete</ConfirmButton></form>
              </div>
            </li>
          ))}
        </ul>
      )}
    </>
  );
}
