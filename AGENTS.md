# AGENTS.md

## Stack

- Laravel 13 on PHP 8.3+ (`composer.json`); Composer will fail on PHP 8.2.
- Admin UI: Tailwind CSS v4 + DaisyUI v5, Alpine.js + `laravel-precognition-alpine`.
- Public front: vanilla CSS/JS only. No Tailwind or Alpine on the front.
- Database: MySQL/MariaDB. `.env.example` still says SQLite, but admin search uses MySQL JSON (`->>`) and `YEAR()`; SQLite will break those queries.
- Spatie packages in use: permission, translatable, sluggable, media-library.

## Fresh setup

Lockfiles are not committed (`.gitignore` ignores `composer.lock` and `package-lock.json`), so dependency versions are not pinned.

```bash
cp .env.example .env
# Set DB_CONNECTION=mysql (or mariadb), DB_DATABASE, DB_USERNAME, DB_PASSWORD.
composer update        # There is no lockfile for `composer install` to honor.
php artisan key:generate
php artisan migrate:fresh --seed
php artisan storage:link
npm install            # .npmrc sets ignore-scripts=true
npm run build
```

- Seeded admin login: `admin@cifo.com` / `admin123`.
- `composer dev` runs the PHP server, queue listener, Pail, and Vite together.
- `respaldo_cifodb.sql` is a MySQL 8.4 dump of the app database; migrations and seeders are the normal setup path.

## Commands

- Full tests: `composer test` (clears config, then runs `php artisan test`).
- Single test: `php artisan test --filter=TestName` or `php artisan test tests/Feature/ExampleTest.php`.
- `phpunit.xml` forces `DB_DATABASE=testing`; create that MySQL/MariaDB database before running tests. Tests do not use in-memory SQLite.
- PHP formatter: `composer fix`; check-only: `composer fix:check`. Config is `.php-cs-fixer.php`; Blade files are excluded. Pint is installed but not wired to any script.
- There is no JS/CSS linter, typecheck, or CI config.
- Only placeholder tests exist. `tests/Feature/ExampleTest.php` asserts 200 on `/`, but `/` returns a 302 redirect to `/es`, so it fails; do not treat `composer test` as a clean baseline until that test is fixed.

## Routing and access

- Public routes are locale-prefixed `/{locale}` with an `es|ca` constraint; `/` redirects to `/es`. The `locale` middleware sets the app locale and URL defaults.
- Admin routes live under `/admin` with `auth`, `verified`, and `admin.locale`. They are not locale-prefixed; admin locale resolution is user preference, then session, then `es`.
- Route names are inconsistent: admin projects use `admin.projects.*`, while the other admin resources keep unprefixed names (`categories.index`, `users.index`, etc.). Check `routes/web.php` before generating URLs.
- `User` does not implement `MustVerifyEmail`, so the `verified` middleware currently does not enforce email verification.
- Every admin action calls `Gate::authorize('entity.action')`. Roles (`Admin`, `Editor`, `Viewer`) and permission strings come from `RolesAndPermissionsSeeder`; add new permission strings there too.

## Conventions

- Models use Laravel 13 `#[Fillable([...])]` attributes, not `$fillable`.
- Translatable content is stored as JSON for `es` and `ca` via Spatie. Slugs are generated for both locales and are not regenerated on update (`doNotGenerateSlugsOnUpdate`). `resolveRouteBinding` searches all locales because route binding runs before the `locale` middleware; copy that pattern for new slugged models.
- Admin and front UI strings live in `lang/es/{admin,front}.php` and `lang/ca/{admin,front}.php`; `lang/en` lacks those groups. Add both `es` and `ca` keys.
- Admin CRUD follows resource controllers in `app/Http/Controllers/Admin`, FormRequests, and `x-admin.table.*`/`x-admin.ui.*` components. `x-layouts.admin` loads `resources/css/admin.css` and `resources/js/admin.js`.
- The public site uses `x-layouts.app`, `resources/css/app.css`, and `resources/js/app.js`. Keep the two asset stacks separate; new front CSS files must be added to the imports in `resources/css/app.css`.
- Admin themes are custom DaisyUI themes `cifo-light`/`cifo-dark` defined in `resources/css/admin.css`; do not use default DaisyUI theme names.
- Front listings show only `status = published`; the home page shows only `featured = true` published projects.
- Project media uses Spatie's `images` collection, the `is_featured` custom property, and non-queued `thumb`/`card` conversions. Run `storage:link` and update `syncMedia()` when changing uploads.
- Project file types are declared in both `App\Enums\ProjectFileType` and the `project_files.type` migration enum; update both when adding a type.
- Root `images/` contains README screenshots, not public web assets. Public assets live in `public/img/`; `Project` references `images/placeholder.webp`, which does not exist.
