"use client";
import { useRef, useState } from "react";
import Icon from "./Icon";

type Item = { src: string; caption: string };

export default function Lightbox({ items, waBase }: { items: Item[]; waBase: string }) {
  const dialog = useRef<HTMLDialogElement>(null);
  const [current, setCurrent] = useState<Item | null>(null);
  const open = (item: Item) => { setCurrent(item); dialog.current?.showModal(); };
  const msg = current ? `Hello Bio Fashion Hub, I saw this in your lookbook${current.caption ? `: ${current.caption}` : ""}. Can you tell me more?` : "";
  return (
    <>
      <div className="gallery">
        {items.map((g, i) => (
          <figure key={i}>
            <button type="button" className="lightbox-open" onClick={() => open(g)}>
              {/* eslint-disable-next-line @next/next/no-img-element */}
              <img src={g.src} alt={g.caption || "Lookbook photo"} loading="lazy" />
            </button>
            {g.caption && <figcaption>{g.caption}</figcaption>}
          </figure>
        ))}
      </div>
      <dialog className="lightbox" ref={dialog} onClick={(e) => e.target === dialog.current && dialog.current?.close()}>
        <button type="button" className="lightbox-close" aria-label="Close" onClick={() => dialog.current?.close()}><Icon name="close" /></button>
        {/* eslint-disable-next-line @next/next/no-img-element */}
        {current && <img src={current.src} alt={current.caption} />}
        <div className="lightbox-bar">
          <p className="lightbox-caption">{current?.caption}</p>
          <a className="btn btn-wa btn-sm" href={`${waBase}?text=${encodeURIComponent(msg)}`} target="_blank" rel="noopener"><Icon name="whatsapp" /> Ask about this</a>
        </div>
      </dialog>
    </>
  );
}
