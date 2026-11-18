export const SUPABASE_URL = process.env.NEXT_PUBLIC_SUPABASE_URL ?? "";
export const SUPABASE_ANON_KEY = process.env.NEXT_PUBLIC_SUPABASE_ANON_KEY ?? "";
export const SITE_URL = (process.env.NEXT_PUBLIC_SITE_URL ?? "http://localhost:3000").replace(/\/$/, "");

/** True when the two required Supabase settings are present. */
export const SUPABASE_CONFIGURED = /^https?:\/\//.test(SUPABASE_URL) && SUPABASE_ANON_KEY.length > 20;

export class SupabaseNotConfiguredError extends Error {
  constructor() {
    super("Supabase is not configured: set NEXT_PUBLIC_SUPABASE_URL and NEXT_PUBLIC_SUPABASE_ANON_KEY in Netlify, then redeploy.");
    this.name = "SupabaseNotConfiguredError";
  }
}
