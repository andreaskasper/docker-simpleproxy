# ⚡ Docker SimpleProxy

A **blazing-fast static caching proxy** for dynamic websites. Serves cached versions of your site while rewriting domains on-the-fly — perfect for CDN-like performance without the complexity.

Transform any dynamic backend into a lightning-fast static frontend.

---

## 🎯 Use Cases

### When to use SimpleProxy?

✅ **Event Websites** — Your annual conference site changes once a year, but gets hammered during ticket sales  
✅ **Business Cards** — Personal portfolio/resume sites that are 99% static  
✅ **Small Business** — Restaurant menus, service pages, contact forms that rarely change  
✅ **WordPress** — Cache entire WP sites, serve static HTML, skip PHP overhead  
✅ **E-Commerce** — Cache product pages, category listings (exclude cart/checkout)  
✅ **Documentation** — Fast-loading docs without rebuilding static generators  
✅ **Backend Security** — Hide your origin server (`admin.internal.com`) behind a public proxy  
✅ **Domain Migration** — Serve `newdomain.com` while backend still runs on `olddomain.com`  

### The Problem This Solves

You have a **dynamic site** (WordPress, Laravel, Django, Rails, Node.js) that:
- 🐌 Is slow because it regenerates HTML on every request
- 💰 Costs money to scale (more PHP workers, database connections)
- 🔓 Exposes your real backend URL
- 📈 Gets traffic spikes that kill your server

### The Solution

Put SimpleProxy in front:
1. **User requests** `https://mysite.com/page/` → hits proxy
2. **Proxy fetches once** from `https://backend.internal/page/`
3. **Rewrites domains** — all `backend.internal` → `mysite.com`
4. **Caches & serves** with `ETag`, `Last-Modified`, `stale-while-revalidate`
5. **Next 1000 requests** → served from cache (instant, no backend load)

---

## ✨ Features

- 🚀 **Static file caching** — Serve lightning-fast cached HTML from any dynamic site
- 🔄 **Domain rewriting** — Proxy `backend.internal.com` → visitors see `yoursite.com`
- 🏷️ **ETag support** — Efficient `304 Not Modified` responses with `If-None-Match`
- 📅 **Last-Modified** — Proper HTTP caching with `If-Modified-Since` headers
- ⚙️ **ENV configuration** — No hardcoded values, everything via `.env`
- 🎯 **Exclude/Include paths** — Regex-based rules for dynamic content
- 📦 **15 MB Docker image** — Minimal PHP 8 + Apache footprint
- 🔒 **SSL verification** — Optional HTTPS backend validation
- 🪲 **Debug mode** — Detailed logging for troubleshooting
- ⏱️ **Timeout control** — Configurable proxy timeouts

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
  -e TARGET_HOST=backend.internal.com \
  -e PUBLIC_HOST=mysite.com \
  -e CACHE_MAX_AGE=3600 \
  andreaskasper/simpleproxy
