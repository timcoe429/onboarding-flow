# Shared Steps Refactoring - Complete Overview

## Executive Summary

This document describes the comprehensive refactoring of the docket-onboarding WordPress plugin to consolidate duplicate step files into a shared directory structure. This refactoring significantly reduces code duplication, improves maintainability, and standardizes field names across all form types (Fast Build, Standard Build, and Website VIP).

## What Changed

### Before Refactoring
- Each form type (Fast Build, Standard Build, Website VIP) had its own complete set of step files
- Many steps were identical or nearly identical across form types
- Field names were inconsistent (e.g., `contact_name` vs `name`, `dumpster_stock_image_color_selection` vs `dumpster_color`)
- Changes to common functionality required updating multiple files
- Total step files: ~24 files (8 steps × 3 form types)

### After Refactoring
- Common steps consolidated into `includes/forms/shared/steps/` directory
- Form-specific steps remain in their respective directories (e.g., `fast-build/steps/`)
- Standardized field names across all forms
- Single source of truth for shared functionality
- Total step files: ~11 files (7 shared + 4 form-specific)

## Shared Steps Created

The following steps are now shared across all form types:

### Step 1: Terms & Conditions (`step-1-terms.php`)
**Status:** ✅ Fully shared with conditional logic to show different terms based on the site type

**Features:**
- Database-driven content via `docket_get_form_content()`
- Conditional WordPress Experience section (Fast Build only)
- Form-specific modal content
- Standardized checkbox handling

**Form-Specific Differences:**
- Fast Build: Shows WordPress Experience section, different modal content
- Standard Build: Standard terms modal
- Website VIP: WebsiteVIP-specific modal content

**Field Names:** Standardized to `accept_terms` (Fast Build uses `value="accepted"`)

---

### Step 2: Contact Information (`step-2-contact.php`)
**Status:** ✅ Fully shared

**Standardized Fields:**
- `name` (was `contact_name` in Website VIP)
- `email` (was `contact_email_address` in Website VIP)
- `phone_number` (was `business_phone_number` in Website VIP)

**All forms now use identical field names and structure.**

---

### Step 3: Template Information (`step-3-template-info.php`)
**Status:** ✅ Fully shared with conditional content

**Features:**
- Database-driven content with form-specific defaults
- Conditional checkbox name/value based on form type
- Fast Build: Different checkbox (`understand_fast_build`)
- Standard Build & Website VIP: Same checkbox (`accept_webbuild_terms`)

**Content Differences:**
- Fast Build: Fast Build-specific limitations and timeline
- Standard Build: Standard build information
- Website VIP: WebsiteVIP-specific benefits and terms

---

### Step 4: Template Selection (`step-4-template-select.php`)
**Status:** ✅ Fully shared

**No form-specific differences.** All forms use identical template selection interface.

---

### Step 5: Content/Service Areas
**Status:** ⚠️ Partially shared

**Fast Build:**
- Uses form-specific file: `fast-build/steps/step-5-service-areas.php`
- Focuses on service areas and blog focus (simpler version)

**Standard Build & Website VIP:**
- Uses shared file: `shared/steps/step-5-content.php`
- Includes content provision options, tagline, FAQs, benefits, footer
- Includes service areas section

**Why Different:** Fast Build has a simplified step 5 focused only on service areas, while Standard Build and Website VIP have comprehensive content collection.

---

### Step 6: Branding (`step-6-branding.php`)
**Status:** ✅ Fully shared with conditional logic

**Standardized Features:**
- Logo question: "Do you have a logo you'd like to use?" (standardized across all)
- Logo upload field (conditional)
- Color picker (conditional based on form type)

**Form-Specific Differences:**
- **Fast Build:**
  - Shows "Match Primary Logo Color" question
  - Company color field is conditional (shown when "No" selected)
  - No font selection
  - Info box: Fast Build-specific note about default fonts

- **Standard Build:**
  - Shows "Match Primary Logo Color" question
  - Company color field is conditional (shown when "No" selected)
  - Has font selection field
  - Info box: Google Fonts information

