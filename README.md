# Verix API Framework

Verix is a lightweight API-first PHP + MySQL framework for React, Electron, Vue, mobile apps, and other separate frontends. It is designed to run on ordinary shared PHP hosting and does not require terminal access on the production server.

## Priorities

- API only, JSON by default
- PHP 8.2+ and MySQL
- Shared-hosting friendly
- `.env` as the runtime configuration source
- JWT access tokens + database-backed refresh tokens
- OTP, email verification and password-reset foundation
- Roles and permissions schema
- PDO prepared statements
- CORS, security headers and database-backed rate limiting
- Database-backed bounded cache, with no file cache by default
- No application log files
- Uploads disabled by default and guarded by per-file size, request-count and total-storage quota limits
- PHPMailer adapter included; add the PHPMailer source folder when needed

## No-terminal production deployment

1. Upload the whole `verix` folder to your PHP hosting.
2. Edit `.env` and enter database, JWT, application, CORS and mail settings.
3. Change `INSTALL_KEY` to a strong random value.
4. POST `install_key=<your key>` to `/public/setup.php` once. This creates the built-in framework tables without SSH/terminal.
5. Immediately set `INSTALL_ENABLED=false` in `.env`.
6. Test `/api/v1/health`.

If your hosting lets you point the domain document root to `public/`, do that. If not, the included root `.htaccess` protects internal framework folders and forwards application requests to `public/index.php`.

## PHPMailer

Put PHPMailer source files here (or change `PHPMAILER_PATH` in `.env`):

`vendor/PHPMailer/src/Exception.php`
`vendor/PHPMailer/src/PHPMailer.php`
`vendor/PHPMailer/src/SMTP.php`

Then set `MAIL_ENABLED=true` and your SMTP values in `.env`.

## Storage policy

Verix intentionally does not write application log files. Your hosting provider or PHP runtime may still maintain infrastructure/error logs outside Verix, which the framework cannot disable. Uploads are disabled unless `UPLOADS_ENABLED=true`. When uploads are enabled, Verix validates MIME type, extension, maximum file size, maximum files per request, and total upload directory quota before saving. Files receive random names and `storage/` is blocked from direct HTTP access.

Database caching is bounded by `CACHE_MAX_ROWS`, and expired cache rows are pruned probabilistically. Rate-limit storage is also bounded by `RATE_LIMIT_MAX_ROWS`.

## Security note

No framework can honestly guarantee 100% security. Verix uses secure defaults, but production security still depends on correct configuration, HTTPS, secure secrets, updated PHP/PHPMailer versions, restrictive hosting permissions, database backups, and application-level authorization rules.

## Request flow

Client → `public/index.php` → bootstrap → router → middleware → controller → service/model → MySQL → JSON response.
