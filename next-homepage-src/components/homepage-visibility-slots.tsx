"use client"

import { Header } from "@/components/header"
import { Footer } from "@/components/footer"
import { HeroSection } from "@/components/hero-section"
import { EstimateHeroSection } from "@/components/estimate-hero-section"
import { useHomepageSettings } from "@/components/homepage-settings-provider"

function isEnabled(value: string | boolean | undefined) {
  return value === true || value === "1" || value === "true"
}

export function HeaderSlot() {
  const settings = useHomepageSettings()

  return isEnabled(settings.show_header) ? <Header /> : null
}

export function FooterSlot() {
  const settings = useHomepageSettings()

  return isEnabled(settings.show_footer) ? <Footer /> : null
}

// Home Settings > Hero > Hero layout. Only one hero renders so #estimate (the
// target of every "Get estimate" link on the page) stays unique.
export function HeroSlot() {
  const settings = useHomepageSettings()

  return settings.hero_variant === "estimate" ? <EstimateHeroSection /> : <HeroSection />
}
