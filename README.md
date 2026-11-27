# FISA · Cookies

Plugin propio de WordPress para el aviso y la gestión de consentimiento de cookies (Ley 21.719) en los sitios de FISA GL events. Incluye Google Consent Mode v2 y usa [CookieConsent v3](https://github.com/orestbida/cookieconsent) de Orest Bida (licencia MIT, en `assets/vendor/cookieconsent/`).

| | |
|---|---|
| Versión actual | ver `Version:` en [`fisa-cookies.php`](fisa-cookies.php) |
| Repositorio | `mariohcl/fisa-cookies` (público), rama `main` |
| Requiere | WordPress 5.8+, PHP 7.4+ |
| Idiomas | Español e inglés (detecta Polylang; si no, usa el idioma de WordPress) |

---

## Instalación (una vez por sitio)

1. *Plugins → Añadir nuevo → Subir plugin* → zip del plugin (la carpeta dentro del zip debe llamarse `fisa-cookies`).
2. Activar y revisar *Ajustes → Cookies*.
3. Poner el link "Configurar cookies" en el footer del theme (ver [Uso](#uso)).

## Actualizaciones desde GitHub

Archivo: [`inc/actualizaciones.php`](inc/actualizaciones.php). No necesita token porque el repositorio es público.

1. WordPress lee el `Version:` de `fisa-cookies.php` en `main` (cada 3 horas, o al pulsar *Escritorio → Actualizaciones → Vuelve a comprobarlo*).
2. Si es mayor que la instalada, aparece la actualización de **FISA · Cookies**.
3. Al actualizar descarga el zip de `main` y renombra la carpeta `fisa-cookies-main` a la carpeta actual del plugin.
4. Después de actualizar, purgar la caché del sitio (Breeze, Varnish, Cloudflare).

Para publicar un cambio: editar, subir el `Version:` y la constante `FISA_COOKIES_VERSION` en `fisa-cookies.php` (deben coincidir), anotar el cambio en el [historial](#historial-de-versiones) y subir a `main`.

**No editar el plugin en el servidor**: cada actualización reemplaza la carpeta completa con lo que hay en GitHub.

---

## Ajustes (*Ajustes → Cookies*)

| Opción | Qué hace |
|---|---|
| Aviso | Muestra u oculta el aviso. |
| Categorías opcionales | Analítica y Marketing. Desmarcar las que el sitio no use. Las necesarias siempre están activas. |
| Google Consent Mode v2 | Envía el estado de consentimiento a GTM / gtag. El snippet de GTM o gtag.js debe imprimirse después de `wp_head()` o enganchado a `wp_head` con prioridad mayor a 1. |
| YouTube | Cambia `youtube.com/embed` por `youtube-nocookie.com/embed` en el contenido y los embeds de WordPress. |
| Política de privacidad | Página enlazada en el aviso. Con Polylang usa su traducción. |
| Posición | Caja abajo a la izquierda, a la derecha o barra inferior. |
| Colores | Botón principal y su texto. |
| Revisión | Subirla (1 → 2) vuelve a mostrar el aviso a todos los visitantes. Usar cuando cambien las cookies o la política. |

## Uso

**Link "Configurar cookies" en el footer** (obligatorio, permite cambiar o revocar la elección):

```php
<?php if ( function_exists( 'fisa_cookies_link' ) ) fisa_cookies_link(); ?>
```

o el shortcode `[fisa_cookies_link]` (acepta `texto="..."`). En un menú, basta un enlace personalizado con URL `#cookies`.

**Bloquear un script hasta que acepten** (Pixel, Clarity, etc. puestos directo en el theme):

```html
<script type="text/plain" data-category="marketing"> … </script>
```

Categorías: `analytics`, `marketing`.

**En GTM:** las etiquetas de Google (GA4, Ads) respetan Consent Mode solas. Para las de terceros, en "Configuración de consentimiento" exigir `analytics_storage` o `ad_storage`, o dispararlas con el evento personalizado `fisa_cookies_update`.

**Textos:** se pueden cambiar con el filtro `fisa_cookies_texts` (`$t, $lang`).

**Recuperación:** si el aviso rompe algo, en `wp-config.php`:

```php
define( 'FISA_COOKIES_DISABLE', true );
```

Desactiva el aviso, pero el plugin sigue recibiendo actualizaciones.

---

## Estructura

```
fisa-cookies.php              Plugin: opciones, textos ES/EN, Consent Mode, link, ajustes
inc/actualizaciones.php       Actualizaciones desde GitHub
uninstall.php                 Borra las opciones al eliminar el plugin
assets/fisa-cookies.js        Inicialización de CookieConsent
assets/fisa-cookies.css       Estilos propios
assets/icon/                  Ícono del plugin (SVG y PNG 128/256)
assets/vendor/cookieconsent/  CookieConsent v3.x (MIT). Sus medidas en rem se pasaron a px (×16)
                              para que no dependan del font-size del <html> de cada theme;
                              repetir esa conversión si se actualiza la librería.
```

## Historial de versiones

| Versión | Cambios |
|---|---|
| 1.0.3 | El aviso se ve igual en todos los sitios: los tamaños pasan de `rem` a `px`, así no lo achican los themes con `html { font-size: 10px }` (Expomin). Ícono del plugin en `assets/icon/`. |
| 1.0.2 | Prueba del actualizador desde GitHub (sin cambios en el sitio). |
| 1.0.1 | Actualizaciones desde GitHub (`inc/actualizaciones.php`). `FISA_COOKIES_DISABLE` ya no bloquea las actualizaciones. |
| 1.0.0 | Primera versión. |
