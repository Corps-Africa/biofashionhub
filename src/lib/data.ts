import { cache } from "react";
import { publicClient } from "./supabase/public";
import type { Settings } from "./types";

/** All site settings as a key → value map (deduplicated per request). */
export const getSettings = cache(async (): Promise<Settings> => {
  const { data, error } = await publicClient().from("settings").select("key, value");
  if (error) throw new Error("Could not load settings: " + error.message);
  return Object.fromEntries((data ?? []).map((r) => [r.key, r.value]));
});
