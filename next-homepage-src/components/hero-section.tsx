"use client"

import { useRef, useEffect } from "react"
import Image from "next/image"
import { Badge } from "@/components/ui/badge"
import { CheckCircle, Phone, Calendar, Shield, Star, ArrowRight, MapPin, Home } from "lucide-react"
import { useHomepageSettings } from "@/components/homepage-settings-provider"

type LeadAttribution = {
  trafficSource?: string
  referrerUrl?: string
  referrerHost?: string
  utmSource?: string
  utmMedium?: string
  utmCampaign?: string
  utmContent?: string
  utmTerm?: string
}

declare global {
  interface Window {
    ftGetAttribution?: () => LeadAttribution
    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    fbq?: (...args: any[]) => void
    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    gtag?: (...args: any[]) => void
    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    dataLayer?: any[]
    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    XDTrack?: { lead: (data: Record<string, string>) => void }
  }
}

// Renders the CRM's "Estimate Form Embed Code" setting (iframe + resize
// script, editable in Home Settings without a code change). Scripts set via
// dangerouslySetInnerHTML never execute - the container's own script tags
// are re-created and run manually instead, the standard workaround.
function EstimateFormEmbed({ html }: { html: string }) {
  const containerRef = useRef<HTMLDivElement>(null)

  useEffect(() => {
    const container = containerRef.current
    if (!container || !html) return

    container.innerHTML = html

    const scripts = Array.from(container.querySelectorAll("script"))
    scripts.forEach((oldScript) => {
      const newScript = document.createElement("script")
      Array.from(oldScript.attributes).forEach((attr) => {
        newScript.setAttribute(attr.name, attr.value)
      })
      newScript.textContent = oldScript.textContent
      oldScript.replaceWith(newScript)
    })
  }, [html])

  if (!html) {
    return (
      <div className="flex min-h-[420px] items-center justify-center rounded-2xl border border-white/50 bg-white p-6 text-center text-sm text-slate-500 shadow-2xl shadow-black/20">
        Estimate form is not configured yet.
      </div>
    )
  }

  return <div ref={containerRef} />
}

