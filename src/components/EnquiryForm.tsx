"use client";
import { useActionState, useState } from "react";
import { submitEnquiry, type EnquiryState } from "@/app/(site)/actions";
import Icon from "./Icon";

const OCCASIONS = ["Wedding (groom)", "Wedding (guest / groomsmen)", "Naming ceremony", "Eid / Jumu’ah", "Graduation", "Work / everyday", "Other"];

export default function EnquiryForm({ type, waHref, initialMessage = "" }: { type: "contact" | "custom"; waHref: string; initialMessage?: string }) {
  const [state, action, pending] = useActionState<EnquiryState, FormData>(submitEnquiry, {});
  const [ts] = useState(() => Date.now());
  const err = state.errors ?? {};
  const val = state.values ?? {};
  const custom = type === "custom";

  if (state.ok) {
    return (
      <div className="success" role="status">
        <span className="success-icon"><Icon name="check" /></span>
        <h2 className="h3">{custom ? "Thank you — request received!" : "Message sent — thank you!"}</h2>
        <p>We’ll get back to you shortly. For a faster reply, send it to us on WhatsApp too.</p>
        <a className="btn btn-wa btn-block" href={state.wa} target="_blank" rel="noopener"><Icon name="whatsapp" /> Send on WhatsApp</a>
      </div>
    );
  }

  const field = (name: keyof NonNullable<EnquiryState["errors"]>) =>
    err[name] ? { "aria-invalid": true, "aria-describedby": `err-${name}` } : {};
  const Err = ({ name }: { name: keyof NonNullable<EnquiryState["errors"]> }) =>
    err[name] ? <p className="field-error" id={`err-${name}`}>{err[name]}</p> : null;

  return (
    <>
      <h2 className="h3">{custom ? "Request a custom outfit" : "Send us a message"}</h2>
      {err.form && <div className="flash flash-error" role="alert">{err.form}</div>}
      <form action={action} noValidate>
        <input type="hidden" name="type" value={type} />
        <input type="hidden" name="ts" value={ts} />
        <div className="hp" aria-hidden="true"><label>Leave this empty <input type="text" name="website" tabIndex={-1} autoComplete="off" /></label></div>
        <div className="field">
          <label htmlFor="name">Your name *</label>
          <input id="name" name="name" autoComplete="name" defaultValue={val.name} {...field("name")} />
          <Err name="name" />
        </div>
        <div className="field-row">
          <div className="field">
            <label htmlFor="phone">Phone / WhatsApp{custom ? " *" : ""}</label>
            <input id="phone" name="phone" type="tel" inputMode="tel" autoComplete="tel" placeholder="e.g. 024 000 0000" defaultValue={val.phone} {...field("phone")} />
            <Err name="phone" />
          </div>
          <div className="field">
            <label htmlFor="email">Email{custom ? " (optional)" : ""}</label>
            <input id="email" name="email" type="email" autoComplete="email" defaultValue={val.email} {...field("email")} />
            <Err name="email" />
          </div>
        </div>
        {custom && (
          <div className="field-row">
            <div className="field">
              <label htmlFor="occasion">Occasion</label>
              <select id="occasion" name="occasion" defaultValue={val.occasion ?? ""}>
                <option value="">Choose…</option>
                {OCCASIONS.map((o) => <option key={o} value={o}>{o}</option>)}
              </select>
            </div>
            <div className="field">
              <label htmlFor="preferred_date">Fitting / visit date</label>
              <input id="preferred_date" name="preferred_date" type="date" min={new Date().toISOString().slice(0, 10)} defaultValue={val.preferred_date} />
            </div>
          </div>
        )}
        <div className="field">
          <label htmlFor="message">{custom ? "What would you like? *" : "Message *"}</label>
          <textarea id="message" name="message" rows={custom ? 5 : 6} placeholder={custom ? "Style, colours, number of outfits, your event date…" : undefined}
            defaultValue={val.message ?? initialMessage} {...field("message")} />
          <Err name="message" />
        </div>
        <button className="btn btn-navy btn-block btn-lg" type="submit" disabled={pending}>
          {pending ? "Sending…" : custom ? "Send request" : "Send message"}
        </button>
        {custom && <p className="small muted center">Prefer to chat? <a href={waHref} target="_blank" rel="noopener">Message us on WhatsApp</a>.</p>}
      </form>
    </>
  );
}
