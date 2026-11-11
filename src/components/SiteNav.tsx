"use client";
import Link from "next/link";
import { usePathname } from "next/navigation";
import { useEffect, useState } from "react";
import Icon from "./Icon";

const NAV: [string, string][] = [
  ["Home", "/"], ["Shop", "/shop"], ["Custom Orders", "/custom-orders"], ["Lookbook", "/lookbook"],
  ["About", "/about"], ["Journal", "/blog"], ["Contact", "/contact"],
];

export default function SiteNav({ waHref }: { waHref: string }) {
  const [open, setOpen] = useState(false);
  const path = usePathname();
  useEffect(() => setOpen(false), [path]);
  useEffect(() => {
    document.body.classList.toggle("nav-open", open);
    const onKey = (e: KeyboardEvent) => e.key === "Escape" && setOpen(false);
    document.addEventListener("keydown", onKey);
    return () => document.removeEventListener("keydown", onKey);
  }, [open]);
  const active = (href: string) => (href === "/" ? path === "/" : path === href || path.startsWith(href + "/") || (href === "/shop" && path.startsWith("/product")));

  return (
    <>
      <nav className="main-nav" id="main-nav" aria-label="Main">
        <ul>
          {NAV.map(([label, href]) => (
            <li key={href}><Link href={href} aria-current={active(href) ? "page" : undefined}>{label}</Link></li>
          ))}
        </ul>
        <a className="btn btn-gold nav-cta" href={waHref} target="_blank" rel="noopener"><Icon name="whatsapp" /> Chat to order</a>
      </nav>
      <button className="nav-toggle" type="button" aria-controls="main-nav" aria-expanded={open} aria-label={open ? "Close menu" : "Open menu"} onClick={() => setOpen(!open)}>
        <Icon name="menu" className="icon i-open" /><Icon name="close" className="icon i-close" />
      </button>
    </>
  );
}