export function HeroSection() {
  const settings = useHomepageSettings()

  const showBackground = settings.hero_show_background === true || settings.hero_show_background === "1" || settings.hero_show_background === "true"
  const showOverlay = settings.hero_show_overlay === true || settings.hero_show_overlay === "1" || settings.hero_show_overlay === "true"
  const overlayOpacity = Math.max(0, Math.min(1, Number.parseFloat(settings.hero_overlay_opacity) || 0))

  // The estimate form itself now lives in the CRM's embedded iframe
  // (settings.estimate_form_embed_code) - it has no ad-tracking pixels of
  // its own and can't reach into this cross-origin parent page to fire
  // any directly. It postMessages 'ft-estimate-submitted' on a successful
  // submit instead (see itech-core's estimate_form.php), so conversion
  // tracking still fires here exactly as it did for the old in-page form.
  useEffect(() => {
    function handleMessage(event: MessageEvent) {
      if (!event.data || event.data.type !== "ft-estimate-submitted") return

      const url = new URL(window.location.href)
      const referrer = document.referrer
      const referrerHost = referrer ? new URL(referrer).hostname.replace(/^www\./, "") : ""
      const attribution = window.ftGetAttribution ? window.ftGetAttribution() : {}
      const utmSource =
        attribution.utmSource || url.searchParams.get("hello_social") || url.searchParams.get("utm_source") || ""
      const trafficSource = attribution.trafficSource || utmSource || attribution.referrerHost || referrerHost || "Direct"

      const trackingData = {
        formId: "hero_estimate",
        formName: "Homepage Hero Estimate Form",
        flooringType: String(event.data.flooringType || ""),
        propertyType: String(event.data.propertyType || ""),
        leadSource: trafficSource,
        utmSource,
      }

      // Facebook Pixel â€” Lead event
      if (window.fbq) {
        window.fbq("track", "Lead", {
          content_name: trackingData.formName,
          content_category: trackingData.flooringType,
          currency: "CAD",
        })
      }

      // GA4 â€” generate_lead event
      if (window.gtag) {
        window.gtag("event", "generate_lead", {
          currency: "CAD",
          form_id: trackingData.formId,
          flooring_type: trackingData.flooringType,
          lead_source: trackingData.leadSource,
        })
      }

      // GTM dataLayer push
      if (window.dataLayer) {
        window.dataLayer.push({
          event: "form_lead",
          form_id: trackingData.formId,
          form_name: trackingData.formName,
          flooring_type: trackingData.flooringType,
          lead_source: trackingData.leadSource,
          utm_source: trackingData.utmSource,
        })
      }

      // XDTrack global (injected by XD CRM plugin on WordPress pages)
      if (window.XDTrack) {
        window.XDTrack.lead(trackingData)
      }
    }

    window.addEventListener("message", handleMessage)
    return () => window.removeEventListener("message", handleMessage)
  }, [])

  return (
    <section className="relative flex min-h-0 items-center overflow-hidden lg:min-h-[90vh]">
      {/* Background Image */}
      <div className="absolute inset-0 z-0">
        {showBackground ? (
          <Image
            src={settings.hero_image}
            alt="Beautiful modern living room with hardwood flooring"
            fill
            className="object-cover object-[58%_center] sm:object-center"
            priority
            sizes="100vw"
          />
        ) : null}
        {showOverlay ? (
          <div
            className="absolute inset-0 bg-foreground"
            style={{ opacity: overlayOpacity }}
          />
        ) : null}
      </div>

      <div className="relative z-10 mx-auto w-full max-w-[1340px] px-4 py-10 sm:py-14 lg:py-16">
        <div className="grid items-center gap-9 sm:gap-8 xl:grid-cols-[minmax(0,1fr)_minmax(625px,625px)] xl:gap-10">
          {/* Left Content */}
          <div className="text-background">
            {/* Trust Badge */}
            <div className="mb-5 flex flex-wrap items-center gap-2 sm:mb-6">
              <div className="flex -space-x-1">
                {[1, 2, 3, 4, 5].map((i) => (
                  <Star key={i} className="h-4 w-4 fill-secondary text-secondary sm:h-5 sm:w-5" />
                ))}
              </div>
              <span className="text-[11px] font-medium text-background/85 min-[380px]:text-xs sm:text-sm">
                Rated 4.9/5 by 2,000+ Ontario homeowners
              </span>
            </div>

            {/* Promotion Badge */}
            <div className="mb-5 sm:mb-6">
              <Badge
                className="ft-hero-badge w-full whitespace-normal border-0 px-4 py-2 text-left font-extrabold tracking-wide"
                style={{
                  backgroundColor: "var(--ft-hero-badge-bg)",
                  color: "var(--ft-hero-badge-text)",
                  fontSize: "var(--ft-hero-badge-font-size)",
                  paddingInline: "var(--ft-hero-badge-padding-x)",
                  paddingBlock: "var(--ft-hero-badge-padding-y)",
                }}
              >
                {settings.hero_badge}
              </Badge>
              <button
                type="button"
                onClick={() => window.dispatchEvent(new CustomEvent("ft:open-offer-details"))}
                className="mt-3 inline-flex items-center gap-1.5 text-sm font-bold text-secondary underline decoration-secondary/50 underline-offset-4 transition-colors hover:text-white hover:decoration-white focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-secondary"
              >
                {settings.deals_details_label}
                <ArrowRight className="h-3.5 w-3.5" aria-hidden="true" />
              </button>
            </div>
            
            <h1 className="max-w-xl font-serif text-[30px] font-bold leading-[1.05] tracking-tight text-balance sm:text-5xl lg:text-6xl">
              {settings.hero_title}{" "}
              <span className="text-secondary">{settings.hero_highlight}</span>
            </h1>

            <p className="mt-5 max-w-lg text-[15px] leading-7 text-background/90 text-pretty sm:mt-6 sm:text-xl">
              {settings.hero_text}
            </p>

            {/* Value Props */}
            <div className="mt-7 grid grid-cols-2 gap-x-3 gap-y-4 sm:mt-8 sm:gap-4">
              {[
                { icon: CheckCircle, text: "No Hidden Fees" },
                { icon: Calendar, text: "Free In-Home Estimate" },
                { icon: Shield, text: "Price Match Guarantee" },
                { icon: Star, text: "Professional Installation" },
              ].map((item, i) => (
                <div key={i} className="flex min-w-0 items-center gap-2.5 sm:gap-3">
                  <div className="flex h-9 w-9 flex-none items-center justify-center rounded-full bg-secondary/20 sm:h-10 sm:w-10">
                    <item.icon className="h-4 w-4 text-secondary sm:h-5 sm:w-5" />
                  </div>
                  <span className="text-xs font-semibold leading-snug text-background min-[390px]:text-sm">{item.text}</span>
                </div>
              ))}
            </div>

            {/* Phone CTA */}
            <div className="mt-8 grid gap-4 min-[440px]:grid-cols-[auto_1fr] min-[440px]:items-center sm:mt-10 sm:flex sm:gap-6">
              <a 
                href={`tel:${settings.phone.replace(/[^\d+]/g, "")}`} 
                className="group flex flex-none items-center gap-3"
              >
                <div className="flex h-10 w-10 flex-none items-center justify-center rounded-full bg-secondary! transition-transform group-hover:scale-110">
                  <Phone className="h-5 w-5 text-secondary-foreground!" />
                </div>
                <div className="min-w-0">
                  <p className="text-sm text-background/70">Call Us Now</p>
                  <p className="text-xl font-bold text-background sm:whitespace-nowrap sm:text-2xl">{settings.phone}</p>
                </div>
              </a>
              <div className="hidden sm:block w-px h-12 bg-background/20" />
              <div className="flex min-w-0 items-start gap-2 border-t border-white/15 pt-4 min-[440px]:border-l min-[440px]:border-t-0 min-[440px]:pl-5 min-[440px]:pt-0 sm:border-0 sm:p-0">
                <MapPin className="h-5 w-5 flex-none text-secondary" />
                <span className="text-background/80">{settings.service_area}</span>
              </div>
            </div>
          </div>

          <div
            id="estimate"
            className="scroll-mt-28 w-full max-w-[625px] justify-self-center xl:justify-self-end"
          >
            <EstimateFormEmbed html={settings.estimate_form_embed_code} />
          </div>
        </div>
      </div>
    </section>
  )
}





