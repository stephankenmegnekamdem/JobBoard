# JobBoard

A Laravel application scaffolded from the **official Laravel React starter kit** (`laravel/react-starter-kit`). This document explains the choices that were made when the project was created, so anyone picking up the repository understands what is already in place and why.

At the time of writing, the app contains only the starter kit itself. No job-board-specific features (jobs, companies, applications, etc.) have been built yet.

---

## Summary of choices

| Area | Choice |
|---|---|
| Framework | Laravel 13 (`laravel/framework` ^13.17, locked at 13.38) |
| PHP | 8.3 minimum; CI runs on 8.5 |
| Starter kit | Laravel React starter kit |
| Frontend | React 19 + TypeScript, served through Inertia.js 3 |
| Build tool | Vite 8 via **Vite+** (`vite-plus`), with the React Compiler enabled |
| Styling | Tailwind CSS 4 + shadcn/ui components (Radix primitives, "new-york" style, neutral base colour) |
| Icons | Lucide |
| Authentication | Laravel Fortify (headless auth backend), **not** Breeze or Jetstream |
| Auth features enabled | Registration, password reset, email verification, two-factor auth (TOTP), passkeys (WebAuthn) |
| Teams | Multi-tenant teams with Owner / Admin / Member roles and email invitations |
| Database | SQLite (local file), also SQLite in-memory for tests |
| Session / cache / queue | Database driver for all three |
| Mail | `log` driver locally (emails are written to the log, not sent) |
| Testing | Pest 5 |
| Static analysis | Larastan (PHPStan) at level 7 |
| Code style | Laravel Pint (Laravel preset) for PHP; Vite+ `check`/`fmt` for TypeScript |
| Routes in JS | Laravel Wayfinder (type-safe route helpers generated into `resources/js/routes`) |
| CI | GitHub Actions on push to `main` and on pull requests |
| Dependabot | Weekly updates for GitHub Actions only |

---

## Backend

### Laravel 13 with the React starter kit

The project was created with `laravel new` and the **React** starter kit option. That kit ships Inertia + React + TypeScript + Tailwind + shadcn/ui and a full authentication flow. The `composer.json` name `laravel/react-starter-kit` is the giveaway.

### Authentication: Fortify

The starter kit uses **Laravel Fortify** as a headless authentication backend. Fortify provides the routes and logic (login, register, password reset, etc.) and the starter kit supplies the React pages for them.

Features turned on in `config/fortify.php`:

- **Registration**
- **Password reset**
- **Email verification** (`User` implements `MustVerifyEmail`, and most routes require the `verified` middleware)
- **Two-factor authentication** (TOTP app codes + recovery codes), with "confirm" and "confirm password" steps required
- **Passkeys / WebAuthn** (via `laravel/fortify` passkey support and the `@laravel/passkeys` JS package), with password confirmation required to enrol

Other auth-related decisions:

- Usernames are email addresses and are lower-cased before saving.
- After login, users are redirected to `/dashboard`.
- Login is rate-limited to 5 attempts per minute per email + IP. Two-factor is 5/min. Passkey attempts are 10/min.
- In **production only**, passwords must be at least 12 characters with mixed case, numbers, symbols, and must not appear in known breach lists (`Password::uncompromised()`). Locally there is no complexity rule, which makes testing easier.

### Teams (multi-tenancy)

The starter kit's **teams** option was enabled. This adds:

- `teams`, `team_members`, and `team_invitations` tables, plus a `current_team_id` column on `users`.
- Every user gets a **personal team** on registration (`is_personal` flag).
- Three roles defined in `app/Enums/TeamRole.php`:
  - **Owner**: all permissions, cannot be reassigned.
  - **Admin**: can update the team and create/cancel invitations.
  - **Member**: no management permissions.
