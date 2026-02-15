---
name: Bug Report
about: Report a bug or issue with SimpleProxy
title: '[BUG] '
labels: bug
assignees: ''
---

## 🐛 Bug Description

A clear and concise description of what the bug is.

## 📋 Steps to Reproduce

1. Set environment variables: `TARGET_HOST=...`, `PUBLIC_HOST=...`
2. Run command: `docker-compose up -d`
3. Access URL: `https://example.com/path`
4. See error: ...

## ✅ Expected Behavior

What you expected to happen.

## ❌ Actual Behavior

What actually happened.

## 🔧 Configuration

**Environment Variables (.env):**
```env
TARGET_HOST=example.com
PUBLIC_HOST=mysite.com
CACHE_MAX_AGE=3600
# ... other relevant settings
```

**Docker Compose Version:**
```
docker-compose --version
```

**Docker Version:**
```
docker --version
```

**SimpleProxy Version:**
- [ ] Latest (`andreaskasper/simpleproxy:latest`)
- [ ] Specific version: ____________

## 📝 Logs

**Proxy logs:**
```
docker-compose logs simpleproxy
```

**Debug logs (if DEBUG_MODE=true):**
```
docker-compose exec simpleproxy tail -n 50 /var/log/apache2/proxy-debug.log
```

## 🌐 Environment

- OS: [e.g., Ubuntu 22.04, macOS 13]
- Backend: [e.g., WordPress 6.4, Laravel 10, custom PHP]
- Reverse Proxy: [e.g., none, Traefik, nginx, Cloudflare]

## 📎 Additional Context

Any other context, screenshots, or information that might help.