"use client"

import Link from "next/link"
import { ArrowRight } from "lucide-react"
import { useHomepageSettings } from "@/components/homepage-settings-provider"

export function CTASection() {
  const settings = useHomepageSettings()

  return (
    <section
      className="py-12 text-primary-foreground sm:py-16 lg:py-20"
      style={{
        background: `linear-gradient(${settings.cta_bg_location}, ${settings.cta_bg_color_1}, ${settings.cta_bg_color_2})`,
      }}
      aria-labelledby="cta-heading"
    >
      <div className="mx-auto max-w-[1340px] px-4 text-center">
        <h2
          id="cta-heading"
          className="mx-auto max-w-[900px] text-balance font-serif text-3xl font-bold leading-tight sm:text-4xl lg:text-5xl"
        >
          {settings.cta_title}
        </h2>
        <p className="mt-4 text-xl font-bold text-secondary sm:text-2xl">
          {settings.cta_subtitle}
        </p>
        <p className="mx-auto mt-2 max-w-2xl text-base opacity-80 sm:text-lg">
          {settings.cta_text}
        </p>
        <Link
          href="#estimate"
          className="ft-cta-shine mt-7 inline-flex min-h-12 w-full items-center justify-center gap-2 rounded-md bg-secondary px-6 py-3 text-base font-bold text-secondary-foreground shadow-md transition-colors hover:bg-secondary/90 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-white sm:mt-8 sm:w-auto"
        >
          <span>{settings.cta_button}</span>
          <ArrowRight className="h-4 w-4" aria-hidden="true" />
        </Link>
      </div>
    </section>
  )
}
