export type LeadAttribution = {
  trafficSource?: string
  referrerUrl?: string
  referrerHost?: string
  utmSource?: string
  utmMedium?: string
  utmCampaign?: string
  utmContent?: string
  utmTerm?: string
}

declare global {
  interface Window {
    ftGetAttribution?: () => LeadAttribution
    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    fbq?: (...args: any[]) => void
    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    gtag?: (...args: any[]) => void
    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    dataLayer?: any[]
    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    XDTrack?: { lead: (data: Record<string, string>) => void }
    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    oaiq?: (...args: any[]) => void
  }
}

// Same traffic-source resolution the WordPress booking forms use: the site-wide
// ftGetAttribution() (first-touch, set by WordPress) wins, then URL params, then
// the referrer.
export function getLeadAttribution() {
  const url = new URL(window.location.href)
  const attribution = window.ftGetAttribution ? window.ftGetAttribution() : {}
  const referrerUrl = attribution.referrerUrl || document.referrer || ""
  let referrerHost = attribution.referrerHost || ""
  if (!referrerHost && document.referrer) {
    try {
      referrerHost = new URL(document.referrer).hostname.replace(/^www\./, "")
    } catch {
      referrerHost = ""
    }
  }
  const utmSource =
    attribution.utmSource || url.searchParams.get("hello_social") || url.searchParams.get("utm_source") || ""

  return {
    trafficSource: attribution.trafficSource || utmSource || referrerHost || "Direct",
    referrerUrl,
    utmSource,
    utmMedium: attribution.utmMedium || url.searchParams.get("utm_medium") || "",
    utmCampaign: attribution.utmCampaign || url.searchParams.get("utm_campaign") || "",
    utmContent: attribution.utmContent || url.searchParams.get("utm_content") || "",
    utmTerm: attribution.utmTerm || url.searchParams.get("utm_term") || "",
  }
}

// Conversion tracking for a successful homepage hero estimate submission. Both
// hero variants report as the same form so existing ad/GTM conversions keep
// counting whichever hero is switched on.
export function trackHeroEstimateLead(details: { flooringType: string; propertyType: string }) {
  const { trafficSource, utmSource } = getLeadAttribution()

  const trackingData = {
    formId: "hero_estimate",
    formName: "Homepage Hero Estimate Form",
    flooringType: details.flooringType,
    propertyType: details.propertyType,
    leadSource: trafficSource,
    utmSource,
  }

  // Facebook Pixel - Lead event
  if (window.fbq) {
    window.fbq("track", "Lead", {
      content_name: trackingData.formName,
      content_category: trackingData.flooringType,
      currency: "CAD",
    })
  }

  // GA4 - generate_lead event
  if (window.gtag) {
    window.gtag("event", "generate_lead", {
      currency: "CAD",
      form_id: trackingData.formId,
      flooring_type: trackingData.flooringType,
      lead_source: trackingData.leadSource,
    })
  }

  // GTM dataLayer push
  if (window.dataLayer) {
    window.dataLayer.push({
      event: "form_lead",
      form_id: trackingData.formId,
      form_name: trackingData.formName,
      flooring_type: trackingData.flooringType,
      lead_source: trackingData.leadSource,
      utm_source: trackingData.utmSource,
    })
  }

  // XDTrack global (injected by XD CRM plugin on WordPress pages)
  if (window.XDTrack) {
    window.XDTrack.lead(trackingData)
  }

  // OpenAI Ads — conversion event. Per developers.openai.com/ads/measurement-pixel,
  // "quote_requested" isn't a standard event name, so it goes through
  // the custom-event path (measure "custom" + custom_event_name).
  if (window.oaiq) {
    window.oaiq(
      "measure",
      "custom",
      { type: "custom" },
      { custom_event_name: "quote_requested" }
    )
  }
}

// Province from the first letter of a Canadian postal code, so the CRM gets
// a province like it does from the booking form (the hero forms don't ask).
const POSTAL_PROVINCE: Record<string, string> = {
  A: "NL", B: "NS", C: "PE", E: "NB", G: "QC", H: "QC", J: "QC",
  K: "ON", L: "ON", M: "ON", N: "ON", P: "ON", R: "MB", S: "SK", T: "AB", V: "BC", Y: "YT",
}
export function provinceFromPostal(postalCode: string) {
  return POSTAL_PROVINCE[postalCode.trim().charAt(0).toUpperCase()] || ""
}

// After a hero lead is saved: the same steps as the WordPress booking form
// ([floors_booking_form], the form the live hero uses) - one XDTrack lead
// event with the booking form's id, then the Form Settings thank-you page
// after a short wait so the tracking call goes out first.
export function finishBookingFormLead(flooringType: string, redirectUrl?: string) {
  const { trafficSource, utmSource } = getLeadAttribution()
  if (window.XDTrack && window.XDTrack.lead) {
    window.XDTrack.lead({
      formId: "booking_form",
      formName: "Booking Form Shortcode",
      flooringType,
      leadSource: trafficSource || utmSource || "Direct",
      utmSource,
    })
  }
  if (redirectUrl) {
    window.setTimeout(() => { window.location.href = redirectUrl }, 600)
  }
}
