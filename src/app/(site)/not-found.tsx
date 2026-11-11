import Link from "next/link";
import Icon from "@/components/Icon";
import { getSettings } from "@/lib/data";
import { waLink } from "@/lib/util";

export default async function NotFound() {
  const st = await getSettings();
  return (
    <section className="section">
      <div className="wrap narrow center">
        <p className="eyebrow">404</p>
        <h1>We couldn’t find that page</h1>
        <p className="lead" style={{ margin: "0 auto" }}>It may have moved or sold out. Try the shop, or ask us directly.</p>
        <div className="btn-row center-row">
          <Link className="btn btn-navy" href="/shop">Go to shop</Link>
          <a className="btn btn-wa" href={waLink(st, "Hello Bio Fashion Hub!")} target="_blank" rel="noopener"><Icon name="whatsapp" /> Ask on WhatsApp</a>
        </div>
      </div>
    </section>
  );
}
