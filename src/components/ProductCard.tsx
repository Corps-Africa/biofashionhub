import Link from "next/link";
import Icon from "./Icon";
import { imgUrl, money, productOrderMessage, waLink } from "@/lib/util";
import type { Product, Settings } from "@/lib/types";

export default function ProductCard({ p, settings }: { p: Product; settings: Settings }) {
  const href = `/product/${p.slug}`;
  return (
    <article className="card product-card">
      <Link className="card-media" href={href}>
        {/* eslint-disable-next-line @next/next/no-img-element */}
        <img src={imgUrl(p.image)} alt={p.name} loading="lazy" />
        {!p.in_stock && <span className="badge">Made to order</span>}
      </Link>
      <div className="card-body">
        {p.categories?.name && <p className="eyebrow">{p.categories.name}</p>}
        <h3 className="card-title"><Link href={href}>{p.name}</Link></h3>
        <p className="price">{money(p.price)}</p>
        <a className="btn btn-wa btn-block" href={waLink(settings, productOrderMessage(p))} target="_blank" rel="noopener">
          <Icon name="whatsapp" /> Order on WhatsApp
        </a>
      </div>
    </article>
  );
}
