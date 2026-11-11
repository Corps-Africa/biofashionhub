"use client";
import Link from "next/link";
import { usePathname } from "next/navigation";
import Icon from "../Icon";

const NAV: [string, string, string][] = [
  ["Dashboard", "/admin", "grid"], ["Products", "/admin/products", "bag"], ["Categories", "/admin/categories", "tag"],
  ["Enquiries", "/admin/enquiries", "chat"], ["Lookbook", "/admin/gallery", "image"], ["Testimonials", "/admin/testimonials", "star"],
  ["Journal / Blog", "/admin/posts", "pen"], ["Site settings", "/admin/settings", "gear"], ["My account", "/admin/account", "user"],
];

export default function SideNav({ unread }: { unread: number }) {
  const path = usePathname();
  return (
    <ul>
      {NAV.map(([label, href, icon]) => {
        const active = href === "/admin" ? path === "/admin" : path.startsWith(href);
        return (
          <li key={href}>
            <Link href={href} aria-current={active ? "page" : undefined}>
              <Icon name={icon} /> {label}
              {href === "/admin/enquiries" && unread > 0 && <span className="pill">{unread}</span>}
            </Link>
          </li>
        );
      })}
    </ul>
  );
}
