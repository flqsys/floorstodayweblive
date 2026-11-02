"use client"

import { useRef, useState, type FormEvent, type InputHTMLAttributes, type Ref } from "react"
import {
  ArrowLeft,
  ArrowRight,
  Briefcase,
  Building2,
  Calendar,
  Check,
  ChevronDown,
  Clock,
  CircleHelp,
  Home,
  Lock,
  Mail,
  MapPin,
  Phone,
  User,
  type LucideIcon,
} from "lucide-react"
import { endpointUrl, useHomepageSettings } from "@/components/homepage-settings-provider"
import { finishBookingFormLead, getLeadAttribution } from "@/lib/lead-tracking"
import { useAddressAutocomplete } from "@/lib/address-autocomplete"
import { cn } from "@/lib/utils"

const inboxEndpoint =
  process.env.NEXT_PUBLIC_WORDPRESS_INBOX_ENDPOINT || "/wp-json/floors-today/v1/inbox-leads"
const imageBase = `${process.env.NEXT_PUBLIC_BASE_PATH || ""}/images/estimate-hero`

// Same questions, choices and order as the WordPress booking form
// ([floors_booking_form] in floors-next-homepage-backend.php), so every lead
// reaches the CRM with the same details whichever hero is switched on.
const propertyTypes = [
  { label: "Residential", icon: Home },
  { label: "Office Space", icon: Building2 },
  { label: "Business", icon: Briefcase },
] as const

const floorTypes = [
  { label: "Solid Hardwood", image: "solid-hardwood.webp" },
  { label: "Engineered Hardwood", image: "engineered.webp" },
  { label: "Laminate", image: "laminate.webp" },
  { label: "Vinyl", image: "vinyl.webp" },
  { label: "Carpet", image: "carpet.webp" },
  { label: "Not sure yet", image: null },
] as const

const provinces = [
  ["AB", "Alberta"], ["BC", "British Columbia"], ["MB", "Manitoba"],
  ["NB", "New Brunswick"], ["NL", "Newfoundland and Labrador"], ["NS", "Nova Scotia"],
  ["NT", "Northwest Territories"], ["NU", "Nunavut"], ["ON", "Ontario"],
  ["PE", "Prince Edward Island"], ["QC", "Quebec"], ["SK", "Saskatchewan"], ["YT", "Yukon"],
] as const

const startTimes = ["ASAP", "Within 1 month", "1-3 months", "3+ months", "Just researching"]
const visitTimes = ["Morning", "Afternoon", "Evening"]

// Same phone check as the booking form: 10 digits, a Canadian area code
const CANADIAN_AREA_CODES = ["204","226","236","249","250","263","289","306","343","354","365","367","368","382","403","416","418","428","431","437","438","450","468","474","506","514","519","548","579","581","584","587","604","613","639","647","672","683","705","709","742","753","778","780","782","807","819","825","867","873","902","905"]
function isValidCanadianPhone(value: string) {
  let d = value.replace(/\D/g, "")
  if (d.length === 11 && d.charAt(0) === "1") d = d.slice(1)
  if (d.length !== 10) return false
  if (d.charAt(3) === "0" || d.charAt(3) === "1") return false
  return CANADIAN_AREA_CODES.includes(d.slice(0, 3))
}

const POSTAL = /^[A-Za-z]\d[A-Za-z][ -]?\d[A-Za-z]\d$/
const stepLabels = ["Project", "Address", "Contact"]

// Same consent wording as the WordPress booking forms (ft_inbox_consent_references)
export const consents = [
  { key: "privacyConsent", text: "I agree to receive promotional emails from Floors Today and have read the", links: true },
  {
    key: "smsConsent",
    text: "I agree to receive SMS marketing and informational messages from Floors Today at the contact information provided above. Message frequency may vary. Message & data rates may apply. Reply STOP to unsubscribe or HELP for assistance.",
    links: false,
  },
  {
    key: "emailConsent",
    text: "I agree to receive email marketing communications from Floors Today at the email address provided above. I understand Floors Today may respond to any messages or emails I send.",
    links: false,
  },
] as const

