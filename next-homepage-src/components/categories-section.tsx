"use client"

import { useHomepageSettings } from "@/components/homepage-settings-provider"

function getCategorySlugId(value: string) {
  const trimmed = value.trim()
  const path = trimmed.split("#")[0]?.split("?")[0] ?? trimmed
  const parts = path.split("/").filter(Boolean)

  return parts.at(-1) || trimmed.replace(/^#/, "") || "category"
}

function getSiteBasePath() {
  if (typeof window === "undefined") return ""

  const publicIndex = window.location.pathname.indexOf("/public")

  if (publicIndex > -1) {
    return window.location.pathname.slice(0, publicIndex)
  }

  const firstSegment = window.location.pathname.split("/").filter(Boolean)[0]

  return firstSegment ? `/${firstSegment}` : ""
}

function getCategoryHref(value: string) {
  const trimmed = value.trim()

  if (!trimmed) return "#estimate"
  if (trimmed.startsWith("#")) return trimmed
  if (/^[a-z][a-z0-9+.-]*:/i.test(trimmed)) return trimmed

  const path = trimmed.startsWith("/") ? trimmed : `/categories/${trimmed.replace(/^\/+|\/+$/g, "")}/`
  const siteBasePath = getSiteBasePath()

  if (!siteBasePath || path === siteBasePath || path.startsWith(`${siteBasePath}/`)) return path

  return `${siteBasePath}${path}`
}

export function CategoriesSection() {
  const settings = useHomepageSettings()

  return (
    <section
      className="py-14 sm:py-16 lg:py-20"
      style={{
        background: `linear-gradient(${settings.category_bg_location}, ${settings.category_bg_color_1}, ${settings.category_bg_color_2})`,
      }}
      aria-labelledby="categories-heading"
    >
      <div className="mx-auto max-w-[1340px] px-4">
        <div className="mb-8 text-center sm:mb-12">
          <h2 id="categories-heading" className="font-serif text-3xl font-bold text-foreground sm:text-4xl">
            {settings.category_title}
          </h2>
          <p className="mx-auto mt-4 max-w-2xl text-base text-muted-foreground sm:text-lg">
            {settings.category_text}
          </p>
        </div>

        <div className="grid grid-cols-2 gap-3 sm:gap-4 md:grid-cols-3 lg:grid-cols-5">
          {settings.categories.map((category) => {
            const categoryId = getCategorySlugId(category.slug)
            const categoryHref = getCategoryHref(category.slug)
            const legacyAnchor = categoryId.replace("-hardwood", "")

            return (
              <a
                key={`${category.name}-${category.slug}`}
                href={categoryHref}
                id={categoryId}
                className="group relative aspect-[4/5] scroll-mt-28 overflow-hidden rounded-xl shadow-sm last:col-span-full last:aspect-[8/5] md:last:col-span-1 md:last:aspect-[4/5]"
              >
                {legacyAnchor !== categoryId ? (
                  <span id={legacyAnchor} className="absolute inset-x-0 top-0 scroll-mt-28" aria-hidden="true" />
                ) : null}
                <img
                  src={category.image}
                  alt={category.name}
                  width={400}
                  height={500}
                  loading="lazy"
                  decoding="async"
                  className="h-full w-full object-cover object-center group-hover:scale-105 transition-transform duration-300"
                />
                <div className="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent" />
                <div className="absolute bottom-0 left-0 right-0 p-3 text-white sm:p-4">
                  <h3 className="text-sm font-semibold leading-tight sm:text-lg">{category.name}</h3>
                  <p className="mt-1 hidden text-sm text-white/80 sm:block">{category.description}</p>
                </div>
              </a>
            )
          })}
        </div>
      </div>
    </section>
  )
}