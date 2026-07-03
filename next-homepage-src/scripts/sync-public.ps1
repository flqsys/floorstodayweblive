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

Write-Host "Syncing $source -> $destination"

# /MIR   mirror (deletes files in destination not present in source)
# /XF    exclude these files: Next's RSC prefetch payloads are unused by
#        this single-page static export and are blocked by public/.htaccess
#        anyway; keep them out of the deploy target entirely.
# /NFL /NDL /NJH /NJS  quiet the noisy per-file/per-dir logging
robocopy $source $destination /MIR /XF "__next.*.txt" "index.txt" "_not-found.txt" /NFL /NDL /NJH /NJS

$exitCode = $LASTEXITCODE
# robocopy exit codes 0-7 are success (see `robocopy /?`); 8+ indicates failure.
if ($exitCode -ge 8) {
    Write-Error "robocopy failed with exit code $exitCode"
    exit $exitCode
}

Write-Host "Sync complete (robocopy exit code $exitCode)"
exit 0