export type ConsentKey = (typeof consents)[number]["key"]
type Errors = Partial<
  Record<"property" | "floors" | "street" | "city" | "postalCode" | "name" | "phone" | "email" | "consent" | "form", string>
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

type FieldProps = {
  id: string
  label: string
  icon: LucideIcon
  error?: string
  hint?: string
  optional?: boolean
  className?: string
  ref?: Ref<HTMLInputElement>
} & InputHTMLAttributes<HTMLInputElement>

function Field({ id, label, icon: Icon, error, hint, optional, className, ...props }: FieldProps) {
  const describedBy = error ? `${id}-error` : hint ? `${id}-hint` : undefined
  return (
    <div className={cn("flex flex-col gap-1.5", className)}>
      <label htmlFor={id} className="text-sm font-semibold text-foreground">
        {label}
        {optional && <span className="font-normal text-muted-foreground"> (optional)</span>}
      </label>
      <div className="relative">
        <Icon
          className={cn(
            "pointer-events-none absolute left-3.5 top-1/2 size-4 -translate-y-1/2",
            error ? "text-destructive" : "text-muted-foreground",
          )}
          aria-hidden="true"
        />
        <input
          id={id}
          aria-invalid={!!error}
          aria-describedby={describedBy}
          className={cn(
            "h-12 w-full rounded-lg border bg-card pl-10 pr-4 text-base text-foreground transition-colors placeholder:text-muted-foreground/70 focus:outline-none focus:ring-4",
            error
              ? "border-destructive focus:ring-destructive/15"
              : "border-input hover:border-primary/50 focus:border-primary focus:ring-primary/15",
          )}
          {...props}
        />
      </div>
      {error ? (
        <p id={`${id}-error`} className="text-xs font-medium text-destructive">
          {error}
        </p>
      ) : hint ? (
        <p id={`${id}-hint`} className="text-xs text-muted-foreground">
          {hint}
        </p>
      ) : null}
    </div>
  )
}

function SelectField({
  id,
  label,
  icon: Icon,
  value,
  onChange,
  options,
  className,
}: {
  id: string
  label: string
  icon: LucideIcon
  value: string
  onChange: (value: string) => void
  options: readonly (readonly [string, string])[]
  className?: string
}) {
  return (
    <div className={cn("flex flex-col gap-1.5", className)}>
      <label htmlFor={id} className="text-sm font-semibold text-foreground">
        {label}
      </label>
      <div className="relative">
        <Icon className="pointer-events-none absolute left-3.5 top-1/2 size-4 -translate-y-1/2 text-muted-foreground" aria-hidden="true" />
        <select
          id={id}
          value={value}
          onChange={(e) => onChange(e.target.value)}
          className="h-12 w-full appearance-none rounded-lg border border-input bg-card pl-10 pr-10 text-base text-foreground transition-colors hover:border-primary/50 focus:border-primary focus:outline-none focus:ring-4 focus:ring-primary/15"
        >
          {options.map(([v, text]) => (
            <option key={v} value={v}>
              {text}
            </option>
          ))}
        </select>
        <ChevronDown className="pointer-events-none absolute right-3.5 top-1/2 size-4 -translate-y-1/2 text-muted-foreground" aria-hidden="true" />
      </div>
    </div>
  )
}

