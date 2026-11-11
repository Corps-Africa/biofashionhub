import "server-only";
import { createClient, type SupabaseClient } from "@supabase/supabase-js";
import { revalidatePath } from "next/cache";
import { redirect } from "next/navigation";
import { SUPABASE_URL } from "./supabase/env";
import { serverClient } from "./supabase/server";
import { isStoragePath, slugify } from "./util";

/** Ensures the visitor is a signed-in admin. Database security (RLS) enforces this too. */
export async function requireAdmin() {
  const sb = await serverClient();
  const { data: { user } } = await sb.auth.getUser();
  if (!user) redirect("/admin/login");
  const { data: isAdmin } = await sb.rpc("is_admin");
  return { sb, user, isAdmin: isAdmin === true };
}

/** Same as requireAdmin but refuses to continue for non-admins (used in server actions). */
export async function adminOnly() {
  const ctx = await requireAdmin();
  if (!ctx.isAdmin) throw new Error("Not authorised");
  return ctx;
}

/** Service-role client for the few admin-management tasks. Only available if the key is configured. */
export function serviceClient(): SupabaseClient | null {
  const key = process.env.SUPABASE_SERVICE_ROLE_KEY;
  return key ? createClient(SUPABASE_URL, key, { auth: { persistSession: false, autoRefreshToken: false } }) : null;
}

/** Refresh every public page, then return to an admin page with a message. */
export function done(path: string, msg: string): never {
  revalidatePath("/", "layout");
  redirect(`${path}${path.includes("?") ? "&" : "?"}msg=${encodeURIComponent(msg)}`);
}

export async function uniqueSlug(sb: SupabaseClient, table: string, base: string, ignoreId?: number) {
  const slug = slugify(base);
  for (let i = 1; ; i++) {
    const attempt = i === 1 ? slug : `${slug}-${i}`;
    let q = sb.from(table).select("id").eq("slug", attempt);
    if (ignoreId) q = q.neq("id", ignoreId);
    const { data } = await q.limit(1);
    if (!data?.length) return attempt;
  }
}

/** Delete replaced/removed photos from storage (never touches bundled placeholder images). */
export async function removeImages(sb: SupabaseClient, ...paths: (string | null | undefined)[]) {
  const list = paths.filter(isStoragePath);
  if (list.length) await sb.storage.from("media").remove(list);
}

export const str = (fd: FormData, k: string, max = 300) => String(fd.get(k) ?? "").trim().slice(0, max);
export const bool = (fd: FormData, k: string) => fd.get(k) === "on" || fd.get(k) === "1";
export const int = (fd: FormData, k: string) => Number.parseInt(String(fd.get(k) ?? "0"), 10) || 0;
