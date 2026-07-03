# Pushes the local WAMP database up to live, overwriting the live copy.
# One-way only: local -> live. Mirror of pull-live-db.ps1, reversed.
#
# This is far more dangerous than the pull script. The live database
# holds real production data (leads, orders, real WP options) that the
# local copy almost certainly does not have if anything came in since the
# last pull - a full push destroys anything live-only. Requires typed
# confirmation before touching live for this reason; the pull script does
# not need this since local data loss is cheap and recoverable, live data
# loss is not.
#
# Credentials for the live DB are never hardcoded here, and never transit
# through this script's own variables - _import-live-db.sh reads them
# from the server's own wp-config.php and imports entirely server-side;
# this script only orchestrates file transfer.
#
# Automatically fixes siteurl/home after import (imported from local, so
# they'd point to http://localhost/floorstodayfinal and 404 the live site
# otherwise) - same fix pull-live-db.ps1 does, in the opposite direction.
#
# Usage (from next-homepage-src/):
#   ./scripts/push-local-db.ps1

$ErrorActionPreference = "Stop"

$LiveHost = "16.54.143.52"
$SshUser = "ubuntu"
$SiteUser = "floorstodayfinal"
$LiveSiteRoot = "/home/floorstodayfinal/htdocs/floorstoday.ca"
$LiveUrl = "https://floorstoday.ca"

$MysqlBin = "C:\wamp64\bin\mysql\mysql8.4.7\bin"
$LocalDbName = "floorstoday-data"
$LocalDbUser = "root"
$LocalDbHost = "localhost"

$scriptDir = Split-Path -Parent $MyInvocation.MyCommand.Path
$dumpScript = Join-Path $scriptDir "_dump-live-db.sh"
$importScript = Join-Path $scriptDir "_import-live-db.sh"

$scratchDir = "C:\wamp64\www\floorstodayfinal_migration"
$timestamp = Get-Date -Format "yyyyMMdd-HHmmss"
$liveBackupFile = Join-Path $scratchDir "live-db-backup-$timestamp.sql"
$localDumpFile = Join-Path $scratchDir "local-db-push-$timestamp.sql"
$remoteBackupPath = "/tmp/live-db-backup-$timestamp.sql"
$remoteDumpScriptPath = "/tmp/_dump-live-db-$timestamp.sh"
$remotePushPath = "/tmp/local-db-push-$timestamp.sql"
$remoteImportScriptPath = "/tmp/_import-live-db-$timestamp.sh"

if (-not (Test-Path $scratchDir)) {
    New-Item -ItemType Directory -Path $scratchDir | Out-Null
}

Write-Host "==============================================================" -ForegroundColor Yellow
Write-Host " This overwrites the LIVE production database with your LOCAL" -ForegroundColor Yellow
Write-Host " copy. Any real leads/orders/data entered on the live site" -ForegroundColor Yellow
Write-Host " since your last pull will be PERMANENTLY LOST." -ForegroundColor Yellow
Write-Host "==============================================================" -ForegroundColor Yellow
$confirmation = Read-Host "Type PUSH TO LIVE to continue"
if ($confirmation -ne "PUSH TO LIVE") {
    Write-Host "Aborted - confirmation phrase did not match." -ForegroundColor Red
    exit 1
}

function Remove-RemoteTemp {
    # Backup/push dumps are owned by $SiteUser (created/read via sudo -u),
    # uploaded scripts are owned by $SshUser (plain scp) - different
    # owners need different removal contexts. Failures here shouldn't
    # abort the script, the important data has already moved by this point.
    try { ssh "${SshUser}@${LiveHost}" "sudo -u $SiteUser rm -f $remoteBackupPath $remotePushPath" 2>$null } catch {}
    try { ssh "${SshUser}@${LiveHost}" "rm -f $remoteDumpScriptPath $remoteImportScriptPath" 2>$null } catch {}
}

trap {
    Write-Host "Push failed: $_" -ForegroundColor Red
    Remove-RemoteTemp
    if (Test-Path $liveBackupFile) {
        Write-Host "Live DB backup (pre-push) is safe at: $liveBackupFile" -ForegroundColor Yellow
    }
    exit 1
}

