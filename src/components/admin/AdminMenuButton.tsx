"use client";
import { usePathname } from "next/navigation";
import { useEffect, useState } from "react";
import Icon from "../Icon";

export default function AdminMenuButton() {
  const [open, setOpen] = useState(false);
  const path = usePathname();
  useEffect(() => setOpen(false), [path]);
  useEffect(() => { document.body.classList.toggle("side-open", open); }, [open]);
  return (
    <button className="a-menu" type="button" aria-controls="a-side" aria-expanded={open} aria-label="Menu" onClick={() => setOpen(!open)}>
      <Icon name={open ? "close" : "menu"} />
    </button>
  );
}
