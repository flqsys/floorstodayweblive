"use client"

import { useState } from "react"
import { Menu, X, MapPin, Phone } from "lucide-react"
import { useHomepageSettingsStatus } from "@/components/homepage-settings-provider"

export function Header() {
  const [mobileMenuOpen, setMobileMenuOpen] = useState(false)
  const { settings } = useHomepageSettingsStatus()
  const logoSrc = settings.logo_image
  const phoneHref = `tel:${settings.phone.replace(/[^\d+]/g, "")}`

  return (
    <header className="sticky top-0 z-50 bg-card shadow-sm">
      <div className="bg-primary text-primary-foreground">
        <div className="mx-auto max-w-[1340px] px-4">
          <div className="flex min-h-10 items-center justify-between py-2 text-xs sm:text-sm">
            <div className="flex min-w-0 items-center gap-2">
              <MapPin className="h-4 w-4 flex-none" />
              <span className="truncate">{settings.service_area}</span>
            </div>
            <div className="hidden sm:flex items-center gap-6">
              <a href="/financing/" className="hover:underline">
                Financing
              </a>
              <a href="/contact/" className="hover:underline">
                Contact
              </a>
              <a href="/faqs/" className="hover:underline">
                FAQs
              </a>
            </div>
          </div>
        </div>
      </div>

      <nav className="mx-auto max-w-[1340px] px-4">
        <div className="flex min-h-16 items-center justify-between gap-3 py-1">
          <a href="/" className="flex min-w-0 items-center text-2xl font-bold text-primary">
            {logoSrc ? (
              <img
                src={logoSrc}
                alt={settings.logo_text}
                width={250}
                height={80}
                className="block h-auto max-h-14 max-w-[180px] object-contain sm:max-h-16 sm:max-w-[250px]"
                style={{ width: settings.logo_size }}
                loading="eager"
                fetchPriority="high"
                decoding="sync"
              />
            ) : (
              <span>{settings.logo_text}</span>
            )}
          </a>

          <div className="hidden lg:flex lg:items-center lg:gap-6">
            {settings.nav_items.map((item) => (
              <a
                key={item.name}
                href={item.href}
                className="text-base font-medium text-foreground hover:text-primary transition-colors"
              >
                {item.name}
              </a>
            ))}
          </div>

          <div className="flex flex-none items-center justify-end gap-3 sm:gap-4">
            <a
              href={phoneHref}
              className="hidden sm:flex items-center gap-2 text-base font-semibold text-foreground whitespace-nowrap"
            >
              <Phone className="h-4 w-4 text-primary" />
              {settings.phone}
            </a>

            <button
              type="button"
              className="flex h-11 w-11 items-center justify-center rounded-md p-0 text-foreground lg:hidden"
              onClick={() => setMobileMenuOpen(!mobileMenuOpen)}
            >
              <span className="sr-only">Open menu</span>
              {mobileMenuOpen ? (
                <X className="h-6 w-6" aria-hidden="true" />
              ) : (
                <Menu className="h-6 w-6" aria-hidden="true" />
              )}
            </button>
          </div>
        </div>

        {mobileMenuOpen && (
          <div className="lg:hidden border-t border-border py-4">
            <div className="flex flex-col gap-4">
              {settings.nav_items.map((item) => (
                <a
                  key={item.name}
                  href={item.href}
                  className="text-base font-medium text-foreground hover:text-primary"
                  onClick={() => setMobileMenuOpen(false)}
                >
                  {item.name}
                </a>
              ))}
              <div className="pt-4 border-t border-border flex flex-col gap-3">
                <a href={phoneHref} className="flex items-center gap-2 text-base font-semibold">
                  <Phone className="h-4 w-4 text-primary" />
                  {settings.phone}
                </a>
              </div>
            </div>
          </div>
        )}
      </nav>
    </header>
  )
}
