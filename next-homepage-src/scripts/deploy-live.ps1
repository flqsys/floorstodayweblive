# Builds the Next.js homepage specifically for the live server (different
# NEXT_PUBLIC_BASE_PATH than local WAMP: /public vs /floorstodayfinal/public)
# and deploys it straight to the server over SSH, bypassing git entirely.
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
$SiteUser = "floorstodayfinal"
$LiveSiteRoot = "/home/floorstodayfinal/htdocs/floorstoday.ca"

$scriptDir = Split-Path -Parent $MyInvocation.MyCommand.Path
$projectRoot = Resolve-Path (Join-Path $scriptDir "..")
$envFile = Join-Path $projectRoot ".env.production"
$backupFile = Join-Path $projectRoot ".env.production.local-backup"
$outDir = Join-Path $projectRoot "out"
$archivePath = Join-Path $projectRoot "live-build.tar.gz"

function Restore-LocalEnv {
    if (Test-Path $backupFile) {
        Move-Item -Force $backupFile $envFile
        Write-Host "Restored local .env.production"
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

Push-Location $projectRoot
try {
    npm run build
    if ($LASTEXITCODE -ne 0) { throw "npm run build failed" }
} finally {
    Pop-Location
}

Write-Host "== Verifying no local WAMP paths leaked into the build =="
$badPath = Select-String -Path (Join-Path $outDir "_next\static\chunks\turbopack-*.js") -Pattern "/floorstodayfinal/public" -ErrorAction SilentlyContinue
if ($badPath) {
    throw "Build still contains /floorstodayfinal/public in the turbopack runtime chunk - aborting deploy"
}

Write-Host "== Packaging build =="
if (Test-Path $archivePath) { Remove-Item $archivePath -Force }
tar -czf $archivePath -C $outDir .

Write-Host "== Uploading to $LiveHost =="
scp -q $archivePath "${SshUser}@${LiveHost}:/tmp/live-build.tar.gz"

Write-Host "== Deploying on server (backup, extract, swap, cleanup) =="
$remoteScript = @"
set -e
sudo -u $SiteUser tar -czf /tmp/public-backup-`$(date +%Y%m%d-%H%M%S).tar.gz -C $LiveSiteRoot public
sudo -u $SiteUser mkdir -p /tmp/new-public-extract
sudo -u $SiteUser tar -xzf /tmp/live-build.tar.gz -C /tmp/new-public-extract
sudo -u $SiteUser bash -c 'cd $LiveSiteRoot/public && find . -mindepth 1 -delete && cp -r /tmp/new-public-extract/. .'
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

Remove-Item $archivePath -Force -ErrorAction SilentlyContinue
Restore-LocalEnv

Write-Host "Deploy complete. Live homepage verified at https://floorstoday.ca/" -ForegroundColor Green
