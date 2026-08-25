# Changelog — create-veldora-app

All notable changes to the `create-veldora-app` npm scaffolder are documented here.

---

## [0.5.0] — 2026-08-25

### Added
- Template now ships with **zero Symfony/Console dependency** — `php veldora` CLI works out-of-the-box using the built-in `Console\Polyfill.php` shim.
- `php veldora ui:list` and `php veldora add <name>` use `executeDirect()` — no third-party packages required.
- Template `bootstrap/autoload.php` auto-loads `Polyfill.php` when Symfony/Console is absent.
- Template now includes anonymous migration classes (prevents duplicate class-name collisions on `make:auth` + existing users migration).
- `php veldora make:auth` scaffolds full auth layer (Login, Register, ForgotPassword, ResetPassword, Profile, EmailVerify) using **100% native `.veldora.php` templates** — no inline PHP, no CDN CSS.

### Changed
- Veldora framework version pin updated to `^0.5.0`.
- Veldora UI version pin updated to `^0.5.0`.
- `veldora` CLI switch-case for `migrate`, `migrate:rollback`, `migrate:fresh`, `down`, `up`, `make:*`, `ui:list`, `add` all call `executeDirect()` directly.

---

## [0.4.0] — 2026-07-15

### Added
- Interactive npx scaffolder with project name prompt.
- Composer dependency install, `APP_KEY` generation, and storage setup.
- Template includes `routes/web.php`, layout, home view, and sample post controller/model/migration.
- `php veldora serve`, `php veldora migrate`, `php veldora make:*`, `php veldora add <component>`.
