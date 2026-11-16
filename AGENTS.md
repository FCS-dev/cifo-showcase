# AGENTS.md

## Stack

- Laravel 13 on PHP 8.3+ (`composer.json`). The host PHP is 8.2, so Composer and Artisan must run inside the `laravel.test` container.
- Admin UI: Tailwind CSS v4 + DaisyUI v5, Alpine.js + `laravel-precognition-alpine`.
- Public front: vanilla CSS/JS only. No Tailwind or Alpine on the front.
- Database: **MariaDB 10.5.29** (Docker image `mariadb:10.5.29`; production runs the same version). The Laravel connection is `mariadb`. Translatable content uses JSON columns; raw JSON SQL must use `JSON_UNQUOTE(JSON_EXTRACT(...))`, because MariaDB 10.5 does not support MySQL's `->>` operator. MariaDB 10.5 is EOL; plan an upgrade to 10.11 or 11.4 LTS separately.
- Spatie packages in use: permission, translatable, sluggable, media-library.

## Fresh setup

Lockfiles are not committed (`.gitignore` ignores `composer.lock` and `package-lock.json`), so dependency versions are not pinned.

```bash
cp .env.example .env
# Set DB_CONNECTION=mariadb, DB_DATABASE, DB_USERNAME, DB_PASSWORD.

docker run --rm -u "$(id -u):$(id -g)" -e COMPOSER_HOME=/tmp/composer \
  -v "$PWD":/app -w /app composer:2 install --no-interaction --prefer-dist
docker compose build
docker compose up -d

docker compose exec -u sail laravel.test php artisan key:generate
docker compose exec -u sail laravel.test php artisan migrate:fresh --seed
docker compose exec -u sail laravel.test php artisan storage:link
docker compose exec -u sail -e npm_config_cache=/tmp/npm-cache laravel.test npm ci
docker compose exec -u sail -e npm_config_cache=/tmp/npm-cache laravel.test npm run build
```

- Seeded admin login: `admin@cifo.com` / `admin123`.
- The database service is still named `mysql`; its volume is `sail-mariadb`. `docker/mariadb/create-testing-database.sh` creates the `testing` database when the volume is first initialized.
- `composer dev` exists but is not the normal Docker flow; for development run Vite separately inside `laravel.test`.
- `respaldo_cifodb.sql` is a legacy MySQL 8.4 dump of the app database; migrations and seeders are the normal setup path.

## Commands

- Full tests: `docker compose exec -u sail laravel.test composer test`.
- Single test: `docker compose exec -u sail laravel.test php artisan test --filter=TestName` or `... php artisan test tests/Feature/ExampleTest.php`.
- `phpunit.xml` forces `DB_DATABASE=testing`; the MariaDB init script creates it. If the volume already exists and `testing` is missing, create it manually with the `sail` user and `GRANT` on ``testing%``. Tests do not use in-memory SQLite.
- PHP formatter: `docker compose exec -u sail laravel.test composer fix`; check-only: `... composer fix:check`. Config is `.php-cs-fixer.php`; Blade files are excluded. Pint is installed but not wired to any script.
- Vite/HMR: `docker compose exec -u sail -e npm_config_cache=/tmp/npm-cache laravel.test npm run dev`. It stops when the container restarts; a stale `public/hot` causes pages without styles. For a static build, remove `public/hot` first and run `npm run build` in the container.
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
- SQL must stay MariaDB 10.5 compatible: use `JSON_UNQUOTE(JSON_EXTRACT(...))` for raw JSON search, avoid `->>`, `JSON_TABLE` (not available in 10.5) and MySQL-only collations such as `utf8mb4_0900_*`.
- Admin and front UI strings live in `lang/es/{admin,front}.php` and `lang/ca/{admin,front}.php`; `lang/en` lacks those groups. Add both `es` and `ca` keys.
- Admin CRUD follows resource controllers in `app/Http/Controllers/Admin`, FormRequests, and `x-admin.table.*`/`x-admin.ui.*` components. `x-layouts.admin` loads `resources/css/admin.css` and `resources/js/admin.js`.
- The public site uses `x-layouts.app`, `resources/css/app.css`, and `resources/js/app.js`. Keep the two asset stacks separate; new front CSS files must be added to the imports in `resources/css/app.css`.
- Admin themes are custom DaisyUI themes `cifo-light`/`cifo-dark` defined in `resources/css/admin.css`; do not use default DaisyUI theme names.
- Front listings show only `status = published`; the home page shows only `featured = true` published projects.
- Project media uses Spatie's `images` collection, the `is_featured` custom property, and non-queued `thumb`/`card` conversions. Run `storage:link` and update `syncMedia()` when changing uploads.
- Institutional images use `SiteImage` rows with a single `image` Media Library collection. Categories use a single `image` collection plus translatable `badge` and `short_description` fields (max 10 / 150). Front fallbacks are served through `/storage` when media exists.
- Project file types are declared in both `App\Enums\ProjectFileType` and the `project_files.type` migration enum; update both when adding a type.
- Root `images/` contains README screenshots, not public web assets. Public assets live in `public/img/`; `Project` references `images/placeholder.webp`, which does not exist.
