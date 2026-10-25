# Tahap 6 — LitSynth ELT

## Implemented

- LitSynth workspace is scoped to a project; only the project owner and assigned staff editors can access it. Archived projects are read-only.
- PDF files are stored on Laravel's private `local` disk under `storage/app/private/litsynth/{project_id}`. Upload validates PDF type, signature, and configurable size. Download is an authorized attachment route; the disk path is never exposed as a public URL.
- When Poppler `pdftotext` is available locally, digital text is extracted page by page. Extracted pages are shown to the project team and searchable together with source metadata and matrix notes.
- If the parser is unavailable or the PDF has no extractable text, the source remains stored and marked for manual handling. No OCR is performed and no file or text is sent to an AI provider.
- Each source has editable metadata and a literature matrix for research question, method, participants/context, key findings, limitations, relevance, and page references. Saving changes clears the verified status; a staff member must explicitly verify the matrix.
- Instrument blueprint drafts can include constructs, respondent context, item prompts, and references to sources in the same project.
- Source upload/update/verify/delete events are written to the existing audit log where applicable.

## Host configuration and limits

- Set `LITSYNTH_PDFTOTEXT_BINARY` if `pdftotext` is not discoverable on `PATH`.
- `LITSYNTH_MAX_PDF_KILOBYTES` controls application validation (default 25,600 KB); PHP and web-server upload limits must be at least as large.
- Search currently uses database `LIKE` across metadata, matrix fields, and extracted page text. It is adequate for small project libraries, not a full-text indexing service.
- Scanned PDF OCR remains a later enhancement. Expert invitations and version-specific instrument validation are implemented in [Tahap 7](tahap-7-validasi-pakar.md). Backup, retention schedules, and accessibility review remain operational work.

## Local verification performed

- New migration applied successfully to the configured workspace database.
- LitSynth routes registered successfully.
- Blade view compilation completed successfully.
- Pint syntax/style check passed for changed PHP files.
- `pdftotext` is not installed in the current environment, so extraction of a real PDF could not be exercised here.