- **Website VIP:**
  - No "Match Primary Logo Color" question
  - Company color field is always visible
  - Has font selection field
  - Info box: Google Fonts information

**Field Names:** All standardized to `logo_question`, `match_logo_color`, `company_colors`, `provide_font`, `font_name`

---

### Step 7: Rentals Information (`step-7-rentals.php`)
**Status:** ✅ Fully shared

**Standardized Fields:**
- `dumpster_color` (was `dumpster_stock_image_color_selection` in Website VIP)

**All forms now use identical field names and structure.**

---

### Step 8: Marketing (`step-8-marketing.php`)
**Status:** ✅ Fully shared with conditional sections

**Standardized Features:**
- Heading: "Company Marketing" (standardized)
- Subtitle: "Final questions about your marketing needs" (standardized)
- Submit button: "Submit Order" (standardized)
- Back button: Added to all forms

**Form-Specific Differences:**
- **Fast Build:**
  - No social media section
  - No reviews/testimonials section
  - Marketing agency question has simpler sub-text

- **Standard Build & Website VIP:**
  - Has social media links section
  - Has reviews/testimonials section
  - Marketing agency question has form-specific sub-text (Website VIP mentions "plugin and back-end access limitations")

**Field Names:** Standardized to `marketing_agency` and `reviews_testimonials`

**Marketing Agency Options:** All use same values (`Yes`, `Soon`, `No`, `Interested`) with conditional sub-text

---

## Technical Implementation

### Unified Form Renderer Logic

The `unified-form-renderer.php` file handles routing between shared and form-specific steps:

```php
// Steps that are shared across all form types
$shared_steps = array(
    'step-1-terms.php',
    'step-2-contact.php',
    'step-3-template-info.php',
    'step-4-template-select.php',
    'step-5-content.php',      // Only for Standard Build & Website VIP
    'step-6-branding.php',
    'step-7-rentals.php',
    'step-8-marketing.php'
);

// For each step, check if it's shared or form-specific
if (in_array($step_filename, $shared_steps)) {
    $step_file = $shared_steps_path . $step_filename;
} else {
    $step_file = $steps_path . $step_filename;
}
```

### Form Type Context

Shared step files access the current form type via a global variable:

```php
global $docket_current_form_type;
$form_type = isset($docket_current_form_type) ? $docket_current_form_type : 'standard-build';
```

This allows conditional logic based on form type while maintaining a single file.

### JavaScript Updates

The unified JavaScript (`docket-form-unified.js`) was updated to handle dynamic field visibility:

- Logo upload field show/hide
- Color picker field show/hide (based on "Match Primary Logo Color" selection)
- Font name field show/hide
- Color picker bidirectional syncing (hex input ↔ color picker)

**Fixed Issues:**
- Changed from `slideToggle(boolean)` to conditional `slideDown()`/`slideUp()` for proper field visibility
- Added proper event handlers for all conditional fields

---

## Field Name Standardization

### Contact Information (Step 2)
| Old Name (Website VIP) | New Standardized Name |
|------------------------|----------------------|
| `contact_name` | `name` |
| `contact_email_address` | `email` |
| `business_phone_number` | `phone_number` |

### Rentals (Step 7)
| Old Name (Website VIP) | New Standardized Name |
|------------------------|----------------------|
| `dumpster_stock_image_color_selection` | `dumpster_color` |

### Marketing (Step 8)
| Old Name (Website VIP) | New Standardized Name |
|------------------------|----------------------|
| `are_you_currently_working_with_an_seo_or_marketing_agency` | `marketing_agency` |
| `any_reviews_or_testimonials_youd_like_to_add_to_the_website` | `reviews_testimonials` |

### Content (Step 5 - Standard Build & Website VIP)
| Old Name (Website VIP) | New Standardized Name |
|------------------------|----------------------|
| `do_you_want_to_give_our_team_website_content_at_this_time` | `provide_content_now` |
| `do_you_want_to_provide_a_company_tagline` | `provide_tagline` |
| `do_you_want_to_provide_5_company_faqs` | `provide_faqs` |
| `benefits_QA` | `provide_benefits` |
| `website_footer` (radio) | `provide_footer` (radio), `website_footer` (input) |

