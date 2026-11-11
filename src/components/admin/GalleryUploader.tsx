"use client";
import { useState } from "react";
import { addGalleryPhotos } from "@/app/(admin)/admin/actions";
import { uploadImage } from "./upload";

export default function GalleryUploader() {
  const [files, setFiles] = useState<File[]>([]);
  const [caption, setCaption] = useState("");
  const [busy, setBusy] = useState(false);
  const [progress, setProgress] = useState("");
  const [errors, setErrors] = useState<string[]>([]);

  async function upload(e: React.FormEvent) {
    e.preventDefault();
    if (!files.length) return;
    setBusy(true);
    setErrors([]);
    const paths: string[] = [];
    const errs: string[] = [];
    for (const [i, f] of files.entries()) {
      setProgress(`Uploading ${i + 1} of ${files.length}…`);
      try { paths.push(await uploadImage(f, "gallery")); } catch (err) { errs.push(`${f.name}: ${(err as Error).message}`); }
    }
    setErrors(errs);
    if (paths.length) {
      setProgress("Saving…");
      const res = await addGalleryPhotos(paths, caption); // redirects on success
      if (res?.error) setErrors([...errs, res.error]);
    }
    setBusy(false);
    setProgress("");
  }

  return (
    <form className="a-card a-form" onSubmit={upload}>
      <h2>Add photos</h2>
      {errors.map((er) => <div key={er} className="flash flash-error">{er}</div>)}
      <div className="a-field">
        <label htmlFor="photos">Choose one or more photos</label>
        <input id="photos" type="file" accept="image/jpeg,image/png,image/webp" multiple onChange={(e) => setFiles(Array.from(e.target.files ?? []))} disabled={busy} />
        <p className="a-help">You can select several at once. New photos appear first.</p>
      </div>
      <div className="a-field">
        <label htmlFor="caption">Caption (optional, used for all photos in this upload)</label>
        <input id="caption" value={caption} onChange={(e) => setCaption(e.target.value)} placeholder="e.g. Groom’s party outfits, June wedding" disabled={busy} />
      </div>
      <button className="a-btn" type="submit" disabled={busy || !files.length}>{busy ? progress : `Upload ${files.length || ""} photo${files.length === 1 ? "" : "s"}`}</button>
    </form>
  );
}
