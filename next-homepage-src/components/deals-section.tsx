"use client"

import { useEffect, useRef, useState } from "react"
import { createPortal } from "react-dom"
import Link from "next/link"
import { ArrowRight, Check, Gift } from "lucide-react"
import { Badge } from "@/components/ui/badge"
import { Card, CardContent } from "@/components/ui/card"
import { useHomepageSettings } from "@/components/homepage-settings-provider"

export function DealsSection() {
  const settings = useHomepageSettings()
  const [isOfferOpen, setIsOfferOpen] = useState(false)
  const dialogRef = useRef<HTMLElement>(null)
  const closeButtonRef = useRef<HTMLButtonElement>(null)
  const previousFocusRef = useRef<HTMLElement | null>(null)
  const includedItems = settings.deals_includes.split(/\r?\n/).map((item) => item.trim()).filter(Boolean)
  const popupSteps = settings.deals_popup_steps.split(/\r?\n/).map((line) => {
    const [title, ...descriptionParts] = line.split("|")
    return {
      title: title.trim(),
      description: descriptionParts.join("|").trim().replaceAll("{phone}", settings.phone),
    }
  }).filter((item) => item.title)


  useEffect(() => {
    const openOfferDetails = () => setIsOfferOpen(true)
    window.addEventListener("ft:open-offer-details", openOfferDetails)

    return () => {
      window.removeEventListener("ft:open-offer-details", openOfferDetails)
    }
  }, [])

  useEffect(() => {
    if (!isOfferOpen) return

    const scrollbarWidth = window.innerWidth - document.documentElement.clientWidth
    const previousOverflow = document.body.style.overflow
    const previousPaddingRight = document.body.style.paddingRight
    previousFocusRef.current = document.activeElement as HTMLElement | null
    closeButtonRef.current?.focus()

    const closeOnEscape = (event: KeyboardEvent) => {
      if (event.key === "Escape") {
        setIsOfferOpen(false)
        return
      }

      if (event.key !== "Tab" || !dialogRef.current) return

      const focusable = Array.from(
        dialogRef.current.querySelectorAll<HTMLElement>(
          'a[href], button:not([disabled]), [tabindex]:not([tabindex="-1"])',
        ),
      )

      if (!focusable.length) return

      const first = focusable[0]
      const last = focusable[focusable.length - 1]

      if (event.shiftKey && document.activeElement === first) {
        event.preventDefault()
        last.focus()
      } else if (!event.shiftKey && document.activeElement === last) {
        event.preventDefault()
        first.focus()
      }
    }

    document.body.style.overflow = "hidden"
    if (scrollbarWidth > 0) {
      document.body.style.paddingRight = `${scrollbarWidth}px`
    }
    window.addEventListener("keydown", closeOnEscape)

    return () => {
      document.body.style.overflow = previousOverflow
      document.body.style.paddingRight = previousPaddingRight
      window.removeEventListener("keydown", closeOnEscape)
      previousFocusRef.current?.focus()
    }
  }, [isOfferOpen])

  return (
    <section
      className="py-14 sm:py-16 lg:py-20"
      style={{
        background: `linear-gradient(${settings.deals_bg_location}, ${settings.deals_bg_color_1}, ${settings.deals_bg_color_2})`,
      }}
      aria-labelledby="deals-heading"
    >
      <div className="mx-auto max-w-[1340px] px-4">
        <div className="mx-auto mb-8 max-w-3xl text-center sm:mb-12">
          <Badge className="mb-4 bg-secondary/10 text-secondary border-secondary/20 hover:bg-secondary/10">
            {settings.deals_badge}
          </Badge>

          <h2 id="deals-heading" className="font-serif text-3xl font-bold text-foreground sm:text-4xl text-balance">
            {settings.deals_title}
          </h2>

          <p className="mt-4 text-base text-muted-foreground text-pretty sm:text-lg">
            {settings.deals_text}
          </p>
        </div>

        <div className="mb-10 grid grid-cols-2 gap-3 sm:gap-4 lg:mb-16 lg:grid-cols-4">
          {settings.offers.map((offer, index) => (
            <Card key={`${offer.title}-${index}`} className="border-2 border-primary/10 hover:border-primary/30 transition-colors bg-card">
              <CardContent className="p-4 text-center sm:p-6">
                <div className="mx-auto mb-3 flex h-10 w-10 items-center justify-center rounded-full bg-primary/10 sm:mb-4 sm:h-12 sm:w-12">
                  <Gift className="h-5 w-5 text-primary sm:h-6 sm:w-6" />
                </div>
                <h3 className="mb-2 text-sm font-bold leading-snug text-foreground sm:text-lg">{offer.title}</h3>
                <p className="text-xs leading-relaxed text-muted-foreground sm:text-sm">{offer.description}</p>
              </CardContent>
            </Card>
          ))}
        </div>

        <div className="grid items-start gap-8 lg:grid-cols-[minmax(280px,0.72fr)_minmax(580px,1.28fr)] lg:items-center lg:gap-10">
          <div className="space-y-8">
            <p className="text-base leading-relaxed text-muted-foreground sm:text-lg">
              {settings.deals_body}
            </p>
          </div>

          <div>
            <Card className="overflow-hidden rounded-lg border border-primary/20 bg-primary text-primary-foreground shadow-xl">
              <CardContent className="p-0">
                <div className="flex items-center gap-3 border-b border-white/15 px-4 py-3 sm:px-6">
                  <span className="flex h-10 w-10 items-center justify-center rounded-md bg-white/10">
                    <Gift className="h-5 w-5 text-secondary" />
                  </span>
                  <div className="text-left">
                    <p className="text-xs font-bold uppercase text-primary-foreground/70">
                      {settings.deals_popup_eyebrow}
                    </p>
                    <p className="text-sm font-semibold text-primary-foreground">
                      {settings.deals_badge}
                    </p>
                  </div>
                </div>

                <div className="grid gap-6 px-4 py-5 sm:px-6 md:grid-cols-[minmax(0,1.05fr)_minmax(250px,0.95fr)] md:items-stretch lg:px-8">
                  <div>
                    <div className="mb-6">
                      <div className="flex flex-wrap items-center gap-2 sm:flex-nowrap sm:gap-3">
                        <h3 className="text-3xl font-extrabold leading-none text-white min-[380px]:text-4xl sm:text-5xl">
                          {settings.deals_card_title}
                        </h3>
                        <span className="ft-sale-badge inline-flex flex-none rounded bg-red-600 px-2 py-0.5 text-[11px] font-extrabold uppercase text-white shadow-sm">
                          {settings.deals_card_subtitle}
                        </span>
                      </div>
                    </div>

                    <div className="flex flex-col items-start gap-4 sm:flex-row sm:flex-wrap sm:items-center sm:gap-x-5 sm:gap-y-3">
                      <Link
                        href="#estimate"
                        className="inline-flex min-h-11 w-full items-center justify-center gap-2 rounded-md bg-secondary px-5 py-2.5 font-bold leading-none text-secondary-foreground shadow-md transition-colors hover:bg-secondary/90 sm:w-auto sm:flex-none sm:whitespace-nowrap"
                      >
                        <span className="whitespace-nowrap">{settings.deals_button}</span>
                        <ArrowRight className="h-4 w-4 flex-none" aria-hidden="true" />
                      </Link>

                      <button
                        type="button"
                        onClick={() => setIsOfferOpen(true)}
                        className="inline-flex items-center text-sm font-semibold text-white underline decoration-white/40 underline-offset-4 transition-colors hover:text-secondary"
                      >
                        {settings.deals_details_label}
                      </button>
                    </div>
                  </div>

                  <div className="hidden border-l border-white/20 pl-7 md:flex md:flex-col md:justify-center">
                    <p className="mb-3 text-xs font-bold uppercase text-white/60">
                      {settings.deals_includes_title}
                    </p>
                    <div className="space-y-2.5">
                      {includedItems.map((item) => (
                        <div key={item} className="flex items-center gap-2.5 text-sm font-semibold text-white">
                          <span className="flex h-6 w-6 flex-none items-center justify-center rounded-full bg-white/10 text-secondary">
                            <Check className="h-3.5 w-3.5" />
                          </span>
                          <span>{item}</span>
                        </div>
                      ))}
                    </div>
                  </div>
                </div>
              </CardContent>
            </Card>
          </div>
        </div>
      </div>

      {isOfferOpen && typeof document !== "undefined" ? createPortal(
        <div
          className="ft-offer-modal-overlay fixed inset-0 z-[2147483000] flex min-h-dvh items-center justify-center overflow-y-auto bg-slate-950/70 p-3 backdrop-blur-sm sm:p-5"
          role="presentation"
          onMouseDown={(event) => {
            if (event.target === event.currentTarget) setIsOfferOpen(false)
          }}
        >
          <section
            ref={dialogRef}
            role="dialog"
            aria-modal="true"
            aria-labelledby="offer-details-title"
            className="ft-offer-modal-dialog relative my-auto max-h-[calc(100dvh-24px)] w-full max-w-6xl overflow-y-auto rounded-lg bg-white shadow-2xl sm:max-h-[calc(100dvh-40px)]"
          >
            <button
              ref={closeButtonRef}
              type="button"
              onClick={() => setIsOfferOpen(false)}
              className="absolute right-3 top-3 z-20 flex h-10 w-10 items-center justify-center rounded-full border border-slate-300 bg-white text-2xl font-normal leading-none text-slate-950 shadow-md transition-colors hover:bg-slate-100"
              aria-label="Close offer details"
            >
              <span aria-hidden="true">&times;</span>
            </button>

            <div className="flex items-center justify-between border-b border-slate-200 bg-white px-5 py-3 sm:px-7">
              <div className="flex items-center gap-3">
                <span className="flex h-10 w-10 items-center justify-center rounded-md bg-primary text-white">
                  <Gift className="h-5 w-5" />
                </span>
                <div>
                  <p className="text-xs font-bold uppercase text-primary">{settings.logo_text}</p>
                  <p className="text-sm text-slate-500">{settings.deals_popup_eyebrow}</p>
                </div>
              </div>
              <span className="h-10 w-10" aria-hidden="true" />
            </div>

            <div className="grid lg:grid-cols-[0.82fr_1.18fr]">
              <div className="flex flex-col justify-between bg-primary px-6 py-7 text-white sm:px-8">
                <div>
                  <p className="text-sm font-bold uppercase text-[#cc9c2e]">
                    {settings.deals_card_title} {settings.deals_card_subtitle}
                  </p>
                  <h2
                    id="offer-details-title"
                    className="mt-3 font-serif text-2xl font-bold leading-tight sm:text-3xl"
                    dangerouslySetInnerHTML={{ __html: settings.deals_popup_title }}
                  />
                  <p className="mt-4 max-w-md text-sm leading-relaxed text-white/75">
                    {settings.deals_popup_intro}
                  </p>
                </div>

                <Link
                  href="#estimate"
                  onClick={() => setIsOfferOpen(false)}
                  className="mt-7 inline-flex min-h-11 w-full items-center justify-center gap-2 rounded-md bg-[#cc9c2e] px-5 py-2.5 font-bold text-white transition-colors hover:bg-[#b98b25] sm:w-fit"
                >
                  {settings.deals_popup_button}
                  <ArrowRight className="h-4 w-4" />
                </Link>
              </div>

              <div className="px-6 py-6 sm:px-8">
                <h3 className="text-lg font-bold text-slate-950">{settings.deals_popup_steps_title}</h3>
                <div className="mt-4 grid gap-x-6 gap-y-4 sm:grid-cols-2">
                  {popupSteps.map((step) => (
                    <div key={step.title} className="flex gap-3">
                      <span className="mt-0.5 flex h-7 w-7 flex-none items-center justify-center rounded-full bg-emerald-100 text-emerald-700">
                        <Check className="h-4 w-4" />
                      </span>
                      <div>
                        <h4 className="text-sm font-bold text-slate-950">{step.title}</h4>
                        <p className="mt-1 text-xs leading-relaxed text-slate-600">{step.description}</p>
                      </div>
                    </div>
                  ))}
                </div>

                <div className="mt-5 border-t border-slate-200 pt-4 text-[11px] leading-relaxed text-slate-500">
                  <p>
                    {settings.deals_popup_terms}
                  </p>
                  <p className="mt-2">
                    {settings.deals_popup_terms_extra}
                  </p>
                </div>
              </div>
            </div>
          </section>
        </div>,
        document.body,
      ) : null}
    </section>
  )
}
