# Tahap 8 — GuruBahasa (Pengabdian)

## Workflow implemented

- Staff with owner/editor access can create and manage a GuruBahasa material inside a project. Students and public users cannot access these project records.
- A material stores its topic, intended participant context, a CEFR target, source text/notes, learning objectives, draft learning text, glossary, questions and answer guidance, worksheet, activities, and rubric.
- A teacher can save, reopen, edit, delete, curate, and mark a curated material ready to share. Editing a curated/published material returns it to draft. Every operation is project-scoped and audited.
- The material detail page is the review view. HTML export can be saved/opened in a browser or word processor; it includes CEFR and human-review notes.
- CEFR is explicitly an intended estimate for drafting, not a certified or measured participant level.

## AI and content limits

- The configured AI gateway remains closed; no GuruBahasa content is sent to a provider. The current implementation provides a structured manual drafting and curation workspace, not automatic AI generation.
- The teacher authors or supplies all content. The application does not infer participant proficiency or certify language level.
- Public sharing/distribution, downloadable PDF/Word rendering, and AI generation remain future work after data/provider policy decisions.

## Local implementation checks

- Migration is additive and project-scoped.
- Project management routes, form validation, archive read-only restrictions, audit events, and HTML export are implemented.
- Automated tests were not run in this implementation pass.
