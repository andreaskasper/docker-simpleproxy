# ⚡ Docker SimpleProxy

A **blazing-fast static caching proxy** for WordPress and dynamic websites. Serves cached versions of your site while rewriting domains on-the-fly — perfect for CDN-like performance without the complexity.

---

## ✨ Features

- 🚀 **Static file caching** — Serve lightning-fast cached HTML from your WordPress site
- 🔄 **Domain rewriting** — Proxy `ptr.example.com` → visitors see `example.com`
- 🏷️ **ETag support** — Efficient `304 Not Modified` responses with `If-None-Match`
- 📅 **Last-Modified** — Proper HTTP caching with `If-Modified-Since` headers
- ⚙️ **ENV configuration** — No hardcoded values, everything via `.env`
- 🎯 **Exclude/Include paths** — Regex-based rules for dynamic content
- 📦 **15 MB Docker image** — Minimal PHP 8 + Apache footprint
- 🔒 **SSL verification** — Optional HTTPS backend validation
- 🪵 **Debug mode** — Detailed logging for troubleshooting
- ⏱️ **Timeout control** — Configurable proxy timeouts

---

## 🎯 Use Case

You have a WordPress site running on `ptr.example.com` (your origin). You want:

1. **Public users** to access `example.com` (the proxy)
2. **Cached static HTML** served instantly (no WordPress overhead)
3. **Domain rewrites** in all HTML/CSS/JS (links point to `example.com`, not `ptr.example.com`)
4. **Cloudflare-ready** caching headers for edge caching

This proxy sits between users and WordPress, caching everything and rewriting domains.

---

## 🚀 Quick Start

### Docker Compose (Recommended)

```bash
git clone https://github.com/andreaskasper/docker-simpleproxy.git
cd docker-simpleproxy
cp .env.example .env
# Edit .env with your settings
docker-compose up -d
```

### Docker Run

```bash
docker run -d \
  -p 80:80 \
  -e TARGET_HOST=ptr.example.com \
  -e PUBLIC_HOST=example.com \
  -e CACHE_MAX_AGE=3600 \
  andreaskasper/simpleproxy
```

---

## 🔧 Environment Variables

| Variable | Default | Description |
|---|---|---|
| **Core Settings** | | |
| `TARGET_HOST` | `example.com` | Backend origin server (without `https://`) |
| `PUBLIC_HOST` | _(auto)_ | Public domain served to users (auto-detected from `HTTP_HOST`) |
| `PROXY_SCHEME` | `https` | Backend protocol: `https` or `http` |
| **Caching** | | |
| `CACHE_MAX_AGE` | `3600` | Browser cache duration in seconds (1 hour default) |
| `CACHE_STALE_REVALIDATE` | `86400` | Stale-while-revalidate window (24 hours) |
| `CACHE_STALE_ERROR` | `86400` | Stale-if-error window (24 hours) |
| `ENABLE_ETAG` | `true` | Generate ETag headers for 304 responses |
| **Path Control** | | |
| `EXCLUDED_PATHS` | `/wp-admin,/wp-login.php` | Comma-separated paths to **never cache** (regex supported) |
| `INCLUDED_PATHS` | `.*` | Only cache paths matching this regex (`.* `= all) |
| **Performance** | | |
| `PROXY_TIMEOUT` | `30` | Backend request timeout in seconds |
| `ENABLE_GZIP` | `true` | Compress responses with gzip |
| **Security** | | |
| `ENABLE_SSL_VERIFY` | `true` | Verify HTTPS certificates on backend |
| `ALLOWED_METHODS` | `GET,HEAD` | HTTP methods allowed through proxy |
| **Debugging** | | |
| `DEBUG_MODE` | `false` | Enable verbose logging to `/var/log/apache2/proxy-debug.log` |

---

## 📋 Example Configurations

### WordPress Behind Proxy

```env
TARGET_HOST=wp.mysite.internal
PUBLIC_HOST=mysite.com
CACHE_MAX_AGE=7200
EXCLUDED_PATHS=/wp-admin,/wp-login.php,/wp-json,/cart,/checkout
ENABLE_ETAG=true
```

### Development Mode

```env
TARGET_HOST=localhost:8080
PROXY_SCHEME=http
CACHE_MAX_AGE=0
DEBUG_MODE=true
ENABLE_SSL_VERIFY=false
```

### Maximum Performance

```env
TARGET_HOST=origin.example.com
CACHE_MAX_AGE=86400
CACHE_STALE_REVALIDATE=604800
EXCLUDED_PATHS=/admin,/api
ENABLE_GZIP=true
```

---

## 🎨 How It Works

1. **User requests** `https://example.com/page/` → hits proxy
2. **Proxy checks** if path is excluded (admin, login, etc.)
3. **Fetches** from `https://ptr.example.com/page/`
4. **Rewrites** all `ptr.example.com` → `example.com` in HTML
5. **Caches** with headers: `Cache-Control`, `ETag`, `Last-Modified`
6. **Serves** static HTML blazing fast
7. **Next request** with `If-None-Match` → `304 Not Modified`

---

## 🔍 Debugging

Enable debug mode to see what's happening:

```bash
docker-compose exec simpleproxy tail -f /var/log/apache2/proxy-debug.log
```

Logs include:
- Requested path and method
- Cache hit/miss decisions
- Excluded/included path matches
- Backend response codes
- Domain rewrite counts

---

## 🛠️ Advanced Usage

### Custom .htaccess Rules

Mount your own `.htaccess`:

```yaml
volumes:
  - ./custom.htaccess:/var/www/html/.htaccess:ro
```

### Exclude Regex Patterns

```env
# Exclude all JSON endpoints and cart pages
EXCLUDED_PATHS=/wp-json/.*,/cart.*,/checkout.*
```

### Multiple Domain Rewrites

Edit `index.php` to add more replacements:

```php
$content = str_replace([
    "//ptr.example.com",
    "//cdn.example.com",
    "//api.example.com"
], [
    "//" . $publicHost,
    "//" . $publicHost,
    "//" . $publicHost
], $content);
```

---

## 🐳 Docker Hub

[![Docker Pulls](https://img.shields.io/docker/pulls/andreaskasper/simpleproxy.svg)](https://hub.docker.com/r/andreaskasper/simpleproxy)
![Image Size](https://img.shields.io/docker/image-size/andreaskasper/simpleproxy/latest)
[![GitHub Issues](https://img.shields.io/github/issues/andreaskasper/docker-simpleproxy.svg)](https://github.com/andreaskasper/docker-simpleproxy/issues)

---

## 🤝 Contributing

Pull requests welcome! For major changes:

1. Fork the repo
2. Create a feature branch
3. Test with `docker-compose up --build`
4. Submit PR with description

---

## 📝 License

MIT License - see [LICENSE](LICENSE) file

---

## 💖 Support

If this saves you time or money:

[![donate via Patreon](https://img.shields.io/badge/Donate-Patreon-green.svg)](https://www.patreon.com/AndreasKasper)
[![donate via PayPal](https://img.shields.io/badge/Donate-PayPal-green.svg)](https://www.paypal.me/AndreasKasper)
[![donate via Ko-fi](https://img.shields.io/badge/Donate-Ko--fi-green.svg)](https://ko-fi.com/andreaskasper)
[![Sponsors](https://img.shields.io/github/sponsors/andreaskasper)](https://github.com/sponsors/andreaskasper)

---

**Built by** [Andreas Kasper](https://github.com/andreaskasper) for the WordPress & dance community 🕺💃
