#!/usr/bin/env node
/**
 * Create (or promote) an admin who can sign in to /admin.
 *
 *   npm run create-admin -- owner@example.com "Their Name"
 *
 * Needs NEXT_PUBLIC_SUPABASE_URL and SUPABASE_SERVICE_ROLE_KEY in the environment or in .env.local.
 * Prints a one-time temporary password for new users; they should change it in Admin → My account.
 */
import { createClient } from "@supabase/supabase-js";
import { randomBytes } from "node:crypto";
import { existsSync, readFileSync } from "node:fs";

for (const file of [".env.local", ".env"]) {
  if (!existsSync(file)) continue;
  for (const line of readFileSync(file, "utf8").split("\n")) {
    const m = line.match(/^\s*([A-Z0-9_]+)\s*=\s*"?(.*?)"?\s*$/);
    if (m && !process.env[m[1]]) process.env[m[1]] = m[2];
  }
}

const [email, displayName = ""] = process.argv.slice(2);
const url = process.env.NEXT_PUBLIC_SUPABASE_URL;
const key = process.env.SUPABASE_SERVICE_ROLE_KEY;
if (!email || !/^[^@\s]+@[^@\s]+\.[^@\s]+$/.test(email)) {
  console.error('Usage: npm run create-admin -- owner@example.com "Their Name"');
  process.exit(1);
}
if (!url || !key) {
  console.error("Set NEXT_PUBLIC_SUPABASE_URL and SUPABASE_SERVICE_ROLE_KEY first (e.g. in .env.local).");
  process.exit(1);
}

const sb = createClient(url, key, { auth: { persistSession: false, autoRefreshToken: false } });

// Find an existing user with this email (paginate through users).
let user = null;
for (let page = 1; !user; page++) {
  const { data, error } = await sb.auth.admin.listUsers({ page, perPage: 200 });
  if (error) throw error;
  user = data.users.find((u) => u.email?.toLowerCase() === email.toLowerCase()) ?? null;
  if (data.users.length < 200) break;
}

let tempPassword = null;
if (!user) {
  tempPassword = randomBytes(9).toString("base64url");
  const { data, error } = await sb.auth.admin.createUser({ email, password: tempPassword, email_confirm: true });
  if (error) throw error;
  user = data.user;
}

const { error } = await sb.from("admins").upsert({ user_id: user.id, display_name: displayName });
if (error) throw error;

console.log(`✔ ${email} is an admin.`);
if (tempPassword) console.log(`  Temporary password: ${tempPassword}\n  Sign in at /admin/login and change it under "My account".`);
else console.log("  Existing user — they can sign in with their current password (or use \"Forgot password\").");
