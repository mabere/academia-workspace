# Tahap 7 — Portal Validasi Pakar LitSynth

## Workflow implemented

- Each instrument blueprint has immutable numbered snapshots. New edits create a new version; existing invitations continue to refer to the version they were issued for.
- A project owner/editor can create a time-limited invitation for an expert email, copy the one-time URL, and share it manually. No email is sent by the application. Tokens are random and only their SHA-256 hashes are stored.
- The invited expert sees only the frozen instrument version and a one-time response form. The form asks for a 1–4 relevance and clarity rating and optional item/general comments. A required consent acknowledgement is stored with the response.
- A submitted response cannot be edited or submitted again. Unused links can be revoked. Expired, revoked, malformed, and completed links are rejected. Public form responses are rate-limited and sent with `no-store`, `no-referrer`, and `noindex` headers.
- Project staff can inspect completion state, comments, and item-level results. Access to management is project-scoped. Invitation, revocation, and response submission are audited without recording response text in audit metadata.

## Statistical definitions

For each item and each dimension (relevance, clarity), the page reports:

- **Aiken's V:** `Σ(r − 1) / [n(4 − 1)]`, where ratings run 1–4 and `n` is the number of complete submitted expert ratings for that item.
- **I-CVI:** proportion of the expert ratings at 3 or 4.
- **S-CVI/Ave:** arithmetic mean of item I-CVI values for items with ratings.
- **S-CVI/UA:** proportion of rated items whose I-CVI equals 1.0 (all submitted ratings were 3 or 4).

These are descriptive indices. The application does not impose a pass threshold, produce p-values/confidence intervals, or label an instrument valid automatically. Interpretation and thresholds must be prespecified in the research protocol. Aiken's coefficient is based on Aiken (1985); the content validity index follows the item/scale distinction described by Lynn (1986). See the references on the portal page.

## Privacy and operational limits

- The invitation URL is a bearer credential. The app displays it once after generation; the project team must deliver it securely and may revoke it until a response is submitted.
- An invitation records the intended email but does not verify ownership of that address. Responses are linked to invitation records and are not anonymous to the project team. The expert-facing notice states this.
- A response becomes immutable after submission. Corrections require a new invitation/round; the original response remains part of the audit trail.
- Data retention, deletion requests, email delivery, multi-round Delphi workflow, and stronger identity verification remain future operational/policy work.

## Local implementation checks

- Additive migration applied successfully; existing blueprint records are backfilled as version 1 snapshots.
- Expert portal and project management routes registered.
- Blade compilation and Pint checks passed for the changed files.
- Automated tests were not run in this implementation pass.
