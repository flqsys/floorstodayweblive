"use client"

import {
  createContext,
  useContext,
  useEffect,
  useLayoutEffect,
  useMemo,
  useState,
  type CSSProperties,
  type ReactNode,
} from "react"
import {
  homepageDefaults,
  mergeHomepageSettings,
  type HomepageSettings,
} from "@/lib/homepage-settings"
import homepageSnapshot from "@/lib/homepage-settings.snapshot.json"

const homepageEndpoint =
  process.env.NEXT_PUBLIC_WORDPRESS_HOMEPAGE_ENDPOINT || "/wp-json/floors-today/v1/homepage"

function localInstallPath() {
  if (typeof window === "undefined") return ""

  const publicMarker = "/public/"
  const publicIndex = window.location.pathname.indexOf(publicMarker)

  if (publicIndex >= 0) {
    return window.location.pathname.slice(0, publicIndex)
  }

  const firstSegment = window.location.pathname.split("/").filter(Boolean)[0]

  return firstSegment ? `/${firstSegment}` : ""
}

function endpointUrl(endpoint: string) {
  if (/^https?:\/\//i.test(endpoint)) {
    return endpoint
  }

  const installPath = localInstallPath()
  const path = endpoint.startsWith("/") ? endpoint : `/${endpoint.replace(/^\/+/, "")}`
  return `${installPath}${path}`
}

function wordpressInstallPath() {
  const endpoint = endpointUrl(homepageEndpoint)

  if (/^https?:\/\//i.test(endpoint)) {
    try {
      const url = new URL(endpoint)
      return url.pathname.split("/wp-json/")[0] || ""
    } catch {
      return ""
    }
  }

  return endpoint.split("/wp-json/")[0] || ""
}

