"use client"

import Image from "next/image"
import { Check, Star } from "lucide-react"
import { EstimateHeroForm } from "@/components/estimate-hero-form"
import { MobileShowroomHero } from "@/components/mobile-showroom-hero"
import { useHomepageSettings } from "@/components/homepage-settings-provider"

// Wood-floor photo from the new-ft design (public/images/hero-wood.png there)
const defaultHeroImage = `${process.env.NEXT_PUBLIC_BASE_PATH || ""}/images/estimate-hero/hero-wood.webp`
const promises = ["Samples brought to your home", "One all-inclusive written price", "We beat any comparable written quote"]

// "Estimate form" hero variant (Home Settings > Hero > Hero layout). The outer
// element is a div, not a section, on purpose: the WordPress runtime bridge
// restyles `main>section:first-child` (background image swap, title size) for
// the classic hero, and none of that should apply to this layout. Phones get
// their own layout (MobileShowroomHero); the section below is tablet/desktop.
export function EstimateHeroSection() {
  const settings = useHomepageSettings()
  // Home Settings > Hero > Hero images (the selected one), else the bundled photo
  const heroImage = settings.estimate_hero_image || defaultHeroImage

  return (
    <div data-ft-hero-variant="estimate">
      {/* Phones: hero + slide-up consultation form */}
      <div className="sm:hidden">
        <MobileShowroomHero />
      </div>

      <section aria-labelledby="estimate-hero-heading" className="relative isolate hidden overflow-hidden sm:block">
        <Image
          src={heroImage}
          alt=""
          fill
          priority
          sizes="100vw"
          className={
            heroImage === defaultHeroImage
              ? "-z-20 object-cover object-bottom"
              : "-z-20 object-cover object-[50%_45%]"
          }
        />
        {/* White veil, matching the new-ft hero exactly: left-to-right 90% / 70% / 20%
            of new-ft's background colour (oklch 0.975 0.006 250) */}
        <span
          aria-hidden="true"
          className="absolute inset-0 -z-10 block bg-gradient-to-r from-[oklch(0.975_0.006_250/0.9)] via-[oklch(0.975_0.006_250/0.7)] to-[oklch(0.975_0.006_250/0.2)]"
        />

        {/* Same container as the header (max-w-[1340px] px-4) so the title lines up with the logo and the form with the phone number */}
        <div className="mx-auto grid max-w-[1340px] items-center gap-8 px-4 py-16 lg:py-20 lg:grid-cols-[1fr_620px] lg:gap-16 xl:gap-24">
          <div className="flex flex-col gap-5">
            <div className="flex items-center gap-2 text-sm">
              <span className="flex text-accent" aria-hidden="true">
                {Array.from({ length: 5 }).map((_, i) => (
                  <Star key={i} className="size-4 fill-current" />
                ))}
              </span>
              <span className="font-semibold">4.9/5</span>
              <span className="font-medium text-foreground">from 2,000+ GTA homeowners</span>
            </div>
            <h1
              id="estimate-hero-heading"
              className="text-3xl font-extrabold leading-tight tracking-tight text-balance md:text-4xl xl:text-5xl"
            >
              New floors, one honest price. <span className="text-primary">Installed by our own crew.</span>
            </h1>
            <p className="max-w-xl text-lg font-medium leading-relaxed text-foreground text-pretty">
              We bring hardwood, vinyl, laminate and carpet samples to your home, measure your space and give you a
              written quote that covers everything, including underlay, trim, disposal and cleanup.
            </p>
            <ul className="hidden flex-col gap-2.5 md:flex">
              {promises.map((p) => (
                <li key={p} className="flex items-center gap-3 font-medium">
                  <span className="flex size-6 items-center justify-center rounded-full bg-primary text-primary-foreground">
                    <Check className="size-3.5" aria-hidden="true" />
                  </span>
                  {p}
                </li>
              ))}
            </ul>
          </div>

          <div id="estimate" className="scroll-mt-32">
            <div className="overflow-hidden rounded-2xl border border-border bg-card shadow-xl shadow-primary/10">
              <div className="flex flex-wrap items-baseline justify-between gap-x-4 gap-y-1 bg-primary px-6 py-3.5 text-primary-foreground">
                <h2 className="text-xl font-bold">Book your free in-home estimate</h2>
                <p className="text-sm opacity-85">Lock in sale pricing today. No obligation.</p>
              </div>
              <EstimateHeroForm />
            </div>
          </div>
        </div>
      </section>
    </div>
  )
}
