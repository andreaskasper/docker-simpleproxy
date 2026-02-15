---
name: Question / Help
about: Ask a question or get help with configuration
title: '[QUESTION] '
labels: question
assignees: ''
---

## ❓ Question

What do you need help with?

## 🎯 What I'm Trying to Do

Describe your goal or use case.

**Example:**
> I want to proxy my WordPress site from `wp.internal.com` to `mysite.com` and cache everything except `/wp-admin` and checkout pages.

## 🔧 Current Configuration

**My .env file:**
```env
TARGET_HOST=...
PUBLIC_HOST=...
CACHE_MAX_AGE=...
# ... other settings
```

**My docker-compose.yml (if modified):**
```yaml
# paste here if you made changes
```

## ❌ What's Not Working

- [ ] Cache not working as expected
- [ ] Domain rewriting issues
- [ ] Backend timeouts
- [ ] SSL/HTTPS problems
- [ ] Path exclusion not working
- [ ] Performance issues
- [ ] Other: ____________

## 📝 What I've Tried

List troubleshooting steps you've already taken.

- [ ] Checked debug logs (`DEBUG_MODE=true`)
- [ ] Verified TARGET_HOST is accessible
- [ ] Tested with curl
- [ ] Checked backend server logs
- [ ] Read [EXAMPLES.md](https://github.com/andreaskasper/docker-simpleproxy/blob/main/EXAMPLES.md)

## 📎 Additional Information

Logs, screenshots, or any other context that might help.