function normalizeAssetUrl(value: unknown) {
  if (typeof value !== "string" || !value) {
    return value
  }

  if (/^(https?:|data:|blob:|mailto:|tel:|#)/i.test(value)) {
    return value
  }

  // Strip legacy install-prefix from stored upload paths (e.g. /floorstodayhome/wp-content/...)
  const wpContentMatch = value.match(/^\/[^/]+(\/(wp-content|wp-includes)\/.+)$/)
  if (wpContentMatch) {
    return `${wordpressInstallPath()}${wpContentMatch[1]}`
  }

  if (value.startsWith("/wp-content/") || value.startsWith("/wp-includes/")) {
    return `${wordpressInstallPath()}${value}`
  }

  return value
}

function normalizeCategoryUrl(href: string): string {
  return href.replace(/\/(?:a\/)?product-category\/([\w-]+)\//g, "/categories/$1/")
}

function normalizeHomepageSettings(data: Partial<HomepageSettings>) {
  return {
    ...data,
    logo_image: normalizeAssetUrl(data.logo_image) as string,
    favicon_image: normalizeAssetUrl(data.favicon_image) as string,
    hero_image: normalizeAssetUrl(data.hero_image) as string,
    guarantee_image: normalizeAssetUrl(data.guarantee_image) as string,
    process_steps: data.process_steps?.map((step) => ({
      ...step,
      image: normalizeAssetUrl(step.image) as string,
    })),
    categories: data.categories?.map((category) => ({
      ...category,
      image: normalizeAssetUrl(category.image) as string,
    })),
    nav_items: data.nav_items?.map((item) => ({
      ...item,
      href: normalizeCategoryUrl(item.href),
    })),
  }
}

declare global {
  interface Window {
    __FT_HOMEPAGE_SETTINGS__?: Partial<HomepageSettings>
  }
}

type HomepageSettingsContextValue = {
  settings: HomepageSettings
  isLoaded: boolean
  hasError: boolean
}

const HomepageSettingsContext = createContext<HomepageSettingsContextValue>({
  settings: homepageDefaults,
  isLoaded: false,
  hasError: false,
})

export function HomepageSettingsProvider({
  children,
  initialSettings,
}: {
  children: ReactNode
  initialSettings?: Partial<HomepageSettings>
}) {
  const snapshotSettings = homepageSnapshot as Partial<HomepageSettings>
  // Use raw (un-normalized) settings for initial state so server and client agree during hydration.
  // normalizeAssetUrl reads window.location which differs between SSR ("") and client (install path).
  const [settings, setSettings] = useState<HomepageSettings>(() =>
    mergeHomepageSettings(initialSettings ?? snapshotSettings),
  )
  const [isLoaded, setIsLoaded] = useState(true)
  const [hasError, setHasError] = useState(false)

  useLayoutEffect(() => {
    // Normalize asset URLs after hydration so the install prefix is computed client-side.
    const source = window.__FT_HOMEPAGE_SETTINGS__ ?? snapshotSettings
    setSettings(mergeHomepageSettings(normalizeHomepageSettings(source)))
    setIsLoaded(true)
    setHasError(false)
  }, [])

  useEffect(() => {
    let alive = true

    fetch(endpointUrl(homepageEndpoint), { cache: "no-store" })
      .then((response) => {
        if (!response.ok) {
          throw new Error(`Homepage settings request failed: ${response.status}`)
        }

        return response.json()
      })
      .then((data) => {
        if (alive) {
          setSettings(mergeHomepageSettings(normalizeHomepageSettings(data)))
          setIsLoaded(true)
          setHasError(false)
        }
      })
      .catch(() => {
        if (alive) {
          setIsLoaded(true)
          setHasError(false)
        }
      })

    return () => {
      alive = false
    }
  }, [])

  // Inject Facebook Pixel after hydration (homepage — PHP pages use wp_head instead)
  useEffect(() => {
    const pixelId = settings.fb_pixel_id
    if (!pixelId || (window as unknown as Record<string, unknown>).fbq) return
    const s = document.createElement("script")
    s.innerHTML = `!function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window,document,'script','https://connect.facebook.net/en_US/fbevents.js');fbq('init','${pixelId}');fbq('track','PageView');`
    document.head.appendChild(s)
  }, [settings.fb_pixel_id])

  // Inject Google Tag Manager after hydration (homepage — PHP pages use wp_head instead)
  useEffect(() => {
    const gtmId = settings.gtm_container_id
    if (!gtmId || document.getElementById("ft-gtm-script")) return
    const w = window as unknown as Record<string, unknown>
    w.dataLayer = w.dataLayer || []
    ;(w.dataLayer as unknown[]).push({ "gtm.start": Date.now(), event: "gtm.js" })
    const s = document.createElement("script")
    s.id = "ft-gtm-script"
    s.async = true
    s.src = `https://www.googletagmanager.com/gtm.js?id=${encodeURIComponent(gtmId)}`
    document.head.appendChild(s)
  }, [settings.gtm_container_id])

  // Inject GA4 after hydration (homepage — PHP pages use the GA plugin via wp_head instead)
  useEffect(() => {
    const ga4Id = settings.ga4_measurement_id
    if (!ga4Id || document.getElementById("ft-ga4-script")) return
    const w = window as unknown as Record<string, unknown>
    w.dataLayer = w.dataLayer || []
    w.gtag = function (...args: unknown[]) { (w.dataLayer as unknown[]).push(args) }
    ;(w.gtag as (...args: unknown[]) => void)("js", new Date())
    ;(w.gtag as (...args: unknown[]) => void)("config", ga4Id)
    const s = document.createElement("script")
    s.id = "ft-ga4-script"
    s.async = true
    s.src = `https://www.googletagmanager.com/gtag/js?id=${encodeURIComponent(ga4Id)}`
    document.head.appendChild(s)
  }, [settings.ga4_measurement_id])

  // Inject chat widget script after hydration so it isn't wiped by React.
  // PHP pages get the same script via wp_footer instead.
  useEffect(() => {
    const src = settings.chat_script_src
    if (!src) return
    const scriptId = settings.chat_script_id || "ft-chat-widget"
    if (document.getElementById(scriptId)) return
    const script = document.createElement("script")
    script.src = src
    script.id = scriptId
    script.defer = true
    document.body.appendChild(script)
  }, [settings.chat_script_src, settings.chat_script_id])

  useEffect(() => {
    const favicon = settings.favicon_image

    if (!favicon) {
      return
    }

    const rels = ["icon", "shortcut icon", "apple-touch-icon"]

    rels.forEach((rel) => {
      let link = document.querySelector<HTMLLinkElement>(`link[rel="${rel}"]`)

      if (!link) {
        link = document.createElement("link")
        link.rel = rel
        document.head.appendChild(link)
      }

      link.href = favicon
    })
  }, [settings.favicon_image])

  const style = useMemo(
    () =>
      ({
        "--primary": settings.primary_color,
        "--ring": settings.primary_color,
        "--chart-1": settings.primary_color,
        "--sidebar-primary": settings.primary_color,
        "--sidebar-ring": settings.primary_color,
        "--secondary": settings.secondary_color,
        "--accent": settings.secondary_color,
        "--chart-2": settings.secondary_color,
        "--background": settings.background_color,
        "--foreground": settings.foreground_color,
        "--ft-button-radius": settings.button_radius,
        "--ft-button-font-weight": settings.button_font_weight,
        "--ft-button-text-transform": settings.button_text_transform,
        "--ft-button-padding-x": settings.button_padding_x,
        "--ft-button-padding-y": settings.button_padding_y,
        "--ft-button-hover-mix": settings.button_hover_mix,
        "--ft-button-border-width": settings.button_border_width,
        "--ft-button-border-style": settings.button_border_style,
        "--ft-button-border-color": settings.button_border_color,
        "--ft-hero-badge-bg": settings.hero_badge_bg_color,
        "--ft-hero-badge-text": settings.hero_badge_text_color,
        "--ft-hero-badge-font-size": settings.hero_badge_font_size,
        "--ft-hero-badge-padding-x": settings.hero_badge_padding_x,
        "--ft-hero-badge-padding-y": settings.hero_badge_padding_y,
        "--ft-hero-badge-animation-color-1": settings.hero_badge_animation_color_1,
        "--ft-hero-badge-animation-color-2": settings.hero_badge_animation_color_2,
        "--ft-hero-badge-animation-location": settings.hero_badge_animation_location,
        "--ft-hero-badge-animation-speed": settings.hero_badge_animation_speed,
      }) as CSSProperties,
    [settings],
  )

  return (
    <HomepageSettingsContext.Provider value={{ settings, isLoaded, hasError }}>
      <div className="ft-homepage-shell" data-ready={isLoaded && !hasError ? "true" : "false"} style={style}>
        {!isLoaded ? (
          <main className="grid min-h-screen place-items-center bg-background px-4 text-foreground">
            <p className="text-sm font-medium text-foreground/70">Loading Floors Today...</p>
          </main>
        ) : hasError ? (
          <main className="grid min-h-screen place-items-center bg-background px-4 text-center text-foreground">
            <div>
              <p className="text-2xl font-bold text-primary">{settings.logo_text}</p>
              <p className="mt-3 max-w-md text-sm text-foreground/70">
                Homepage settings could not load. Please refresh the page.
              </p>
            </div>
          </main>
        ) : (
          children
        )}
      </div>
    </HomepageSettingsContext.Provider>
  )
}

export function useHomepageSettings() {
  return useContext(HomepageSettingsContext).settings
}

export function useHomepageSettingsStatus() {
  return useContext(HomepageSettingsContext)
}
