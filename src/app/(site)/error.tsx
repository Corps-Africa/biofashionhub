"use client";
import { useEffect } from "react";
import Icon from "@/components/Icon";

/** Shown if a page can't load its content (e.g. database unreachable or not set up yet). */
export default function SiteError({ error, reset }: { error: Error & { digest?: string }; reset: () => void }) {
  useEffect(() => console.error(error), [error]);
  return (
    <section className="section">
      <div className="wrap narrow center">
        <p className="eyebrow">Bio Fashion Hub</p>
        <h1>We’ll be right back</h1>
        <p className="lead" style={{ margin: "0 auto" }}>This page couldn’t load just now. Please try again in a moment — or send us a message, we’re happy to help.</p>
        <div className="btn-row center-row">
          <button className="btn btn-navy" type="button" onClick={reset}>Try again</button>
          <a className="btn btn-wa" href="https://wa.me/233548473309?text=Hello%20Bio%20Fashion%20Hub!" target="_blank" rel="noopener"><Icon name="whatsapp" /> Chat on WhatsApp</a>
        </div>
      </div>
    </section>
  );
}