- **Email invitations** that expire after 3 days. A scheduled task (in `routes/console.php`) deletes expired invitations daily.
- Team-scoped URLs: authenticated pages live under a `/{team-slug}/...` prefix (e.g. `/{team-slug}/dashboard`). The `EnsureTeamMembership` middleware checks the user belongs to that team and switches their "current team" to match the URL.
- A team switcher and team settings pages in the sidebar.

### Database: SQLite

`DB_CONNECTION=sqlite` is the default. The database lives at `database/database.sqlite` (git-ignored). This was chosen for zero-setup local development. Switching to MySQL or PostgreSQL only requires changing the `DB_*` values in `.env`.

Session, cache, and queue all use the **database** driver, so no Redis or Memcached is required locally.

### Mail

`MAIL_MAILER=log`. Verification emails, password resets, and team invitations are written to `storage/logs/laravel.log` instead of being sent. Use `php artisan pail` to tail them.

### Other backend defaults (in `AppServiceProvider`)

- All dates use `CarbonImmutable`.
- Destructive database commands (`migrate:fresh`, `db:wipe`, etc.) are **blocked in production**.

---

## Frontend

### React 19 + TypeScript via Inertia

Pages are React components in `resources/js/pages`, rendered server-side by Laravel through Inertia. There is **no** separate API and **no** React Router: Laravel routes decide which page component to render and pass props to it.

Server-side rendering (SSR) is **not** set up. There is no `ssr.tsx` entry, although the `build:ssr` script exists in `package.json` if it is ever wanted.

### Vite+ and the React Compiler

The build runs through **Vite+** (`vite-plus`), a bundled toolchain that wraps Vite and adds a linter and formatter (`vp check`, `vp check --fix`). The **React Compiler** is enabled through a Babel preset, which auto-memoises components so manual `useMemo`/`useCallback` is rarely needed.

Formatter settings (in `vite.config.ts`): 4-space indent, single quotes, semicolons, 80-column width, Tailwind class sorting.

### Tailwind CSS 4 + shadcn/ui

- Tailwind 4 is configured in CSS (`resources/css/app.css`), not in a `tailwind.config.js`.
- UI primitives come from **shadcn/ui** (`components.json`: "new-york" style, neutral base colour, CSS variables, Lucide icons). Generated components live in `resources/js/components/ui` and are excluded from linting and formatting on purpose, so they can be re-generated from shadcn.
- **Light / dark / system appearance** is supported. The choice is stored in a cookie named `appearance` and applied before first paint.
- The font is **Instrument Sans**, loaded from Bunny Fonts through the Laravel Vite plugin.
- Toast notifications use **Sonner**; flash messages from Laravel are shown as toasts automatically.

### Wayfinder

**Laravel Wayfinder** generates TypeScript functions for every named Laravel route into `resources/js/routes`, `resources/js/actions`, and `resources/js/wayfinder`. These folders are git-ignored and are regenerated on build and on `php artisan dev`. Use them instead of hard-coding URLs in React, e.g. `dashboard()` rather than `'/dashboard'`.

### Layouts

Layouts are chosen automatically by page name in `resources/js/app.tsx`:

- `welcome` has no layout.
- `auth/*` pages use the auth layout (`auth-simple-layout` by default; `auth-card-layout` and `auth-split-layout` are also available).
- `settings/*` and `teams/*` use the app layout wrapped around the settings layout.
- Everything else uses the app layout, which is the **sidebar** variant (`app-sidebar-layout`). A header variant (`app-header-layout`) exists if you prefer a top nav.

---

## Quality tooling

| Tool | Command | What it does |
|---|---|---|
| Pint | `composer lint` / `composer lint:check` | Formats PHP (Laravel preset) |
| Larastan | `composer types:check` | PHPStan at **level 7** over `app/`, `config/`, `database/`, `routes/` |
| Pest | `php artisan test` | Feature tests run against an in-memory SQLite DB |
| Vite+ lint | `npm run check` / `npm run check:fix` | Lints and formats TypeScript/TSX; warnings are treated as errors |
| TypeScript | `npm run types:check` | `tsc --noEmit` |
| Everything | `composer test` | Clears config, then runs Pint check, PHPStan, and Pest |
| CI equivalent | `composer ci:check` | `npm run check` + `npm run types:check` + `composer test` |

