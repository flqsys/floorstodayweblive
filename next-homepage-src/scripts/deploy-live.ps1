# Builds the Next.js homepage specifically for the live server (different
# NEXT_PUBLIC_BASE_PATH than local WAMP: /public vs whatever .env.local
# currently has) and deploys it straight to the server over SSH, bypassing
# git entirely.
#
# This exists because the base path gets baked into compiled JS at build
# time (Turbopack's chunk loader, the Next Image loader, etc.) - a single
# git-tracked public/ folder cannot be correct for both environments at
# once. Local WAMP keeps using the committed public/ as before; live is
# updated only through this script.
#
# Usage (from next-homepage-src/):
#   ./scripts/deploy-live.ps1

$ErrorActionPreference = "Stop"

$LiveHost = "16.54.143.52"
$SshUser = "ubuntu"
# Hardcoded rather than auto-discovered via `find /home/...`: the ubuntu
# SSH user can't traverse into /home/floorstodayfinal without sudo (the
# host restricts other-user access to home dirs), so a `find`-based lookup
# silently comes back empty and every deploy fails with SITE_NOT_FOUND.
# push-local-db.ps1 hit the same issue and was fixed the same way - mirror
# that here instead of re-introducing auto-discovery.
$SiteUser = "floorstodayfinal"
$LiveSiteRoot = "/home/floorstodayfinal/htdocs/floorstoday.ca"

$scriptDir = Split-Path -Parent $MyInvocation.MyCommand.Path
$projectRoot = Resolve-Path (Join-Path $scriptDir "..")
$envFile = Join-Path $projectRoot ".env.production"
$backupFile = Join-Path $projectRoot ".env.production.local-backup"
# Next.js gives .env.local higher priority than .env.production during
# `next build` - so even after we overwrite .env.production with live
# values below, a committed/local .env.local silently wins and the local
# WAMP base path gets baked into the live build instead. Must be moved
# out of the way for the duration of the build, not just .env.production.
$envLocalFile = Join-Path $projectRoot ".env.local"
$envLocalBackup = Join-Path $projectRoot ".env.local.live-backup"
$outDir = Join-Path $projectRoot "out"
$archivePath = Join-Path $projectRoot "live-build.tar.gz"

function Restore-LocalEnv {
    if (Test-Path $backupFile) {
        Move-Item -Force $backupFile $envFile
        Write-Host "Restored local .env.production"
    }
    if (Test-Path $envLocalBackup) {
        Move-Item -Force $envLocalBackup $envLocalFile
        Write-Host "Restored local .env.local"
    }
}

trap {
    Write-Host "Deploy failed: $_" -ForegroundColor Red
    Restore-LocalEnv
    exit 1
}

Write-Host "== Building for live (NEXT_PUBLIC_BASE_PATH=/public) =="
Copy-Item $envFile $backupFile -Force
@"
NEXT_PUBLIC_BASE_PATH=/public
NEXT_PUBLIC_WORDPRESS_ORIGIN=https://floorstoday.ca
NEXT_PUBLIC_WORDPRESS_HOMEPAGE_ENDPOINT=/wp-json/floors-today/v1/homepage
NEXT_PUBLIC_WORDPRESS_INBOX_ENDPOINT=/wp-json/floors-today/v1/inbox-leads
"@ | Set-Content -Path $envFile -Encoding ascii

# .env.local outranks .env.production in Next.js's own precedence order, so
# it must be out of the way entirely during the live build, not overwritten.
$localBasePath = $null
if (Test-Path $envLocalFile) {
    $localBasePathLine = Select-String -Path $envLocalFile -Pattern '^NEXT_PUBLIC_BASE_PATH=(.+)$'
    if ($localBasePathLine) { $localBasePath = $localBasePathLine.Matches[0].Groups[1].Value.Trim() }
    Move-Item -Force $envLocalFile $envLocalBackup
    Write-Host "Stashed .env.local for the duration of the build"
}

Write-Host "== Cleaning previous build output =="
if (Test-Path $outDir) { Remove-Item -Recurse -Force $outDir }

Push-Location $projectRoot
try {
    npm run build
    if ($LASTEXITCODE -ne 0) { throw "npm run build failed" }
} finally {
    Pop-Location
}

