# Pulls the live production database down to local WAMP, overwriting the
# local copy. One-way only: live -> local. Never push local -> live; the
# live database holds real production data and this must never run in
# reverse.
#
# Credentials for the live DB are never hardcoded here, and never transit
# through this script's own variables - _dump-live-db.sh reads them from
# the server's own wp-config.php and dumps the database entirely
# server-side; this script only orchestrates file transfer.
#
# Automatically fixes siteurl/home after import (imported from live, so
# they point to https://floorstoday.ca and would 404 the local site
# otherwise) - confirmed via a real 404 the first time this ran.
#
# Usage (from next-homepage-src/):
#   ./scripts/pull-live-db.ps1

$ErrorActionPreference = "Stop"

$LiveHost = "16.54.143.52"
$SshUser = "ubuntu"
$SiteUser = "floorstodayfinal"
$LiveSiteRoot = "/home/floorstodayfinal/htdocs/floorstoday.ca"

$MysqlBin = "C:\wamp64\bin\mysql\mysql8.4.7\bin"
$LocalDbName = "floorstoday-data"
$LocalDbUser = "root"
$LocalDbHost = "localhost"

$scriptDir = Split-Path -Parent $MyInvocation.MyCommand.Path
$dumpScript = Join-Path $scriptDir "_dump-live-db.sh"

$scratchDir = "C:\wamp64\www\floorstodayfinal_migration"
$timestamp = Get-Date -Format "yyyyMMdd-HHmmss"
$localBackupFile = Join-Path $scratchDir "local-db-backup-$timestamp.sql"
$liveDumpFile = Join-Path $scratchDir "live-db-pull-$timestamp.sql"
$remoteDumpPath = "/tmp/live-db-pull-$timestamp.sql"
$remoteScriptPath = "/tmp/_dump-live-db-$timestamp.sh"

if (-not (Test-Path $scratchDir)) {
    New-Item -ItemType Directory -Path $scratchDir | Out-Null
}

function Remove-RemoteTemp {
    # The dump is owned by $SiteUser (created via sudo -u), the uploaded
    # script is owned by $SshUser (plain scp) - different owners need
    # different removal contexts. Failures here shouldn't abort the script,
    # the important data has already been transferred by this point.
    try { ssh "${SshUser}@${LiveHost}" "sudo -u $SiteUser rm -f $remoteDumpPath" 2>$null } catch {}
    try { ssh "${SshUser}@${LiveHost}" "rm -f $remoteScriptPath" 2>$null } catch {}
}

trap {
    Write-Host "Pull failed: $_" -ForegroundColor Red
    Remove-RemoteTemp
    exit 1
}

Write-Host "== Dumping live database on the server (credentials never leave the server) =="
scp -q $dumpScript "${SshUser}@${LiveHost}:${remoteScriptPath}"
$dumpResult = ssh "${SshUser}@${LiveHost}" "sudo -u $SiteUser bash $remoteScriptPath $LiveSiteRoot/wp-config.php $remoteDumpPath"
if ($dumpResult -notmatch "DUMP_OK:") {
    throw "Remote dump did not confirm success. Output: $dumpResult"
}
Write-Host "Dumped: $dumpResult"

Write-Host "== Downloading dump to $liveDumpFile =="
scp -q "${SshUser}@${LiveHost}:${remoteDumpPath}" $liveDumpFile

Write-Host "== Cleaning up remote temp files =="
Remove-RemoteTemp

if (-not (Test-Path $liveDumpFile) -or (Get-Item $liveDumpFile).Length -eq 0) {
    throw "Downloaded dump is missing or empty - aborting before touching local DB"
}
Write-Host "Downloaded dump size: $((Get-Item $liveDumpFile).Length) bytes"

Write-Host "== Backing up current local database to $localBackupFile (safety net) =="
& "$MysqlBin\mysqldump.exe" -u $LocalDbUser -h $LocalDbHost $LocalDbName | Out-File -FilePath $localBackupFile -Encoding utf8
if (-not (Test-Path $localBackupFile) -or (Get-Item $localBackupFile).Length -eq 0) {
    throw "Local backup failed or is empty - aborting before overwriting local DB"
}
Write-Host "Local backup complete: $((Get-Item $localBackupFile).Length) bytes"

Write-Host "== Importing live dump into local database ($LocalDbName) =="
Get-Content $liveDumpFile -Raw | & "$MysqlBin\mysql.exe" -u $LocalDbUser -h $LocalDbHost $LocalDbName
if ($LASTEXITCODE -ne 0) {
    Write-Error "Import failed. Local DB may be in a partial state. Restore from: $localBackupFile"
    exit 1
}

Write-Host "== Fixing siteurl/home (imported values point to the live domain) =="
$fixSql = @"
UPDATE floors1_options SET option_value = 'http://localhost/floorstodayfinal' WHERE option_name = 'siteurl';
UPDATE floors1_options SET option_value = 'http://localhost/floorstodayfinal' WHERE option_name = 'home';
"@
$fixSql | & "$MysqlBin\mysql.exe" -u $LocalDbUser -h $LocalDbHost $LocalDbName
if ($LASTEXITCODE -ne 0) {
    Write-Warning "Could not auto-fix siteurl/home - local site will show 404s until you run this manually:"
    Write-Warning $fixSql
}

Write-Host ""
Write-Host "Pull complete." -ForegroundColor Green
Write-Host "  Local DB backup (pre-import): $localBackupFile"
Write-Host "  Live dump imported from:      $liveDumpFile"
Write-Host "  To roll back: Get-Content '$localBackupFile' -Raw | & `"$MysqlBin\mysql.exe`" -u $LocalDbUser -h $LocalDbHost $LocalDbName"
