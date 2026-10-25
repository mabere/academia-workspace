# Tahap 9 — Output & Evidence

## Implemented

- A cross-module index gathers existing LitSynth sources and blueprints, expert-validation snapshots with submitted responses, GuruBahasa materials, published WriteWise assignments, and published WriteWise feedback.
- Staff see only projects they own or where they are an assigned editor. Students/public accounts are denied access to the cross-project index. Artifact detail and download routes repeat the project-scope check rather than trusting list links.
- Filters support project, module type, actual source status, and title/description/project search. The source status is shown as-is (draft, reviewed, curated, published, verified, or review needed); a combined listing does not change or imply validation.
- Every artifact has a preview and an authorized HTML export. Literature source download returns the private PDF through a scoped attachment response. Draft WriteWise feedback is excluded; only published feedback and published assignments appear in the index.
- LitSynth expert-validation output uses the existing descriptive calculator and explicitly avoids claiming automatic instrument validity. GuruBahasa exports retain the target-CEFR estimate notice.
- The module appears in the staff dashboard and sidebar. The index is a read-only view over module records; it does not duplicate the source artifacts or relax their module permissions.

## Limits

- The current index assembles records in application memory and is intended for pilot-sized data. Large deployments should move filtering/pagination into a unified artifact index or dedicated search service.
- HTML is the common export format; there is no PDF/Word renderer. This stage does not implement a new public sharing flow or add evidence records for every operational event.

## Local implementation checks

- Migration not required; records are read from the stage 4, 6, 7, and 8 tables.
- Routes, Blade compilation, Pint, and access-query scope are wired for staff projects. Automated tests were not run in this implementation pass.
