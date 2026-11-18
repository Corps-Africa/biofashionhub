import { cache } from "react";
import { FALLBACK_SETTINGS } from "./fallbackSettings";
import { SUPABASE_CONFIGURED } from "./supabase/env";
import { publicClient } from "./supabase/public";
import type { Settings } from "./types";

/**
 * All site settings as a key → value map (deduplicated per request).
 * Never throws: if the database can't be reached the header and footer still
 * render with the real business details, and the page itself shows the error screen.
 */
export const getSettings = cache(async (): Promise<Settings> => {
  if (!SUPABASE_CONFIGURED) return FALLBACK_SETTINGS;
  const { data, error } = await publicClient().from("settings").select("key, value");
  if (error) {
    console.error("Could not load settings:", error.message);
    return FALLBACK_SETTINGS;
  }
  return Object.fromEntries((data ?? []).map((r) => [r.key, r.value]));
});
