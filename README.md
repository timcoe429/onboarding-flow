# Docket Onboarding Plugin

A WordPress plugin that provides a multi-step onboarding flow for Docket website plan selection and order forms.

## 🚀 Quick Start

**Want to test locally in your browser?**  
👉 See **[docs/QUICK-START.md](docs/QUICK-START.md)** for quick setup or **[docs/DOCKER-SETUP.md](docs/DOCKER-SETUP.md)** for detailed guide!

**Want to run automated tests?**  
👉 See **[docs/QUICK-START.md](docs/QUICK-START.md)** to get started, or **[docs/README-TESTING.md](docs/README-TESTING.md)** for detailed guide

## Description

This plugin creates a smooth onboarding experience for customers to:
- Select between Grow and Pro website plans
- Complete a pre-build checklist
- Choose management options (Self-managed or WebsiteVIP)
- Select build type (Fast Build 3-day or Standard Build 21-30 day)
- Fill out comprehensive order forms

## Features

- Multi-step form wizard with progress tracking
- Three form flows (Fast Build, Standard Build, Website VIP)
- Mobile responsive design
- Form data persistence between steps
- Email notifications on form submission
- Clean, modern UI with custom styling

## Installation

### For Production (WordPress Site)

1. Upload the `docket-onboarding` folder to `/wp-content/plugins/`
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Add the shortcode `[docket_onboarding]` to any page

### For Local Development

**Option 1: Docker (Recommended - Self-contained)**
- See **[docs/DOCKER-SETUP.md](docs/DOCKER-SETUP.md)** for step-by-step guide
- Just run: `docker-compose up -d`
- Open: http://localhost:8080

**Option 2: Automated Tests (No browser needed)**
- See **[docs/QUICK-START.md](docs/QUICK-START.md)** for quick start or **[docs/README-TESTING.md](docs/README-TESTING.md)** for details
- Run: `composer test`

## Documentation

All documentation is in the `docs/` folder:

- **[QUICK-START.md](docs/QUICK-START.md)** - Get up and running quickly (Docker setup & testing)
- **[DOCKER-SETUP.md](docs/DOCKER-SETUP.md)** - Detailed Docker setup guide for local WordPress testing
- **[README-TESTING.md](docs/README-TESTING.md)** - Complete guide for running automated tests locally
- **[INTEGRATION-GUIDE.md](docs/INTEGRATION-GUIDE.md)** - How to integrate with Elementor Site Cloner (cross-site setup)
- **[QUICK-REFERENCE.md](docs/QUICK-REFERENCE.md)** - Quick reference for common tasks and file locations
- **[PROJECT-STRUCTURE.md](docs/PROJECT-STRUCTURE.md)** - Overview of project structure and key files
- **[DEVELOPMENT-NOTES.md](docs/DEVELOPMENT-NOTES.md)** - Code patterns, conventions, and development guidelines
- **[FORM-ARCHITECTURE.md](docs/FORM-ARCHITECTURE.md)** - Unified form architecture and how forms work

## File Structure