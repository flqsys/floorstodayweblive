"use client"

import { useEffect, useRef } from "react"
import { ArrowRight } from "lucide-react"
import { useHomepageSettings } from "@/components/homepage-settings-provider"

function isEnabled(value: string | boolean | undefined) {
  return value === true || value === "1" || value === "true"
}

// Promotion bar along the top of the header (Home Settings > Promotion bar).
// On phones the header is position:fixed with a fixed body offset from the
// WordPress runtime bridge, so the bar publishes its height as
// --ft-promo-bar-h for that offset to include.
export function PromoBar() {
  const settings = useHomepageSettings()
  const barRef = useRef<HTMLDivElement>(null)
  const show = isEnabled(settings.promo_bar_show) && Boolean(settings.promo_bar_text || settings.promo_bar_title)

  useEffect(() => {
    const root = document.documentElement
    const bar = barRef.current

    if (!show || !bar) {
      root.style.removeProperty("--ft-promo-bar-h")
      return
    }

    const update = () => root.style.setProperty("--ft-promo-bar-h", `${bar.offsetHeight}px`)
    update()
    const observer = new ResizeObserver(update)
    observer.observe(bar)

    return () => {
      observer.disconnect()
      root.style.removeProperty("--ft-promo-bar-h")
    }
  }, [show])

  if (!show) return null

  return (
    <div
      ref={barRef}
      className="ft-promo-bar bg-accent text-accent-foreground"
      style={{
        backgroundColor: settings.promo_bar_bg_color || undefined,
        color: settings.promo_bar_text_color || undefined,
      }}
    >
      <div className="mx-auto max-w-[1340px] px-4 py-2 text-center text-xs leading-snug sm:text-sm">
        {/* Phones get just the short text + link so the bar stays one line */}
        {settings.promo_bar_title && (
          <strong className={settings.promo_bar_mobile_text ? "hidden font-bold sm:inline" : "font-bold"}>
            {settings.promo_bar_title}:{" "}
          </strong>
        )}
        <span className={settings.promo_bar_mobile_text ? "hidden sm:inline" : undefined}>{settings.promo_bar_text} </span>
        {settings.promo_bar_mobile_text && <span className="sm:hidden">{settings.promo_bar_mobile_text} </span>}
        {settings.promo_bar_ends && <span className="whitespace-nowrap font-semibold">{settings.promo_bar_ends} </span>}
        {settings.deals_details_label && (
          <button
            type="button"
            onClick={() => window.dispatchEvent(new CustomEvent("ft:open-offer-details"))}
            className="inline-flex items-center gap-1 whitespace-nowrap border-0 bg-transparent p-0 align-baseline font-semibold leading-[inherit] text-inherit underline underline-offset-2 hover:no-underline focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-current"
          >
            {settings.deals_details_label}
            <ArrowRight className="size-3.5" aria-hidden="true" />
          </button>
        )}
      </div>
    </div>
  )
}
