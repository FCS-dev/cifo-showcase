# Flujo diario — cifo-showcase-v2

Entorno Docker independiente del proyecto original. Solo usa dos servicios: `laravel.test` y `mysql`.

## URLs y puertos

| Qué | Valor |
| --- | --- |
| Front | http://localhost:8081/es |
| Admin | http://localhost:8081/admin |
| Login admin | `admin@cifo.com` / `admin123` |
| Vite (HMR) | http://localhost:5174 |
| MySQL desde el host | `127.0.0.1:3309` |
| Base de datos | `cifobd` |
| Usuario / password MySQL | `sail` / `password` |

## Levantar el proyecto

```bash
cd /home/franco/Proyectos/cifo-showcase-v2
docker compose up -d
docker compose ps
```

Esperá a que `mysql` figure como `healthy`.

## Vite (para ver cambios de CSS/JS)

Obligatorio si `public/hot` no existe o querés HMR. Dejá esta terminal abierta:

```bash
docker compose exec -u sail -e npm_config_cache=/tmp/npm-cache \
  laravel.test npm run dev
```

Alternativa: generar assets estáticos una sola vez (hay que repetirlo en cada cambio de CSS/JS):

```bash
docker compose exec -u sail laravel.test npm run build
```

> El plugin de Vite detecta `LARAVEL_SAIL=1` y usa el puerto `VITE_PORT=5174` del `.env`. No hace falta tocar `vite.config.js`.

## Verificar que está todo bien

```bash
docker compose exec -T mysql mysql -usail -ppassword -e \
  "SELECT COUNT(*) projects FROM cifobd.projects; \
   SELECT COUNT(*) media FROM cifobd.media; \
   SELECT COUNT(*) users FROM cifobd.users;"

docker compose exec -u sail laravel.test php artisan migrate:status

curl -I http://localhost:8081
# esperado: 302 -> /es

curl -o /dev/null -s -w '%{http_code}\n' \
  http://localhost:8081/storage/1/project-1-1.jpg
# esperado: 200
```

## Comandos útiles de Laravel

Siempre con `-u sail` para no dejar archivos de `storage/`/caché como root:

```bash
docker compose exec -u sail laravel.test php artisan optimize:clear
docker compose exec -u sail laravel.test php artisan storage:link
docker compose exec -u sail laravel.test php artisan tinker
docker compose exec -u sail laravel.test php artisan test            # necesita la BD testing
docker compose exec -u sail laravel.test php artisan test --filter=TestName
```

## Logs

```bash
docker compose logs -f laravel.test
docker compose logs -f mysql
```

## Apagar

```bash
docker compose down
```

Conserva el volumen `cifo-showcase-v2_sail-mysql` (la BD no se pierde).

> NUNCA usar `docker compose down -v` en este proyecto ni en el original: borra el volumen de MySQL y perdés los datos.

## Actualizar dependencias PHP

Tu PHP local es 8.2 y el proyecto exige `^8.3`, así que Composer corre dentro del contenedor:

```bash
docker compose run --rm --no-deps -e COMPOSER_HOME=/.composer \
  laravel.test composer install --no-interaction --prefer-dist
```

## Notas

- El proyecto original (`/home/franco/Proyectos/Proyectos-PHP/laravel/cifo-showcase-app`) se levanta y apaga por separado; no comparten contenedores, red ni volúmenes.
- Ambos usan la imagen `sail-8.3/app`. Rebuildeá solo si cambiás el runtime de Sail.
- La BD del fork ya está importada desde `storage/backups/cifobd-v2.sql`; no corras `migrate:fresh` ni seeders salvo que quieras borrar/reemplazar los datos.
- Los archivos subidos viven en `storage/app/public` y se sirven vía `public/storage` (symlink creado con `storage:link`).
- El front/admin no usan Redis, Meilisearch, Mailpit ni Selenium: cola, caché y sesiones usan la base de datos, y el correo usa el log (`storage/logs/laravel.log`).
