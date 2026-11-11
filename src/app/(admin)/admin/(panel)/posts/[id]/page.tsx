import Link from "next/link";
import { notFound } from "next/navigation";
import { ActionForm, SubmitButton } from "@/components/admin/ActionForm";
import ImageField from "@/components/admin/ImageField";
import { requireAdmin } from "@/lib/admin";
import type { Post } from "@/lib/types";
import { savePost } from "../../../actions";

export const metadata = { title: "Edit article" };

export default async function PostEdit({ params }: { params: Promise<{ id: string }> }) {
  const { id } = await params;
  const { sb } = await requireAdmin();
  const isNew = id === "new";
  const data = isNew ? null : (await sb.from("posts").select("*").eq("id", Number(id)).maybeSingle()).data;
  if (!isNew && !data) notFound();
  const p: Partial<Post> = data ?? { published: true, published_at: new Date().toISOString() };

  return (
    <>
      <h1>{isNew ? "Write article" : "Edit article"}</h1>
      <ActionForm action={savePost} className="a-form">
        <input type="hidden" name="id" value={p.id ?? 0} />
        <div className="a-card">
          <div className="a-field"><label htmlFor="title">Title *</label><input id="title" name="title" required defaultValue={p.title} /></div>
          <div className="a-field"><label htmlFor="excerpt">Short summary</label><textarea id="excerpt" name="excerpt" rows={2} maxLength={300} defaultValue={p.excerpt} /><p className="a-help">Shown on the Journal list and when the link is shared.</p></div>
          <div className="a-field"><label htmlFor="body">Article</label><textarea id="body" name="body" rows={14} defaultValue={p.body} />
            <p className="a-help">Leave an empty line between paragraphs. Wrap words in **double stars** to make them bold.</p></div>
          <ImageField name="image" label="Cover photo" value={p.image} folder="posts" />
        </div>
        <div className="a-card">
          <label className="a-check"><input type="checkbox" name="published" defaultChecked={p.published} /> Published (untick to save as a draft)</label>
          <div className="a-field a-narrow"><label htmlFor="published_date">Date</label><input id="published_date" name="published_date" type="date" defaultValue={p.published_at?.slice(0, 10)} /></div>
        </div>
        <div className="a-actions">
          <SubmitButton>Save article</SubmitButton>
          <Link className="a-btn a-btn-ghost" href="/admin/posts">Cancel</Link>
        </div>
      </ActionForm>
    </>
  );
}
