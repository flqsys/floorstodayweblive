"use client"

import { useCallback, useEffect, useRef, useState, type FormEvent } from "react"
import { ArrowLeft, ArrowRight, Check, MapPin, Phone, Star, X } from "lucide-react"
import { endpointUrl, useHomepageSettings, useSiteUrl } from "@/components/homepage-settings-provider"
import { ConsentList, Stepper, consents, type ConsentKey } from "@/components/estimate-hero-form"
import { finishBookingFormLead, getLeadAttribution, provinceFromPostal } from "@/lib/lead-tracking"
import { useAddressAutocomplete } from "@/lib/address-autocomplete"
import { cn } from "@/lib/utils"

const inboxEndpoint =
  process.env.NEXT_PUBLIC_WORDPRESS_INBOX_ENDPOINT || "/wp-json/floors-today/v1/inbox-leads"
const defaultImage = `${process.env.NEXT_PUBLIC_BASE_PATH || ""}/images/estimate-hero/phone-hero.webp`
// Must match ft_inbox_quick_lead_sources() in floors-leads-inbox.php - that's
// what lets this form submit without a last name or email.
const leadSource = "Homepage mobile consultation form"
const phoneQuery = "(max-width: 639.98px)"

const floorOptions = ["Hardwood", "Engineered", "Laminate", "Vinyl", "Carpet", "Not sure yet"]
const POSTAL = /^[A-Za-z]\d[A-Za-z][ -]?\d[A-Za-z]\d$/
const stepLabels = ["Details", "Address", "Confirm"] as const
const provinces = [
  ["AB", "Alberta"], ["BC", "British Columbia"], ["MB", "Manitoba"],
  ["NB", "New Brunswick"], ["NL", "Newfoundland and Labrador"], ["NS", "Nova Scotia"],
  ["NT", "Northwest Territories"], ["NU", "Nunavut"], ["ON", "Ontario"],
  ["PE", "Prince Edward Island"], ["QC", "Quebec"], ["SK", "Saskatchewan"], ["YT", "Yukon"],
] as const

type Errors = Partial<
  Record<"floors" | "firstName" | "phone" | "postalCode" | "email" | "street" | "city" | "consent" | "form", string>
>

function formatPhone(value: string) {
  const d = value.replace(/\D/g, "").slice(0, 10)
  if (d.length < 4) return d
  if (d.length < 7) return `(${d.slice(0, 3)}) ${d.slice(3)}`
  return `(${d.slice(0, 3)}) ${d.slice(3, 6)}-${d.slice(6)}`
}

function formatPostal(value: string) {
  const c = value.replace(/[^A-Za-z0-9]/g, "").toUpperCase().slice(0, 6)
  return c.length > 3 ? `${c.slice(0, 3)} ${c.slice(3)}` : c
}

const inputClass = (error?: string) =>
  cn(
    "h-12 w-full rounded-lg border bg-card px-3.5 text-base text-foreground transition-[border-color,box-shadow] duration-200 placeholder:text-muted-foreground/70 focus:outline-none focus:ring-4",
    error
      ? "border-destructive focus:ring-destructive/15"
      : "border-input hover:border-primary/50 focus:border-primary focus:ring-primary/15",
  )

