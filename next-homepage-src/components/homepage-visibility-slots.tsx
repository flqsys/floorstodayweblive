"use client"

import { Header } from "@/components/header"
import { Footer } from "@/components/footer"
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
