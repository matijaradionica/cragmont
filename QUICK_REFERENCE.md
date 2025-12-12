# Quick Reference - Development Commands

## 🚀 Start Development

```bash
# Terminal 1: Start Laravel server
php artisan serve

# Terminal 2: Start Vite (hot reload)
npm run dev
```

**Access Application:** http://127.0.0.1:8000

---

## 🔐 Test Accounts

| Email | Password | Role |
|-------|----------|------|
| admin@cragmont.test | password | Admin |
| moderator@cragmont.test | password | Moderator |
| user@cragmont.test | password | Standard |
| club@cragmont.test | password | Club/Equipper |

---

## 📊 Database Commands

```bash
# Run migrations
php artisan migrate

# Rollback last migration
php artisan migrate:rollback

# Rollback and re-run all migrations
php artisan migrate:refresh

# Reset database and run seeders
php artisan migrate:fresh --seed

# Run only seeders
php artisan db:seed

# Run specific seeder
php artisan db:seed --class=LocationSeeder

# Open database CLI
php artisan db

# Check database connection
php artisan db:show
```

---

## 🧪 Testing Commands

```bash
# Run all tests
php artisan test

# Run specific test file
php artisan test tests/Feature/RouteTest.php

# Run tests with coverage
php artisan test --coverage

# Run specific test method
php artisan test --filter test_user_can_create_route
```

---

## 🔧 Artisan Generators

```bash
# Create controller
php artisan make:controller ControllerName

# Create model with migration
php artisan make:model ModelName -m

# Create model with everything (migration, factory, seeder, policy, controller)
php artisan make:model ModelName -mfsc --policy

# Create Livewire component
php artisan make:livewire ComponentName

# Create form request
php artisan make:request StoreRouteRequest

# Create policy
php artisan make:policy PolicyName --model=ModelName

# Create seeder
php artisan make:seeder SeederName

# Create migration
php artisan make:migration create_table_name
```

---

## 🧹 Maintenance Commands

```bash
# Clear all caches
php artisan optimize:clear

# Clear specific caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Cache for production
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Fix code style
./vendor/bin/pint

# Check code style without fixing
./vendor/bin/pint --test
```

---

## 📦 Asset Commands

```bash
# Install npm packages
npm install

# Run Vite dev server (hot reload)
npm run dev

# Build for production
npm run build

# Watch mode (alternative to dev)
npm run watch
```

---

## 🗄️ Storage Commands

```bash
# Create storage link
php artisan storage:link

# Clear uploaded files (be careful!)
php artisan storage:clear

# Check storage permissions
ls -la storage/
ls -la public/storage/
```

---

## 🔐 Authentication Commands

```bash
# Create user via tinker
php artisan tinker
>>> User::factory()->create(['email' => 'test@example.com', 'password' => Hash::make('password')])

# Reset password for user
php artisan tinker
>>> $user = User::where('email', 'user@test.com')->first()
>>> $user->password = Hash::make('newpassword')
>>> $user->save()
```

---

## 🐛 Debugging Commands

```bash
# View logs in real-time (Windows alternative to pail)
Get-Content storage/logs/laravel.log -Wait -Tail 50

# Or use CMD:
tail -f storage/logs/laravel.log

# Open Tinker (Laravel REPL)
php artisan tinker

# Example Tinker commands:
>>> User::count()
>>> Route::pending()->count()
>>> Route::where('name', 'LIKE', '%test%')->get()
>>> DB::table('routes')->where('is_approved', false)->update(['is_approved' => true])
```

---

## 📝 Common Tinker Snippets

```php
// Create test route
$route = Route::create([
    'name' => 'Test Route',
    'location_id' => 1,
    'created_by_user_id' => 1,
    'grade_type' => 'UIAA',
    'grade_value' => '6',
    'route_type' => 'Sport',
    'risk_rating' => 'None',
    'pitch_count' => 1,
    'status' => 'New',
]);

// Approve all pending routes
Route::pending()->each(fn($r) => $r->approve(User::first()));

// Change user role
$user = User::where('email', 'user@test.com')->first();
$user->update(['role_id' => 1]); // 1=Admin, 2=Moderator, 3=Standard, 4=Club

// Get statistics
[
    'total' => Route::count(),
    'approved' => Route::approved()->count(),
    'pending' => Route::pending()->count(),
    'locations' => Location::count(),
];
```

