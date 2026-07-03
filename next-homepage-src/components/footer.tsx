"use client"

import React, { useEffect, useRef } from "react"
import { Mail, Phone } from "lucide-react"
import { useHomepageSettingsStatus } from "@/components/homepage-settings-provider"

const socialIcons: Record<string, ({ className }: { className?: string }) => React.ReactElement> = {
  Facebook: ({ className }) => (
    <svg viewBox="0 0 24 24" className={className} aria-hidden="true">
      <path fill="currentColor" d="M13.5 22v-9h3l.5-3.5h-3.5V7.3c0-1 .3-1.8 1.8-1.8H17V2.4c-.3 0-1.4-.1-2.7-.1-2.7 0-4.6 1.7-4.6 4.8v2.4H7V13h2.7v9h3.8Z" />
    </svg>
  ),
  Instagram: ({ className }) => (
    <svg viewBox="0 0 24 24" className={className} aria-hidden="true">
      <path fill="currentColor" d="M7.2 2h9.6A5.2 5.2 0 0 1 22 7.2v9.6a5.2 5.2 0 0 1-5.2 5.2H7.2A5.2 5.2 0 0 1 2 16.8V7.2A5.2 5.2 0 0 1 7.2 2Zm-.2 2A3 3 0 0 0 4 7v10a3 3 0 0 0 3 3h10a3 3 0 0 0 3-3V7a3 3 0 0 0-3-3H7Zm10.3 1.5a1.2 1.2 0 1 1 0 2.4 1.2 1.2 0 0 1 0-2.4ZM12 7a5 5 0 1 1 0 10 5 5 0 0 1 0-10Zm0 2a3 3 0 1 0 0 6 3 3 0 0 0 0-6Z" />
    </svg>
  ),
  LinkedIn: ({ className }) => (
    <svg viewBox="0 0 24 24" className={className} aria-hidden="true">
      <path fill="currentColor" d="M5.3 7.8A2.3 2.3 0 1 1 5.3 3a2.3 2.3 0 0 1 0 4.7ZM3.3 9.5h4V21h-4V9.5Zm6.5 0h3.8v1.6h.1c.5-1 1.8-2.1 3.8-2.1 4 0 4.8 2.7 4.8 6.1V21h-4v-5.2c0-1.3 0-3-1.9-3s-2.1 1.4-2.1 2.9V21h-4V9.5Z" />
    </svg>
  ),
  YouTube: ({ className }) => (
    <svg viewBox="0 0 24 24" className={className} aria-hidden="true">
      <path fill="currentColor" d="M23 7.1a3 3 0 0 0-2.1-2.2C19 4.4 12 4.4 12 4.4s-7 0-8.9.5A3 3 0 0 0 1 7.1 31 31 0 0 0 .5 12a31 31 0 0 0 .5 4.9 3 3 0 0 0 2.1 2.2c1.9.5 8.9.5 8.9.5s7 0 8.9-.5a3 3 0 0 0 2.1-2.2 31 31 0 0 0 .5-4.9 31 31 0 0 0-.5-4.9ZM9.7 15.3V8.7L15.5 12l-5.8 3.3Z" />
    </svg>
  ),
  TikTok: ({ className }) => (
    <svg viewBox="0 0 24 24" className={className} aria-hidden="true">
      <path fill="currentColor" d="M16.7 2c.3 2.2 1.6 3.6 3.8 3.8v3.7a9.2 9.2 0 0 1-3.8-1.1v7.1a6.6 6.6 0 1 1-5.7-6.6v3.8a2.9 2.9 0 1 0 2 2.8V2h3.7Z" />
    </svg>
  ),
}

type FooterLink = { label: string; url: string }

function resolveLinks(value: unknown): FooterLink[] {
  if (Array.isArray(value)) return value.filter((item) => item?.label && item?.url)
  if (typeof value === "string") {
    return value.split(/\r?\n/).map((line) => {
      const [label, ...urlParts] = line.split("|")
      return { label: label.trim(), url: urlParts.join("|").trim() }
    }).filter((item) => item.label && item.url)
  }
  return []
}

