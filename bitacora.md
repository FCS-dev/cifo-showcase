# Bitácora de commits

| # | Commit | Resumen |
|---|---|---|
| 1 | `595a484` | Importa la app base (Laravel 13, panel admin y portal público) desde `cifo-showcase-app-mail`. |
| 2 | `cb3cf80` | Aísla Docker a `app + mysql`, añade documentación de flujo y media del proyecto original. |
| 3 | `60daec2` | Adapta `.env.example` al entorno Docker del fork. |
| 4 | `728d58e` | Muestra email de alumnos y docente en el detalle público del proyecto. |
| 5 | `8bb6a70` | Añade página de política de privacidad en `es`, `ca` y `en`, con enlace en footer. |
| 6 | `9cd8f4c` | Añade gestión de imágenes institucionales (`SiteImage`) y de categorías. |
| 7 | `58d831a` | Dinamiza “Nuestros cursos” y agrega badge/descripción corta a categorías. |
| 8 | `5fbeebd` | Cambia el detalle del proyecto para mostrar alumnos como lista, sin desplegable. |
| 9 | `0ebd845` | Migra a MariaDB 10.5.29, corrige búsquedas JSON y añade DB de testing en Docker. |
| 10 | `b787df6` | Documenta en README que el proyecto es un fork independiente. |
| 11 | `c354fc0` | Elimina `.gitignore` internos duplicados. |
| 12 | `2821799` | Elimina el registro público y añade confirmación + redirección al editar perfil/contraseña. |
| 13 | `723f703` | Separa seeders producción/demo, Admin desde `.env`, email inmutable y protecciones de la cuenta Admin. |
| 14 | `95060ab` | Evita el autocompletado en formularios de alta/edición de usuarios. |
| 15 | `d1a554e` | Añade activación/reset de contraseña por enlace, estado “pendiente” y bloqueo de login; quita “olvidé mi contraseña”. |
| 16 | `8e6c86e` | Añade rol `Teacher`, vínculo usuario-profesor 1:1, proyectos limitados por curso, bloqueo de borrado de estudiantes y corrección del modal Cancelar. |
