export type NavItem = {
  name: string
  href: string
}

export type ProcessStep = {
  title: string
  description: string
  button?: string
  image: string
}

export type CategoryItem = {
  name: string
  slug: string
  description: string
  image: string
}

export type OfferItem = {
  title: string
  description: string
}

export type TestimonialItem = {
  name: string
  location: string
  floorType: string
  text: string
}

export type HomepageSettings = {
  primary_color: string
  secondary_color: string
  background_color: string
  foreground_color: string
  phone: string
  email: string
  service_area: string
  logo_text: string
  logo_image: string
  favicon_image: string
  logo_size: string
  cta_label: string
  show_header: string | boolean
  show_footer: string | boolean
  facebook_url: string
  instagram_url: string
  linkedin_url: string
  youtube_url: string
  tiktok_url: string
  button_radius: string
  button_font_weight: string
  button_text_transform: string
  button_padding_x: string
  button_padding_y: string
  button_hover_mix: string
  button_border_width: string
  button_border_style: string
  button_border_color: string
  hero_badge: string
  hero_badge_bg_color: string
  hero_badge_text_color: string
  hero_badge_font_size: string
  hero_badge_padding_x: string
  hero_badge_padding_y: string
  hero_title: string
  hero_highlight: string
  hero_badge_animation_color_1: string
  hero_badge_animation_color_2: string
  hero_badge_animation_location: string
  hero_badge_animation_speed: string
  hero_text: string
  hero_image: string
  hero_show_background: string | boolean
  hero_show_overlay: string | boolean
  hero_overlay_opacity: string
  form_title: string
  form_subtitle: string
  estimate_form_embed_code: string
  process_title: string
  process_text: string
  process_bg_color_1: string
  process_bg_color_2: string
  process_bg_location: string
  process_steps: ProcessStep[]
  comparison_title: string
  comparison_table_title: string
  comparison_text: string
  comparison_disclaimer: string
  comparison_rows: string[]
  comparison_button: string
  comparison_bg_color_1: string
  comparison_bg_color_2: string
  comparison_bg_location: string
  cta_title: string
  cta_subtitle: string
  cta_text: string
  cta_button: string
  cta_bg_color_1: string
  cta_bg_color_2: string
  cta_bg_location: string
  category_title: string
  category_text: string
  category_bg_color_1: string
  category_bg_color_2: string
  category_bg_location: string
  categories: CategoryItem[]
  guarantee_title: string
  guarantee_subtitle: string
  guarantee_text: string
  guarantee_link: string
  guarantee_image: string
  guarantee_bg_color_1: string
  guarantee_bg_color_2: string
  guarantee_bg_location: string
  deals_badge: string
  deals_title: string
  deals_text: string
  deals_body: string
  deals_card_title: string
  deals_card_subtitle: string
  deals_button: string
  deals_details_label: string
  deals_includes_title: string
  deals_includes: string
  deals_popup_eyebrow: string
  deals_popup_title: string
  deals_popup_intro: string
  deals_popup_button: string
  deals_popup_steps_title: string
  deals_popup_steps: string
  deals_popup_terms: string
  deals_popup_terms_extra: string
  deals_bg_color_1: string
  deals_bg_color_2: string
  deals_bg_location: string
  offers: OfferItem[]
  testimonials_title: string
  testimonials_text: string
  show_testimonials: string | boolean
  testimonials_embed_code: string
  testimonials_script_src: string
  testimonials_script_id: string
  chat_embed_code: string
  chat_script_src: string
  chat_script_id: string
  fb_pixel_id: string
  ga4_measurement_id: string
  gtm_container_id: string
  recaptcha_site_key: string
  testimonials_bg_color_1: string
  testimonials_bg_color_2: string
  testimonials_bg_location: string
  testimonials: TestimonialItem[]
  newsletter_title: string
  newsletter_text: string
  newsletter_button: string
  newsletter_details_text: string
  footer_about: string
  footer_about_title: string
  footer_about_links: Array<{ label: string; url: string }> | string
  footer_categories_title: string
  footer_help_title: string
  footer_help_links: Array<{ label: string; url: string }> | string
  footer_policies_title: string
  footer_policy_links: Array<{ label: string; url: string }> | string
  footer_bottom_links: Array<{ label: string; url: string }> | string
  footer_copyright: string
  google_places_api_key: string
  footer_bg_color_1: string
  footer_bg_color_2: string
  footer_bg_location: string
  nav_items: NavItem[]
  seo_title: string
  seo_description: string
  seo_canonical_url: string
  seo_robots: string
  seo_og_title: string
  seo_og_description: string
  seo_og_image: string
  footer_badge_image_1: string
  footer_badge_image_2: string
  footer_badge_image_3: string
  footer_badge_image_4: string
  footer_badge_image_5: string
  footer_badge_image_6: string
  footer_badge_height: string
}