export function Footer() {
  const { settings } = useHomepageSettingsStatus()
  const nctaRef = useRef<HTMLDivElement>(null)

  useEffect(() => {
    const el = nctaRef.current
    if (!el) return
    const html = (window as { __FT_NCTA_HTML__?: string }).__FT_NCTA_HTML__
    if (!html) return
    const temp = document.createElement("div")
    temp.innerHTML = html
    // Move styles + scripts to <head> so they apply globally and execute
    temp.querySelectorAll("style, script").forEach((node) => {
      if (node.tagName === "STYLE") {
        const s = document.createElement("style")
        s.id = (node as HTMLElement).id || ""
        s.textContent = node.textContent
        document.head.appendChild(s)
      } else {
        const s = document.createElement("script")
        s.textContent = node.textContent
        document.head.appendChild(s)
      }
      node.remove()
    })
    el.parentNode!.insertBefore(temp, el)
    el.remove()
  }, [])

  const logoSrc = settings.logo_image
  const phoneHref = `tel:${settings.phone.replace(/[^\d+]/g, "")}`
  const copyright = settings.footer_copyright.replaceAll("{year}", String(new Date().getFullYear()))
  const aboutLinks = resolveLinks(settings.footer_about_links)
  const helpLinks = resolveLinks(settings.footer_help_links)
  const policyLinks = resolveLinks(settings.footer_policy_links)
  const socialLinks = [
    ["Facebook", settings.facebook_url],
    ["Instagram", settings.instagram_url],
    ["LinkedIn", settings.linkedin_url],
    ["YouTube", settings.youtube_url],
    ["TikTok", settings.tiktok_url],
  ].filter(([, url]) => Boolean(url))

  return (
    <>
      <div ref={nctaRef} id="ft-ncta-placeholder" />

      <footer className="text-background" style={{ background: `linear-gradient(${settings.footer_bg_location}, ${settings.footer_bg_color_1}, ${settings.footer_bg_color_2})` }} role="contentinfo">
        <div className="mx-auto max-w-[1340px] px-4 py-10 sm:py-12">
          <div className="grid grid-cols-2 gap-x-6 gap-y-9 lg:grid-cols-[minmax(320px,1.7fr)_repeat(4,minmax(max-content,1fr))] lg:gap-x-8">
            <div className="col-span-2 flex flex-col items-center text-center lg:col-span-1 lg:items-start lg:text-left">
              <a href="/" className="inline-flex items-center text-2xl font-bold">{logoSrc ? <img src={logoSrc} alt={settings.logo_text} width={250} height={80} className="h-auto object-contain" style={{ width: settings.logo_size, maxWidth: "100%" }} loading="eager" fetchPriority="high" /> : <span>{settings.logo_text}</span>}</a>
              <p className="mt-4 text-sm text-background/70 leading-relaxed">{settings.footer_about}</p>
              {socialLinks.length > 0 ? (
                <div className="mt-6 flex items-center justify-center gap-3 lg:justify-start">
                  {socialLinks.map(([label, url]) => {
                    const Icon = socialIcons[label]
                    return (
                      <a
                        key={label}
                        href={url}
                        className="flex h-10 w-10 min-w-10 max-w-10 shrink-0 basis-10 aspect-square items-center justify-center rounded-full border border-white/25 bg-white/10 text-white transition-colors hover:border-secondary hover:bg-secondary hover:text-secondary-foreground sm:h-8 sm:w-8 sm:min-w-8 sm:max-w-8 sm:basis-8"
                        aria-label={label}
                        target="_blank"
                        rel="noopener noreferrer"
                      >
                        {Icon ? <Icon className="h-4 w-4" /> : label.slice(0, 1)}
                      </a>
                    )
                  })}
                </div>
              ) : null}
            </div>
            <div className="hidden lg:block"><h4 className="font-semibold mb-4">{settings.footer_about_title}</h4><ul className="space-y-3">{aboutLinks.map((link) => <li key={`${link.label}-${link.url}`}><a href={link.url} className="text-sm text-background/70 hover:text-background lg:whitespace-nowrap">{link.label}</a></li>)}</ul></div>
            <div className="hidden lg:block"><h4 className="font-semibold mb-4">{settings.footer_categories_title}</h4><ul className="space-y-3">{settings.nav_items.map((link) => <li key={link.name}><a href={link.href} className="text-sm text-background/70 hover:text-background lg:whitespace-nowrap">{link.name}</a></li>)}</ul></div>
            <div className="hidden lg:block"><h4 className="font-semibold mb-4">{settings.footer_help_title}</h4><ul className="space-y-3">{helpLinks.map((link) => <li key={`${link.label}-${link.url}`}><a href={link.url} className="text-sm text-background/70 hover:text-background lg:whitespace-nowrap">{link.label}</a></li>)}</ul></div>
            <div className="hidden lg:block lg:justify-self-end"><h4 className="font-semibold mb-4">{settings.footer_policies_title}</h4><ul className="space-y-3">{policyLinks.map((link) => <li key={`${link.label}-${link.url}`}><a href={link.url} className="text-sm text-background/70 hover:text-background lg:whitespace-nowrap">{link.label}</a></li>)}</ul></div>
          </div>
          <div className="mt-10 border-t border-background/10 pt-7 sm:mt-12 sm:pt-8">
            <div className="flex flex-col items-center gap-4 text-sm text-background/70 lg:flex-row lg:items-center lg:justify-between">
              <div className="flex flex-col items-center gap-3 sm:flex-row sm:flex-wrap sm:gap-6">
                <a href={phoneHref} className="flex items-center gap-2 hover:text-background"><Phone className="h-4 w-4" /><span>{settings.phone}</span></a>
                <a href={`mailto:${settings.email}`} className="flex items-center gap-2 hover:text-background"><Mail className="h-4 w-4" /><span>{settings.email}</span></a>
              </div>
              <p className="text-center lg:text-right">{copyright}</p>
            </div>
          </div>
        </div>
      </footer>
    </>
  )
}
