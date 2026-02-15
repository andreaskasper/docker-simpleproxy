# Security Policy

## 🔒 Supported Versions

Security updates are provided for the following versions:

| Version | Supported          |
| ------- | ------------------ |
| latest  | :white_check_mark: |
| 2.x.x   | :white_check_mark: |
| 1.x.x   | :x:                |

## 🚨 Reporting a Vulnerability

We take security seriously. If you discover a security vulnerability in SimpleProxy, please report it responsibly.

### How to Report

**DO NOT** open a public GitHub issue for security vulnerabilities.

Instead, please email:

📧 **andreas.kasper@goo1.de**

Include:
- Description of the vulnerability
- Steps to reproduce
- Potential impact
- Suggested fix (if you have one)

### What to Expect

1. **Acknowledgment** — Within 48 hours
2. **Assessment** — Within 1 week
3. **Fix & Release** — Within 2-4 weeks (depending on severity)
4. **Public Disclosure** — After fix is released

### Security Considerations

SimpleProxy is designed to:

✅ **Cache public content** — Safe for publicly accessible pages  
✅ **Exclude sensitive paths** — Use `EXCLUDED_PATHS` for admin/login pages  
✅ **Verify SSL** — Set `ENABLE_SSL_VERIFY=true` in production  
✅ **Hide backend** — Origin server not exposed to public  

⚠️ **SimpleProxy should NOT be used for:**

❌ Caching authenticated content (user-specific pages)  
❌ Proxying payment gateways or financial data  
❌ Storing sensitive user information  
❌ As a WAF or DDoS protection (use Cloudflare/similar)  

## 🛡️ Security Best Practices

### 1. Exclude Sensitive Paths

```env
# ALWAYS exclude admin and authentication endpoints
EXCLUDED_PATHS=/admin,/wp-admin,/login,/auth,/api/private
```

### 2. Enable SSL Verification

```env
# Verify backend SSL certificates
ENABLE_SSL_VERIFY=true
```

### 3. Limit HTTP Methods

```env
# Only allow read operations
ALLOWED_METHODS=GET,HEAD
```

### 4. Use HTTPS

Deploy SimpleProxy behind a reverse proxy with SSL termination:

- Traefik with Let's Encrypt
- nginx with SSL certificates
- Cloudflare SSL

### 5. Keep Updated

```bash
# Pull latest version regularly
docker pull andreaskasper/simpleproxy:latest
docker-compose down
docker-compose up -d
```

### 6. Monitor Logs

```bash
# Check for suspicious activity
docker-compose logs simpleproxy | grep -i "error\|403\|500"
```

### 7. Firewall Backend

Ensure your origin server is NOT publicly accessible:

```bash
# Only allow connections from SimpleProxy IP
iptables -A INPUT -p tcp --dport 80 -s PROXY_IP -j ACCEPT
iptables -A INPUT -p tcp --dport 80 -j DROP
```

## 🔍 Security Headers

SimpleProxy sets these security headers by default:

```apache
X-Frame-Options: SAMEORIGIN
X-Content-Type-Options: nosniff
X-XSS-Protection: 1; mode=block
```

For additional headers, modify `.htaccess` or use a reverse proxy.

## ⚠️ Known Limitations

1. **No WAF** — SimpleProxy is a caching proxy, not a web application firewall
2. **No DDoS protection** — Use Cloudflare or similar services
3. **No rate limiting** — Implement at reverse proxy level
4. **No authentication** — Should not cache authenticated content

## 📝 Security Changelog

Security-related changes are documented in [CHANGELOG.md](CHANGELOG.md) with a 🔒 prefix.

## 🙏 Responsible Disclosure

We follow coordinated disclosure:

1. Reporter notifies us privately
2. We confirm and develop a fix
3. We release the patched version
4. We publicly disclose the issue
5. We credit the reporter (if desired)

## 📧 Contact

For security concerns: **andreas.kasper@goo1.de**  
For general questions: [GitHub Issues](https://github.com/andreaskasper/docker-simpleproxy/issues)

---

Thank you for helping keep SimpleProxy secure! 🛡️