function ConsultationForm({ onClose }: { onClose: () => void }) {
  const settings = useHomepageSettings()
  const siteUrl = useSiteUrl()
  const phoneHref = `tel:${settings.phone.replace(/[^\d+]/g, "")}`
  const mapUrl = settings.mobile_hero_map_url && settings.mobile_hero_map_url !== "#" ? siteUrl(settings.mobile_hero_map_url) : ""

  const [floors, setFloors] = useState<string[]>([])
  const [firstName, setFirstName] = useState("")
  const [phone, setPhone] = useState("")
  const [postalCode, setPostalCode] = useState("")
  const [step, setStep] = useState(0)
  const [goingBack, setGoingBack] = useState(false)
  const [email, setEmail] = useState("")
  const [street, setStreet] = useState("")
  const [unit, setUnit] = useState("")
  const [city, setCity] = useState("")
  const [province, setProvince] = useState("ON")
  const [rooms, setRooms] = useState("")
  const [consent, setConsent] = useState<Record<ConsentKey, boolean>>({
    privacyConsent: false,
    smsConsent: false,
    emailConsent: false,
  })
  const [trap, setTrap] = useState("")
  const [errors, setErrors] = useState<Errors>({})
  const [pending, setPending] = useState(false)
  const [done, setDone] = useState(false)

  const clearError = (key: keyof Errors) => setErrors((e) => ({ ...e, [key]: undefined, form: undefined }))

  // Google address suggestions on the street field (fills city, province, postal code)
  const streetRef = useRef<HTMLInputElement>(null)
  useAddressAutocomplete(
    streetRef,
    (a) => {
      if (a.street) setStreet(a.street)
      if (a.city) setCity(a.city)
      if (a.province && provinces.some(([code]) => code === a.province)) setProvince(a.province)
      if (a.postalCode) setPostalCode(formatPostal(a.postalCode))
      setErrors((e) => ({ ...e, street: undefined, city: undefined, postalCode: undefined, form: undefined }))
    },
    step === 1,
  )

  const submit = async (ev: FormEvent) => {
    ev.preventDefault()
    if (pending) return

    const e: Errors = {}
    if (step === 0) {
      if (floors.length === 0) e.floors = "Pick at least one option."
      if (!firstName.trim()) e.firstName = "Please enter your first name."
      if (phone.replace(/\D/g, "").length < 10) e.phone = "Enter a 10-digit mobile number."
      if (!POSTAL.test(postalCode.trim())) e.postalCode = "Enter a valid postal code."
    } else if (step === 1) {
      if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.trim())) e.email = "Please enter a valid email address."
      if (!street.trim()) e.street = "Please enter your street address."
      if (!city.trim()) e.city = "Please enter your city."
    } else {
      if (!consents.every((c) => consent[c.key])) e.consent = "Please check all three boxes to continue."
    }
    setErrors(e)
    if (Object.keys(e).length > 0) return

    // Step 1 done: on to the address step, with the province guessed from the postal code
    if (step === 0) {
      setProvince((p) => provinceFromPostal(postalCode) || p)
      setGoingBack(false)
      setStep(1)
      return
    }
    if (step === 1) {
      setGoingBack(false)
      setStep(2)
      return
    }

    const flooringType = floors.join(", ")
    const streetLine = street.trim() + (unit.trim() ? ` Unit ${unit.trim()}` : "")
    setPending(true)
    try {
      const response = await fetch(endpointUrl(inboxEndpoint), {
        method: "POST",
        credentials: "same-origin",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
          fullName: firstName.trim(),
          email: email.trim(),
          phone: `+1 ${phone}`,
          address: [streetLine, city.trim(), province, postalCode.trim(), "Canada"].filter(Boolean).join(", "),
          street: street.trim(),
          unit: unit.trim(),
          city: city.trim(),
          province,
          postalCode: postalCode.trim(),
          flooringType,
          ftInboxTrap: trap,
          source: leadSource,
          pageUrl: window.location.href,
          ...getLeadAttribution(),
          devicePlatform: /Mobi|Android|iPhone|iPad/i.test(navigator.userAgent) ? "Mobile / Tablet" : "Desktop",
          numberOfRooms: rooms,
          privacyConsent: consent.privacyConsent,
          smsConsent: consent.smsConsent,
          emailConsent: consent.emailConsent,
        }),
      })
      const result = await response.json().catch(() => null)
      if (!response.ok) {
        throw new Error(result?.message || "We could not send your request. Please try again.")
      }

      setDone(true)
      finishBookingFormLead(flooringType, settings.booking_form_redirect_url)
    } catch (err) {
      setErrors({
        form: err instanceof Error && err.message ? err.message : "We could not send your request. Please try again.",
      })
    } finally {
      setPending(false)
    }
  }

  if (done) {
    return (
      <div className="flex flex-col items-center gap-4 px-2 py-6 text-center animate-in fade-in zoom-in-95 duration-300" role="status">
        <span className="flex size-14 items-center justify-center rounded-full bg-primary text-primary-foreground ring-8 ring-primary/10">
          <Check className="size-7" aria-hidden="true" />
        </span>
        <h3 className="font-serif text-2xl font-bold">{`Thanks, ${firstName.trim()}! You're booked in.`}</h3>
        <p className="text-sm leading-relaxed text-muted-foreground">{settings.mobile_form_note}</p>
        <a href={phoneHref} className="font-semibold text-primary underline underline-offset-4">
          Prefer to talk now? {settings.phone}
        </a>
        <button
          type="button"
          onClick={onClose}
          className="mt-2 h-11 w-full rounded-lg border-2 border-border bg-card font-semibold text-foreground"
        >
          Close
        </button>
      </div>
    )
  }

  return (
    <form onSubmit={submit} noValidate className="relative flex flex-col gap-4">
      {/* Honeypot - the inbox endpoint rejects any submission that fills this in */}
      <div aria-hidden="true" className="absolute -left-[9999px] h-px w-px overflow-hidden">
        <label>
          Leave this field empty
          <input
            name="ftInboxTrap"
            type="text"
            tabIndex={-1}
            autoComplete="new-password"
            value={trap}
            onChange={(e) => setTrap(e.target.value)}
          />
        </label>
      </div>

      <Stepper step={step} labels={stepLabels} />

      {step === 0 && (
      <div key="step-0" className={cn("flex flex-col gap-4 animate-in fade-in duration-300 ease-out", goingBack ? "slide-in-from-left-6" : "slide-in-from-right-6")}>
      <fieldset aria-describedby={errors.floors ? "ft-mc-floors-error" : undefined}>
        <legend className="mb-2 text-sm font-bold text-foreground">What floors are you thinking about?</legend>
        <div className="flex flex-wrap gap-2">
          {floorOptions.map((option) => {
            const active = floors.includes(option)
            return (
              <button
                key={option}
                type="button"
                aria-pressed={active}
                onClick={() => {
                  clearError("floors")
                  setFloors((prev) => (active ? prev.filter((f) => f !== option) : [...prev, option]))
                }}
                className={cn(
                  "rounded-md border px-3 py-1.5 text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-primary/20",
                  active
                    ? "border-primary bg-primary/10 text-primary"
                    : "border-border bg-card text-foreground hover:border-primary/40",
                )}
              >
                {option}
              </button>
            )
          })}
        </div>
        {errors.floors && (
          <p id="ft-mc-floors-error" className="mt-1.5 text-xs font-medium text-destructive">
            {errors.floors}
          </p>
        )}
      </fieldset>

      <div className="flex flex-col gap-1.5">
        <label htmlFor="ft-mc-first-name" className="text-sm font-bold text-foreground">
          First name
        </label>
        <input
          id="ft-mc-first-name"
          value={firstName}
          onChange={(e) => {
            clearError("firstName")
            setFirstName(e.target.value)
          }}
          autoComplete="given-name"
          placeholder="Your first name"
          aria-invalid={!!errors.firstName}
          className={inputClass(errors.firstName)}
        />
        {errors.firstName && <p className="text-xs font-medium text-destructive">{errors.firstName}</p>}
      </div>

      <div className="grid grid-cols-2 gap-3">
        <div className="flex flex-col gap-1.5">
          <label htmlFor="ft-mc-phone" className="text-sm font-bold text-foreground">
            Mobile
          </label>
          <input
            id="ft-mc-phone"
            type="tel"
            inputMode="tel"
            value={phone}
            onChange={(e) => {
              clearError("phone")
              setPhone(formatPhone(e.target.value))
            }}
            autoComplete="tel-national"
            placeholder="(___) ___-____"
            aria-invalid={!!errors.phone}
            className={inputClass(errors.phone)}
          />
          {errors.phone && <p className="text-xs font-medium text-destructive">{errors.phone}</p>}
        </div>
        <div className="flex flex-col gap-1.5">
          <label htmlFor="ft-mc-postal" className="text-sm font-bold text-foreground">
            Postal code
          </label>
          <input
            id="ft-mc-postal"
            value={postalCode}
            onChange={(e) => {
              clearError("postalCode")
              setPostalCode(formatPostal(e.target.value))
            }}
            autoComplete="postal-code"
            placeholder="e.g. M5V 2T6"
            aria-invalid={!!errors.postalCode}
            className={inputClass(errors.postalCode)}
          />
          {errors.postalCode && <p className="text-xs font-medium text-destructive">{errors.postalCode}</p>}
        </div>
      </div>

      {mapUrl && (
        <a href={mapUrl} className="flex items-center gap-1.5 text-sm font-semibold text-primary underline underline-offset-2">
          <MapPin className="size-4 shrink-0" aria-hidden="true" />
          Check if we serve your area – view map
          <ArrowRight className="size-3.5" aria-hidden="true" />
        </a>
      )}
      </div>
      )}

      {step === 1 && (
      <div key="step-1" className={cn("flex flex-col gap-4 animate-in fade-in duration-300 ease-out", goingBack ? "slide-in-from-left-6" : "slide-in-from-right-6")}>
      <div className="flex flex-col gap-1.5">
        <label htmlFor="ft-mc-email" className="text-sm font-bold text-foreground">
          Email
        </label>
        <input
          id="ft-mc-email"
          type="email"
          value={email}
          onChange={(e) => {
            clearError("email")
            setEmail(e.target.value)
          }}
          autoComplete="email"
          placeholder="you@email.com"
          aria-invalid={!!errors.email}
          className={inputClass(errors.email)}
        />
        {errors.email && <p className="text-xs font-medium text-destructive">{errors.email}</p>}
      </div>

      <div className="grid grid-cols-[1fr_88px] gap-3">
        <div className="flex flex-col gap-1.5">
          <label htmlFor="ft-mc-street" className="text-sm font-bold text-foreground">
            Street address
          </label>
          <input
            id="ft-mc-street"
            ref={streetRef}
            value={street}
            onChange={(e) => {
              clearError("street")
              setStreet(e.target.value)
            }}
            autoComplete="address-line1"
            placeholder="123 Main St"
            aria-invalid={!!errors.street}
            className={inputClass(errors.street)}
          />
          {errors.street && <p className="text-xs font-medium text-destructive">{errors.street}</p>}
        </div>
        <div className="flex flex-col gap-1.5">
          <label htmlFor="ft-mc-unit" className="text-sm font-bold text-foreground">
            Unit
          </label>
          <input
            id="ft-mc-unit"
            value={unit}
            onChange={(e) => setUnit(e.target.value)}
            autoComplete="address-line2"
            placeholder="2B"
            className={inputClass()}
          />
        </div>
      </div>

      <div className="grid grid-cols-2 gap-3">
        <div className="flex flex-col gap-1.5">
          <label htmlFor="ft-mc-city" className="text-sm font-bold text-foreground">
            City
          </label>
          <input
            id="ft-mc-city"
            value={city}
            onChange={(e) => {
              clearError("city")
              setCity(e.target.value)
            }}
            autoComplete="address-level2"
            placeholder="Toronto"
            aria-invalid={!!errors.city}
            className={inputClass(errors.city)}
          />
          {errors.city && <p className="text-xs font-medium text-destructive">{errors.city}</p>}
        </div>
        <div className="flex flex-col gap-1.5">
          <label htmlFor="ft-mc-province" className="text-sm font-bold text-foreground">
            Province
          </label>
          <select
            id="ft-mc-province"
            value={province}
            onChange={(e) => setProvince(e.target.value)}
            autoComplete="address-level1"
            className={inputClass()}
          >
            {provinces.map(([code, label]) => (
              <option key={code} value={code}>
                {label}
              </option>
            ))}
          </select>
        </div>
      </div>

      </div>
      )}

      {step === 2 && (
      <div key="step-2" className={cn("flex flex-col gap-4 animate-in fade-in duration-300 ease-out", goingBack ? "slide-in-from-left-6" : "slide-in-from-right-6")}>
      <div className="flex flex-col gap-1.5">
        <label htmlFor="ft-mc-rooms" className="text-sm font-bold text-foreground">
          Number of rooms
        </label>
        <input
          id="ft-mc-rooms"
          type="number"
          inputMode="numeric"
          min={1}
          max={99}
          value={rooms}
          onChange={(e) => setRooms(e.target.value.replace(/\D/g, "").slice(0, 2))}
          placeholder="Enter number of rooms"
          className={inputClass()}
        />
      </div>

      {/* Same three consents and look as the computer form - all required (SMS providers need the SMS one) */}
      <ConsentList
        idPrefix="ft-mc"
        value={consent}
        error={errors.consent}
        onChange={(key, checked) => {
          clearError("consent")
          setConsent((prev) => ({ ...prev, [key]: checked }))
        }}
      />
      </div>
      )}

      {errors.form && (
        <p role="alert" className="rounded-lg bg-destructive/10 px-3 py-2 text-sm font-medium text-destructive">
          {errors.form}
        </p>
      )}

      <div className="flex gap-2">
        {step > 0 && (
          <button
            type="button"
            onClick={() => {
              setErrors({})
              setGoingBack(true)
              setStep((s) => s - 1)
            }}
            aria-label="Previous step"
            className="flex h-13 w-13 shrink-0 items-center justify-center rounded-lg border-2 border-border bg-card text-foreground"
          >
            <ArrowLeft className="size-5" aria-hidden="true" />
          </button>
        )}
        <button
          type="submit"
          disabled={pending}
          className="group min-h-13 h-auto w-full ft-form-button rounded-lg px-3 py-2.5 text-base font-bold leading-tight text-balance shadow-lg transition-all active:scale-[0.99] disabled:opacity-70"
        >
          <span className="inline-flex items-center justify-center gap-2">
            {step < 2 ? "Continue" : pending ? "Booking…" : settings.mobile_form_button}
            {!pending && <ArrowRight className="size-5 transition-transform group-active:translate-x-1" aria-hidden="true" />}
          </span>
        </button>
      </div>
      <p className="text-center text-xs text-muted-foreground">{settings.mobile_form_note}</p>
    </form>
  )
}

