/**
 * Supabase pauses free-tier projects after about a week without database activity.
 * This Netlify scheduled function makes one tiny read each day so the site stays online.
 * Not needed on Supabase Pro — delete this file if you upgrade.
 */
export default async () => {
  const url = process.env.NEXT_PUBLIC_SUPABASE_URL;
  const key = process.env.NEXT_PUBLIC_SUPABASE_ANON_KEY;
  if (!url || !key) return new Response("Supabase env vars missing", { status: 500 });
  const res = await fetch(`${url}/rest/v1/settings?select=key&limit=1`, {
    headers: { apikey: key, Authorization: `Bearer ${key}` },
  });
  console.log(`Supabase keep-alive: HTTP ${res.status}`);
  return new Response(`keep-alive ${res.status}`, { status: res.ok ? 200 : 502 });
};

export const config = { schedule: "@daily" };
