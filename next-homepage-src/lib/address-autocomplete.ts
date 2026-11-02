"use client"

import { useEffect, useRef, type RefObject } from "react"

// Google address suggestions for the hero forms - same setup as the WordPress
// booking forms (Canada only, street addresses). The homepage <head> loads the
// Google script when Home Settings has a Places API key, then sets
// window.__ftPlacesLoaded and fires "ft:places:ready". No key, no suggestions:
// the field stays a normal text box.

export type PickedAddress = {
  street: string
  city: string
  province: string
  postalCode: string
}

type AddressComponent = { long_name: string; short_name: string; types: string[] }
type PlacesAutocomplete = {
  addListener: (event: string, handler: () => void) => { remove: () => void }
  getPlace: () => { address_components?: AddressComponent[] } | undefined
}

declare global {
  interface Window {
    __ftPlacesLoaded?: boolean
    google?: {
      maps?: {
        places?: {
          Autocomplete: new (
            input: HTMLInputElement,
            options: Record<string, unknown>,
          ) => PlacesAutocomplete
        }
        event?: { clearInstanceListeners: (instance: unknown) => void }
      }
    }
  }
}

export function useAddressAutocomplete(
  inputRef: RefObject<HTMLInputElement | null>,
  onPick: (address: PickedAddress) => void,
  enabled = true,
) {
  // Keep the latest callback without re-attaching Google to the input
  const pickRef = useRef(onPick)
  useEffect(() => {
    pickRef.current = onPick
  })

  useEffect(() => {
    if (!enabled) return
    let ac: PlacesAutocomplete | null = null
    let listener: { remove: () => void } | null = null

    const attach = () => {
      const input = inputRef.current
      const places = window.google?.maps?.places
      if (ac || !input || !places) return
      ac = new places.Autocomplete(input, {
        types: ["address"],
        componentRestrictions: { country: "ca" },
        fields: ["address_components"],
      })
      listener = ac.addListener("place_changed", () => {
        const parts = ac?.getPlace()?.address_components
        if (!parts) return
        const byType: Record<string, AddressComponent> = {}
        parts.forEach((c) => c.types.forEach((t) => (byType[t] = c)))
        pickRef.current({
          street: `${byType.street_number?.long_name || ""} ${byType.route?.long_name || ""}`.trim(),
          city: byType.locality?.long_name || byType.sublocality?.long_name || byType.postal_town?.long_name || "",
          province: byType.administrative_area_level_1?.short_name || "",
          postalCode: byType.postal_code?.long_name || "",
        })
      })
    }

    if (window.__ftPlacesLoaded || window.google?.maps?.places) attach()
    document.addEventListener("ft:places:ready", attach)
    return () => {
      document.removeEventListener("ft:places:ready", attach)
      listener?.remove()
      if (ac) window.google?.maps?.event?.clearInstanceListeners(ac)
    }
  }, [inputRef, enabled])
}
