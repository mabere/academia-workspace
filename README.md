# ELT Academic Hub

ELT Academic Hub is a web workspace intended to support lecturers in the three pillars of higher education: education and teaching, research, and community engagement. AI generated academic material is a draft for human review. The platform does not set final student grades or certify research instruments.

## Current implementation

This project uses the existing Laravel 13 and Livewire starter installation. The initial pilot groundwork currently includes:

- public landing page and account registration/sign-in;
- account roles `public`, `student`, `lecturer`, `admin`, and `superadmin`;
- lecturer-owned projects with teaching/research/community focus and archive;
- WriteWise assignments, a starter rubric, explicit student assignment by registered email, immutable revision records, and manual feedback review/publication;
- LitSynth ELT project workspaces with private PDF storage, local page text extraction when `pdftotext` is installed, searchable sources, editable literature matrices with page references and human verification, and instrument blueprint drafts;
- LitSynth expert validation invitations tied to immutable blueprint snapshots, one-time ratings, access revocation, and descriptive Aiken's V/CVI summaries;
- GuruBahasa project studios for manually drafted and curated community language materials, with glossary, exercises, worksheets, activities, rubrics, and HTML export; AI generation remains disabled;
- IndoEFL CorpusLab (one per research project) for superadmin-reviewed protocols, manual corpus entry, basic PII-pattern redaction, consent attestations, human annotations, inclusion status, KWIC search, private CSV export, and scheduled retention purge;
- Output & Evidence index for project-scoped literature sources, instruments and expert results, GuruBahasa materials, published WriteWise tasks, and published feedback;
- server-side ownership checks, CSRF-protected forms, validation, login throttling, audit events, and baseline security headers;
- private PDF storage and download authorization, plus an operational backup and recovery runbook in `docs/tahap-10-hardening-operasional.md`.

WriteWise is the initial pilot. LitSynth is a local, human-reviewed research workflow; its uploaded documents are not sent to an AI provider. Expert invitation links are bearer credentials and should only be shared directly with intended validators. GuruBahasa provides a manual drafting/curation workspace; AI text generation is not active. IndoEFL CorpusLab requires an actual ethics approval reference and superadmin review before data collection. SpeakFlow Studio and LokalTalk English are not active modules.

## Requirements and setup

- PHP 8.3 or later and Composer
- Node.js compatible with the installed Vite toolchain
- SQLite or MySQL with the matching PDO extension enabled in the PHP CLI used by Artisan (the starter defaults to `database/database.sqlite`)

Copy `.env.example` to `.env` if needed, set a unique `APP_KEY` with `php artisan key:generate`, then run:

```sh
composer install
npm install
php artisan migrate
npm run build
```

For MySQL, set `DB_CONNECTION=mysql` and the `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, and `DB_PASSWORD` values in `.env`. Confirm extensions using `php -m`; CLI PHP needs `pdo_sqlite` for SQLite and `pdo_mysql` for MySQL. The local XAMPP PHP configuration in this workspace enables both, so standard `php artisan ...` commands work with either connection.

LitSynth stores uploaded PDFs on Laravel's private `local` disk under `storage/app/private/litsynth`; direct disk serving is disabled and download routes check project access. To extract digital PDF text page by page, install Poppler's `pdftotext` on the application host and make it available on `PATH`, or set `LITSYNTH_PDFTOTEXT_BINARY` to its executable path. Scanned PDFs need OCR and currently remain available for manual review without extracted pages. Configure the upload limit with `LITSYNTH_MAX_PDF_KILOBYTES` (default 25600 KB). Keep the PDF storage directory out of the public web root and include it in protected backup and retention procedures.

Run the local application and queue worker with the commands in `composer.json` (`composer run dev` where supported), or run Laravel and Vite separately. A production environment must use its own secure environment variables and managed database/storage configuration.

## Accounts and roles

Self-registration permits `student` or `public`; it cannot grant lecturer, admin, or superadmin access. A server operator can assign a role to an existing account from the project console:

```sh
php artisan hub:user-role lecturer@example.test lecturer
```

Create the initial superadmin account with the interactive command below. It asks for the password without echoing it and does not accept a password argument or use a default credential:

```sh
php artisan hub:superadmin:create admin@example.test "Academic Hub Administrator"
```

On production, add `--force` after verifying the operator identity. To elevate an existing trusted account, run `hub:user-role admin@example.test superadmin` (also with `--force` on production).

Do not expose this command through a public route or share privileged accounts. Students only see assignments to which a lecturer explicitly links their existing student account. General users can access public pages and their own account, not project or student records.

## Database and storage

Project, WriteWise, LitSynth, and CorpusLab tables are managed by migrations. Review migrations before applying them to any database containing user data. LitSynth provides project-authorized private upload/download and source deletion. CorpusLab stores only its cleaned text variant and schedules a purge after the configured retention date. Backup and recovery procedures are documented in `docs/tahap-10-hardening-operasional.md`; automated backup scheduling and LitSynth OCR are not implemented. Review institutional policy before uploading sensitive research documents or entering corpus text.

## AI and data processing

AI feedback generation is intentionally disabled. No student text is sent to an AI provider. A server-side gateway checks the activation flag, processing approval, permission to send student text, credentials, and provider adapter before any request. Provider, processing terms, allowed payload, retention, and institutional notice/consent requirements must be decided first. Do not put secrets in frontend variables or source control. A provider adapter is not installed; after policy approval, add and review one, then configure quotas and operational limits before activation.

## Checks

Available project scripts include:

```sh
composer test
composer run types:check
npm run build
```

`composer test` runs formatting check, static analysis, and PHPUnit per the existing Composer scripts. Run checks before each release and report their actual outcomes.

## Known gaps before pilot with real users

- AI provider integration and approved student-data processing policy.
- Fine-grained editor permissions beyond the current owner/editor project boundary.
- Email verification, password reset, and account recovery flow.
- Assignment edit/version lifecycle, rubric editing/version lifecycle, and late-submission policy.
- Backup scheduling, operational monitoring, and cross-browser/screen-reader usability review.
- LitSynth OCR and multi-round Delphi workflow, GuruBahasa AI.
- Pilot usability evaluation and institutional review of data handling.
- CorpusLab's automated text masking is limited and does not replace researcher-led anonymization or institutional ethics review. See `docs/tahap-11-indoefl-corpuslab.md` before using real data.

Do not describe the full product as complete while these modules and safeguards are outstanding.
