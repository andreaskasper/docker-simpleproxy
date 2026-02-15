# 📚 Configuration Examples

Real-world configuration examples for common use cases.

---

## 🎯 WordPress Production Setup

Perfect for serving a fast, cached version of your WordPress site.

```env
# Backend WordPress server
TARGET_HOST=wp.internal.example.com
PUBLIC_HOST=example.com
PROXY_SCHEME=https

# Aggressive caching (2 hours)
CACHE_MAX_AGE=7200
CACHE_STALE_REVALIDATE=604800
CACHE_STALE_ERROR=604800

# Exclude dynamic WordPress paths
EXCLUDED_PATHS=/wp-admin,/wp-login.php,/wp-json,/xmlrpc.php,/wp-cron.php,/cart,/checkout,/my-account

# Performance optimization
ENABLE_GZIP=true
ENABLE_ETAG=true
PROXY_TIMEOUT=45

# Production security
ENABLE_SSL_VERIFY=true
ALLOWED_METHODS=GET,HEAD
DEBUG_MODE=false
```

**Docker Compose:**
```yaml
version: '3.8'
services:
  proxy:
    image: andreaskasper/simpleproxy:latest
    ports:
      - "80:80"
      - "443:443"  # With SSL termination via Traefik/Nginx
    env_file: .env
    restart: always
```

---

## 🧪 Development & Testing

Local development with no caching and verbose logging.

```env
TARGET_HOST=localhost:8080
PUBLIC_HOST=dev.local
PROXY_SCHEME=http

# No caching in dev
CACHE_MAX_AGE=0
CACHE_STALE_REVALIDATE=0
CACHE_STALE_ERROR=0

# No exclusions - cache everything
EXCLUDED_PATHS=

# Developer-friendly settings
ENABLE_SSL_VERIFY=false
DEBUG_MODE=true
PROXY_TIMEOUT=120
```

**Access logs:**
```bash
docker-compose logs -f simpleproxy
docker-compose exec simpleproxy tail -f /var/log/apache2/proxy-debug.log
```

---

## 🌍 Multi-Domain Rewriting

Proxy multiple backend domains to one frontend domain.

```env
TARGET_HOST=api.backend.com
PUBLIC_HOST=mysite.com

# Standard settings
CACHE_MAX_AGE=3600
EXCLUDED_PATHS=/admin,/auth,/api/v1/webhook
```

**Advanced rewriting** - edit `index.php` to handle multiple domains:

```php
// Around line 130 in index.php
$content = str_replace([
    "//api.backend.com",
    "//cdn.backend.com",
    "//assets.backend.com",
    "//images.backend.com"
], [
    "//mysite.com",
    "//mysite.com/cdn",
    "//mysite.com/assets",
    "//mysite.com/images"
], $content);
```

---

## ⚡ Maximum Performance

Edge-optimized configuration for CDN-like speed.

```env
TARGET_HOST=origin.example.com
PUBLIC_HOST=example.com
PROXY_SCHEME=https

# Ultra-long cache (24 hours)
CACHE_MAX_AGE=86400
CACHE_STALE_REVALIDATE=2592000  # 30 days
CACHE_STALE_ERROR=2592000

# Minimal exclusions
EXCLUDED_PATHS=/admin

# Performance tuning
ENABLE_GZIP=true
ENABLE_ETAG=true
PROXY_TIMEOUT=20

# Trust backend SSL
ENABLE_SSL_VERIFY=true
ALLOWED_METHODS=GET,HEAD
```

**With Cloudflare:**
- Set Cloudflare Page Rules to respect origin cache headers
- Enable "Cache Everything" for static assets
- Use this proxy as the origin server

---

## 🛡️ High-Security Configuration

For sensitive content with strict controls.

```env
TARGET_HOST=secure.internal.company.com
PUBLIC_HOST=public.company.com
PROXY_SCHEME=https

# Moderate caching
CACHE_MAX_AGE=1800
CACHE_STALE_REVALIDATE=3600
CACHE_STALE_ERROR=3600

# Strict exclusions (regex patterns)
EXCLUDED_PATHS=/admin.*,/user.*,/account.*,/api/.*,/auth.*,/oauth.*

# Security first
ENABLE_SSL_VERIFY=true
ALLOWED_METHODS=GET
PROXY_TIMEOUT=15
DEBUG_MODE=false
```

**Additional .htaccess rules** (mount custom file):
```apache
# Block bad bots
RewriteCond %{HTTP_USER_AGENT} (bot|crawler|spider) [NC]
RewriteRule .* - [F,L]

# IP whitelist
Require ip 192.168.1.0/24
Require ip 10.0.0.0/8
```

---