// The three consent boxes as tappable cards with a custom check - used by
// both hero forms so they look the same. All three are required.
export function ConsentList({
  value,
  onChange,
  error,
  idPrefix,
}: {
  value: Record<ConsentKey, boolean>
  onChange: (key: ConsentKey, checked: boolean) => void
  error?: string
  idPrefix: string
}) {
  return (
    <div className="flex flex-col gap-2" role="group" aria-labelledby={`${idPrefix}-consent-title`} aria-describedby={error ? `${idPrefix}-consent-error` : undefined}>
      <p id={`${idPrefix}-consent-title`} className="text-sm font-bold text-foreground">
        Please confirm
      </p>
      {consents.map((c) => {
        const on = value[c.key]
        return (
          <label
            key={c.key}
            className={cn(
              "flex cursor-pointer items-start gap-3 rounded-xl border-2 p-3 text-xs leading-snug transition-colors",
              on ? "border-primary bg-primary/5 text-foreground" : error ? "border-destructive/50 text-muted-foreground" : "border-border text-muted-foreground hover:border-primary/40",
            )}
          >
            <input
              type="checkbox"
              required
              checked={on}
              onChange={(e) => onChange(c.key, e.target.checked)}
              className="peer sr-only"
            />
            <span
              className={cn(
                "mt-px flex size-5 shrink-0 items-center justify-center rounded-md border-2 transition-colors peer-focus-visible:ring-4 peer-focus-visible:ring-primary/20",
                on ? "border-primary bg-primary text-primary-foreground" : "border-input bg-card",
              )}
              aria-hidden="true"
            >
              {on && <Check className="size-3.5 animate-in zoom-in-50 duration-200" strokeWidth={3} />}
            </span>
            <span>
              {c.text}
              {c.links && (
                <>
                  {" "}
                  <a href={endpointUrl("/privacy-policy/")} className="font-semibold text-primary underline">
                    Privacy Policy
                  </a>{" "}
                  and{" "}
                  <a href={endpointUrl("/terms-of-use/")} className="font-semibold text-primary underline">
                    Terms &amp; Conditions
                  </a>
                  .
                </>
              )}
            </span>
          </label>
        )
      })}
      {error && (
        <p id={`${idPrefix}-consent-error`} className="text-xs font-medium text-destructive">
          {error}
        </p>
      )}
    </div>
  )
}

// Step tabs - also used by the phone form so both look the same
export function Stepper({ step, labels = stepLabels }: { step: number; labels?: readonly string[] }) {
  return (
    <ol className="flex items-center gap-2" aria-label="Form progress">
      {labels.map((label, i) => {
        const done = i < step
        const current = i === step
        return (
          <li key={label} className="flex flex-1 items-center gap-2" aria-current={current ? "step" : undefined}>
            <span
              className={cn(
                "flex size-7 shrink-0 items-center justify-center rounded-full text-xs font-bold transition-colors",
                done && "bg-primary text-primary-foreground",
                current && "bg-primary text-primary-foreground ring-4 ring-primary/15",
                !done && !current && "bg-muted text-muted-foreground",
              )}
            >
              {done ? <Check className="size-3.5" aria-hidden="true" /> : i + 1}
            </span>
            <span className={cn("text-sm font-semibold", current || done ? "text-foreground" : "text-muted-foreground")}>
              {label}
            </span>
            {i < labels.length - 1 && (
              <span className={cn("h-0.5 flex-1 rounded-full", done ? "bg-primary" : "bg-muted")} aria-hidden="true" />
            )}
          </li>
        )
      })}
    </ol>
  )
}

const tileBase =
  "relative flex items-center gap-3 rounded-xl border-2 bg-card text-left text-sm font-semibold transition-all focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-primary/20"
const tileOn = "border-primary bg-primary/5 text-foreground"
const tileOff = "border-border text-foreground hover:border-primary/40 hover:bg-muted/40"