Write-Host "== Backing up LIVE database first (safety net, credentials never leave the server) =="
scp -q $dumpScript "${SshUser}@${LiveHost}:${remoteDumpScriptPath}"
$backupResult = ssh "${SshUser}@${LiveHost}" "sudo -u $SiteUser bash $remoteDumpScriptPath $LiveSiteRoot/wp-config.php $remoteBackupPath"
if ($backupResult -notmatch "DUMP_OK:") {
    throw "Live backup did not confirm success. Output: $backupResult. Aborting before touching live DB."
}
scp -q "${SshUser}@${LiveHost}:${remoteBackupPath}" $liveBackupFile
if (-not (Test-Path $liveBackupFile) -or (Get-Item $liveBackupFile).Length -eq 0) {
    throw "Live backup download is missing or empty - aborting before touching live DB"
}
Write-Host "Live DB backed up: $liveBackupFile ($((Get-Item $liveBackupFile).Length) bytes)"

Write-Host "== Dumping local database ($LocalDbName) =="
& "$MysqlBin\mysqldump.exe" -u $LocalDbUser -h $LocalDbHost $LocalDbName | Out-File -FilePath $localDumpFile -Encoding utf8
if (-not (Test-Path $localDumpFile) -or (Get-Item $localDumpFile).Length -eq 0) {
    throw "Local dump failed or is empty - aborting before touching live DB"
}
Write-Host "Local dump complete: $((Get-Item $localDumpFile).Length) bytes"

Write-Host "== Uploading local dump to server =="
scp -q $localDumpFile "${SshUser}@${LiveHost}:${remotePushPath}"
scp -q $importScript "${SshUser}@${LiveHost}:${remoteImportScriptPath}"

Write-Host "== Importing local dump into LIVE database =="
$importResult = ssh "${SshUser}@${LiveHost}" "sudo -u $SiteUser bash $remoteImportScriptPath $LiveSiteRoot/wp-config.php $remotePushPath"
if ($importResult -notmatch "IMPORT_OK:") {
    Write-Error "Import did not confirm success. Output: $importResult. Live DB may be in a partial state. Restore from: $liveBackupFile"
    Remove-RemoteTemp
    exit 1
}
Write-Host "Imported: $importResult"

Write-Host "== Fixing siteurl/home on live (imported values point to localhost) =="
# Reuses _import-live-db.sh (same credential handling already proven two
# steps ago) instead of a hand-built nested SSH/bash quoting chain - just
# feed it a one-statement SQL file instead of a full dump.
$fixSql = "UPDATE floors1_options SET option_value = '$LiveUrl' WHERE option_name IN ('siteurl','home');"
$fixSqlFile = Join-Path $scratchDir "fix-siteurl-$timestamp.sql"
$remoteFixSqlPath = "/tmp/fix-siteurl-$timestamp.sql"
Set-Content -Path $fixSqlFile -Value $fixSql -Encoding utf8 -NoNewline
scp -q $fixSqlFile "${SshUser}@${LiveHost}:${remoteFixSqlPath}"
$fixResult = ssh "${SshUser}@${LiveHost}" "sudo -u $SiteUser bash $remoteImportScriptPath $LiveSiteRoot/wp-config.php $remoteFixSqlPath"
# Uploaded via plain scp, so it's owned by $SshUser, not $SiteUser - deleting
# it via sudo -u $SiteUser fails ("Operation not permitted", /tmp sticky bit).
# Non-fatal either way: wrapped so a cleanup hiccup can never abort the
# script after the real work above is already done.
try { ssh "${SshUser}@${LiveHost}" "rm -f $remoteFixSqlPath" 2>$null } catch {}
Remove-Item $fixSqlFile -Force -ErrorAction SilentlyContinue
if ($fixResult -notmatch "IMPORT_OK:") {
    Write-Warning "Could not auto-fix siteurl/home - live site will 404 until you run this manually on the server:"
    Write-Warning $fixSql
}

Write-Host "== Cleaning up remote temp files =="
Remove-RemoteTemp

Write-Host ""
Write-Host "Push complete." -ForegroundColor Green
Write-Host "  Live DB backup (pre-push):  $liveBackupFile"
Write-Host "  Local dump pushed from:     $localDumpFile"
Write-Host "  To roll back live, run:"
Write-Host "    scp `"$importScript`" ${SshUser}@${LiveHost}:/tmp/_import-rollback.sh"
Write-Host "    scp `"$liveBackupFile`" ${SshUser}@${LiveHost}:/tmp/rollback.sql"
Write-Host "    ssh ${SshUser}@${LiveHost} `"sudo -u $SiteUser bash /tmp/_import-rollback.sh $LiveSiteRoot/wp-config.php /tmp/rollback.sql`""
