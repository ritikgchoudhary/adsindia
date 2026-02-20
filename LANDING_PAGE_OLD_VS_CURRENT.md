# Landing Page – Old Project vs Current (Vue)

## Old project location (Blade)

Landing / home page structure is in the **core** folder (Laravel Blade templates):

| Old (Blade) | Path |
|-------------|------|
| Home layout | `core/resources/views/templates/basic/home.blade.php` |
| Banner | `core/resources/views/templates/basic/sections/banner.blade.php` |
| About | `core/resources/views/templates/basic/sections/about.blade.php` |
| Why Choose Us | `core/resources/views/templates/basic/sections/why_choose_us.blade.php` |
| Work Process | `core/resources/views/templates/basic/sections/work_process.blade.php` |
| Campaigns | `core/resources/views/templates/basic/sections/campaigns.blade.php` |
| Other sections | `core/resources/views/templates/basic/sections/*.blade.php` |
| Header | `core/resources/views/templates/basic/partials/header.blade.php` |
| Footer | `core/resources/views/templates/basic/partials/footer.blade.php` |
| Frontend layout | `core/resources/views/templates/basic/layouts/frontend.blade.php` |
| Section config | `core/resources/views/templates/basic/sections.json` |

**Old home flow:**  
`home.blade.php` → includes `sections.banner` → then loops over `$sectionList` and includes each `sections.{name}`.  
Default section order (when no CMS data):  
`about`, `category`, `campaigns`, `work_process`, `why_choose_us`, `benefit_section`, `counter_section`, `testimonials`, `cta_section`, `faq_section`, `blog`, `partner_section`.

---

## Current (Vue) – same structure

| Current (Vue) | Path |
|---------------|------|
| Home page | `frontend/src/views/Home.vue` |
| Banner | `frontend/src/components/sections/BannerSection.vue` |
| About | `frontend/src/components/sections/AboutSection.vue` |
| Why Choose Us | `frontend/src/components/sections/WhyChooseUsSection.vue` |
| Work Process | `frontend/src/components/sections/WorkProcessSection.vue` |
| Campaigns | `frontend/src/components/sections/CampaignsSection.vue` |
| Other sections | `frontend/src/components/sections/*.vue` |
| Header | `frontend/src/components/Header.vue` |
| Footer | `frontend/src/components/Footer.vue` |
| Layout | `frontend/src/components/Layout.vue` |

**Current home flow:**  
`Home.vue` → renders `BannerSection` → then fetches section list from API `GET /sections` (same order as old Blade) → renders each section as a dynamic component.  
Default section order in code matches the old project:  
`about`, `category`, `campaigns`, `work_process`, `why_choose_us`, `benefit_section`, `counter_section`, `testimonials`, `cta_section`, `faq_section`, `blog`, `partner_section`.

---

## API (sections)

- **Blade:** Section list and content came from `Page::where('slug', '/')->first()->secs` and `getContent()` in the controller.
- **Vue:** Section list from `GET /api/sections` (no key). Backend: `AppController@allSections` returns the same default list when no page/secs in DB.  
  Section content (e.g. about, banner) is loaded by each Vue section via `GET /api/sections/{key}` (e.g. `about`, `about.element`).

---

## Summary

Landing behaviour is aligned with the old project:

- Same section order and names.
- Same layout idea: header → banner → dynamic sections → footer.
- Same defaults if CMS/DB has no data.
- Content still comes from the same backend (sections API / Frontend content).

To change the landing page, edit `frontend/src/views/Home.vue` and the section components under `frontend/src/components/sections/`. To match old Blade content/styling exactly, compare with the Blade files under `core/resources/views/templates/basic/sections/`.