Write-Host "== Verifying no local WAMP paths leaked into the build =="
# Scans every chunk, not just turbopack-*.js - a stale committed fallback
# (homepage-settings.snapshot.json, statically bundled into whichever
# chunk imports it) leaked a local base path into a completely different
# chunk once already, undetected, because this check used to only look at
# the turbopack runtime chunk. Built from the *actual* current .env.local
# value (read above, before it was stashed) rather than a hardcoded old
# folder name - a hardcoded string silently stops matching the moment the
# local folder is renamed, same as it did for "floorstodayfinal" -> "floortoday".
# Matches the specific local site path, not bare "localhost" - that alone
# false-positives inside a bundled URL-parsing polyfill that legitimately
# compares against the literal string "localhost" per the WHATWG URL spec.
if ($localBasePath -and $localBasePath -ne "/public") {
    $escapedLocalBasePath = [Regex]::Escape($localBasePath)
    $badPathPatterns = @($escapedLocalBasePath, "localhost$escapedLocalBasePath")
    $badPath = Select-String -Path (Join-Path $outDir "_next\static\chunks\*.js") -Pattern $badPathPatterns -ErrorAction SilentlyContinue
    if ($badPath) {
        Write-Host ($badPath | Format-Table Filename, LineNumber -AutoSize | Out-String)
        throw "Build still contains a local WAMP path ($localBasePath) in a chunk - aborting deploy"
    }
}

Write-Host "== Packaging build =="
if (Test-Path $archivePath) { Remove-Item $archivePath -Force }
tar -czf $archivePath -C $outDir .

Write-Host "== Uploading to $LiveHost =="
scp -q $archivePath "${SshUser}@${LiveHost}:/tmp/live-build.tar.gz"

Write-Host "== Deploying on server (backup, extract, swap, cleanup) =="
# .htaccess is hand-maintained in public/, not part of Next's build output -
# excluded from the wipe (! -name '.htaccess') so it survives every deploy,
# and rewritten below in case it's missing (e.g. a fresh site).
$remoteScript = @"
set -e
sudo -u $SiteUser tar -czf /tmp/public-backup-`$(date +%Y%m%d-%H%M%S).tar.gz -C $LiveSiteRoot public
sudo -u $SiteUser mkdir -p /tmp/new-public-extract
sudo -u $SiteUser tar -xzf /tmp/live-build.tar.gz -C /tmp/new-public-extract
sudo -u $SiteUser bash -c 'cd $LiveSiteRoot/public && find . -mindepth 1 ! -name ".htaccess" -delete && cp -r /tmp/new-public-extract/. .'
sudo -u $SiteUser bash -c 'cat > $LiveSiteRoot/public/.htaccess' <<'HTACCESS_EOF'
# Next.js emits RSC/prefetch payload files (__next*.txt, index.txt,
# _not-found.txt) on every build. The client router actively fetches these
# at runtime for its cache - do not block them.

# Debug/log files should never be written here, but block them defensively
# in case a future debugging session leaves one behind again.
<FilesMatch "\.log`$">
    <IfModule mod_authz_core.c>
        Require all denied
    </IfModule>
    <IfModule !mod_authz_core.c>
        Order allow,deny
        Deny from all
    </IfModule>
</FilesMatch>
HTACCESS_EOF
sudo -u $SiteUser rm -rf /tmp/new-public-extract
rm -f /tmp/live-build.tar.gz
echo DEPLOY_OK
"@
$result = ssh "${SshUser}@${LiveHost}" $remoteScript
if ($result -notmatch "DEPLOY_OK") {
    throw "Remote deploy did not confirm success. Output: $result"
}

Write-Host "== Verifying live site =="
Start-Sleep -Seconds 2
$check = Invoke-WebRequest -Uri "https://floorstoday.ca/" -UseBasicParsing
if ($check.StatusCode -ne 200) {
    throw "Live homepage did not return 200 after deploy (got $($check.StatusCode))"
}
# A 200 alone isn't enough - the page shell loads fine even when every
# asset it references 404s (this is exactly how the local-path-baked-in
# bug above went unnoticed). Confirm the HTML actually references the
# live asset path, and if a bad local path was captured, that it's gone.
if ($check.Content -notmatch [Regex]::Escape('/public/_next/')) {
    throw "Live homepage HTML does not reference /public/_next/ - asset paths look wrong, deploy likely broken"
}
if ($localBasePath -and $localBasePath -ne "/public" -and $check.Content -match [Regex]::Escape($localBasePath + "/_next/")) {
    throw "Live homepage HTML still references local path $localBasePath - deploy is broken"
}

Remove-Item $archivePath -Force -ErrorAction SilentlyContinue
Restore-LocalEnv

Write-Host "Deploy complete. Live homepage verified at https://floorstoday.ca/" -ForegroundColor Green
