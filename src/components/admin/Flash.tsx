"use client";
import { usePathname, useRouter, useSearchParams } from "next/navigation";
import { useEffect, useState } from "react";

/** Shows the "?msg=" message left by a server action, then tidies the URL. */
export default function Flash() {
  const params = useSearchParams();
  const router = useRouter();
  const path = usePathname();
  const [msg, setMsg] = useState<string | null>(null);
  useEffect(() => {
    const m = params.get("msg");
    if (m) {
      setMsg(m);
      const rest = new URLSearchParams(params);
      rest.delete("msg");
      router.replace(path + (rest.size ? `?${rest}` : ""), { scroll: false });
    }
  }, [params, path, router]);
  return msg ? <div className="flash flash-success" role="status">{msg}</div> : null;
}
