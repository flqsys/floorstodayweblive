"use client"

import React, { useEffect, useRef, useState } from "react"
import { ChevronRight, Menu, X, MapPin, Phone } from "lucide-react"
import { useHomepageSettingsStatus } from "@/components/homepage-settings-provider"
import { PromoBar } from "@/components/promo-bar"

// Menu pill icons, matched by link name - same set as ft_next_header_pill_icon()
// in floors-next-homepage-backend.php, so the homepage menu looks like the inner pages.
const pillIcons: [string, React.ReactNode][] = [
  ["how", <><path d="M3.5 5.5l1.5 1.5l2.5 -2.5" /><path d="M3.5 11.5l1.5 1.5l2.5 -2.5" /><path d="M3.5 17.5l1.5 1.5l2.5 -2.5" /><path d="M11 6h9" /><path d="M11 12h9" /><path d="M11 18h9" /></>],
  ["financ", <><rect x="3" y="5" width="18" height="14" rx="3" /><path d="M3 10h18" /><path d="M7 15h.01" /><path d="M11 15h2" /></>],
  ["contact", <><rect x="3" y="5" width="18" height="14" rx="2" /><path d="m3 7 9 6 9-6" /></>],
  ["faq", <><circle cx="12" cy="12" r="9" /><path d="M12 16v.01" /><path d="M12 13a2 2 0 0 0 .914 -3.782a1.98 1.98 0 0 0 -2.414 .483" /></>],
  ["about", <><circle cx="12" cy="12" r="9" /><path d="M12 9h.01" /><path d="M11 12h1v4h1" /></>],
  ["product", <><rect x="4" y="4" width="6" height="6" rx="1" /><rect x="14" y="4" width="6" height="6" rx="1" /><rect x="4" y="14" width="6" height="6" rx="1" /><rect x="14" y="14" width="6" height="6" rx="1" /></>],
  ["shop", <><rect x="4" y="4" width="6" height="6" rx="1" /><rect x="14" y="4" width="6" height="6" rx="1" /><rect x="4" y="14" width="6" height="6" rx="1" /><rect x="14" y="14" width="6" height="6" rx="1" /></>],
  ["locat", <><path d="M12 21s-7-6.2-7-11a7 7 0 0 1 14 0c0 4.8-7 11-7 11z" /><circle cx="12" cy="10" r="2.5" /></>],
  ["store", <><path d="M12 21s-7-6.2-7-11a7 7 0 0 1 14 0c0 4.8-7 11-7 11z" /><circle cx="12" cy="10" r="2.5" /></>],
  ["blog", <><path d="M5 4h11l3 3v13H5z" /><path d="M9 9h6" /><path d="M9 13h6" /><path d="M9 17h4" /></>],
  ["review", <path d="m12 3 2.6 5.6 6.1.6-4.6 4.1 1.3 6-5.4-3.1-5.4 3.1 1.3-6-4.6-4.1 6.1-.6z" />],
]

function PillIcon({ name }: { name: string }) {
  const n = name.toLowerCase()
  const match = pillIcons.find(([key]) => n.includes(key))
  return (
    <svg
      viewBox="0 0 24 24"
      fill="none"
      stroke="currentColor"
      strokeWidth={2}
      strokeLinecap="round"
      strokeLinejoin="round"
      aria-hidden="true"
      className="h-[18px] w-[18px] flex-none text-primary"
    >
      {match ? match[1] : <><path d="M5 12h14" /><path d="m13 6 6 6-6 6" /></>}
    </svg>
  )
}

