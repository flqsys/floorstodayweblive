# Floors Today Homepage Deployment

The Next.js homepage must not hardcode local folder paths in source code.
Use environment variables for each environment.

## Deploy workflow — local and live are two separate targets

Local WAMP and the live server need **different builds** — `NEXT_PUBLIC_BASE_PATH`
gets baked directly into compiled JS (the Turbopack chunk loader, the Next
Image loader), so one build can never be correct for both. This is also why
they use two separate deploy scripts, and why `git push`/`pull` alone must
never be relied on to update the live homepage.

### Local WAMP

```
cd next-homepage-src
npm run build
./scripts/sync-public.ps1
git add public/
git commit
git push
```

`sync-public.ps1` runs `robocopy out ../public /MIR`, which deletes any file
in `public/` that isn't in the new `out/` — so orphaned build artifacts from
previous builds can't accumulate.

### Live (floorstoday.ca)

```
cd next-homepage-src
./scripts/deploy-live.ps1
```

This builds with the live env values, verifies the output has no leftover
local paths, and deploys directly to the server over SSH — it does **not**
go through git. The live server's `public/` is intentionally untracked from
git (`git update-index --skip-worktree`) specifically so a routine `git pull`
there can never overwrite it. `git push`/`pull` remain safe for everything
else (PHP, theme, plugins) — only homepage changes need this separate step.

Colors/sizes come from WP admin at request time in both cases (see
`ft_next_homepage_shell_style_vars()` in the mu-plugin), not from the build,
so you only need to run either deploy when you change component/CSS source
— not when you change a color in WP admin.

### Database: live → local only

```
cd next-homepage-src
./scripts/pull-live-db.ps1
```

One-way only — pulls the live database down to local WAMP, overwriting the
local copy. **Never run this in reverse**; the live database holds real
production data. Always backs up the current local database first
(timestamped, in `../floorstodayfinal_migration/`, outside the repo) before
touching anything, and auto-fixes `siteurl`/`home` afterward (imported
values point to `https://floorstoday.ca` and will 404 the local site
otherwise). Live DB credentials are read from the server's own
`wp-config.php` at run time — never hardcoded or committed anywhere.

## API endpoints are now dynamic

`NEXT_PUBLIC_WORDPRESS_HOMEPAGE_ENDPOINT` and `NEXT_PUBLIC_WORDPRESS_INBOX_ENDPOINT`
should always be set to the path relative to the WordPress install root, without any
install-folder prefix:

```env
NEXT_PUBLIC_WORDPRESS_HOMEPAGE_ENDPOINT=/wp-json/floors-today/v1/homepage
NEXT_PUBLIC_WORDPRESS_INBOX_ENDPOINT=/wp-json/floors-today/v1/inbox-leads
```

The `endpointUrl()` helper in the React components prepends `localInstallPath()` at
runtime so the correct full URL is constructed on every environment automatically.

## Local WAMP (e.g. localhost/floorstodayfinal/)

```env
NEXT_PUBLIC_BASE_PATH=/floorstodayfinal/public
NEXT_PUBLIC_WORDPRESS_ORIGIN=http://localhost/floorstodayfinal
NEXT_PUBLIC_WORDPRESS_HOMEPAGE_ENDPOINT=/wp-json/floors-today/v1/homepage
NEXT_PUBLIC_WORDPRESS_INBOX_ENDPOINT=/wp-json/floors-today/v1/inbox-leads
```

`NEXT_PUBLIC_WORDPRESS_ORIGIN` is only used during the build-time SSG fetch.
`NEXT_PUBLIC_BASE_PATH` sets the Next.js asset prefix for the static export;
the WordPress mu-plugin normalizes it at runtime for every environment.

## Production at domain root (e.g. floorstoday.ca/)

```env
NEXT_PUBLIC_BASE_PATH=/public
NEXT_PUBLIC_WORDPRESS_HOMEPAGE_ENDPOINT=/wp-json/floors-today/v1/homepage
NEXT_PUBLIC_WORDPRESS_INBOX_ENDPOINT=/wp-json/floors-today/v1/inbox-leads
```

If WordPress is on a subdomain, use an absolute origin:

```env
NEXT_PUBLIC_WORDPRESS_HOMEPAGE_ENDPOINT=https://store.example.com/wp-json/floors-today/v1/homepage
NEXT_PUBLIC_WORDPRESS_INBOX_ENDPOINT=https://store.example.com/wp-json/floors-today/v1/inbox-leads
```

## Required WordPress Data

The WordPress install that serves the API must have:

- `wp-content/mu-plugins/floors-next-homepage-backend.php`
- the `ft_next_homepage_settings` option saved in that WordPress database

If the option is missing, the API returns an error instead of demo content.
