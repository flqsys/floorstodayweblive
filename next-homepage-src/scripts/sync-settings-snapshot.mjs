// Overwrites homepage-settings.snapshot.json with the live CRM/WordPress
// settings response, run as a required step in deploy-live.ps1 before
// `npm run build`. This file gets compiled directly into the JS bundle as
// the pre-hydration fallback (see homepage-settings-provider.tsx) - if it's
// left stale, whatever was on disk at build time (including local dev data)
// ships to every visitor. Regenerating it from live data on every deploy
// means it can never silently drift again.

const ENDPOINT = "https://floorstoday.ca/wp-json/floors-today/v1/homepage"
const OUT_PATH = new URL("../lib/homepage-settings.snapshot.json", import.meta.url)

const response = await fetch(ENDPOINT, { cache: "no-store" })
if (!response.ok) {
  throw new Error(`Failed to fetch live homepage settings: ${response.status} ${response.statusText}`)
}

const data = await response.json()
const { writeFile } = await import("node:fs/promises")
await writeFile(OUT_PATH, JSON.stringify(data, null, 2) + "\n")

console.log(`Updated homepage-settings.snapshot.json from ${ENDPOINT}`)