## 🎨 Static Site Generation

Cache entire sites for instant loading.

```env
TARGET_HOST=gatsby.build.internal
PUBLIC_HOST=myblog.com
PROXY_SCHEME=https

# Permanent caching (7 days)
CACHE_MAX_AGE=604800
CACHE_STALE_REVALIDATE=2592000
CACHE_STALE_ERROR=2592000

# No exclusions - everything is static
EXCLUDED_PATHS=

# Optimize for static content
ENABLE_GZIP=true
ENABLE_ETAG=true
PROXY_TIMEOUT=10
ALLOWED_METHODS=GET,HEAD
```

---

## 🔄 Staging Environment

Proxy staging site with moderate caching.

```env
TARGET_HOST=staging.internal.example.com
PUBLIC_HOST=staging.example.com
PROXY_SCHEME=https

# Short cache for testing (5 minutes)
CACHE_MAX_AGE=300
CACHE_STALE_REVALIDATE=600
CACHE_STALE_ERROR=600

# Common staging exclusions
EXCLUDED_PATHS=/wp-admin,/preview,/draft

# Staging-friendly settings
ENABLE_SSL_VERIFY=false  # Self-signed certs OK
DEBUG_MODE=true
PROXY_TIMEOUT=60
```

---

## 🎬 Media/Asset CDN

Serve media files with ultra-long caching.

```env
TARGET_HOST=media-origin.example.com
PUBLIC_HOST=cdn.example.com
PROXY_SCHEME=https

# Extreme caching (30 days)
CACHE_MAX_AGE=2592000
CACHE_STALE_REVALIDATE=7776000  # 90 days
CACHE_STALE_ERROR=7776000

# Only serve media
INCLUDED_PATHS=/images/.*|/videos/.*|/downloads/.*|/assets/.*

# Media optimization
ENABLE_GZIP=false  # Media already compressed
ENABLE_ETAG=true
PROXY_TIMEOUT=120  # Large files may take time
```

---

## 📱 API Gateway Cache

Cache public API responses.

```env
TARGET_HOST=api.backend.internal
PUBLIC_HOST=api.example.com
PROXY_SCHEME=https

# API-appropriate cache (10 minutes)
CACHE_MAX_AGE=600
CACHE_STALE_REVALIDATE=1200
CACHE_STALE_ERROR=3600

# Cache only GET endpoints
EXCLUDED_PATHS=/v1/auth.*,/v1/webhook.*,/v1/admin.*
INCLUDED_PATHS=/v1/.*

# API settings
ALLOWED_METHODS=GET,HEAD
ENABLE_ETAG=true
ENABLE_GZIP=true
PROXY_TIMEOUT=30
```

---

## 🧩 Traefik Integration

Use with Traefik reverse proxy for SSL termination.

**docker-compose.yml:**
```yaml
version: '3.8'

services:
  traefik:
    image: traefik:v2.10
    command:
      - --providers.docker=true
      - --entrypoints.web.address=:80
      - --entrypoints.websecure.address=:443
      - --certificatesresolvers.letsencrypt.acme.email=you@example.com
      - --certificatesresolvers.letsencrypt.acme.storage=/acme.json
      - --certificatesresolvers.letsencrypt.acme.httpchallenge.entrypoint=web
    ports:
      - \"80:80\"
      - \"443:443\"
    volumes:
      - /var/run/docker.sock:/var/run/docker.sock:ro
      - ./acme.json:/acme.json

  simpleproxy:
    image: andreaskasper/simpleproxy:latest
    env_file: .env
    labels:
      - \"traefik.enable=true\"
      - \"traefik.http.routers.proxy.rule=Host(`example.com`)\"
      - \"traefik.http.routers.proxy.entrypoints=websecure\"
      - \"traefik.http.routers.proxy.tls.certresolver=letsencrypt\"
```

---

## 🎯 Tips & Tricks

### Clear Browser Cache
Force refresh to test cache changes:
```bash
curl -H \"Cache-Control: no-cache\" https://example.com/test-page/
```

### Monitor Cache Performance
Check if ETag is working:
```bash
# First request
curl -I https://example.com/page/

# Second request with ETag
curl -I -H \"If-None-Match: <etag-from-first-request>\" https://example.com/page/
# Should return 304 Not Modified
```

### Debug Domain Rewrites
Count replacements:
```bash
docker-compose exec simpleproxy grep \"Domain rewrites\" /var/log/apache2/proxy-debug.log
```

### Health Check
```bash
curl -f http://localhost/ && echo \"OK\" || echo \"FAIL\"
```

---

Need more examples? [Open an issue](https://github.com/andreaskasper/docker-simpleproxy/issues) with your use case!
