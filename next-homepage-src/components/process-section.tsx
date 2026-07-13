"use client"

import Link from "next/link"
import { Calendar, FileText, Wrench } from "lucide-react"
import { useHomepageSettings } from "@/components/homepage-settings-provider"

export function ProcessSection() {
  const settings = useHomepageSettings()
  const icons = [Calendar, FileText, Wrench]
  const scrollToEstimate = (event: React.MouseEvent<HTMLAnchorElement>) => {
    event.preventDefault()
    document.getElementById("estimate")?.scrollIntoView({ behavior: "smooth", block: "start" })
  }

  return (
    <section
      id="how-it-works"
      className="py-14 sm:py-16 lg:py-20"
      style={{
        background: `linear-gradient(${settings.process_bg_location}, ${settings.process_bg_color_1}, ${settings.process_bg_color_2})`,
      }}
      aria-labelledby="process-heading"
    >
      <div className="mx-auto max-w-[1340px] px-4">
        <div className="mb-8 text-center sm:mb-12 lg:mb-16">
          <h2 id="process-heading" className="font-serif text-3xl font-bold text-foreground sm:text-4xl">
            {settings.process_title}
          </h2>
          <p className="mx-auto mt-4 max-w-2xl text-base text-muted-foreground sm:text-lg">
            {settings.process_text}
          </p>
        </div>

        <div className="grid gap-6 md:grid-cols-3 lg:gap-8">
          {settings.process_steps.map((step, index) => {
            const StepIcon = icons[index] || Calendar

            return (
            <article
              key={`${step.title}-${index}`}
              className="group relative overflow-hidden rounded-xl border border-border bg-card shadow-md shadow-black/5 transition-shadow hover:shadow-lg"
            >
              <div className="aspect-[4/3] overflow-hidden">
                <img
                  src={step.image}
                  alt={step.title}
                  width={400}
                  height={300}
                  loading="lazy"
                  decoding="async"
                  className="h-full w-full object-cover group-hover:scale-105 transition-transform duration-300"
                />
                <div className="absolute top-4 left-4">
                  <div className="flex items-center justify-center h-10 w-10 rounded-full bg-primary text-primary-foreground font-bold text-lg">
                    {index + 1}
                  </div>
                </div>
              </div>
              <div className="p-5 sm:p-6">
                <div className="flex items-center gap-3 mb-3">
                  <StepIcon className="h-5 w-5 text-secondary" />
                  <h3 className="text-lg font-semibold leading-snug text-foreground">{step.title}</h3>
                </div>
                <p className="text-muted-foreground leading-relaxed">{step.description}</p>
                {step.button && (
                  <Link
                    href="#estimate"
                    onClick={scrollToEstimate}
                    className="mt-4 inline-flex min-h-10 w-full items-center justify-center rounded-md bg-primary px-4 py-2 text-sm font-bold text-primary-foreground transition-colors hover:bg-primary/90 sm:w-auto"
                  >
                    {step.button}
                  </Link>
                )}
              </div>
            </article>
          )})}
        </div>
      </div>
    </section>
  )
}
