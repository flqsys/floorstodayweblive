"use client"

import { useEffect, useRef, useState } from "react"
import { Menu, X, MapPin, Phone } from "lucide-react"
import { useHomepageSettingsStatus } from "@/components/homepage-settings-provider"
import { PromoBar } from "@/components/promo-bar"

export function Header() {
  const [mobileMenuOpen, setMobileMenuOpen] = useState(false)
  const { settings } = useHomepageSettingsStatus()
  const logoSrc = settings.logo_image
  const phoneHref = `tel:${settings.phone.replace(/[^\d+]/g, "")}`
  // Estimate form layout on phones: logo + Call only (no menu, info bar or promo bar)
  const slim = settings.hero_variant === "estimate"
  const headerRef = useRef<HTMLElement>(null)

  // On phones the WordPress bridge fixes the header and offsets the page by a
  // set height; the slim header is shorter, so publish its real height instead.
  useEffect(() => {
    const root = document.documentElement
    const header = headerRef.current

    if (!slim || !header) {
      root.style.removeProperty("--ft-mobile-header-h")
      return
    }

    const update = () => root.style.setProperty("--ft-mobile-header-h", `${header.offsetHeight}px`)
    update()
    const observer = new ResizeObserver(update)
    observer.observe(header)

    return () => {
      observer.disconnect()
      root.style.removeProperty("--ft-mobile-header-h")
    }
  }, [slim])

  return (
    <header ref={headerRef} className="sticky top-0 z-50 bg-card shadow-sm">
      <div className={slim ? "hidden sm:block" : undefined}>
        <PromoBar />
      </div>
      <div className={`bg-primary text-primary-foreground${slim ? " hidden sm:block" : ""}`}>
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
                className="flex items-center gap-1.5 text-base font-medium text-foreground hover:text-primary transition-colors"
              >
                {item.name}
                {item.badge === "new" && (
                  <span className="ft-nav-badge-new rounded-full bg-red-600 px-1.5 py-0.5 text-[10px] font-extrabold uppercase leading-none tracking-wide text-white">
                    New
                  </span>
                )}
              </a>
            ))}
          </div>

          <div className="flex flex-none items-center justify-end gap-3 sm:gap-4">
            <a
              href={phoneHref}
              className="hidden sm:flex items-center gap-2.5 text-lg font-bold text-foreground whitespace-nowrap"
            >
              <span
                className="ft-phone-icon flex h-[28px] w-[28px] flex-none items-center justify-center rounded-full text-white"
                style={{ WebkitTapHighlightColor: "transparent" }}
              >
                <Phone className="h-3 w-3" />
              </span>
              {settings.phone}
            </a>

            {slim ? (
              <a
                href={phoneHref}
                aria-label={`Call ${settings.phone}`}
                className="flex h-9 flex-none items-center gap-1.5 rounded-full bg-primary px-4 text-sm font-bold text-primary-foreground sm:hidden"
                style={{ WebkitTapHighlightColor: "transparent" }}
              >
                <Phone className="h-3.5 w-3.5" aria-hidden="true" />
                Call
              </a>
            ) : (
            <a
              href={phoneHref}
              aria-label={`Call ${settings.phone}`}
              className="ft-phone-icon flex h-[34px] w-[34px] flex-none items-center justify-center rounded-full text-white sm:hidden"
              style={{ WebkitTapHighlightColor: "transparent" }}
            >
              <Phone className="h-4 w-4" />
            </a>
            )}

            <button
              type="button"
              className={`${slim ? "hidden sm:flex" : "flex"} h-11 w-11 items-center justify-center rounded-md p-0 text-foreground lg:hidden`}
              style={{ WebkitTapHighlightColor: "transparent" }}
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
                  className="flex items-center gap-1.5 text-base font-medium text-foreground hover:text-primary"
                  onClick={() => setMobileMenuOpen(false)}
                >
                  {item.name}
                  {item.badge === "new" && (
                    <span className="ft-nav-badge-new rounded-full bg-red-600 px-1.5 py-0.5 text-[10px] font-extrabold uppercase leading-none tracking-wide text-white">
                      New
                    </span>
                  )}
                </a>
              ))}
              <div className="pt-4 border-t border-border flex flex-col gap-3">
                <a href={phoneHref} className="flex items-center gap-2.5 text-lg font-bold">
                  <span
                className="ft-phone-icon flex h-[28px] w-[28px] flex-none items-center justify-center rounded-full text-white"
                style={{ WebkitTapHighlightColor: "transparent" }}
              >
                    <Phone className="h-3 w-3" />
                  </span>
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