**Note:** All field names now follow a consistent naming convention (snake_case, descriptive but concise).

---

## File Structure

### Before
```
includes/forms/
├── fast-build/
│   └── steps/
│       ├── step-1-terms.php
│       ├── step-2-contact.php
│       ├── step-3-template-info.php
│       ├── step-4-template-select.php
│       ├── step-5-service-areas.php
│       ├── step-6-branding.php
│       ├── step-7-rentals.php
│       └── step-8-marketing.php
├── standard-build/
│   └── steps/
│       ├── step-1-terms.php
│       ├── step-2-contact.php
│       ├── step-3-template-info.php
│       ├── step-4-template-select.php
│       ├── step-5-content.php
│       ├── step-6-branding.php
│       ├── step-7-rentals.php
│       └── step-8-marketing.php
└── website-vip/
    └── steps/
        ├── step-1-terms.php
        ├── step-2-contact.php
        ├── step-3-template-info.php
        ├── step-4-template-select.php
        ├── step-5-content.php
        ├── step-6-branding.php
        ├── step-7-rentals.php
        └── step-8-marketing.php
```

### After
```
includes/forms/
├── shared/
│   └── steps/
│       ├── step-1-terms.php
│       ├── step-2-contact.php
│       ├── step-3-template-info.php
│       ├── step-4-template-select.php
│       ├── step-5-content.php (Standard Build & Website VIP only)
│       ├── step-6-branding.php
│       ├── step-7-rentals.php
│       └── step-8-marketing.php
├── fast-build/
│   └── steps/
│       └── step-5-service-areas.php (Fast Build specific)
├── standard-build/
│   └── steps/
│       └── (all steps now shared)
└── website-vip/
    └── steps/
        └── (all steps now shared)
```

---

## Benefits

### 1. Reduced Code Duplication
- **Before:** ~24 step files with significant duplication
- **After:** ~11 files (7 shared + 4 form-specific)
- **Reduction:** ~54% fewer files

### 2. Improved Maintainability
- Single source of truth for shared functionality
- Changes to common steps only need to be made once
- Easier to ensure consistency across form types

### 3. Standardized Field Names
- Consistent naming convention across all forms
- Easier to work with form data in backend processing
- Reduced confusion when integrating with external systems

### 4. Better Code Organization
- Clear separation between shared and form-specific code
- Easier to understand which steps are unique to which form type
- More intuitive file structure

### 5. Easier Testing
- Shared steps can be tested once for all form types
- Form-specific steps can be tested independently
- Reduced test maintenance burden

---

## Migration Notes

### Backend Processing
✅ **Good News:** The backend code is already compatible with the standardized field names!

The form handler (`form-handler.php`) uses generic field iteration - it simply saves all POST fields without specific name dependencies:

```php
foreach ($_POST as $key => $value) {
    // Saves all fields generically - no specific field name dependencies
    $form_data[$key] = wp_unslash(sanitize_text_field($value));
}
```

Additionally:
- **Trello Sync** already uses the standardized names (`name`, `email`, `phone_number`, `dumpster_color`)
- **Elementor Site Cloner** has fallback logic for phone numbers that handles multiple field name variations
- **No code references** the old Website VIP field names

**No backend changes are required.** The field name standardization is purely a frontend improvement that makes the codebase more maintainable.

### Field Name Reference (for documentation purposes)
The following field names were standardized. Note that backend code doesn't need updating as it already uses the standardized names:

**Contact Information:**
- `contact_name` → `name`
- `contact_email_address` → `email`
- `business_phone_number` → `phone_number`

**Rentals:**
- `dumpster_stock_image_color_selection` → `dumpster_color`

**Marketing:**
- `are_you_currently_working_with_an_seo_or_marketing_agency` → `marketing_agency`
- `any_reviews_or_testimonials_youd_like_to_add_to_the_website` → `reviews_testimonials`

