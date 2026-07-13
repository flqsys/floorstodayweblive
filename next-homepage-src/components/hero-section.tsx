"use client"

import { useState, useRef, useEffect } from "react"
import Image from "next/image"
import { Button } from "@/components/ui/button"
import { Input } from "@/components/ui/input"
import { Badge } from "@/components/ui/badge"
import { Card, CardContent } from "@/components/ui/card"
import { CheckCircle, Phone, Calendar, Shield, Star, ArrowRight, MapPin, ChevronLeft, Home, Building2, BriefcaseBusiness, Check, User, Mail } from "lucide-react"
import { useHomepageSettings } from "@/components/homepage-settings-provider"

const configuredInboxEndpoint =
  process.env.NEXT_PUBLIC_WORDPRESS_INBOX_ENDPOINT || "/wp-json/floors-today/v1/inbox-leads"

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
    __ftPlacesLoaded?: boolean
    __ftPlacesScript?: boolean
    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    google?: any
    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    grecaptcha?: any
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

function CustomSelect({
  id,
  value,
  onChange,
  options,
  placeholder = "Select...",
}: {
  id: string
  value: string
  onChange: (value: string) => void
  options: string[] | { value: string; label: string }[]
  placeholder?: string
}) {
  const [open, setOpen] = useState(false)
  const wrapRef = useRef<HTMLDivElement>(null)

  const normalizedOptions = options.map((option) =>
    typeof option === "string" ? { value: option, label: option } : option,
  )
  const selectedLabel = normalizedOptions.find((option) => option.value === value)?.label

  useEffect(() => {
    function handleClickOutside(event: MouseEvent) {
      if (wrapRef.current && !wrapRef.current.contains(event.target as Node)) {
        setOpen(false)
      }
    }
    document.addEventListener("click", handleClickOutside)
    return () => document.removeEventListener("click", handleClickOutside)
  }, [])

  return (
    <div ref={wrapRef} className="relative">
      <button
        type="button"
        id={id}
        onClick={() => setOpen((prev) => !prev)}
        aria-haspopup="listbox"
        aria-expanded={open}
        className={`flex h-12 w-full items-center rounded-lg border bg-white pl-4 pr-11 text-left text-base font-medium outline-none transition-[border-color,box-shadow] hover:border-stone-300 ${
          selectedLabel ? "text-slate-900" : "text-slate-400"
        } ${
          open
            ? "border-primary shadow-[0_0_0_3px_color-mix(in_srgb,var(--color-primary)_18%,transparent)]"
            : "border-stone-200 shadow-sm"
        }`}
      >
        <span className="truncate">{selectedLabel || placeholder}</span>
      </button>
      <span
        className={`pointer-events-none absolute right-4 top-1/2 block -translate-y-1/2 bg-slate-400 transition-transform duration-150 ${
          open ? "rotate-180" : ""
        }`}
        style={{ width: "0.65em", height: "0.42em", clipPath: "polygon(100% 0%, 0% 0%, 50% 100%)" }}
        aria-hidden="true"
      />
      {open && (
        <div
          role="listbox"
          className="absolute z-30 mt-2 max-h-64 w-full overflow-auto rounded-lg border border-stone-200 bg-white p-1.5 shadow-[0_18px_45px_rgba(15,23,42,0.16)]"
        >
          {normalizedOptions.map((option) => (
            <button
              key={option.value}
              type="button"
              role="option"
              aria-selected={value === option.value}
              onClick={() => {
                onChange(option.value)
                setOpen(false)
              }}
              className={`block w-full rounded-md px-2.5 py-2 text-left text-sm transition-colors ${
                value === option.value
                  ? "bg-primary/20 font-bold text-slate-900"
                  : "text-slate-900 hover:bg-primary/10"
              }`}
            >
              {option.label}
            </button>
          ))}
        </div>
      )}
    </div>
  )
}