export function Header() {
  const [mobileMenuOpen, setMobileMenuOpen] = useState(false)
  const { settings } = useHomepageSettingsStatus()
  const logoSrc = settings.logo_image
  const phoneHref = `tel:${settings.phone.replace(/[^\d+]/g, "")}`
  // Estimate form layout on phones: logo, Call and menu (no info bar or promo bar)
  const slim = settings.hero_variant === "estimate"
  // Same extra links as the inner-page menu (Home Settings > utility links)
  const utilityLinks = settings.utility_links?.length
    ? settings.utility_links
    : [
        { name: "Financing", href: "/financing/" },
        { name: "Contact", href: "/contact/" },
        { name: "FAQs", href: "/faqs/" },
      ]
  const headerRef = useRef<HTMLElement>(null)
  // Bottom of the logo bar: the open phone menu starts here and fills the rest
  // of the screen, so the hero is hidden behind it
  const barRef = useRef<HTMLDivElement>(null)
  const [menuTop, setMenuTop] = useState(0)
  const menuOpenRef = useRef(false)
  menuOpenRef.current = mobileMenuOpen

  useEffect(() => {
    if (!mobileMenuOpen || !slim) return
    const place = () => setMenuTop(barRef.current?.getBoundingClientRect().bottom ?? 0)
    place()
    window.addEventListener("resize", place)
    // The page behind the open menu doesn't scroll
    const html = document.documentElement
    const saved = [html.style.overflow, document.body.style.overflow]
    html.style.overflow = "hidden"
    document.body.style.overflow = "hidden"
    return () => {
      window.removeEventListener("resize", place)
      html.style.overflow = saved[0]
      document.body.style.overflow = saved[1]
    }
  }, [mobileMenuOpen, slim])

  // On phones the WordPress bridge fixes the header and offsets the page by a
  // set height; the slim header is shorter, so publish its real height instead.
  useEffect(() => {
    const root = document.documentElement
    const header = headerRef.current

    if (!slim || !header) {
      root.style.removeProperty("--ft-mobile-header-h")
      return
    }

    const update = () => {
      if (menuOpenRef.current) return
      root.style.setProperty("--ft-mobile-header-h", `${header.offsetHeight}px`)
    }
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
        <div ref={barRef} className="flex min-h-16 items-center justify-between gap-3 py-1">
          <a href="/" className="flex min-w-0 items-center text-2xl font-bold text-primary">
            {logoSrc ? (
              <img
                src={logoSrc}
                alt={settings.logo_text}
                width={250}
                height={80}
                className={`block h-auto object-contain sm:max-h-16 sm:max-w-[250px] ${slim ? "max-h-[60px] max-w-[210px]" : "max-h-14 max-w-[180px]"}`}
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
                // Same as the site header on other pages: round green phone icon
                // with a soft pulse (no "Call" text, so small phones fit the logo)
                className="ft-phone-icon flex h-10 w-10 flex-none items-center justify-center rounded-full text-white sm:hidden"
                style={{ WebkitTapHighlightColor: "transparent" }}
              >
                <Phone className="h-[18px] w-[18px]" aria-hidden="true" />
              </a>
            ) : (
            <a
              href={phoneHref}
              aria-label={`Call ${settings.phone}`}
              className="ft-phone-icon flex h-10 w-10 flex-none items-center justify-center rounded-full text-white sm:hidden"
              style={{ WebkitTapHighlightColor: "transparent" }}
            >
              <Phone className="h-[18px] w-[18px]" />
            </a>
            )}

            {slim ? (
              // Light hero on phones: round button the same height as Call,
              // the icon turns into an X while the menu is open.
              <button
                type="button"
                aria-expanded={mobileMenuOpen}
                aria-controls="ft-mobile-menu"
                className={`flex h-9 w-9 flex-none items-center justify-center rounded-full border transition-colors duration-200 lg:hidden ${
                  mobileMenuOpen
                    ? "border-primary bg-primary/10 text-primary"
                    : "border-gray-300 bg-card text-gray-700 hover:border-gray-400"
                }`}
                // Inline so the site-wide button style (Home Settings > Button Style:
                // padding, no border) can't squeeze the icon away or hide the border.
                style={{
                  WebkitTapHighlightColor: "transparent",
                  padding: 0,
                  borderWidth: 1,
                  borderStyle: "solid",
                  borderColor: mobileMenuOpen ? "var(--primary)" : "#d1d5db",
                  borderRadius: 9999,
                  minHeight: 0,
                }}
                onClick={() => setMobileMenuOpen(!mobileMenuOpen)}
              >
                <span className="sr-only">{mobileMenuOpen ? "Close menu" : "Open menu"}</span>
                <span key={mobileMenuOpen ? "x" : "menu"} className="animate-in fade-in zoom-in-75 duration-200">
                  {mobileMenuOpen ? (
                    <X className="h-[18px] w-[18px]" aria-hidden="true" />
                  ) : (
                    <Menu className="h-[18px] w-[18px]" aria-hidden="true" />
                  )}
                </span>
              </button>
            ) : (
            <button
              type="button"
              className="flex h-11 w-11 items-center justify-center rounded-md p-0 text-foreground lg:hidden"
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
            )}
          </div>
        </div>

        {mobileMenuOpen && slim && (
          // Light hero: menu drops down as tappable rows with arrows
          <div
            id="ft-mobile-menu"
            className="fixed inset-x-0 bottom-0 z-50 overflow-y-auto overscroll-contain bg-card px-4 animate-in fade-in slide-in-from-top-2 duration-200 lg:hidden"
            style={{ top: menuTop }}
          >
            <div className="border-t border-border pb-5 pt-3">
              {/* Rows slide in one after the other */}
              <ul className="flex flex-col gap-1">
                {settings.nav_items.map((item, index) => (
                  <li
                    key={item.name}
                    className="animate-in fade-in slide-in-from-top-1 fill-mode-both duration-300"
                    style={{ animationDelay: `${index * 40}ms` }}
                  >
                    <a
                      href={item.href}
                      className="group flex min-h-[52px] items-center justify-between gap-3 rounded-xl px-3 text-[17px] font-semibold text-foreground transition-colors hover:bg-muted hover:text-primary active:bg-muted"
                      onClick={() => setMobileMenuOpen(false)}
                    >
                      <span className="flex items-center gap-2">
                        {item.name}
                        {item.badge === "new" && (
                          <span className="ft-nav-badge-new rounded-full bg-red-600 px-1.5 py-0.5 text-[10px] font-extrabold uppercase leading-none tracking-wide text-white">
                            New
                          </span>
                        )}
                      </span>
                      <span className="flex h-8 w-8 flex-none items-center justify-center rounded-full bg-muted text-muted-foreground transition-colors group-hover:bg-primary group-hover:text-primary-foreground">
                        <ChevronRight className="h-4 w-4 transition-transform group-hover:translate-x-0.5" aria-hidden="true" />
                      </span>
                    </a>
                  </li>
                ))}
              </ul>
              {/* Same pills as the inner-page menu ([floors_header]): icon + name, 2 per row */}
              <div className="mt-4 grid grid-cols-2 gap-2 border-t border-border pt-4 text-[16px] font-bold animate-in fade-in fill-mode-both duration-300 [animation-delay:200ms]">
                {utilityLinks.map((item) => (
                  <a
                    key={item.name}
                    href={item.href}
                    onClick={() => setMobileMenuOpen(false)}
                    className="flex min-h-[46px] items-center justify-start gap-2 whitespace-nowrap rounded-full border border-border bg-background px-4 text-left text-foreground hover:border-primary hover:text-primary active:border-primary active:text-primary"
                  >
                    <PillIcon name={item.name} />
                    <span className="overflow-hidden text-ellipsis">{item.name}</span>
                  </a>
                ))}
              </div>
              <a
                href={phoneHref}
                className="mt-3 flex h-14 items-center justify-center gap-3 text-[22px] font-extrabold tracking-tight text-foreground animate-in fade-in fill-mode-both duration-300 [animation-delay:250ms]"
              >
                <span className="ft-phone-icon flex h-10 w-10 flex-none items-center justify-center rounded-full text-white">
                  <Phone className="h-[18px] w-[18px]" aria-hidden="true" />
                </span>
                {settings.phone}
              </a>
            </div>
          </div>
        )}

        {mobileMenuOpen && !slim && (
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
