"use client"

import { useEffect } from "react"
import { Star, Quote } from "lucide-react"
import { Card, CardContent } from "@/components/ui/card"
import { useHomepageSettings } from "@/components/homepage-settings-provider"

function isEnabled(val: string | boolean | undefined): boolean {
  if (val === undefined) return true
  return val === true || val === "1"
}

export function TestimonialsSection() {
  const settings = useHomepageSettings()

  const hasEmbed =
    typeof settings.testimonials_script_src === "string" &&
    settings.testimonials_script_src.trim() !== ""

  // Inject the review widget script inside the mount div after React hydrates.
  // The ConnectXMedia widget renders adjacent to its own <script> tag, so the
  // script must live inside #ft-reviews-embed — not at the end of <body>.
  useEffect(() => {
    if (!hasEmbed) return
    const src = settings.testimonials_script_src
    const scriptId = settings.testimonials_script_id || "ft-embed-widget"
    if (document.getElementById(scriptId)) return // already injected
    const container = document.getElementById("ft-reviews-embed")
    if (!container) return
    const script = document.createElement("script")
    script.src = src
    script.id = scriptId
    script.defer = true
    container.appendChild(script)
  }, [hasEmbed, settings.testimonials_script_src, settings.testimonials_script_id])

  if (!isEnabled(settings.show_testimonials)) return null

  return (
    <section
      className="py-14 sm:py-16 lg:py-20"
      style={{
        background: `linear-gradient(${settings.testimonials_bg_location}, ${settings.testimonials_bg_color_1}, ${settings.testimonials_bg_color_2})`,
      }}
      aria-labelledby="testimonials-heading"
    >
      <div className="mx-auto max-w-[1340px] px-4">
        <div className="mb-8 text-center sm:mb-12">
          <h2 id="testimonials-heading" className="font-serif text-3xl font-bold text-foreground sm:text-4xl">
            {settings.testimonials_title}
          </h2>
          <p className="mx-auto mt-4 max-w-2xl text-base text-muted-foreground sm:text-lg">
            {settings.testimonials_text}
          </p>
        </div>

        {hasEmbed ? (
          /* Mount point — the ConnectXMedia widget appends itself here after the script runs */
          <div id="ft-reviews-embed" className="w-full" />
        ) : (
          <div className="grid gap-5 md:grid-cols-3 lg:gap-8">
            {settings.testimonials.map((testimonial, index) => (
              <Card key={`${testimonial.name}-${index}`} className="relative rounded-xl shadow-sm">
                <CardContent className="p-5 pt-8 sm:p-6 sm:pt-8">
                  <Quote className="absolute top-4 right-4 h-8 w-8 text-secondary/20" />

                  <div className="flex gap-1 mb-4">
                    {[...Array(5)].map((_, i) => (
                      <Star key={i} className="h-5 w-5 fill-secondary text-secondary" />
                    ))}
                  </div>

                  <p className="text-muted-foreground leading-relaxed mb-6">
                    &ldquo;{testimonial.text}&rdquo;
                  </p>

                  <div className="border-t border-border pt-4">
                    <p className="font-semibold text-foreground">{testimonial.name}</p>
                    <p className="text-sm text-muted-foreground">{testimonial.location}</p>
                    <p className="text-sm text-secondary mt-1">{testimonial.floorType}</p>
                  </div>
                </CardContent>
              </Card>
            ))}
          </div>
        )}
      </div>
    </section>
  )
}
