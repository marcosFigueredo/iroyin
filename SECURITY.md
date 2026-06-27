# Security Policy

## Supported Versions

| Version | Supported |
| --- | --- |
| 1.0.x | Yes |

Only the latest release receives security fixes.

---

## Reporting a Vulnerability

**Please do not open a public GitHub issue for security vulnerabilities.**

Report vulnerabilities by e-mail to:

**marcos.b.figueredo@gmail.com**

Include in your report:

- A description of the vulnerability
- Steps to reproduce
- Potential impact
- Suggested fix (if any)

You will receive an acknowledgement within **48 hours** and a status update within **7 days**.

If the vulnerability is confirmed, a fix will be released as soon as possible and credited to you in the changelog (unless you prefer to remain anonymous).

---

## Security Design Notes

The following decisions were made intentionally to reduce the attack surface:

- **No public registration** — user accounts are created exclusively by administrators through the admin panel
- **Weather API key** — stored in the database and proxied server-side via `/api/clima`; never sent to the browser or included in the public `/api/config` response
- **Inactive users** — users with `active = false` cannot authenticate even with valid credentials
- **SSL verification** — all outbound HTTP requests (RSS feed fetching, weather API) use the system CA bundle; `CURLOPT_SSL_VERIFYPEER` is never disabled
- **Kiosk display** — `public/index.html` is intentionally unauthenticated; it contains no sensitive data and communicates only with public read-only API endpoints
- **Admin routes** — protected by `auth` middleware; system-level routes (users, feeds, institution, configuration) additionally require the `admin` role
