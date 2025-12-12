# Repository Guidelines

## Project Structure
- `app/`: Laravel application code (models, controllers, policies, notifications).
- `resources/views/`: Blade + Livewire (Volt) components (UI).
- `resources/js/`: Frontend JavaScript (imported by `resources/js/app.js`, built by Vite).
- `routes/`: HTTP routes (`web.php`) and console scheduling (`console.php`).
- `database/migrations/`: Schema changes.
- `database/seeders/`: Seed data for local/dev (roles, users, locations, routes).
- `tests/`: PHPUnit tests.

## Build, Test, and Development Commands
- `composer run dev`: Runs local server + queue + logs + Vite (recommended for development).
- `npm run dev`: Runs Vite only (frontend HMR).
- `npm run build`: Builds production assets.
- `php artisan test`: Runs PHPUnit tests.
- `php artisan migrate:fresh --seed`: Recreates DB and seeds required data (roles, etc.).

## Coding Style & Naming
- PHP: follow existing Laravel conventions; keep controllers thin, push logic into models/services when reused.
- Indentation: 4 spaces (see `.editorconfig`).
- Livewire: prefer Volt-style components in `resources/views/livewire/...`; use `wire:navigate` friendly patterns (avoid inline scripts; put JS in `resources/js/`).
- Naming: migrations in timestamp format; routes use RESTful naming; Blade components use kebab-case folders.

## Testing Guidelines
- Add/adjust tests in `tests/` when changing core behavior (policies, query visibility, moderation flows).
- Prefer focused tests around the touched feature; avoid broad refactors.

## Database & Seeding
- Roles are required for registration/permissions. If you run `migrate:refresh`, also run `php artisan db:seed` (or use `migrate:fresh --seed`) before creating users.

## Commits & Pull Requests
- Commit messages in this repo are short, imperative summaries (e.g., “Added gallery for route”).
- PRs should include: what changed, how to test locally, and screenshots for UI changes.