// Phone layout of the "Estimate form" hero: the hero is part one; the
// consultation form slides up as part two from the hero button or any
// "#estimate" link on the page.
export function MobileShowroomHero() {
  const settings = useHomepageSettings()
  const [open, setOpen] = useState(false)
  const panelRef = useRef<HTMLDivElement>(null)
  const openerRef = useRef<HTMLElement | null>(null)
  const phoneHref = `tel:${settings.phone.replace(/[^\d+]/g, "")}`
  // Hero > "Estimate form layout - phones" > Phone hero image, else the bundled photo
  const heroImage = settings.mobile_hero_image || defaultImage

  const openForm = useCallback(() => {
    openerRef.current = document.activeElement as HTMLElement | null
    setOpen(true)
  }, [])

  const closeForm = useCallback(() => {
    setOpen(false)
    openerRef.current?.focus?.()
  }, [])

  // On phones, every "Get estimate" style link on the page opens the sheet
  useEffect(() => {
    const phone = window.matchMedia(phoneQuery)

    function handleClick(event: MouseEvent) {
      if (!phone.matches) return
      const link = (event.target as Element | null)?.closest?.("a[href]")
      if (!link || !link.getAttribute("href")?.endsWith("#estimate")) return
      event.preventDefault()
      openForm()
    }

    if (phone.matches && window.location.hash === "#estimate") openForm()

    document.addEventListener("click", handleClick, true)
    return () => document.removeEventListener("click", handleClick, true)
  }, [openForm])

  useEffect(() => {
    if (!open) return

    const { overflow } = document.body.style
    document.body.style.overflow = "hidden"
    panelRef.current?.focus()

    // The chat widget sits at the maximum z-index over the sheet's submit area
    const chat = document.getElementById("chatbot-container")
    const chatVisibility = chat?.style.visibility ?? ""
    if (chat) chat.style.visibility = "hidden"

    function handleKey(event: KeyboardEvent) {
      if (event.key === "Escape") closeForm()
    }
    document.addEventListener("keydown", handleKey)

    // Leaving phone width (rotation / resize) closes the sheet
    const phone = window.matchMedia(phoneQuery)
    const handleChange = () => {
      if (!phone.matches) setOpen(false)
    }
    phone.addEventListener("change", handleChange)

    return () => {
      document.body.style.overflow = overflow
      if (chat) chat.style.visibility = chatVisibility
      document.removeEventListener("keydown", handleKey)
      phone.removeEventListener("change", handleChange)
    }
  }, [open, closeForm])

  return (
    <section aria-labelledby="ft-mobile-hero-heading" className="bg-background">
      {/* The photo sits behind the text (not as its own block) to keep the phone hero short */}
      <div className="relative isolate overflow-hidden px-4 pb-6 pt-4 [text-shadow:0_0_10px_rgb(255_255_255/0.9)]">
        {/* eslint-disable-next-line @next/next/no-img-element */}
        <img
          src={heroImage}
          alt=""
          decoding="async"
          fetchPriority="high"
          className="ft-floor-drift absolute inset-0 -z-20 size-full object-cover object-[50%_35%]"
        />
        {/* White veil so the text reads on the photo, fading into the page at the bottom */}
        <span aria-hidden="true" className="absolute inset-0 -z-10 block bg-background/70" />
        {/* Soft warm light gliding across the floor (lower part of the photo) */}
        <span aria-hidden="true" className="ft-floor-light pointer-events-none absolute inset-x-0 bottom-0 -z-10 block h-3/5 overflow-hidden" />
        <span
          aria-hidden="true"
          className="absolute inset-x-0 bottom-0 -z-10 block h-10 bg-gradient-to-t from-background to-transparent"
        />
        {settings.mobile_hero_tag && (
          <p className="inline-flex rounded-full bg-primary/10 px-4 py-1.5 text-sm font-extrabold uppercase tracking-normal leading-tight text-primary">
            {settings.mobile_hero_tag}
          </p>
        )}

        {settings.hero_badge && (
          <p className="ft-mobile-offer relative mt-3 overflow-hidden rounded-lg bg-gradient-to-r from-[#e9c66b] via-[#d6a934] to-[#e9c66b] py-2.5 text-center text-[28px] font-extrabold leading-none text-[#b3261e] shadow-md shadow-[#d6a934]/30">
            {settings.hero_badge}
          </p>
        )}

        <p className="mt-2 text-center text-xs font-medium text-muted-foreground">
          {settings.mobile_hero_offer_note}
          {settings.deals_details_label && (
            <>
              {settings.mobile_hero_offer_note ? " · " : ""}
              <button
                type="button"
                onClick={() => window.dispatchEvent(new CustomEvent("ft:open-offer-details"))}
                className="inline border-0 bg-transparent p-0 align-baseline font-semibold text-primary underline underline-offset-2"
              >
                Offer details →
              </button>
            </>
          )}
        </p>

        <h2 id="ft-mobile-hero-heading" className="mt-4 font-serif text-[32px] font-bold leading-[1.1] tracking-tight">
          {settings.mobile_hero_title}{" "}
          <span className="block text-primary">{settings.mobile_hero_highlight}</span>
        </h2>
        <p className="mt-3 text-[15px] font-medium leading-relaxed text-foreground/85">{settings.mobile_hero_text}</p>
      </div>

      {/* Bottom padding is half the phone section gap (see globals.css --ft-mobile-section-gap) */}
      <div className="relative z-10 flex flex-col gap-3 px-4 pb-[calc(var(--ft-mobile-section-gap,30px)/2)]">
        <div className="rounded-2xl border border-border bg-card p-4 shadow-xl">
          <p className="font-serif text-xl font-bold">{settings.mobile_form_title}</p>
          <p className="mt-0.5 text-xs text-muted-foreground">{settings.mobile_form_subtitle}</p>
          <button
            type="button"
            onClick={openForm}
            aria-haspopup="dialog"
            aria-expanded={open}
            className="mt-4 flex h-13 w-full items-center justify-center gap-2 ft-form-button rounded-lg text-base font-bold shadow-lg active:scale-[0.99]"
          >
            {settings.mobile_hero_button}
            <ArrowRight className="size-5" aria-hidden="true" />
          </button>
        </div>

        <a
          href={phoneHref}
          className="flex h-13 items-center justify-center gap-2 rounded-lg border border-primary bg-card text-base font-bold text-primary"
        >
          <Phone className="size-4" aria-hidden="true" />
          Call {settings.phone}
        </a>

        {settings.mobile_hero_trust && (
          <div className="flex items-center gap-3 rounded-xl border border-border bg-card p-3">
            <span className="flex shrink-0 text-secondary" aria-hidden="true">
              {Array.from({ length: 5 }).map((_, i) => (
                <Star key={i} className="size-3.5 fill-current" />
              ))}
            </span>
            <p className="text-xs leading-snug text-foreground/80">{settings.mobile_hero_trust}</p>
          </div>
        )}
      </div>

      {/* Part two: the consultation form sheet */}
      <div
        className={cn("fixed inset-0 z-[1100]", open ? "visible" : "invisible")}
        aria-hidden={!open}
        inert={!open}
      >
        <div
          className={cn("absolute inset-0 bg-black/50 transition-opacity duration-300", open ? "opacity-100" : "opacity-0")}
          onClick={closeForm}
        />
        <div
          ref={panelRef}
          role="dialog"
          aria-modal="true"
          aria-labelledby="ft-mc-title"
          tabIndex={-1}
          className={cn(
            "absolute inset-x-0 bottom-0 flex max-h-[92dvh] flex-col rounded-t-2xl bg-card shadow-2xl transition-transform duration-300 ease-out focus:outline-none",
            open ? "translate-y-0" : "translate-y-full",
          )}
        >
          <div className="flex items-start justify-between gap-3 border-b border-border px-4 pb-3 pt-4">
            <div>
              <h2 id="ft-mc-title" className="whitespace-nowrap font-serif text-[clamp(1.05rem,5.2vw,1.25rem)] font-bold leading-tight">
                {settings.mobile_form_title}
              </h2>
              <p className="mt-0.5 text-xs text-muted-foreground">{settings.mobile_form_subtitle}</p>
            </div>
            <button
              type="button"
              onClick={closeForm}
              aria-label="Close"
              className="flex size-9 shrink-0 items-center justify-center rounded-full border border-border bg-card p-0 text-foreground"
            >
              <X className="size-5" aria-hidden="true" />
            </button>
          </div>
          <div className="overflow-y-auto overscroll-contain px-4 pb-[max(1.25rem,env(safe-area-inset-bottom))] pt-4">
            <ConsultationForm onClose={closeForm} />
          </div>
        </div>
      </div>
    </section>
  )
}
