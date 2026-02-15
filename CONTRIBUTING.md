# Contributing to SimpleProxy

Thank you for considering contributing to SimpleProxy! 🎉

## 📋 Ways to Contribute

- 🐛 **Report bugs** — Use [Bug Report template](.github/ISSUE_TEMPLATE/bug_report.md)
- 💡 **Suggest features** — Use [Feature Request template](.github/ISSUE_TEMPLATE/feature_request.md)
- 📝 **Improve documentation** — README, EXAMPLES, comments
- 🔧 **Submit code** — Bug fixes, features, optimizations
- 🌍 **Share use cases** — Help others by sharing your config

## 🚀 Getting Started

### Development Setup

```bash
# Fork the repo on GitHub, then clone your fork
git clone https://github.com/YOUR_USERNAME/docker-simpleproxy.git
cd docker-simpleproxy

# Create a feature branch
git checkout -b feature/my-awesome-feature

# Make your changes
nano src/html/index.php

# Test locally
cp .env.example .env
nano .env  # Configure for your test backend
docker-compose up --build

# Check logs
docker-compose logs -f
```

### Testing Your Changes

1. **Build and run:**
   ```bash
   docker-compose down
   docker-compose up --build
   ```

2. **Test basic functionality:**
   ```bash
   # First request (cache miss)
   curl -I http://localhost/
   
   # Second request (should be cached)
   curl -I http://localhost/
   
   # Test with ETag
   ETAG=$(curl -s -I http://localhost/ | grep -i etag | cut -d' ' -f2)
   curl -I -H "If-None-Match: $ETAG" http://localhost/
   # Should return 304 Not Modified
   ```

3. **Test domain rewriting:**
   ```bash
   curl http://localhost/ | grep "TARGET_HOST"
   # Should NOT find TARGET_HOST, should find PUBLIC_HOST instead
   ```

4. **Test excluded paths:**
   ```bash
   curl -I http://localhost/wp-admin/
   # Should return 403 or fetch from backend (no cache)
   ```

5. **Enable debug mode:**
   ```env
   DEBUG_MODE=true
   ```
   ```bash
   docker-compose restart
   docker-compose exec simpleproxy tail -f /var/log/apache2/proxy-debug.log
   ```

## 📝 Code Style

### PHP Code

- Use 4 spaces for indentation (no tabs)
- Add comments for complex logic
- Use descriptive variable names
- Follow existing code style

**Example:**
```php
// Good
$cacheMaxAge = (int)(getenv('CACHE_MAX_AGE') ?: 3600);
if ($cacheMaxAge > 0) {
    header('Cache-Control: public, max-age=' . $cacheMaxAge);
}

// Bad
$c = getenv('CACHE_MAX_AGE');
if($c>0){header('Cache-Control: public, max-age='.$c);}
```

### Documentation

- Keep README concise, move details to EXAMPLES.md
- Use emoji for section headers (sparingly)
- Add real-world examples
- Update CHANGELOG.md for significant changes

## 🔀 Pull Request Process

1. **Update documentation** if you:
   - Add/change environment variables
   - Add new features
   - Change behavior

2. **Update CHANGELOG.md** under `[Unreleased]` section:
   ```markdown
   ### Added
   - New feature X that does Y
   
   ### Changed
   - Modified Z to improve performance
   
   ### Fixed
   - Resolved bug with ABC
   ```

3. **Update .env.example** if adding new configuration options

4. **Test thoroughly** before submitting:
   - Build from scratch: `docker-compose build --no-cache`
   - Test with real backend (WordPress/Laravel/etc.)
   - Test cache behavior
   - Test error cases

5. **Use the PR template** when creating your pull request

6. **Keep PRs focused** — One feature/fix per PR

7. **Be responsive** — Reply to review comments promptly

## 🐛 Reporting Bugs

Before reporting a bug:

1. **Check existing issues** — Someone may have reported it already
2. **Test with latest version** — `docker pull andreaskasper/simpleproxy:latest`
3. **Enable debug mode** — Set `DEBUG_MODE=true`
4. **Gather logs:**
   ```bash
   docker-compose logs simpleproxy > proxy-logs.txt
   docker-compose exec simpleproxy cat /var/log/apache2/proxy-debug.log > debug-logs.txt
   ```

Then use the [Bug Report template](.github/ISSUE_TEMPLATE/bug_report.md).

## 💡 Suggesting Features

Great features come from real use cases!

Before suggesting:

1. **Check existing issues** — Feature may already be requested
2. **Describe the problem** — What are you trying to solve?
3. **Explain the use case** — Who would benefit?
4. **Propose a solution** — How should it work?

Use the [Feature Request template](.github/ISSUE_TEMPLATE/feature_request.md).

## 📚 Documentation Contributions

Documentation is just as important as code!

**High-value documentation contributions:**

- Real-world configuration examples (EXAMPLES.md)
- Troubleshooting guides
- Integration guides (Traefik, nginx, Cloudflare)
- Performance tuning tips
- Use case tutorials

## 🌍 Sharing Your Use Case

Help others by sharing how you use SimpleProxy!

Create an issue titled "Use Case: [Your Scenario]" with:

- Brief description of your setup
- Your .env configuration
- Performance improvements you've seen
- Any tips or tricks

**Example:**
> **Use Case: Annual Tech Conference**
> 
> We run a tech conference website that gets 50,000 visitors during the 2-week registration period but barely any traffic the rest of the year.
> 
> **Configuration:**
> ```env
> TARGET_HOST=conference-cms.internal
> PUBLIC_HOST=techcon2026.com
> CACHE_MAX_AGE=604800  # 1 week
> EXCLUDED_PATHS=/admin,/registration/payment
> ```
> 
> **Results:**
> - Origin server load reduced from 100% to <1%
> - Page load time: 2.8s → 0.04s
> - Hosting costs reduced 80%
> - No downtime during registration rush

## ⚖️ License

By contributing, you agree that your contributions will be licensed under the [MIT License](LICENSE).

## 🤝 Code of Conduct

**Be respectful:**
- Critique code, not people
- Accept constructive criticism gracefully
- Focus on what's best for the community
- Show empathy towards others

**Examples of unacceptable behavior:**
- Trolling, insulting comments, personal attacks
- Public or private harassment
- Publishing others' private information
- Other unethical or unprofessional conduct

## ❓ Questions?

Not sure if something is a bug or how to implement a feature?

- Use the [Question template](.github/ISSUE_TEMPLATE/question.md)
- Check [EXAMPLES.md](EXAMPLES.md) for configuration help
- Review existing issues and discussions

## 🙏 Thank You!

Every contribution makes SimpleProxy better for everyone. Whether it's code, documentation, bug reports, or just spreading the word — thank you! 🚀
