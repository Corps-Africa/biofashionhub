"use client";
import { useState } from "react";
import { imgUrl } from "@/lib/util";
import { uploadImage } from "./upload";

/** Photo picker: uploads straight to storage and keeps the resulting path in a hidden input. */
export default function ImageField({ name, label, value = "", folder, help }: { name: string; label: string; value?: string; folder: string; help?: string }) {
  const [path, setPath] = useState(value);
  const [status, setStatus] = useState<"" | "uploading" | "error">("");
  const [message, setMessage] = useState("");

  async function onChange(e: React.ChangeEvent<HTMLInputElement>) {
    const file = e.target.files?.[0];
    if (!file) return;
    setStatus("uploading");
    setMessage("Uploading…");
    try {
      setPath(await uploadImage(file, folder));
      setStatus("");
      setMessage("Photo ready — remember to save.");
    } catch (err) {
      setStatus("error");
      setMessage((err as Error).message);
    }
    e.target.value = "";
  }

  return (
    <div className="a-field">
      <label htmlFor={`file-${name}`}>{label}</label>
      <input type="hidden" name={name} value={path} />
      {path && (
        <div className="a-img-current">
          {/* eslint-disable-next-line @next/next/no-img-element */}
          <img src={imgUrl(path)} alt="Current photo" />
          <button type="button" className="a-btn a-btn-ghost a-btn-sm" onClick={() => { setPath(""); setMessage("Photo removed — remember to save."); }}>Remove photo</button>
        </div>
      )}
      <input id={`file-${name}`} type="file" accept="image/jpeg,image/png,image/webp" onChange={onChange} disabled={status === "uploading"} data-uploading={status === "uploading" || undefined} />
      {message && <p className={status === "error" ? "a-help a-help-error" : "a-help"} role="status">{message}</p>}
      <p className="a-help">{help ?? "JPG, PNG or WebP. Photos from your phone are fine — they’re resized automatically."}</p>
    </div>
  );
}
