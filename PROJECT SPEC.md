# Technical Setup & Database Design Guide

## 1. Technical Prerequisites & Environment Setup

| Item | Description | Why it's Needed |
|------|-------------|-----------------|
| **Authentication Setup** | Run initial scaffolding (`php artisan breeze:install`) to handle user registration and login quickly. | User management is a core feature. |
| **Configure leaflet** | Obtain and configure Leaflet | Essential for the Interactive Map and GPS storage. |

---

## 2. Database Design (Schema Definition)

 **This is the most critical step before development.** You need to map every feature onto database tables.

### Recommended Models & Fields

You need to define the columns, data types, and relationships for your primary tables. This directly translates to your Laravel migrations.

| Model (Table Name) | Key Fields (Examples) | Relationships |
|--------------------|-----------------------|---------------|
| `users` | Standard Laravel fields + `role_id` (foreign key) | Many to One with `roles`. One to Many with `routes`, `ascents`, `comments`. |
| `roles` | `name` (Admin, Moderator, Standard) | One to Many with `users`. |
| `locations` | `name`, `parent_id` (for nested sectors), `gps_lat`, `gps_lng` | One to Many with `routes`. |
| `routes` | `name`, `location_id`, `created_by_user_id`, `length_m`, `pitch_count`, `grade_type`, `grade_value`, `risk_rating` (R/X), `status` (`enum`), `topo_url`, `is_approved` (`boolean`) | Many to One with `users` and `locations`. One to Many with `ascents`, `comments`, `photos`. |
| `ascents` (Logbook) | `route_id`, `user_id`, `date_climbed`, `success` (`boolean`), `partners` (`text`), `impression` | Many to One with `routes` and `users`. |
| `comments` | `route_id`, `user_id`, `text`, `rating` (1-5 stars) | Many to One with `routes` and `users`. |
| `photos` | `route_id`, `user_id`, `path`, `is_topo` (`boolean`) | Many to One with `routes` and `users`. |

### Action Item: Create Migrations

Write and run the migration files (`php artisan make:model X -m`) to create all these tables and relationships.

---

## 3. Authorization & Policy Definition

You have defined roles, now you must define what each role can do. In Laravel, this means defining **Policies**.

### `RoutePolicy`

| Action | Permission |
|--------|------------|
| `create` | Allowed for Standard Users (but requires moderation). Allowed for Admin/Club (bypass moderation). |
| `update` | Allowed for Admin/Moderator. Allowed for the `created_by_user_id` (but requires re-moderation). |
| `delete` | Allowed only for Admin. |

### `AscentPolicy`

| Action | Permission |
|--------|------------|
| `create` | Allowed for authenticated users. |
| `update` / `delete` | Only allowed by the owner of the ascent. |

### Action Item: Define Policies

Create the necessary Policy classes (`php artisan make:policy RoutePolicy`) and register them.