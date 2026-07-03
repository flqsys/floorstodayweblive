"use client"

import { Shield, ArrowRight } from "lucide-react"
import { useHomepageSettings } from "@/components/homepage-settings-provider"

export function GuaranteeSection() {
  const settings = useHomepageSettings()

  return (
    <section
      className="py-14 sm:py-16 lg:py-20"
      style={{
        background: `linear-gradient(${settings.guarantee_bg_location}, ${settings.guarantee_bg_color_1}, ${settings.guarantee_bg_color_2})`,
      }}
      aria-labelledby="guarantee-heading"
    >
      <div className="mx-auto max-w-[1340px] px-4">
        <div className="grid items-center gap-8 lg:grid-cols-2 lg:gap-12">
          <div className="relative">
            <img
              src={settings.guarantee_image}
              alt={settings.guarantee_title}
              width={600}
              height={400}
              loading="lazy"
              decoding="async"
              className="aspect-[16/10] w-full rounded-xl object-cover shadow-lg"
            />
            <div className="absolute -bottom-6 -right-6 bg-secondary text-secondary-foreground p-4 rounded-xl shadow-lg hidden md:block">
              <Shield className="h-8 w-8" />
            </div>
          </div>

          <div className="text-center lg:text-left">
            <h2 id="guarantee-heading" className="font-serif text-3xl font-bold text-foreground sm:text-4xl">
              {settings.guarantee_title}
            </h2>
            <p className="mt-2 text-lg font-semibold text-secondary sm:text-xl">
              {settings.guarantee_subtitle}
            </p>
            <p className="mt-4 text-base leading-relaxed text-muted-foreground sm:mt-6 sm:text-lg">
              {settings.guarantee_text}
            </p>
            <a
              href="/contact/"
              className="inline-flex items-center gap-2 mt-6 text-primary font-semibold hover:underline lg:justify-start"
            >
              {settings.guarantee_link}
              <ArrowRight className="h-4 w-4" />
            </a>
          </div>
        </div>
      </div>
    </section>
  )
}