The starter kit's tests cover the whole auth flow (login, registration, verification, password reset, 2FA, password confirmation), profile and security settings, and teams (creation, members, invitations, expiry pruning).

**Laravel Boost** is installed as a dev dependency. It gives AI coding assistants (Claude Code, Cursor, etc.) an MCP server with project context. Its generated files (`CLAUDE.md`, `AGENTS.md`, `boost.json`, `.claude/`, `.cursor/`, etc.) are git-ignored so each developer keeps their own.

**Laravel Sail** is also available if you prefer running everything in Docker, but the setup below assumes a local PHP install.

Smaller helper packages that also came with the kit:

- `laravel/chisel`: toolkit the starter kit uses to strip out features you do not want (for example removing teams or passkeys).
- `laravel/pao`: makes Pest and PHPStan output easier for AI agents to read.
- `laravel/pail`: `php artisan pail` tails the log in the terminal.
- `@laravel/multiplex` (optional npm dep): the tabbed terminal UI that `composer dev` uses to show the server, queue, logs, and Vite side by side.

---

## Continuous integration

`.github/workflows/tests.yml` runs on every push to `main` and every pull request:

1. Checks out the code.
2. Installs **PHP 8.5** and **Node 22**.
3. Runs `composer setup` (install, `.env`, key, migrate, `npm install`, `npm run build`).
4. Runs `composer ci:check`.

Action versions are pinned to commit SHAs. Dependabot checks weekly for updates to those actions (not to Composer or npm packages).

---

## Local setup

Requirements: PHP 8.3+ (8.5 recommended to match CI), Composer, Node 22+, npm. SQLite support must be enabled in PHP.

One-shot setup (this is what CI runs):

```bash
composer setup
```

That is equivalent to:

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
npm install
npm run build
```

Then start everything (Laravel server, queue worker, log tail, and Vite) with one command:

```bash
composer dev
```

The app is at <http://localhost:8000>.

Optional: seed a test user (`test@example.com` / `password`):

```bash
php artisan db:seed
```

Note: `.npmrc` sets `ignore-scripts=true`, so npm packages cannot run install scripts. This is a supply-chain safety measure. If a package genuinely needs its post-install step, run it manually.

---

## Project layout at a glance

```
app/
  Actions/        Fortify user creation/reset + CreateTeam
  Concerns/       HasTeams trait, validation rule traits
  Enums/          TeamRole, TeamPermission
  Http/
    Controllers/  Dashboard, Settings (profile, security), Teams
    Middleware/   EnsureTeamMembership, SetTeamUrlDefaults, HandleAppearance, HandleInertiaRequests
    Requests/     Form request classes for settings and teams
    Responses/    Custom Fortify responses that redirect to the current team
  Models/         User, Team, Membership, TeamInvitation
  Policies/       TeamPolicy
  Rules/          TeamName, UniqueTeamInvitation, ValidTeamInvitation
resources/
  css/app.css     Tailwind 4 config + shadcn theme variables
  js/
    app.tsx       Inertia entry; layout selection
    components/   App chrome, team/2FA/passkey modals, shadcn `ui/`
    hooks/        Appearance, clipboard, mobile, two-factor, flash toasts
    layouts/      app (sidebar/header), auth (simple/card/split), settings
    pages/        welcome, dashboard, auth/*, settings/*, teams/*
    routes/       Generated by Wayfinder (git-ignored)
routes/
  web.php         Home, team-scoped dashboard, invitation accept/decline
  settings.php    Profile, security, appearance, teams management
  console.php     Daily pruning of expired invitations
tests/
  Feature/        Auth, Settings, Teams, Dashboard
```
