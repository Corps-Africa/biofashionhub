"use client";
import { browserClient } from "@/lib/supabase/browser";

const MAX_DIM = 1600;

/**
 * Resize a photo in the browser (phone photos are often 4–12 MB) and upload it to Supabase Storage.
 * Re-encoding as JPEG also strips location data and anything hidden in the file.
 * Returns the storage path to save in the database.
 */
export async function uploadImage(file: File, folder: string): Promise<string> {
  if (!/^image\/(jpeg|png|webp)$/.test(file.type)) throw new Error("Please choose a JPG, PNG or WebP photo.");
  if (file.size > 25 * 1024 * 1024) throw new Error("That photo is too large (max 25 MB before resizing).");

  const bitmap = await createImageBitmap(file, { imageOrientation: "from-image" } as ImageBitmapOptions).catch(() => null);
  if (!bitmap) throw new Error("That image could not be read.");
  const scale = Math.min(1, MAX_DIM / Math.max(bitmap.width, bitmap.height));
  const canvas = document.createElement("canvas");
  canvas.width = Math.round(bitmap.width * scale);
  canvas.height = Math.round(bitmap.height * scale);
  const ctx = canvas.getContext("2d")!;
  ctx.fillStyle = "#faf8f3"; // flatten transparency onto off-white
  ctx.fillRect(0, 0, canvas.width, canvas.height);
  ctx.drawImage(bitmap, 0, 0, canvas.width, canvas.height);
  bitmap.close();
  const blob = await new Promise<Blob | null>((r) => canvas.toBlob(r, "image/jpeg", 0.84));
  if (!blob) throw new Error("Could not process the image.");

  const d = new Date();
  const rand = Array.from(crypto.getRandomValues(new Uint8Array(10)), (b) => b.toString(16).padStart(2, "0")).join("");
  const path = `${folder}/${d.getFullYear()}/${String(d.getMonth() + 1).padStart(2, "0")}/${rand}.jpg`;
  const { error } = await browserClient().storage.from("media").upload(path, blob, { contentType: "image/jpeg", cacheControl: "31536000" });
  if (error) throw new Error("Upload failed: " + error.message);
  return path;
}
