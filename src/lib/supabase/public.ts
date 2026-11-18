import { createClient } from "@supabase/supabase-js";
import { SUPABASE_ANON_KEY, SUPABASE_CONFIGURED, SUPABASE_URL, SupabaseNotConfiguredError } from "./env";

/** Read-only client for public pages (no cookies). */
export function publicClient() {
  if (!SUPABASE_CONFIGURED) throw new SupabaseNotConfiguredError();
  return createClient(SUPABASE_URL, SUPABASE_ANON_KEY, {
    auth: { persistSession: false, autoRefreshToken: false },
  });
}
