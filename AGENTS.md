# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

This is a Laravel 12 application using PHP 8.2+, Vite for asset compilation, and Tailwind CSS 4.0. The project uses SQLite as the default database and includes queue, cache, and session management capabilities.

## Development Commands

### Initial Setup
```bash
composer run setup
```
This runs the complete setup: composer install, creates .env from .env.example, generates app key, runs migrations, and builds frontend assets.

### Development Server
```bash
composer run dev
```
This starts all development services concurrently:
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
composer run test
# or directly:
php artisan test
```

Run specific test suites:
```bash
php artisan test --testsuite=Unit
php artisan test --testsuite=Feature
```

Run specific test files or methods:
```bash
php artisan test tests/Feature/ExampleTest.php
php artisan test --filter test_method_name
```

### Code Quality
```bash
./vendor/bin/pint        # Laravel Pint (PHP CS Fixer)
./vendor/bin/pint --test # Check without fixing
```

### Asset Compilation
```bash
npm run build  # Production build
npm run dev    # Development with HMR
```

### Artisan Console
```bash
php artisan [command]
php artisan list          # Show all available commands
php artisan tinker        # REPL for Laravel
```

### Database Operations
```bash
php artisan migrate              # Run migrations
php artisan migrate:fresh        # Drop all tables and re-run migrations
php artisan migrate:fresh --seed # With seeders
php artisan db:seed              # Run seeders
php artisan migrate:rollback     # Rollback last migration batch
php artisan migrate:status       # Show migration status
```

## Architecture

### Application Bootstrap
The application bootstrap is configured in `bootstrap/app.php` using Laravel 12's new application builder pattern. Key configurations:
- Routing: web routes in `routes/web.php`, console commands in `routes/console.php`
- Health check endpoint: `/up` (automatically configured)
- Middleware and exception handling configured via closures

### Directory Structure
- `app/Http/Controllers/` - HTTP controllers
- `app/Models/` - Eloquent models
- `app/Providers/` - Service providers
- `routes/` - Route definitions (web.php, console.php)
- `database/migrations/` - Database migrations
- `database/factories/` - Model factories
- `database/seeders/` - Database seeders
- `resources/views/` - Blade templates
- `resources/js/` - JavaScript assets (compiled by Vite)
- `resources/css/` - CSS assets (using Tailwind CSS 4.0)
- `tests/Feature/` - Feature tests (test HTTP requests, database interactions)
- `tests/Unit/` - Unit tests (test isolated functionality)
- `config/` - Configuration files

### Autoloading
PSR-4 autoloading is configured in `composer.json`:
- `App\` → `app/`
- `Database\Factories\` → `database/factories/`
- `Database\Seeders\` → `database/seeders/`
- `Tests\` → `tests/`

### Frontend Assets
Vite configuration in `vite.config.js`:
- Entry points: `resources/css/app.css` and `resources/js/app.js`
- Tailwind CSS 4.0 integrated via `@tailwindcss/vite` plugin
- Hot Module Replacement (HMR) enabled for development
- Storage views ignored from watch to prevent unnecessary rebuilds

### Testing Environment
PHPUnit configuration in `phpunit.xml`:
- Uses in-memory SQLite database for tests
- Separate test suites: Unit and Feature
- Test environment variables preconfigured (array cache, sync queue, etc.)
- Bcrypt rounds reduced to 4 for faster test execution

### Default Configuration
Current configuration (`.env`):
- Database: MySQL (host: 127.0.0.1:3306, database: laravel, user: root)
- Queue: Database driver
- Cache: Database driver
- Session: Database driver
- Mail: Log driver (development)
- Broadcasting: Log driver (development)

Note: The `.env.example` uses SQLite by default, but this project is configured to use MySQL.

## Key Laravel 12 Patterns

### Routing
Routes are defined in `routes/web.php`. Laravel 12 uses the new routing structure defined in `bootstrap/app.php`:
```php
Route::get('/', function () {
    return view('welcome');
});
```

### Models
Models extend `Illuminate\Database\Eloquent\Model`. The base User model is in `app/Models/User.php`.

### Controllers
Controllers extend `App\Http\Controllers\Controller`. The base controller is in `app/Http/Controllers/Controller.php`.

### Service Providers
Service providers extend `Illuminate\Support\ServiceProvider`. The main app provider is `app/Providers/AppServiceProvider.php`.

### Console Commands
Custom Artisan commands can be registered in `routes/console.php` or as command classes in `app/Console/Commands/`.

## Notes

- This project uses Laravel 12's new application bootstrap structure (streamlined configuration)
- The database migrations include user authentication, cache, and job tables by default
- Queue connection is configured to use database driver, so ensure migrations are run
- Vite watches and rebuilds assets automatically in development mode
- The dev script uses colored output for easier identification of different services
