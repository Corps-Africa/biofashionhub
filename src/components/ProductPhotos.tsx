"use client";
import { useState } from "react";

export default function ProductPhotos({ images, alt }: { images: string[]; alt: string }) {
  const [current, setCurrent] = useState(0);
  const list = images.length ? images : ["/placeholder/hero.jpg"];
  return (
    <div className="pd-media">
      {/* eslint-disable-next-line @next/next/no-img-element */}
      <img id="pd-main" src={list[current]} alt={alt} />
      {list.length > 1 && (
        <div className="pd-thumbs">
          {list.map((src, i) => (
            <button key={src} type="button" onClick={() => setCurrent(i)} aria-label={`Show photo ${i + 1}`} aria-pressed={i === current}>
              {/* eslint-disable-next-line @next/next/no-img-element */}
              <img src={src} alt="" />
            </button>
          ))}
        </div>
      )}
    </div>
  );
}