export const homepageDefaults: HomepageSettings = {
  primary_color: "#235bb8",
  secondary_color: "#cc9c2e",
  background_color: "#ffffff",
  foreground_color: "#111111",
  phone: "",
  email: "",
  service_area: "",
  logo_text: "",
  logo_image: "",
  favicon_image: "",
  logo_size: "250px",
  cta_label: "",
  show_header: "1",
  show_footer: "1",
  facebook_url: "",
  instagram_url: "",
  linkedin_url: "",
  youtube_url: "",
  tiktok_url: "",
  button_radius: "8px",
  button_font_weight: "700",
  button_text_transform: "none",
  button_padding_x: "18px",
  button_padding_y: "12px",
  button_hover_mix: "88%",
  button_border_width: "0px",
  button_border_style: "solid",
  button_border_color: "transparent",
  hero_badge: "",
  hero_badge_bg_color: "#cc9c2e",
  hero_badge_text_color: "#ffffff",
  hero_badge_font_size: "16px",
  hero_badge_padding_x: "16px",
  hero_badge_padding_y: "8px",
  hero_title: "",
  hero_highlight: "",
  hero_badge_animation_color_1: "#cc9c2e",
  hero_badge_animation_color_2: "#ffffff",
  hero_badge_animation_location: "90deg",
  hero_badge_animation_speed: "4s",
  hero_text: "",
  hero_image: "",
  hero_show_background: "1",
  hero_show_overlay: "1",
  hero_overlay_opacity: "0.72",
  form_title: "",
  form_subtitle: "",
  estimate_form_embed_code: "",
  process_title: "",
  process_text: "",
  process_bg_color_1: "#ffffff",
  process_bg_color_2: "#ffffff",
  process_bg_location: "to bottom",
  process_steps: [],
  comparison_title: "",
  comparison_table_title: "",
  comparison_text: "",
  comparison_disclaimer: "",
  comparison_rows: [],
  comparison_button: "",
  comparison_bg_color_1: "var(--primary)",
  comparison_bg_color_2: "var(--primary)",
  comparison_bg_location: "to bottom",
  cta_title: "",
  cta_subtitle: "",
  cta_text: "",
  cta_button: "",
  cta_bg_color_1: "var(--primary)",
  cta_bg_color_2: "var(--primary)",
  cta_bg_location: "to bottom",
  category_title: "",
  category_text: "",
  category_bg_color_1: "#ffffff",
  category_bg_color_2: "#ffffff",
  category_bg_location: "to bottom",
  categories: [],
  guarantee_title: "",
  guarantee_subtitle: "",
  guarantee_text: "",
  guarantee_link: "",
  guarantee_image: "",
  guarantee_bg_color_1: "#ffffff",
  guarantee_bg_color_2: "#ffffff",
  guarantee_bg_location: "to bottom",
  deals_badge: "",
  deals_title: "",
  deals_text: "",
  deals_body: "",
  deals_card_title: "",
  deals_card_subtitle: "",
  deals_button: "",
  deals_details_label: "",
  deals_includes_title: "",
  deals_includes: "",
  deals_popup_eyebrow: "",
  deals_popup_title: "",
  deals_popup_intro: "",
  deals_popup_button: "",
  deals_popup_steps_title: "",
  deals_popup_steps: "",
  deals_popup_terms: "",
  deals_popup_terms_extra: "",
  deals_bg_color_1: "#ffffff",
  deals_bg_color_2: "#ffffff",
  deals_bg_location: "to bottom",
  offers: [],
  testimonials_title: "",
  testimonials_text: "",
  show_testimonials: "1",
  testimonials_embed_code: "",
  testimonials_script_src: "",
  testimonials_script_id: "",
  chat_embed_code: "",
  chat_script_src: "",
  chat_script_id: "",
  fb_pixel_id: "",
  ga4_measurement_id: "",
  gtm_container_id: "",
  recaptcha_site_key: "",
  testimonials_bg_color_1: "#ffffff",
  testimonials_bg_color_2: "#ffffff",
  testimonials_bg_location: "to bottom",
  testimonials: [],
  newsletter_title: "",
  newsletter_text: "",
  newsletter_button: "",
  newsletter_details_text: "Complete the short form in the newsletter section to receive the latest flooring deals, project tips, and details about your $300 store credit.",
  footer_about: "",
  footer_about_title: "",
  footer_about_links: [],
  footer_categories_title: "",
  footer_help_title: "",
  footer_help_links: [],
  footer_policies_title: "",
  footer_policy_links: [],
  footer_bottom_links: "",
  footer_copyright: "",
  google_places_api_key: "",
  footer_bg_color_1: "#111111",
  footer_bg_color_2: "#111111",
  footer_bg_location: "to bottom",
  nav_items: [],
  seo_title: "",
  seo_description: "",
  seo_canonical_url: "",
  seo_robots: "index, follow",
  seo_og_title: "",
  seo_og_description: "",
  seo_og_image: "",
  footer_badge_image_1: "",
  footer_badge_image_2: "",
  footer_badge_image_3: "",
  footer_badge_image_4: "",
  footer_badge_image_5: "",
  footer_badge_image_6: "",
  footer_badge_height: "60px",
}

export function mergeHomepageSettings(
  settings: Partial<HomepageSettings> | null | undefined,
): HomepageSettings {
  if (!settings) {
    return homepageDefaults
  }

  const merged = {
    ...homepageDefaults,
    ...settings,
    nav_items: settings.nav_items?.length ? settings.nav_items : homepageDefaults.nav_items,
    process_steps: settings.process_steps?.length
      ? settings.process_steps
      : homepageDefaults.process_steps,
    comparison_rows: settings.comparison_rows?.length
      ? settings.comparison_rows
      : homepageDefaults.comparison_rows,
    categories: settings.categories?.length ? settings.categories : homepageDefaults.categories,
    offers: settings.offers?.length ? settings.offers : homepageDefaults.offers,
    testimonials: settings.testimonials?.length
      ? settings.testimonials
      : homepageDefaults.testimonials,
  }

  const oldBlue = "#155f99"
  const sectionFallback = "var(--primary)"

  ;[
    "comparison_bg_color_1",
    "comparison_bg_color_2",
    "cta_bg_color_1",
    "cta_bg_color_2",
  ].forEach((field) => {
    const key = field as keyof HomepageSettings

    if (String(merged[key]).toLowerCase() === oldBlue) {
      ;(merged as Record<string, unknown>)[field] = sectionFallback
    }
  })

  return merged
}
