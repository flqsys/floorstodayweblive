import type { Metadata } from 'next'
import { ProcessSection } from "@/components/process-section"
import dynamic from "next/dynamic"
import { HomepageSettingsProvider } from "@/components/homepage-settings-provider"
import { HeaderSlot, HeroSlot, FooterSlot } from "@/components/homepage-visibility-slots"
import type { HomepageSettings } from "@/lib/homepage-settings"

const ComparisonSection = dynamic(() =>
  import("@/components/comparison-section").then((module) => module.ComparisonSection),
)
const CategoriesSection = dynamic(() =>
  import("@/components/categories-section").then((module) => module.CategoriesSection),
)
const GuaranteeSection = dynamic(() =>
  import("@/components/guarantee-section").then((module) => module.GuaranteeSection),
)
const DealsSection = dynamic(() =>
  import("@/components/deals-section").then((module) => module.DealsSection),
)
const TestimonialsSection = dynamic(() =>
  import("@/components/testimonials-section").then((module) => module.TestimonialsSection),
)
const CTASection = dynamic(() =>
  import("@/components/cta-section").then((module) => module.CTASection),
)

async function getInitialHomepageSettings(): Promise<Partial<HomepageSettings> | undefined> {
  const endpoint =
    process.env.NEXT_PUBLIC_WORDPRESS_HOMEPAGE_ENDPOINT || "/wp-json/floors-today/v1/homepage"
  const url = /^https?:\/\//i.test(endpoint)
    ? endpoint
    : `${process.env.NEXT_PUBLIC_WORDPRESS_ORIGIN || "http://localhost"}${endpoint}`

  try {
    const response = await fetch(url, { cache: "no-store" })

    if (!response.ok) {
      return undefined
    }

    return response.json()
  } catch {
    return undefined
  }
}

export async function generateMetadata(): Promise<Metadata> {
  const settings = await getInitialHomepageSettings()

  const title = settings?.seo_title || ""
  const description = settings?.seo_description || ""
  const ogTitle = settings?.seo_og_title || title
  const ogDescription = settings?.seo_og_description || description
  const ogImage = settings?.seo_og_image || ""
  const canonical = settings?.seo_canonical_url || ""

  let metadataBase: URL | undefined
  try {
    if (canonical) metadataBase = new URL(canonical)
  } catch { /* non-fatal */ }

  return {
    ...(metadataBase ? { metadataBase } : {}),
    ...(title ? { title } : {}),
    ...(description ? { description } : {}),
    ...(canonical ? { alternates: { canonical } } : {}),
    openGraph: {
      ...(ogTitle ? { title: ogTitle } : {}),
      ...(ogDescription ? { description: ogDescription } : {}),
      ...(ogImage ? { images: [ogImage] } : {}),
      type: "website",
    },
    twitter: {
      card: "summary_large_image",
      ...(ogTitle ? { title: ogTitle } : {}),
      ...(ogDescription ? { description: ogDescription } : {}),
      ...(ogImage ? { images: [ogImage] } : {}),
    },
  }
}

export default async function HomePage() {
  const initialSettings = await getInitialHomepageSettings()

  return (
    <HomepageSettingsProvider initialSettings={initialSettings}>
      <HeaderSlot />
      <main>
        <HeroSlot />
        <ProcessSection />
        <ComparisonSection />
        <CategoriesSection />
        <GuaranteeSection />
        <DealsSection />
        <TestimonialsSection />
        <CTASection />
      </main>
      <FooterSlot />
      {/* The LocalBusiness schema (ld+json) is added to <head> by the WordPress
          mu-plugin from live settings. Rendering it here too made WordPress
          rewrite React's markup, which broke hydration (React error #418). */}
    </HomepageSettingsProvider>
  )
}
