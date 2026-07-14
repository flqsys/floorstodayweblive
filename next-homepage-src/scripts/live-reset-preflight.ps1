# Dry-run safety check before running `git reset --hard` (or any operation
# that moves live's HEAD to a different commit) directly on live.
#
# Built after the 2026-07-13 incident: a `git reset --hard origin/main` on
# live deleted WordPress core (wp-admin/wp-includes), the parent theme,
# every third-party plugin, and Elementor's CSS cache in one shot, because
# live's git index still tracked them from old history even though they'd
# already been untracked locally. This script computes exactly which files
# a reset to a given target commit would delete BEFORE running it for real,
# and refuses to give the all-clear if any of them fall under a protected
# path - the check that would have caught that incident before it happened.
#
# Usage (from next-homepage-src/):
#   ./scripts/live-reset-preflight.ps1                  # checks against origin/main
#   ./scripts/live-reset-preflight.ps1 -TargetRef <ref>  # checks against any ref/commit

param(
    [string]$TargetRef = "origin/main"
)

$LiveHost = "16.54.143.52"
$SshUser = "ubuntu"
$SiteUser = "floorstodayfinal"
$LiveSiteRoot = "/home/floorstodayfinal/htdocs/floorstoday.ca"

# Anything under these paths is intentionally untracked in the current
# history (WordPress core/vendor/generated data) - if a reset would DELETE
# any of them, that means live's index has drifted out of sync again and
# must be fixed with `git rm --cached` (see check-live-health.ps1 Check 0)
# before the reset is safe to run.
$protectedPaths = @(
    "public/", "wp-admin/", "wp-includes/",
    "wp-activate.php", "wp-blog-header.php", "wp-comments-post.php",
    "wp-cron.php", "wp-links-opml.php", "wp-load.php", "wp-login.php",
    "wp-mail.php", "wp-settings.php", "wp-signup.php", "wp-trackback.php",
    "xmlrpc.php", "license.txt", "readme.html",
    "wp-content/themes/hello-elementor/", "wp-content/uploads/",
    "wp-content/languages/",
    "wp-content/plugins/admin-menu-editor-pro/",
    "wp-content/plugins/advanced-custom-fields-pro/",
    "wp-content/plugins/amazon-s3-and-cloudfront-pro/",
    "wp-content/plugins/child-theme-configurator/",
    "wp-content/plugins/classic-editor/",
    "wp-content/plugins/elementor-pro/",
    "wp-content/plugins/elementor/",
    "wp-content/plugins/filebird-pro/",
    "wp-content/plugins/lara-google-analytics-pro/",
    "wp-content/plugins/wpvivid-backuprestore/"
)

function Assert-SshOk([string]$StepName) {
    if ($LASTEXITCODE -ne 0) {
        Write-Host ""
        Write-Host "==============================================================" -ForegroundColor Red
        Write-Host " STOP: could not reach live to run '$StepName' (exit code $LASTEXITCODE)." -ForegroundColor Red
        Write-Host " This is NOT a green light - it means nothing was actually" -ForegroundColor Red
        Write-Host " verified. Fix the connection and re-run before doing anything" -ForegroundColor Red
        Write-Host " on live. Never treat 'no output' as 'no deletions'." -ForegroundColor Red
        Write-Host "==============================================================" -ForegroundColor Red
        exit 1
    }
}

Write-Host "== Fetching latest refs on live =="
ssh "${SshUser}@${LiveHost}" "sudo -u $SiteUser bash -c 'cd $LiveSiteRoot && git fetch origin --quiet'" | Out-Null
Assert-SshOk "git fetch origin"

Write-Host "== Computing what would be deleted by resetting to $TargetRef =="
$diffRaw = ssh "${SshUser}@${LiveHost}" "sudo -u $SiteUser bash -c 'cd $LiveSiteRoot && git diff --name-status HEAD $TargetRef'"
Assert-SshOk "git diff --name-status HEAD $TargetRef"
$deletions = @($diffRaw | Where-Object { $_ -match '^D\s+(.+)$' } | ForEach-Object { $Matches[1] })

if ($deletions.Count -eq 0) {
    Write-Host "No files would be deleted by this reset. Safe to proceed." -ForegroundColor Green
    exit 0
}

Write-Host "$($deletions.Count) file(s) would be deleted by resetting to $TargetRef :" -ForegroundColor Yellow
$dangerous = @()
foreach ($file in $deletions) {
    $isProtected = $false
    foreach ($p in $protectedPaths) {
        if ($file -eq $p.TrimEnd('/') -or $file.StartsWith($p)) {
            $isProtected = $true
            break
        }
    }
    if ($isProtected) {
        $dangerous += $file
        Write-Host "  [DANGER] $file" -ForegroundColor Red
    }
}

if ($dangerous.Count -eq 0) {
    Write-Host ""
    Write-Host "$($deletions.Count) file(s) would be deleted, but none fall under a protected path. Safe to proceed." -ForegroundColor Green
    exit 0
}

Write-Host ""
Write-Host "==============================================================" -ForegroundColor Red
Write-Host " STOP: $($dangerous.Count) protected file(s) would be deleted." -ForegroundColor Red
Write-Host " This means live's git index has drifted out of sync again -" -ForegroundColor Red
Write-Host " these paths are tracked on live but shouldn't be. Fix with" -ForegroundColor Red
Write-Host " 'git rm --cached' for the affected paths BEFORE running the" -ForegroundColor Red
Write-Host " reset, or WordPress core/plugins/theme will be deleted again," -ForegroundColor Red
Write-Host " same as the 2026-07-13 incident." -ForegroundColor Red
Write-Host "==============================================================" -ForegroundColor Red
exit 1
