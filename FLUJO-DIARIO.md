# Flujo diario — cifo-showcase-v2

Entorno Docker independiente del proyecto original. Usa dos servicios: `laravel.test` y `mysql` (imagen **MariaDB 10.5.29**). El servicio se sigue llamando `mysql` para no cambiar `DB_HOST`.

## URLs y puertos

| Qué | Valor |
| --- | --- |
| Front | http://localhost:8081/es |
| Admin | http://localhost:8081/admin |
| Login admin | `admin@cifo.com` / `admin123` |
| Vite (HMR) | http://localhost:5174 |
| MariaDB desde el host | `127.0.0.1:3309` |
| Base de datos | `cifobd` |
| Usuario / password MariaDB | `sail` / `password` |

> El PHP del host es 8.2.12 y el proyecto exige 8.3+. **No ejecutes `php artisan`, `composer` ni `phpunit` en el host**: usá siempre el contenedor `laravel.test`.

## Levantar el proyecto

```bash
cd /home/franco/Proyectos/cifo-showcase-v2
docker compose up -d
docker compose ps
```

Esperá a que `mysql` figure como `healthy`.

Verificación rápida de la BD:

```bash
docker compose exec -T mysql mariadb -usail -ppassword -e "SELECT VERSION();"
# esperado: 10.5.29-MariaDB
```

## Vite (para ver cambios de CSS/JS)

Vite **no queda corriendo** automáticamente y se detiene al reiniciar los contenedores. Si el front/admin se ve sin estilos, `public/hot` probablemente sigue apuntando a `http://localhost:5174` pero Vite no está activo.

Levantalo en una terminal dedicada y dejala abierta:

```bash
docker compose exec -u sail -e npm_config_cache=/tmp/npm-cache \
  laravel.test npm run dev
```

Verificación:

```bash
curl -s -o /dev/null -w "%{http_code}\n" \
  http://localhost:5174/resources/css/app.css
# esperado: 200
```

Alternativa: generar assets estáticos (hay que repetirlo en cada cambio de CSS/JS):

```bash
rm -f public/hot

docker compose exec -u sail -e npm_config_cache=/tmp/npm-cache \
  laravel.test npm run build
```

> Es importante borrar `public/hot` antes de un build estático; si existe, Laravel sigue usando el servidor de Vite.
>
> El plugin de Vite detecta `LARAVEL_SAIL=1` y usa el puerto `VITE_PORT=5174` del `.env`. No hace falta tocar `vite.config.js`.

## Verificar que está todo bien

```bash
docker compose exec -T mysql mariadb -usail -ppassword -D cifobd -e "
SELECT VERSION();
SELECT COUNT(*) AS migrations FROM migrations;
SELECT COUNT(*) AS categories FROM categories;
SELECT COUNT(*) AS projects FROM projects;
SELECT COUNT(*) AS media FROM media;
SELECT COUNT(*) AS site_images FROM site_images;
SELECT COUNT(*) AS users FROM users;
"
```

Valores de referencia al momento de la migración: **10.5.29-MariaDB**, `18` migraciones, `5` categorías, `24` proyectos, `93` media, `4` site_images y `12` usuarios.

```bash
docker compose exec -u sail laravel.test php artisan migrate:status

curl -I http://localhost:8081
# esperado: 302 -> /es

curl -o /dev/null -s -w '%{http_code}\n' \
  http://localhost:8081/storage/1/project-1-1.jpg
# esperado: 200
```

Para la BD de tests:

```bash
docker compose exec -T mysql mariadb -usail -ppassword -e "SHOW DATABASES LIKE 'testing';"
```

La BD `testing` se crea con `docker/mariadb/create-testing-database.sh` la primera vez que se inicializa el volumen `sail-mariadb`.

## Comandos útiles de Laravel

Siempre dentro del contenedor y con `-u sail` para no dejar archivos de `storage/`/caché como root:

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

`logs -f` deja el comando siguiendo la salida; se corta con `Ctrl+C` sin apagar el contenedor.

## Apagar

```bash
docker compose down
```

Conserva el volumen `cifo-showcase-v2_sail-mariadb` (la BD no se pierde). El volumen viejo `cifo-showcase-v2_sail-mysql` puede seguir existiendo para rollback.

> NUNCA usar `docker compose down -v` en este proyecto ni en el original: borra el volumen de la base de datos y perdés los datos.

## Actualizar dependencias PHP

Tu PHP local es 8.2 y el proyecto exige `^8.3`, así que Composer corre dentro de un contenedor:

```bash
docker compose run --rm --no-deps -e COMPOSER_HOME=/.composer \
  laravel.test composer install --no-interaction --prefer-dist
```

Para dependencias JS:

```bash
docker compose exec -u sail -e npm_config_cache=/tmp/npm-cache \
  laravel.test npm ci
```

## Notas

- El proyecto original (`/home/franco/Proyectos/Proyectos-PHP/laravel/cifo-showcase-app`) se levanta y apaga por separado; no comparten contenedores, red ni volúmenes.
- Ambos usan la imagen `sail-8.3/app`. Rebuildeá solo si cambiás el runtime de Sail.
- La BD del fork fue restaurada desde un dump MySQL en MariaDB 10.5.29; no corras `migrate:fresh` ni seeders salvo que quieras borrar/reemplazar los datos.
- MariaDB 10.5 está EOL (fin de soporte comunitario: junio de 2025). Producción usa 10.5.29; a futuro conviene planificar un upgrade a 10.11 o 11.4 LTS.
- SQL JSON: usar `JSON_UNQUOTE(JSON_EXTRACT(...))`. No usar el operador MySQL `->>` porque MariaDB 10.5 no lo soporta.
- Los archivos subidos viven en `storage/app/public` y se sirven vía `public/storage` (symlink creado con `storage:link`).
- El front/admin no usan Redis, Meilisearch, Mailpit ni Selenium: cola, caché y sesiones usan la base de datos, y el correo usa el log (`storage/logs/laravel.log`).
