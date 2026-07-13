# Mirrors the Next.js static export (out/) into the WordPress-served public/
# folder. /MIR deletes anything in public/ that no longer exists in out/, so
# orphaned CSS/JS chunks from old builds cannot pile up.
#
# Usage (from next-homepage-src/):
#   npm run build
#   ./scripts/sync-public.ps1

$ErrorActionPreference = "Stop"

$scriptDir = Split-Path -Parent $MyInvocation.MyCommand.Path
$source = Resolve-Path (Join-Path $scriptDir "..\out")
$destination = Resolve-Path (Join-Path $scriptDir "..\..\public")

# `next build` never wipes out/ between runs - it only adds a new
# randomly-named build-ID folder under out/_next/static/ (chunks/ and
# media/ are always present and not build-IDs). If more than one build-ID
# folder exists, a stale one from a previous build could get shipped
# alongside the fresh one - refuse to sync rather than silently mirroring
# stale cruft.
$buildIdDirs = Get-ChildItem (Join-Path $source "_next\static") -Directory |
    Where-Object { $_.Name -notin @("chunks", "media") }
if ($buildIdDirs.Count -gt 1) {
    Write-Error "Found $($buildIdDirs.Count) build-ID folders in out/_next/static/ (expected 1): $($buildIdDirs.Name -join ', '). Delete out/ and run 'npm run build' again before syncing."
    exit 1
}

Write-Host "Syncing $source -> $destination"

# /MIR   mirror (deletes files in destination not present in source)
# /XF    exclude .htaccess from the purge - it's hand-maintained in public/,
#        not part of Next's build output, so /MIR would delete it every run
# /NFL /NDL /NJH /NJS  quiet the noisy per-file/per-dir logging
#
# Next's RSC prefetch payload files (__next*.txt, index.txt, _not-found.txt)
# ARE actively fetched by the client router at runtime (confirmed via
# console 404s when they were excluded) - keep them in the sync.
robocopy $source $destination /MIR /XF ".htaccess" /NFL /NDL /NJH /NJS

$exitCode = $LASTEXITCODE
# robocopy exit codes 0-7 are success (see `robocopy /?`); 8+ indicates failure.
if ($exitCode -ge 8) {
    Write-Error "robocopy failed with exit code $exitCode"
    exit $exitCode
}

Write-Host "Sync complete (robocopy exit code $exitCode)"
exit 0