export function EstimateHeroForm() {
  const settings = useHomepageSettings()
  const phoneHref = `tel:${settings.phone.replace(/[^\d+]/g, "")}`

  const [step, setStep] = useState(0)
  const [propertyType, setPropertyType] = useState("")
  const [flooringType, setFlooringType] = useState("")
  const [street, setStreet] = useState("")
  const [unit, setUnit] = useState("")
  const [city, setCity] = useState("")
  const [province, setProvince] = useState("ON")
  const [postalCode, setPostalCode] = useState("")
  const [name, setName] = useState("")
  const [phone, setPhone] = useState("")
  const [email, setEmail] = useState("")
  const [rooms, setRooms] = useState("")
  const [startTime, setStartTime] = useState(startTimes[0])
  const [visitTime, setVisitTime] = useState(visitTimes[0])
  const [consent, setConsent] = useState<Record<ConsentKey, boolean>>({
    privacyConsent: false,
    smsConsent: false,
    emailConsent: false,
  })
  const [trap, setTrap] = useState("")
  const [errors, setErrors] = useState<Errors>({})
  const [done, setDone] = useState(false)
  const [pending, setPending] = useState(false)

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

  // Same checks, in the same steps, as the booking form
  const validate = (): Errors => {
    const e: Errors = {}
    if (step === 0) {
      if (!propertyType) e.property = "Please choose a property type."
      if (!flooringType) e.floors = "Please choose a flooring option."
    }
    if (step === 1) {
      if (!street.trim()) e.street = "Please enter your street address."
      if (!city.trim()) e.city = "Please enter your city."
      if (!postalCode.trim()) e.postalCode = "Please enter your postal code."
      else if (!POSTAL.test(postalCode.trim())) e.postalCode = "Enter a valid postal code, e.g. L5B 1M2."
    }
    if (step === 2) {
      // The inbox endpoint rejects single-word names
      if (!name.trim()) e.name = "Please enter your full name."
      else if (name.trim().split(/\s+/).filter(Boolean).length < 2) e.name = "Please enter your first and last name."
      if (!email.trim()) e.email = "Please enter your email address."
      else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.trim())) e.email = "Please enter a valid email address."
      if (!isValidCanadianPhone(phone)) e.phone = "Please enter a valid Canadian phone number."
      if (!consents.every((c) => consent[c.key])) e.consent = "Please check all three boxes to continue."
    }
    return e
  }

  const submit = async (ev: FormEvent) => {
    ev.preventDefault()
    if (pending) return
    const e = validate()
    setErrors(e)
    if (Object.keys(e).length > 0) return
    if (step < 2) return setStep((s) => s + 1)

    const attribution = getLeadAttribution()
    const streetLine = street.trim() + (unit.trim() ? ` Unit ${unit.trim()}` : "")

    setPending(true)
    try {
      const response = await fetch(endpointUrl(inboxEndpoint), {
        method: "POST",
        credentials: "same-origin",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
          fullName: name.trim(),
          email: email.trim(),
          phone: `+1 ${phone}`,
          address: [streetLine, city.trim(), province, postalCode.trim(), "Canada"].filter(Boolean).join(", "),
          street: street.trim(),
          unit: unit.trim(),
          city: city.trim(),
          province,
          postalCode: postalCode.trim(),
          flooringType,
          propertyType,
          numberOfRooms: rooms.trim(),
          startTime,
          preferredVisitTime: visitTime,
          ftInboxTrap: trap,
          source: "Homepage hero estimate form",
          pageUrl: window.location.href,
          ...attribution,
          devicePlatform: /Mobi|Android|iPhone|iPad/i.test(navigator.userAgent) ? "Mobile / Tablet" : "Desktop",
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
      <div className="flex flex-col items-center gap-4 p-8 text-center" role="status">
        <span className="flex size-14 items-center justify-center rounded-full bg-primary text-primary-foreground ring-8 ring-primary/10">
          <Check className="size-7" aria-hidden="true" />
        </span>
        <h3 className="text-2xl font-bold text-balance">{`You're booked in, ${name.trim().split(/\s+/)[0]}.`}</h3>
        <p className="max-w-md leading-relaxed text-muted-foreground text-pretty">
          A flooring specialist will call you shortly to confirm a time. Your sale pricing is locked in.
        </p>
        <a href={phoneHref} className="font-semibold text-primary underline underline-offset-4">
          Prefer to talk now? {settings.phone}
        </a>
      </div>
    )
  }

  return (
    <form onSubmit={submit} noValidate className="relative flex flex-col gap-5 p-5 md:p-6">
      <Stepper step={step} />

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

      {step === 0 && (
        <div className="flex flex-col gap-5">
          <fieldset aria-describedby={errors.property ? "property-error" : undefined}>
            <legend className="mb-3 text-lg font-bold">Property type</legend>
            <div className="grid grid-cols-3 gap-2">
              {propertyTypes.map((p) => {
                const active = propertyType === p.label
                const Icon = p.icon
                return (
                  <button
                    key={p.label}
                    type="button"
                    aria-pressed={active}
                    onClick={() => {
                      clearError("property")
                      setPropertyType(p.label)
                    }}
                    className={cn(tileBase, "h-14 justify-center px-2 text-center", active ? tileOn : tileOff)}
                  >
                    <Icon className={cn("size-4 shrink-0", active ? "text-primary" : "text-muted-foreground")} aria-hidden="true" />
                    <span className="leading-tight">{p.label}</span>
                  </button>
                )
              })}
            </div>
            {errors.property && (
              <p id="property-error" className="mt-2 text-xs font-medium text-destructive">
                {errors.property}
              </p>
            )}
          </fieldset>
          <fieldset aria-describedby={errors.floors ? "floors-error" : undefined}>
            <legend className="mb-3 text-lg font-bold">What floors interest you?</legend>
            <div className="grid grid-cols-2 gap-2 sm:grid-cols-3">
              {floorTypes.map((f) => {
                const active = flooringType === f.label
                return (
                  <button
                    key={f.label}
                    type="button"
                    aria-pressed={active}
                    onClick={() => {
                      clearError("floors")
                      setFlooringType(f.label)
                    }}
                    className={cn(tileBase, "p-1.5 pr-3", active ? tileOn : tileOff)}
                  >
                    <span className="relative size-10 shrink-0 overflow-hidden rounded-lg bg-muted">
                      {f.image ? (
                        // eslint-disable-next-line @next/next/no-img-element
                        <img
                          src={`${imageBase}/${f.image}`}
                          alt=""
                          width={40}
                          height={40}
                          loading="lazy"
                          className="size-full object-cover"
                        />
                      ) : (
                        <CircleHelp className="absolute inset-0 m-auto size-5 text-muted-foreground" aria-hidden="true" />
                      )}
                      {/* Phones show the selected check on the thumbnail - there is no room for the round marker */}
                      {active && (
                        <span
                          className="absolute inset-0 flex items-center justify-center bg-primary/75 text-primary-foreground sm:hidden"
                          aria-hidden="true"
                        >
                          <Check className="size-4" strokeWidth={3} />
                        </span>
                      )}
                    </span>
                    <span className="min-w-0 flex-1 leading-tight">{f.label}</span>
                    <span
                      className={cn(
                        "hidden size-5 shrink-0 items-center justify-center rounded-full border-2 transition-colors sm:flex",
                        active ? "border-primary bg-primary text-primary-foreground" : "border-border",
                      )}
                      aria-hidden="true"
                    >
                      {active && <Check className="size-3" strokeWidth={3} />}
                    </span>
                  </button>
                )
              })}
            </div>
            {errors.floors && (
              <p id="floors-error" className="mt-2 text-xs font-medium text-destructive">
                {errors.floors}
              </p>
            )}
          </fieldset>
        </div>
      )}

      {step === 1 && (
        <div className="grid gap-4 sm:grid-cols-[1fr_140px]">
          <p className="text-lg font-bold sm:col-span-2">Where should we visit?</p>
          <Field
            id="ft-estimate-street"
            ref={streetRef}
            label="Street address"
            icon={Home}
            value={street}
            onChange={(e) => {
              clearError("street")
              setStreet(e.target.value)
            }}
            autoComplete="address-line1"
            placeholder="123 Main St"
            error={errors.street}
          />
          <Field
            id="ft-estimate-unit"
            label="Unit / Apt"
            icon={Home}
            value={unit}
            onChange={(e) => setUnit(e.target.value)}
            autoComplete="address-line2"
            placeholder="2B"
          />
          <div className="grid gap-4 sm:col-span-2 sm:grid-cols-2">
            <Field
              id="ft-estimate-city"
              label="City"
              icon={MapPin}
              value={city}
              onChange={(e) => {
                clearError("city")
                setCity(e.target.value)
              }}
              autoComplete="address-level2"
              placeholder="Toronto"
              error={errors.city}
            />
            <SelectField
              id="ft-estimate-province"
              label="Province"
              icon={MapPin}
              value={province}
              onChange={setProvince}
              options={provinces}
            />
            <Field
              id="ft-estimate-postal"
              label="Postal code"
              icon={MapPin}
              value={postalCode}
              onChange={(e) => {
                clearError("postalCode")
                setPostalCode(formatPostal(e.target.value))
              }}
              placeholder="A1A 1A1"
              autoComplete="postal-code"
              error={errors.postalCode}
            />
            <Field id="ft-estimate-country" label="Country" icon={MapPin} value="Canada" readOnly tabIndex={-1} />
          </div>
        </div>
      )}

      {step === 2 && (
        <div className="flex flex-col gap-5">
          <div className="grid gap-4 sm:grid-cols-2">
            <p className="text-lg font-bold sm:col-span-2">Your contact details</p>
            <Field
              id="ft-estimate-name"
              label="Full name"
              icon={User}
              value={name}
              onChange={(e) => {
                clearError("name")
                setName(e.target.value)
              }}
              autoComplete="name"
              placeholder="Jane Doe"
              error={errors.name}
            />
            <Field
              id="ft-estimate-email"
              label="Email"
              icon={Mail}
              type="email"
              value={email}
              onChange={(e) => {
                clearError("email")
                setEmail(e.target.value)
              }}
              autoComplete="email"
              placeholder="jane@email.com"
              error={errors.email}
            />
            <Field
              id="ft-estimate-phone"
              label="Phone"
              icon={Phone}
              type="tel"
              inputMode="tel"
              value={phone}
              onChange={(e) => {
                clearError("phone")
                setPhone(formatPhone(e.target.value))
              }}
              autoComplete="tel-national"
              placeholder="(416) 555-0199"
              error={errors.phone}
            />
            <Field
              id="ft-estimate-rooms"
              label="Number of rooms"
              icon={Home}
              type="number"
              inputMode="numeric"
              min={1}
              max={99}
              step={1}
              value={rooms}
              onChange={(e) => setRooms(e.target.value.replace(/\D/g, "").slice(0, 2))}
              placeholder="Enter number of rooms"
            />
            <SelectField
              id="ft-estimate-start"
              label="When are you looking to start?"
              icon={Calendar}
              value={startTime}
              onChange={setStartTime}
              options={startTimes.map((t) => [t, t] as const)}
            />
            <SelectField
              id="ft-estimate-visit"
              label="Preferred visit time"
              icon={Clock}
              value={visitTime}
              onChange={setVisitTime}
              options={visitTimes.map((t) => [t, t] as const)}
            />
          </div>
          <ConsentList
            idPrefix="ft-estimate"
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

      <div className="flex flex-col gap-3">
        <div className="flex gap-2">
          {step > 0 && (
            <button
              type="button"
              onClick={() => {
                setErrors({})
                setStep((s) => s - 1)
              }}
              className="flex h-14 w-14 shrink-0 items-center justify-center rounded-lg border-2 border-border bg-card text-foreground transition-colors hover:border-primary/40"
              aria-label="Previous step"
            >
              <ArrowLeft className="size-5" aria-hidden="true" />
            </button>
          )}
          <button
            type="submit"
            disabled={pending}
            className="group flex h-14 flex-1 items-center justify-center gap-2 ft-form-button rounded-lg text-lg font-bold shadow-lg transition-all hover:brightness-105 active:scale-[0.99] disabled:opacity-70"
          >
            {step < 2 ? "Continue" : pending ? "Sending..." : "Book Your Appointment"}
            {!pending && (
              <ArrowRight className="size-5 transition-transform group-hover:translate-x-0.5" aria-hidden="true" />
            )}
          </button>
        </div>

        <div className="flex flex-col items-center justify-center gap-x-6 gap-y-2 text-center text-xs text-muted-foreground sm:flex-row">
          <span className="flex items-center gap-1.5">
            <Lock className="size-3.5" aria-hidden="true" />
            No obligation. We never share your details.
          </span>
          <a href={phoneHref} className="flex items-center gap-1.5 font-semibold text-primary">
            <Phone className="size-3.5" aria-hidden="true" />
            Or call {settings.phone}
          </a>
        </div>
      </div>
    </form>
  )
}