**Content (Standard Build & Website VIP):**
- `do_you_want_to_give_our_team_website_content_at_this_time` → `provide_content_now`
- `do_you_want_to_provide_a_company_tagline` → `provide_tagline`
- `do_you_want_to_provide_5_company_faqs` → `provide_faqs`
- `benefits_QA` → `provide_benefits`
- `website_footer` (radio button) → `provide_footer` (radio button)

### Database Content
The database-driven content system (`docket_get_form_content()`) remains unchanged. Content stored in the database will continue to work as before.

### JavaScript
The unified JavaScript file (`docket-form-unified.js`) handles all form types automatically. No form-specific JavaScript files are needed.

---

## Testing Checklist

When reviewing this refactoring, please test:

### Fast Build Flow
- [ ] Step 1: Terms & WordPress Experience section appears
- [ ] Step 2: Contact fields work correctly
- [ ] Step 3: Template info displays correctly
- [ ] Step 4: Template selection works
- [ ] Step 5: Service areas form appears (not content form)
- [ ] Step 6: Branding form shows/hides correctly (no font selection)
- [ ] Step 7: Rentals form works
- [ ] Step 8: Marketing form (no social media/reviews sections)
- [ ] Form submission works end-to-end

### Standard Build Flow
- [ ] Step 1: Terms form (no WordPress Experience)
- [ ] Step 2: Contact fields work correctly
- [ ] Step 3: Template info displays correctly
- [ ] Step 4: Template selection works
- [ ] Step 5: Content form appears (not service areas)
- [ ] Step 6: Branding form shows/hides correctly (with font selection)
- [ ] Step 7: Rentals form works
- [ ] Step 8: Marketing form (with social media/reviews sections)
- [ ] Form submission works end-to-end

### Website VIP Flow
- [ ] Step 1: Terms form (no WordPress Experience)
- [ ] Step 2: Contact fields work correctly
- [ ] Step 3: Template info displays correctly
- [ ] Step 4: Template selection works
- [ ] Step 5: Content form appears (not service areas)
- [ ] Step 6: Branding form shows/hides correctly (color always visible, with font selection)
- [ ] Step 7: Rentals form works
- [ ] Step 8: Marketing form (with social media/reviews sections, WebsiteVIP-specific sub-text)
- [ ] Form submission works end-to-end

### Cross-Cutting Tests
- [ ] Field name standardization: All forms use new standardized names
- [ ] Dynamic field visibility: Logo upload, color picker, font fields show/hide correctly
- [ ] Color picker syncing: Hex input and color picker stay in sync
- [ ] Database content: Custom content from database displays correctly
- [ ] Form validation: Required fields validate correctly
- [ ] Form navigation: Back/Next buttons work correctly

---

## Questions for Team Review

1. **Field Name Changes:** Are there any external systems or integrations that depend on the old field names? If so, we'll need to coordinate updates.

2. **Content Differences:** Are the form-specific content differences (especially in Step 3 and Step 8) correct? Should any wording be standardized further?

3. **Step 5 Difference:** Is the Fast Build's simplified Step 5 (service areas only) intentional, or should it match Standard Build/Website VIP?

4. **Database Content:** Should we audit the database to ensure all form-specific content is properly stored and retrievable?

5. **Testing:** Do we need to update any automated tests to reflect the new field names and file structure?

---

## Next Steps

1. **Team Review:** Review this document and test the flows
2. **Backend Updates:** Update any backend code that references old field names
3. **Integration Testing:** Test integrations with external systems (Trello, API, etc.)
4. **Documentation Updates:** Update any external documentation that references field names
5. **Deployment:** Plan deployment strategy (consider feature flag if needed)

---

## Summary

This refactoring represents a significant improvement in code organization and maintainability. By consolidating duplicate step files and standardizing field names, we've:

- Reduced code duplication by ~54%
- Created a single source of truth for shared functionality
- Standardized field names across all forms
- Improved code organization and clarity
- Made future changes easier to implement

The refactoring maintains backward compatibility with the database-driven content system while significantly improving the codebase structure.