export function HeroSection() {
  const [step, setStep] = useState(1)
  const [isSubmitting, setIsSubmitting] = useState(false)
  const [isSubmitted, setIsSubmitted] = useState(false)
  const [submitError, setSubmitError] = useState("")
  const settings = useHomepageSettings()
  const inboxEndpoint = endpointUrl(configuredInboxEndpoint)

  // Load reCAPTCHA v3 script when a site key is configured
  useEffect(() => {
    const key = settings.recaptcha_site_key
    if (!key || document.querySelector('script[src*="recaptcha/api.js"]')) return
    const script = document.createElement("script")
    script.src = `https://www.google.com/recaptcha/api.js?render=${encodeURIComponent(key)}`
    script.async = true
    document.head.appendChild(script)
  }, [settings.recaptcha_site_key])

  const [formData, setFormData] = useState({
    fullName: "",
    email: "",
    phone: "+1 ",
    street: "",
    unit: "",
    city: "",
    province: "ON",
    postalCode: "",
    country: "Canada",
    flooringType: "",
    propertyType: "",
    startTime: "ASAP",
    preferredVisitTime: "Morning",
    ftInboxTrap: "",
  })

  const streetRef = useRef<HTMLInputElement>(null)

  useEffect(() => {
    function initPlaces() {
      const streetEl = streetRef.current
      if (!streetEl || streetEl.dataset.acInit) return
      streetEl.dataset.acInit = "1"
      const ac = new window.google.maps.places.Autocomplete(streetEl, {
        types: ["address"],
        componentRestrictions: { country: "ca" },
        fields: ["address_components"],
      })
      ac.addListener("place_changed", () => {
        const place = ac.getPlace()
        if (!place?.address_components) return
        const map: Record<string, { long_name: string; short_name: string }> = {}
        // eslint-disable-next-line @typescript-eslint/no-explicit-any
        place.address_components.forEach((c: any) => {
          // eslint-disable-next-line @typescript-eslint/no-explicit-any
          c.types.forEach((t: any) => { map[t] = c })
        })
        const streetNumber = map["street_number"]?.long_name ?? ""
        const route = map["route"]?.long_name ?? ""
        setFormData((prev) => ({
          ...prev,
          street: `${streetNumber} ${route}`.trim(),
          city: map["locality"]?.long_name ?? prev.city,
          province: map["administrative_area_level_1"]?.short_name ?? prev.province,
          postalCode: map["postal_code"]?.long_name ?? prev.postalCode,
        }))
      })
    }

    if (window.__ftPlacesLoaded) {
      initPlaces()
    } else {
      document.addEventListener("ft:places:ready", initPlaces, { once: true })
    }
    return () => {
      document.removeEventListener("ft:places:ready", initPlaces)
    }
  }, [])

  const flooringOptions = ["Solid Hardwood", "Engineered Hardwood", "Laminate", "Vinyl", "Carpet", "Not sure yet"]
  const propertyOptions = [
    { label: "Residential", icon: Home },
    { label: "Office Space", icon: Building2 },
    { label: "Business", icon: BriefcaseBusiness },
  ]
  const showBackground = settings.hero_show_background === true || settings.hero_show_background === "1" || settings.hero_show_background === "true"
  const showOverlay = settings.hero_show_overlay === true || settings.hero_show_overlay === "1" || settings.hero_show_overlay === "true"
  const overlayOpacity = Math.max(0, Math.min(1, Number.parseFloat(settings.hero_overlay_opacity) || 0))

  const handleNext = () => {
    setSubmitError("")

    if (step === 1 && !formData.flooringType) {
      setSubmitError("Please choose a flooring option.")
      return
    }

    if (step === 2 && !formData.propertyType) {
      setSubmitError("Please choose a property type.")
      return
    }

    if (step === 3) {
      const name = formData.fullName.trim()
      if (!name) { setSubmitError("Please enter your full name."); return }
      if (name.split(/\s+/).filter(Boolean).length < 2) { setSubmitError("Please enter your first and last name."); return }
      if (!formData.email.trim()) { setSubmitError("Please enter your email address."); return }
      if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(formData.email.trim())) { setSubmitError("Please enter a valid email address."); return }
      if (!formData.phone.replace(/^\+1\s*/, "").trim()) { setSubmitError("Please enter your phone number."); return }
    }

    if (step < 4) setStep(step + 1)
  }

  const handlePrev = () => {
    if (step > 1) setStep(step - 1)
  }

  const handleSubmit = async (e: React.FormEvent<HTMLFormElement>) => {
    e.preventDefault()
    setSubmitError("")

    const submitFormData = new FormData(e.currentTarget)
    const smsConsent = submitFormData.get("smsConsent") === "on"
    const emailConsent = submitFormData.get("emailConsent") === "on"
    const privacyConsent = submitFormData.get("privacyConsent") === "on"
    const nameParts = formData.fullName.trim().split(/\s+/).filter(Boolean)

    if (nameParts.length < 2) {
      setSubmitError("Please enter your first and last name.")
      return
    }

    setIsSubmitting(true)

    const { street, unit, city, province, postalCode, country, ...restFormData } = formData
    const streetActual = streetRef.current?.value || street
    const addressParts = [
      streetActual + (unit ? ` Unit ${unit}` : ""),
      city,
      province,
      postalCode,
      country,
    ].filter(Boolean)
    const address = addressParts.join(", ")

    try {
      const url = new URL(window.location.href)
      const referrer = document.referrer
      const referrerHost = referrer ? new URL(referrer).hostname.replace(/^www\./, "") : ""
      const attribution = window.ftGetAttribution ? window.ftGetAttribution() : {}
      const utmSource =
        attribution.utmSource || url.searchParams.get("hello_social") || url.searchParams.get("utm_source") || ""
      const trafficSource = attribution.trafficSource || utmSource || attribution.referrerHost || referrerHost || "Direct"
      const userAgent = navigator.userAgent
      const devicePlatform = /Mobi|Android|iPhone|iPad/i.test(userAgent)
        ? "Mobile / Tablet"
        : "Desktop"

      // Get reCAPTCHA v3 token if configured
      let recaptchaToken = ""
      const recaptchaKey = settings.recaptcha_site_key
      if (recaptchaKey && window.grecaptcha) {
        try {
          await new Promise<void>((resolve) => window.grecaptcha.ready(resolve))
          recaptchaToken = await window.grecaptcha.execute(recaptchaKey, { action: "estimate" })
        } catch {
          // non-blocking â€” submit without token if reCAPTCHA fails
        }
      }

      const response = await fetch(inboxEndpoint, {
        method: "POST",
        credentials: "same-origin",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify({
          ...restFormData,
          address,
          street: streetActual,
          unit,
          city,
          province,
          postalCode,
          source: "Homepage hero estimate form",
          pageUrl: window.location.href,
          trafficSource,
          referrerUrl: attribution.referrerUrl || referrer,
          utmSource,
          utmMedium: attribution.utmMedium || url.searchParams.get("utm_medium") || "",
          utmCampaign: attribution.utmCampaign || url.searchParams.get("utm_campaign") || "",
          utmContent: attribution.utmContent || url.searchParams.get("utm_content") || "",
          utmTerm: attribution.utmTerm || url.searchParams.get("utm_term") || "",
          devicePlatform,
          smsConsent,
          emailConsent,
          privacyConsent,
          ...(recaptchaToken ? { recaptchaToken } : {}),
        }),
      })

      const result = await response.json().catch(() => null)

      if (!response.ok) {
        throw new Error(result?.message || "We could not send your request.")
      }

      // â”€â”€ Conversion tracking â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
      const trackingData = {
        formId: "hero_estimate",
        formName: "Homepage Hero Estimate Form",
        flooringType: formData.flooringType,
        propertyType: formData.propertyType,
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
      // â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

      setIsSubmitted(true)
    } catch (error) {
      setSubmitError(
        error instanceof Error
          ? error.message
          : "We could not send your request. Please try again.",
      )
    } finally {
      setIsSubmitting(false)
    }
  }

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

          <Card
            id="estimate"
            className="scroll-mt-28 w-full max-w-[625px] justify-self-center overflow-visible rounded-[1.05rem] border border-white/50 bg-white shadow-2xl shadow-black/20 xl:justify-self-end"
          >
            <CardContent className="px-5 py-6 min-[390px]:px-6 min-[390px]:py-7 sm:p-9 lg:p-9">
              <div className="text-center">
                <h2 className="mx-auto max-w-[18ch] font-serif text-[25px] font-bold leading-[1.12] text-slate-950 sm:max-w-none sm:text-[1.9rem]">
                  {settings.form_title}
                </h2>
                <p className="mt-3 text-[13px] leading-5 text-slate-700">
                  {settings.form_subtitle}
                </p>
              </div>

                <div className="my-6 flex items-center justify-center sm:my-7">
                {[1, 2, 3, 4].map((item) => {
                  const isDone = isSubmitted || item < step
                  const isCurrent = !isSubmitted && item === step

                  return (
                    <div key={item} className="flex items-center">
                      <div
                        className={`flex h-9 w-9 items-center justify-center rounded-full text-sm font-bold transition-colors ${
                          isDone
                            ? "bg-emerald-600 text-white"
                            : isCurrent
                              ? "bg-primary text-primary-foreground"
                              : "bg-stone-100 text-stone-500"
                        }`}
                      >
                        {isDone ? <Check className="h-4 w-4" /> : item}
                      </div>
                      {item < 4 && (
                        <div
                          className={`mx-2 h-0.5 w-7 rounded-full sm:mx-3 sm:w-10 ${
                            isSubmitted || item < step ? "bg-emerald-600" : "bg-stone-100"
                          }`}
                        />
                      )}
                    </div>
                  )
                })}
              </div>

              {isSubmitted ? (
                <div className="flex min-h-[360px] flex-col items-center justify-center text-center">
                  <div className="flex h-16 w-16 items-center justify-center rounded-full bg-emerald-50 text-emerald-700">
                    <CheckCircle className="h-8 w-8" />
                  </div>
                  <h3 className="mt-5 text-2xl font-bold text-slate-950">Request received</h3>
                  <p className="mt-3 max-w-sm text-base leading-relaxed text-slate-600">
                    Thank you, {formData.fullName}. A Floors Today specialist will contact you shortly.
                  </p>
                </div>
              ) : (
              <form onSubmit={handleSubmit}>
                <div className="absolute left-[-10000px] top-auto h-px w-px overflow-hidden" aria-hidden="true">
                  <label htmlFor="ftInboxTrap">Leave this field empty</label>
                  <input
                    id="ftInboxTrap"
                    name="ftInboxTrap"
                    type="text"
                    tabIndex={-1}
                    autoComplete="new-password"
                    value={formData.ftInboxTrap}
                    onChange={(e) => setFormData({ ...formData, ftInboxTrap: e.target.value })}
                  />
                </div>
                {step === 1 && (
                  <div className="space-y-5">
                    <h3 className="font-serif text-[17px] font-bold leading-tight text-slate-950">What floors interest you?</h3>
                    <div className="grid grid-cols-2 gap-3">
                      {flooringOptions.map((option) => (
                        <button
                          key={option}
                          type="button"
                          onClick={() => setFormData({ ...formData, flooringType: option })}
                          className={`w-full min-h-[52px] rounded-md border px-4 py-3 text-left text-sm font-bold leading-snug whitespace-normal break-words transition-colors ${
                            formData.flooringType === option
                              ? "border-primary bg-primary/5 text-primary"
                              : "border-stone-200 bg-white text-slate-900 hover:border-primary"
                          }`}
                        >
                          {option}
                        </button>
                      ))}
                    </div>
                    <div className="flex flex-col items-stretch gap-3 pt-1 min-[420px]:flex-row min-[420px]:items-center min-[420px]:justify-between">
                      <p className="text-sm text-slate-500">Takes under 60 seconds</p>
                      <Button
                        type="button"
                        onClick={handleNext}
                        className="min-h-10 w-full rounded-full bg-primary px-5 text-sm font-bold text-primary-foreground hover:bg-primary/90 min-[420px]:w-auto"
                      >
                        Continue
                        <ArrowRight className="ml-2 h-4 w-4" />
                      </Button>
                    </div>
                  </div>
                )}

                {step === 2 && (
                  <div className="space-y-6">
                    <div>
                      <h3 className="mb-4 text-base font-semibold text-slate-950">Property type</h3>
                      <div className="grid grid-cols-3 gap-3">
                        {propertyOptions.map((option) => (
                          <button
                            key={option.label}
                            type="button"
                            onClick={() => setFormData({ ...formData, propertyType: option.label })}
                            className={`w-full flex min-h-24 flex-col items-center justify-center gap-2 rounded-lg border px-3 py-3 text-sm font-medium whitespace-normal break-words text-center transition-colors ${
                              formData.propertyType === option.label
                                ? "border-secondary bg-secondary/10 text-slate-950"
                                : "border-stone-200 bg-white text-slate-600 hover:border-primary"
                            }`}
                          >
                            <option.icon className="h-6 w-6" />
                            {option.label}
                          </button>
                        ))}
                      </div>
                    </div>

                    <div className="grid grid-cols-2 gap-3">
                      <div>
                        <label htmlFor="startTime" className="mb-3 block text-base font-semibold text-slate-950">
                          When are you looking to start?
                        </label>
                        <CustomSelect
                          id="startTime"
                          value={formData.startTime}
                          onChange={(value) => setFormData({ ...formData, startTime: value })}
                          options={["ASAP", "Within 1 month", "1-3 months", "3+ months", "Just researching"]}
                        />
                      </div>

                      <div>
                        <label htmlFor="preferredVisitTime" className="mb-3 block text-base font-semibold text-slate-950">
                          Preferred visit time
                        </label>
                        <CustomSelect
                          id="preferredVisitTime"
                          value={formData.preferredVisitTime}
                          onChange={(value) => setFormData({ ...formData, preferredVisitTime: value })}
                          options={["Morning", "Afternoon", "Evening"]}
                        />
                      </div>
                    </div>
                  </div>
                )}

                {step === 3 && (
                  <div className="space-y-4">
                    <h3 className="text-base font-semibold text-slate-950">Your contact details</h3>
                    <div>
                      <label htmlFor="fullName" className="mb-2 block text-sm font-medium text-slate-600">
                        Full name
                      </label>
                      <div className="relative">
                        <User className="pointer-events-none absolute left-4 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" aria-hidden="true" />
                        <input
                          id="fullName"
                          name="fullName"
                          type="text"
                          placeholder="Jane Doe"
                          value={formData.fullName}
                          onChange={(e) => setFormData({ ...formData, fullName: e.target.value })}
                          autoComplete="name"
                          pattern="\S+\s+\S+.*"
                          title="Please enter your first and last name."
                          className="h-12 w-full rounded-lg border border-stone-200 bg-white pl-11 pr-4 text-base text-slate-900 outline-none transition-[border-color,box-shadow] focus:border-primary focus:shadow-[0_0_0_3px_color-mix(in_srgb,var(--color-primary)_18%,transparent)]"
                          required
                        />
                      </div>
                    </div>
                    <div>
                      <label htmlFor="email" className="mb-2 block text-sm font-medium text-slate-600">
                        Email
                      </label>
                      <div className="relative">
                        <Mail className="pointer-events-none absolute left-4 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" aria-hidden="true" />
                        <input
                          id="email"
                          name="email"
                          type="email"
                          placeholder="jane@email.com"
                          value={formData.email}
                          onChange={(e) => setFormData({ ...formData, email: e.target.value })}
                          autoComplete="email"
                          className="h-12 w-full rounded-lg border border-stone-200 bg-white pl-11 pr-4 text-base text-slate-900 outline-none transition-[border-color,box-shadow] focus:border-primary focus:shadow-[0_0_0_3px_color-mix(in_srgb,var(--color-primary)_18%,transparent)]"
                          required
                        />
                      </div>
                    </div>
                    <div>
                      <label htmlFor="phone" className="mb-2 block text-sm font-medium text-slate-600">
                        Phone
                      </label>
                      <div className="flex h-12 overflow-hidden rounded-lg border border-stone-200 bg-white transition-[border-color,box-shadow] focus-within:border-primary focus-within:shadow-[0_0_0_3px_color-mix(in_srgb,var(--color-primary)_18%,transparent)]">
                        <span className="flex shrink-0 select-none items-center border-r border-stone-200 bg-stone-50 px-3 text-base text-slate-500">+1</span>
                        <input
                          id="phone"
                          type="tel"
                          value={formData.phone.replace(/^\+1\s*/, "")}
                          onChange={(e) => setFormData({ ...formData, phone: "+1 " + e.target.value })}
                          autoComplete="tel"
                          placeholder="(416) 555-0199"
                          className="min-w-0 flex-1 bg-transparent px-3 text-base text-slate-900 outline-none"
                          required
                        />
                      </div>
                    </div>
                  </div>
                )}

                {step === 4 && (
                  <div className="space-y-4">
                    <h3 className="text-base font-semibold text-slate-950">Where should we visit?</h3>
                    {/* Street + Unit */}
                    <div className="grid gap-3" style={{ gridTemplateColumns: "3fr 1fr" }}>
                      <div>
                        <label htmlFor="street" className="mb-2 block text-sm font-medium text-slate-600">
                          Street address
                        </label>
                        <input
                          ref={streetRef}
                          id="street"
                          name="street"
                          type="text"
                          placeholder="123 Main St"
                          value={formData.street}
                          onChange={(e) => setFormData({ ...formData, street: e.target.value })}
                          autoComplete="address-line1"
                          className="h-12 w-full rounded-lg border border-stone-200 bg-white px-4 text-base text-slate-900 outline-none transition-[border-color,box-shadow] focus:border-primary focus:shadow-[0_0_0_3px_color-mix(in_srgb,var(--color-primary)_18%,transparent)]"
                          required
                        />
                      </div>
                      <div>
                        <label htmlFor="unit" className="mb-2 block text-sm font-medium text-slate-600">
                          Unit
                        </label>
                        <input
                          id="unit"
                          name="unit"
                          type="text"
                          placeholder="2B"
                          value={formData.unit}
                          onChange={(e) => setFormData({ ...formData, unit: e.target.value })}
                          autoComplete="address-line2"
                          className="h-12 w-full rounded-lg border border-stone-200 bg-white px-4 text-base text-slate-900 outline-none transition-[border-color,box-shadow] focus:border-primary focus:shadow-[0_0_0_3px_color-mix(in_srgb,var(--color-primary)_18%,transparent)]"
                        />
                      </div>
                    </div>
                    {/* City + Province */}
                    <div className="grid grid-cols-2 gap-3">
                      <div>
                        <label htmlFor="city" className="mb-2 block text-sm font-medium text-slate-600">
                          City
                        </label>
                        <input
                          id="city"
                          name="city"
                          type="text"
                          placeholder="Toronto"
                          value={formData.city}
                          onChange={(e) => setFormData({ ...formData, city: e.target.value })}
                          autoComplete="address-level2"
                          className="h-12 w-full rounded-lg border border-stone-200 bg-white px-4 text-base text-slate-900 outline-none transition-[border-color,box-shadow] focus:border-primary focus:shadow-[0_0_0_3px_color-mix(in_srgb,var(--color-primary)_18%,transparent)]"
                          required
                        />
                      </div>
                      <div>
                        <label htmlFor="province" className="mb-2 block text-sm font-medium text-slate-600">
                          Province
                        </label>
                        <CustomSelect
                          id="province"
                          value={formData.province}
                          onChange={(value) => setFormData({ ...formData, province: value })}
                          placeholder="Select..."
                          options={[
                            { value: "AB", label: "Alberta" },
                            { value: "BC", label: "British Columbia" },
                            { value: "MB", label: "Manitoba" },
                            { value: "NB", label: "New Brunswick" },
                            { value: "NL", label: "Newfoundland and Labrador" },
                            { value: "NS", label: "Nova Scotia" },
                            { value: "NT", label: "Northwest Territories" },
                            { value: "NU", label: "Nunavut" },
                            { value: "ON", label: "Ontario" },
                            { value: "PE", label: "Prince Edward Island" },
                            { value: "QC", label: "Quebec" },
                            { value: "SK", label: "Saskatchewan" },
                            { value: "YT", label: "Yukon" },
                          ]}
                        />
                      </div>
                    </div>
                    {/* Postal + Country */}
                    <div className="grid grid-cols-2 gap-3">
                      <div>
                        <label htmlFor="postalCode" className="mb-2 block text-sm font-medium text-slate-600">
                          Postal code
                        </label>
                        <input
                          id="postalCode"
                          name="postalCode"
                          type="text"
                          placeholder="A1A 1A1"
                          value={formData.postalCode}
                          onChange={(e) => setFormData({ ...formData, postalCode: e.target.value })}
                          autoComplete="postal-code"
                          className="h-12 w-full rounded-lg border border-stone-200 bg-white px-4 text-base text-slate-900 outline-none transition-[border-color,box-shadow] focus:border-primary focus:shadow-[0_0_0_3px_color-mix(in_srgb,var(--color-primary)_18%,transparent)]"
                          required
                        />
                      </div>
                      <div>
                        <label htmlFor="country" className="mb-2 block text-sm font-medium text-slate-600">
                          Country
                        </label>
                        <input
                          id="country"
                          name="country"
                          type="text"
                          value="Canada"
                          readOnly
                          className="h-12 w-full rounded-lg border border-stone-200 bg-white px-4 text-base text-slate-900 outline-none opacity-65"
                        />
                      </div>
                    </div>
                  </div>
                )}

                {step === 4 && (
                  <div className="mt-5 grid gap-2 rounded-lg border border-slate-200 bg-slate-50 p-4 text-left text-[12px] leading-5 text-slate-700">
                    <label className="group flex items-start gap-3 rounded-md border border-slate-200 bg-white p-3 shadow-sm transition-colors focus-within:border-primary">
                      <input name="privacyConsent" type="checkbox" className="mt-0.5 h-4 w-4 shrink-0 rounded border-slate-300 text-primary shadow-sm focus:ring-2 focus:ring-primary/25" required />
                      <span>I agree to receive promotional emails from Floors Today and have read the <a href="/privacy-policy/" className="font-semibold text-primary underline underline-offset-2">Privacy Policy</a> and <a href="/terms-conditions/" className="font-semibold text-primary underline underline-offset-2">Terms & Conditions</a>.</span>
                    </label>
                    <label className="group flex items-start gap-3 rounded-md border border-slate-200 bg-white p-3 shadow-sm transition-colors focus-within:border-primary">
                      <input name="smsConsent" type="checkbox" className="mt-0.5 h-4 w-4 shrink-0 rounded border-slate-300 text-primary shadow-sm focus:ring-2 focus:ring-primary/25" required />
                      <span>I agree to receive SMS marketing and informational messages from Floors Today at the contact information provided above. Message frequency may vary. Message & data rates may apply. Reply STOP to unsubscribe or HELP for assistance.</span>
                    </label>
                    <label className="group flex items-start gap-3 rounded-md border border-slate-200 bg-white p-3 shadow-sm transition-colors focus-within:border-primary">
                      <input name="emailConsent" type="checkbox" className="mt-0.5 h-4 w-4 shrink-0 rounded border-slate-300 text-primary shadow-sm focus:ring-2 focus:ring-primary/25" required />
                      <span>I agree to receive email marketing communications from Floors Today at the email address provided above. I understand Floors Today may respond to any messages or emails I send.</span>
                    </label>
                  </div>
                )}

                {step > 1 && (
                  <div className="mt-7 flex items-center justify-between gap-3">
                    <button
                      type="button"
                      onClick={handlePrev}
                      className="inline-flex items-center gap-2 rounded-full px-2 py-2 text-base font-medium text-slate-500 hover:text-slate-950"
                    >
                      <ChevronLeft className="h-4 w-4" />
                      Back
                    </button>
                    <Button
                      type={step === 4 ? "submit" : "button"}
                      onClick={step < 4 ? handleNext : undefined}
                      disabled={isSubmitting}
                      className="min-h-10 min-w-0 rounded-full bg-primary px-5 text-sm font-bold text-primary-foreground hover:bg-primary/90 sm:px-5"
                    >
                      {step === 4
                        ? isSubmitting
                          ? "Sending..."
                          : "Get My Free Estimate"
                        : "Continue"}
                      <ArrowRight className="ml-2 h-4 w-4" />
                    </Button>
                  </div>
                )}
                {submitError ? (
                  <p className="mt-4 text-sm font-medium text-red-600" role="alert">
                    {submitError}
                  </p>
                ) : null}
              </form>
              )}
            </CardContent>
          </Card>
        </div>
      </div>
    </section>
  )
}





