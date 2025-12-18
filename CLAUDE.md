# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

This is a **climbing route management and community platform** built with Laravel 12. It enables climbers to discover, document, rate, and discuss climbing routes across multiple locations with hierarchical geographic organization (Mountain > Cliff > Sector).

**Tech Stack:**
- Laravel 12 (PHP 8.2+)
- Livewire 3.6.4 + Volt 1.7.0 for reactive UI
- Tailwind CSS 4.0 via Vite
- Leaflet.js 1.9.4 with MarkerCluster for interactive mapping
- MySQL database (SQLite fallback in .env.example)
- Laravel Breeze for authentication

## Development Commands

### Initial Setup
```bash
composer run setup
```
Runs complete setup: composer install, creates .env from .env.example, generates app key, runs migrations, and builds frontend assets.

### Development Server
```bash
composer run dev
```
Starts all development services concurrently:
- PHP development server (http://localhost:8000)
- Queue worker
- Laravel Pail (real-time logs)
- Vite dev server (HMR for CSS/JS)

Alternatively, start services individually:
```bash
php artisan serve          # Development server only
npm run dev               # Vite dev server only
php artisan queue:listen  # Queue worker only
php artisan pail          # Log viewer only
```

### Testing
```bash
composer run test         # PHPUnit tests
php artisan test          # Direct testing
npx playwright test       # E2E tests (tests/e2e/)

# Run specific test suites
php artisan test --testsuite=Unit
php artisan test --testsuite=Feature
php artisan test --filter test_method_name
```

### Code Quality
```bash
./vendor/bin/pint        # Laravel Pint (PHP CS Fixer)
./vendor/bin/pint --test # Check without fixing
```

### Database Operations
```bash
php artisan migrate              # Run migrations
php artisan migrate:fresh --seed # Drop all tables, re-run migrations with seeders
php artisan db:seed              # Run seeders
php artisan migrate:rollback     # Rollback last migration batch
```

**Important:** Roles are required for registration/permissions. Always run seeders after `migrate:fresh` before creating users.

## Application Architecture

### Core Domain Models

The application revolves around a hierarchical climbing location system with route metadata and community engagement:

**Location (Hierarchical - Mountain > Cliff > Sector)**
- `parent_id` for self-referencing hierarchy
- Methods: `getFullPath()`, `getAncestors()`, `isTopLevel()`
- Scopes: `topLevel()`, `byLevel()`
- Stores GPS coordinates for map markers

**Route (Core entity)**
- Belongs to `Location`, created by `User`, optionally approved by admin/moderator
- Fields: name, grade_type, grade_value, pitch_count, length_m, risk_rating, route_type, status, topo_url, topo_data (JSON), is_approved
- Key methods: `approve(User $approver)`, `canBeEditedBy(User $user)`
- Scopes: `approved()`, `pending()`, `byGrade()`, `byType()`, `byStatus()`, `search()`
- Has many: Photos, ConditionReports, Ascents, Ratings, Comments

**User**
- Belongs to `Role` (Admin, Moderator, Club/Equipper, User) and optionally `ClimbingClub`
- Has many: Routes (created), Ascents (logbook), Ratings, Comments, UserWarnings

**Ascent (Climbing logbook entries)**
- Tracks user climbs with date, status (Success/Failed/Attempt), partners, notes
- Required for rating eligibility

**Comment (Threaded discussion)**
- Self-referencing via `parent_id` for nested replies
- 30-minute edit window after creation
- @mention parsing via `parseMentions()`, `saveMentions()`
- Has many: CommentVotes (upvote/downvote/helpful), CommentMentions

**Rating (Sentiment voting)**
- Thumbs up/down on routes
- Requires user to have an ascent of the route

**ConditionReport**
- Route condition updates (wet/closed/bolts missing) with expiry dates
- Requires moderation approval

### Authorization & Security

**Role-Based Access Control:**
- **Admin**: Full system access, route approval, user management, warning issuance
- **Moderator**: Route approval, comment moderation, condition report review
- **Club/Equipper**: Auto-approve own routes, equipment management
- **User**: Default authenticated user

**Key Policies:**
- `RoutePolicy`: Public can view approved routes; creators/admins can edit; only admins/mods can approve/delete
- `LocationPolicy`: All auth users can view; only admins/mods can create/update/delete
- `AscentPolicy`: Owners can edit their own ascents; admins have full access
- Policy checks enforced via `@can()` Blade directives and controller gates

**Security Features:**
- Auto-re-moderation: Non-admin edits to routes require re-approval
- Comment 30-minute edit window enforced by `canBeEditedBy()`
- Soft deletes on comments with file cleanup hooks
- File cleanup on model deletion via `boot()` methods

### Livewire Components

The app uses Livewire 3 for reactive UI. Key components:

| Component | Purpose | Location |
|-----------|---------|----------|
| `RouteSearch` | Real-time route filtering with pagination | `app/Livewire/RouteSearch.php` |
| `LocationMap` | Interactive Leaflet.js map with marker clustering | `app/Livewire/LocationMap.php` |
| `LocationSelector` | Hierarchical location picker (Mountain > Cliff > Sector) | `app/Livewire/LocationSelector.php` |
| `TopoUpload` | Topo diagram file upload | `app/Livewire/TopoUpload.php` |

**Livewire Patterns:**
- Uses query string parameters for shareable filter URLs (`RouteSearch`)
- Debounced inputs for search performance
- `WithPagination` trait for large result sets
- `WithFileUploads` trait for photo/topo handling
- Event listeners for map reinitialization on Livewire navigation

### Frontend Architecture

**Views Structure:**
```
resources/views/
├── layouts/
│   ├── app.blade.php (authenticated layout)
│   └── guest.blade.php (login/register layout)
├── routes/          # Route CRUD views
├── locations/       # Location CRUD views
├── ascents/         # Logbook views
├── admin/           # Admin dashboard, user management, moderation
└── livewire/        # Livewire component views
```

**Leaflet Integration:**
- CDN-loaded Leaflet + MarkerCluster plugins
- Dynamic marker generation from location GPS data
- OpenStreetMap tiles
- Cleanup on page leave to prevent memory leaks

**Tailwind CSS 4.0:**
- Uses `@tailwindcss/vite` plugin
- Forms plugin (`@tailwindcss/forms`)
- Utility-based styling throughout

### Key Controllers

| Controller | Responsibilities |
|------------|------------------|
| `RouteController` | CRUD routes, approval/rejection, photo handling, topo upload, auto-approval for privileged users |
| `LocationController` | CRUD locations, hierarchy management |
| `AscentController` | CRUD climbing logbook entries, policy-enforced ownership |
| `CommentController` | Create/update/destroy comments, mention parsing, voting |
| `RatingController` | Store sentiment ratings (requires ascent) |
| `CommentReportController` | Users report comments; admin approves/dismisses + issues warnings |
| `Admin\DashboardController` | Route approval queue, bulk actions |
| `Admin\UserRoleController` | Assign roles to users |

### Routing Structure

**Public Routes:**
- `/` - Welcome page
- `/routes` - Route search (Livewire filtered view)
- `/routes/{route}` - Route detail (policy-gated by approval status)

**Authenticated Routes:**
- `/dashboard` - User dashboard
- `/routes/create`, `/routes/{route}/edit` - Route CRUD
- `/routes/{route}/approve`, `/routes/{route}/reject` - Admin actions
- `/locations/*`, `/ascents/*` - Resource CRUD
- `/routes/{route}/rate`, `/routes/{route}/comments` - Community features

**Admin Routes** (`/admin` prefix, `can:viewAdmin` gate):
- `/admin/dashboard` - Approval queue
- `/admin/users` - User role management
- `/admin/reports` - Comment moderation
- `/admin/condition-reports` - Route condition moderation

### Data Flow Examples

**Creating a Route (Typical User):**
1. User submits route form with LocationSelector, TopoUpload, PhotoUpload
2. `RouteController@store` validates via `StoreRouteRequest`
3. Files stored to `storage/public/topos/` and `storage/public/photos/`
4. Route created with `is_approved=false` (unless user is Admin/Club Equipper → auto-approved)
5. Redirect to route show page

**Approving a Route (Admin):**
1. Admin clicks "Approve" on `/admin/dashboard`
2. `RouteController@approve()` calls `$route->approve($admin_user)`
3. Sets `is_approved=true`, `approved_by_user_id`, `approved_at=now()`
4. Redirect with success flash

**Searching Routes (Livewire):**
1. User types in search box → `RouteSearch::updatingSearch()` resets pagination
2. Livewire re-renders → chains scopes: `.approved()` → `.search()` → `.where()`
3. Returns paginated results (15 per page)
4. Component updates table HTML in-place

**Posting a Comment:**
1. User submits comment form on route show page
2. `CommentController@store` validates content (max 5000 chars)
3. Stores comment, calls `$comment->saveMentions()` to parse @usernames
4. Creates `CommentMention` records for notifications
5. Redirect back with nested replies rendered

## Common Development Tasks

### Add a New Eloquent Scope
1. Add scope method to model in `app/Models/`
2. Chain scope in controller queries
3. Update tests in `tests/Feature/`

### Add a New Livewire Component
1. Create component class in `app/Livewire/`
2. Create corresponding view in `resources/views/livewire/`
3. Use `<livewire:component-name />` in Blade templates
4. Add query string parameters if filters should be shareable

### Add Authorization Logic
1. Create/update policy in `app/Policies/`
2. Add inverse relation checks (e.g., `$user->id === $route->created_by_user_id`)
3. Use `@can('action', $model)` in Blade views
4. Check policies in controllers via `$this->authorize('action', $model)`

### Add Admin Feature
1. Add route to `/admin` prefix group in `routes/web.php` with `can:viewAdmin` gate
2. Add action to `DashboardController` or create new admin controller
3. Create view in `resources/views/admin/`
4. Update admin navigation in layout

### Modify Database Schema
1. Create migration: `php artisan make:migration descriptive_name`
2. Update model `$fillable`, `$casts`, relationships
3. Run `php artisan migrate`
4. Update seeders if needed in `database/seeders/`

## Testing Environment

PHPUnit configuration in `phpunit.xml`:
- Uses in-memory SQLite database for tests
- Separate test suites: Unit and Feature
- Bcrypt rounds reduced to 4 for faster test execution

Playwright configuration in `playwright.config.js`:
- E2E tests in `tests/e2e/`
- Chromium only (Desktop Chrome)
- Screenshots/videos on failure only

## Key Architectural Patterns

1. **Policy-Based Authorization**: All resource access controlled via Laravel policies
2. **Scope Chaining**: Models use query scopes for readable, reusable query building
3. **Self-Referencing Models**: Location (parent-child) and Comment (parent-replies) use self-joins
4. **Soft Deletes on Comments**: Comments use soft deletes; force delete removes associated photos
5. **Livewire with Query String Params**: Filters serialized to URL for shareable results
6. **Model Methods for Business Logic**: Route approval, comment editing window, mention parsing encapsulated in models
7. **Auto-Cleanup Hooks**: Model `boot()` methods clean up files on deletion
8. **Reactive UI**: Livewire components with debounced inputs and live model binding

## Notes

- Database uses MySQL in production (configured in `.env`), SQLite for tests
- Queue connection uses database driver, so ensure migrations are run
- Roles seed data is required for registration - always run `db:seed` after `migrate:fresh`
- Comment edit window is hardcoded to 30 minutes in `Comment::canBeEditedBy()`
- Route approval workflow: non-admin edits trigger re-moderation requirement
- Topo data stored as JSON in `topo_data` column for future editor integration
- Commit messages follow short, imperative style (e.g., "Add gallery for route")
