"use client";
import { createContext, startTransition, useActionState, useContext } from "react";
import { useFormStatus } from "react-dom";
import type { FormState } from "@/app/(admin)/admin/actions";

type Action = (state: FormState, fd: FormData) => Promise<FormState>;
const Pending = createContext(false);

/**
 * A form bound to a server action that shows its error/info message.
 * Submits manually (instead of <form action>) so React does not clear what the
 * person typed when the server sends back an error.
 */
export function ActionForm({ action, children, className }: { action: Action; children: React.ReactNode; className?: string }) {
  const [state, formAction, pending] = useActionState(action, {});
  return (
    <form className={className} noValidate={false}
      onSubmit={(e) => {
        e.preventDefault();
        if (e.currentTarget.querySelector("[data-uploading]")) {
          alert("Please wait for the photo to finish uploading.");
          return;
        }
        const fd = new FormData(e.currentTarget);
        startTransition(() => formAction(fd));
      }}>
      {state.error && <div className="flash flash-error" role="alert">{state.error}</div>}
      {state.info && <div className="flash flash-success" role="status">{state.info}</div>}
      <Pending.Provider value={pending}>{children}</Pending.Provider>
    </form>
  );
}

export function SubmitButton({ children, className = "a-btn", pendingText = "Saving…" }: { children: React.ReactNode; className?: string; pendingText?: string }) {
  const inActionForm = useContext(Pending);
  const { pending: nativePending } = useFormStatus();
  const pending = inActionForm || nativePending;
  return <button className={className} type="submit" disabled={pending}>{pending ? pendingText : children}</button>;
}

/** Submit button that asks before doing something destructive. */
export function ConfirmButton({ children, message, className = "a-btn a-btn-danger a-btn-sm" }: { children: React.ReactNode; message: string; className?: string }) {
  return <button className={className} type="submit" onClick={(e) => { if (!confirm(message)) e.preventDefault(); }}>{children}</button>;
}
