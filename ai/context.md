# Docket Onboarding - Project Context

## What This Project Is
WordPress plugin that powers multi-step onboarding forms for Docket's website development services. Customers select a plan, fill out forms, and a new WordPress site gets automatically created from a template.

## The Architecture

### Two-Server Setup
- **Forms Site**: yourdocketonline.com (where this plugin lives)
  - Hosts the onboarding forms
  - Stores form submissions and client data
  - Integrates with Trello and ChurnZero
- **Cloning Site**: dockethosting5.com (WordPress Multisite)
  - Houses template sites for each plan
  - Handles site cloning via API
  - Hosts all client websites

### Local Development
- Docker environment at http://localhost:8080
- Mirrors production WordPress setup
- Uses composer for testing (`composer test`)

## Three Website Plans

### Fast Build
- **Timeline**: 3 days
- **Features**: Simplified options, core functionality
- **Unique Step**: Service areas selection (step 5)
- **Target**: Clients who need quick turnaround

### Standard Build
- **Timeline**: Standard development cycle
- **Features**: Full option set, all integrations
- **Unique Step**: None (all shared steps)
- **Target**: Standard website projects

### Website VIP
- **Timeline**: 21-30 days
- **Features**: Premium features, extensive customization
- **Unique Step**: None (all shared steps)
- **Target**: High-value, complex projects

## Form Architecture

### Config-Driven Design
- `includes/forms/form-config.php` is the **source of truth**
- Defines steps, fields, validation, and display logic for each form
- All three forms share 90% of their steps

### Shared Steps Pattern
- Common steps live in `includes/forms/shared/steps/`
- Controlled by form-config.php, not duplicated code
- Only Fast Build step 5 is truly unique: `fast-build/steps/step-5-service-areas.php`

### Unified JavaScript
- Single file: `assets/docket-form-unified.js`
- Handles all three forms
- Form-specific behavior driven by data attributes and config

## Key Integration Points

### Trello
- Creates project card on form submission
- Tracks development progress
- Managed by `includes/trello-sync.php`

### ChurnZero
- Customer success tracking
- Email tracking integration (in progress)
- Event tracking for onboarding milestones

### Site Cloner API
- Endpoint: dockethosting5.com API
- Clones template sites based on plan selection
- Returns new site URL and credentials
- API settings in `includes/cloner-settings.php`

## Data Storage

### Database Tables
- `wp_docket_form_submissions` - All form submission data
- `wp_docket_client_sites` - Created site records
- `wp_docket_form_content` - CMS-editable form content

### Form Submission Flow
1. User completes multi-step form
2. `includes/form-handler.php` processes submission
3. API call to dockethosting5.com triggers site cloning
4. Trello card created for project tracking
5. ChurnZero event logged
6. User redirected to their new site dashboard

## Technical Stack
- **Backend**: WordPress plugin architecture, PHP 7.4+
- **Frontend**: Vanilla JavaScript (unified approach)
- **CSS**: Inline styles + theme integration
- **Testing**: Composer + PHPUnit
- **Version Control**: GitHub
- **Development Tool**: Cursor AI for code generation

## Team
- **Tim**: Project lead, handles technical implementation and testing
- **Kayla**: Team member, collaborates on features and testing

## Current Focus Areas
- WordPress cache issues during multisite context switching
- Client dashboard improvements for Trello integration
- ChurnZero email tracking integration
- Legacy code cleanup (docket-automated-site-creator removal)