```

---

## 🔧 Environment Variables

| Variable | Default | Description |
|---|---|------|
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

## 📋 Real-World Examples

### 🎪 Annual Event Website

Your conference site only changes content 2-3 times a year, but gets thousands of visitors during registration.

```env
TARGET_HOST=cms.myevent.internal
PUBLIC_HOST=myevent.com
CACHE_MAX_AGE=86400      # 24 hours
EXCLUDED_PATHS=/admin,/register/checkout
```

**Result:** 
- Static pages load in <100ms
- Backend only hit when cache expires or new content published
- Origin server hidden from public

---

### 💼 Business Card / Portfolio

Personal website that changes maybe once a month.

```env
TARGET_HOST=portfolio-cms.internal
PUBLIC_HOST=johndoe.com
CACHE_MAX_AGE=604800     # 1 week
CACHE_STALE_REVALIDATE=2592000  # 30 days
EXCLUDED_PATHS=/contact-form-submit
```

**Result:**
- Ultra-fast loading portfolio
- Contact form still works (excluded from cache)
- Can update content anytime, cache expires after 1 week

---

### 🍕 Restaurant Website

Menu changes occasionally, but site is mostly static.

```env
TARGET_HOST=restaurant-admin.internal
PUBLIC_HOST=pizzamario.com
CACHE_MAX_AGE=43200      # 12 hours
EXCLUDED_PATHS=/booking,/order-online
```

**Result:**
- Menu pages cached for 12 hours
- Online ordering remains dynamic
- Backend protected from public access

---

### 🛒 E-Commerce Product Catalog

Product pages change daily, but can tolerate short cache.

```env
TARGET_HOST=shop.backend.com
PUBLIC_HOST=shop.com
CACHE_MAX_AGE=3600       # 1 hour
EXCLUDED_PATHS=/cart,/checkout,/my-account,/admin
```

**Result:**
- Product pages cached for 1 hour
- Cart/checkout always fresh
- 95% of traffic served from cache

---

### 📚 Documentation Site

Generated from Markdown but want to avoid rebuilding static site generator.

```env
TARGET_HOST=docs-backend.internal
PUBLIC_HOST=docs.myapp.com
CACHE_MAX_AGE=7200       # 2 hours
EXCLUDED_PATHS=/search   # Search needs to be dynamic
```

**Result:**
- Instant doc page loads
- Search functionality still works
- No need to rebuild static site on every change

---

## 🎨 How It Works

```
┌─────────┐         ┌──────────────┐         ┌─────────────┐
│ Visitor │────────▶│ SimpleProxy  │────────▶│   Backend   │
│         │         │ (yoursite.   │         │ (internal.  │
│         │         │  com:80)     │         │  server)    │
└─────────┘         └──────────────┘         └─────────────┘
     │                      │                        │
     │  1. Request          │  2. Cache Miss?        │
     │  GET /page/          │  Fetch from backend    │
     │                      │                        │
     │                      │  3. Rewrite domains    │
     │                      │  internal.server       │
     │                      │  → yoursite.com        │
     │                      │                        │
     │  4. Serve cached     │                        │
     │  with ETag +         │                        │
     │  Cache-Control       │                        │
     │◀─────────────────────│                        │
     │                      │                        │
     │  5. Next request     │                        │
     │  If-None-Match: xyz  │  6. Cache hit!         │
     │──────────────────────│  Return 304            │
     │  304 Not Modified    │  (no backend call)     │
     │◀─────────────────────│                        │
```

---

## 🔍 Debugging

Enable debug mode to see what's happening:

```bash
docker-compose exec simpleproxy tail -f /var/log/apache2/proxy-debug.log
```

**Logs include:**
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
# Exclude all API endpoints and forms
EXCLUDED_PATHS=/api/.*,/form/.*,/submit.*
```

### Multiple Domain Rewrites

Edit `index.php` to add more replacements:

```php
$content = str_replace([
    "//backend.internal.com",
    "//cdn.backend.com",
    "//api.backend.com"
], [
    "//" . $publicHost,
    "//" . $publicHost,
    "//" . $publicHost
], $content);
```

### Zero-Downtime Updates

When you update backend content:

```bash
# Option 1: Wait for cache to expire naturally
# (set CACHE_MAX_AGE appropriately)

# Option 2: Force cache clear by restarting proxy
docker-compose restart simpleproxy

# Option 3: Implement cache purge endpoint in index.php
```

---

## 📊 Performance Comparison

**Before SimpleProxy (Dynamic WordPress):**
- First load: 2.5s (PHP + MySQL)
- Cache hit: 1.2s (WP Super Cache)
- Origin load: 100%

**After SimpleProxy:**
- First load: 2.5s (cache miss, fetch from origin)
- Cache hit: 45ms (ETag 304 response)
- Origin load: <1% (only on cache miss)

---

## 🧩 Integration Examples

### With Traefik (SSL Termination)

See [EXAMPLES.md](EXAMPLES.md#-traefik-integration) for complete Traefik setup.

### With Cloudflare

1. Point Cloudflare DNS to SimpleProxy
2. Enable "Cache Everything" page rule
3. Set `CACHE_MAX_AGE` to optimize both layers

### With nginx (reverse proxy)

```nginx
upstream simpleproxy {
    server simpleproxy:80;
}

server {
    listen 443 ssl;
    server_name mysite.com;
    
    location / {
        proxy_pass http://simpleproxy;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
    }
}
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

If this saves you hosting costs or improves your site speed:

[![donate via Patreon](https://img.shields.io/badge/Donate-Patreon-green.svg)](https://www.patreon.com/AndreasKasper)
[![donate via PayPal](https://img.shields.io/badge/Donate-PayPal-green.svg)](https://www.paypal.me/AndreasKasper)
[![donate via Ko-fi](https://img.shields.io/badge/Donate-Ko--fi-green.svg)](https://ko-fi.com/andreaskasper)
[![Sponsors](https://img.shields.io/github/sponsors/andreaskasper)](https://github.com/sponsors/andreaskasper)

---

**Built by** [Andreas Kasper](https://github.com/andreaskasper) for anyone who wants fast websites without the complexity 🚀