---

## 🚨 Common Issues & Fixes

### Issue: "Class not found"
```bash
# Regenerate autoload files
composer dump-autoload
```

### Issue: "Mix manifest not found"
```bash
# Rebuild assets
npm run build
```

### Issue: "Permission denied" on storage
```bash
# Windows (PowerShell as Admin)
icacls "storage" /grant Everyone:(OI)(CI)F /T
icacls "bootstrap/cache" /grant Everyone:(OI)(CI)F /T

# Linux/Mac
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

### Issue: Database connection failed
```bash
# Check .env file has correct credentials
# For MySQL, verify service is running:
# Windows: Check Services panel
# Linux: sudo systemctl status mysql
# Mac: brew services list
```

### Issue: 419 Page Expired (CSRF)
- Clear browser cookies for localhost
- Check @csrf token in forms
- Verify APP_KEY is set in .env

---

## 📊 Useful Database Queries

```sql
-- Check pending routes count
SELECT COUNT(*) FROM routes WHERE is_approved = 0;

-- Find routes by user
SELECT r.name, u.name as creator
FROM routes r
JOIN users u ON r.created_by_user_id = u.id
WHERE u.email = 'user@test.com';

-- Location hierarchy
SELECT
    l1.name as mountain,
    l2.name as cliff,
    l3.name as sector
FROM locations l1
LEFT JOIN locations l2 ON l2.parent_id = l1.id
LEFT JOIN locations l3 ON l3.parent_id = l2.id
WHERE l1.level = 0;

-- Route statistics by grade
SELECT grade_type, grade_value, COUNT(*) as count
FROM routes
WHERE is_approved = 1
GROUP BY grade_type, grade_value
ORDER BY grade_type, grade_value;
```

---

## 🔗 Important URLs

- **Application:** http://127.0.0.1:8000
- **Vite Dev Server:** http://localhost:5173
- **Register:** http://127.0.0.1:8000/register
- **Login:** http://127.0.0.1:8000/login
- **Routes:** http://127.0.0.1:8000/routes
- **Locations:** http://127.0.0.1:8000/locations
- **Admin Dashboard:** http://127.0.0.1:8000/admin/dashboard
- **User Management:** http://127.0.0.1:8000/admin/users

---

## 📚 Documentation Links

- **Laravel 12:** https://laravel.com/docs/12.x
- **Livewire 3:** https://livewire.laravel.com/docs
- **Tailwind CSS 4:** https://tailwindcss.com/docs
- **Alpine.js:** https://alpinejs.dev/start-here
- **Laravel Breeze:** https://laravel.com/docs/12.x/starter-kits#breeze

---

## 🎯 Development Workflow

1. **Start servers:**
   ```bash
   php artisan serve        # Terminal 1
   npm run dev             # Terminal 2
   ```

2. **Make changes to code**

3. **Test changes in browser:**
   - Livewire/Blade changes: Auto-reload
   - PHP changes: Refresh browser
   - CSS/JS changes: Auto-reload via Vite

4. **Check for errors:**
   - Browser console (F12)
   - Laravel logs: `storage/logs/laravel.log`
   - Terminal output

5. **Commit changes:**
   ```bash
   git add .
   git commit -m "Description of changes"
   ```

---

## 🏗️ Project Structure

```
cragmont/
├── app/
│   ├── Http/
│   │   ├── Controllers/      # Route, Location, Admin controllers
│   │   └── Requests/         # Form validation classes
│   ├── Livewire/             # Livewire components
│   ├── Models/               # Eloquent models
│   └── Policies/             # Authorization policies
├── database/
│   ├── migrations/           # Database schema
│   └── seeders/              # Test data
├── resources/
│   └── views/
│       ├── layouts/          # Base layouts
│       ├── livewire/         # Livewire views
│       ├── routes/           # Route CRUD views
│       ├── locations/        # Location CRUD views
│       └── admin/            # Admin dashboard views
├── routes/
│   └── web.php              # Application routes
├── storage/
│   └── app/public/topos/    # Uploaded topo images
└── public/
    └── storage/             # Symlink to storage/app/public
```

---

**Need help? Check logs in `storage/logs/laravel.log`**
