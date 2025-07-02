# Template Site Content Examples

This file shows examples of how to use placeholders in your template site content.

## Example Home Page Content

```html
<h1>{{CITY}}'s Best Junk Removal</h1>

<p>{{BUSINESS_NAME}} is a junk removal service for all of {{CITY}}, {{STATE}} and surrounding areas. We're dedicated to helping you with your residential junk and commercial junk removal needs.</p>

<p>We can answer all of your questions about what you can or can't throw into your container for your home or business. Our flexible online booking ensures you have enough time for even the toughest projects. We provide affordable junk removal rates for construction, concrete, shingles, roofing, dirt, brush, yard waste, junk removal, and more.</p>

<h2>Contact {{BUSINESS_NAME}}</h2>
<ul>
<li><strong>Phone:</strong> {{PHONE}}</li>
<li><strong>Email:</strong> {{EMAIL}}</li>
<li><strong>Address:</strong> {{ADDRESS}}</li>
</ul>

<h2>Service Areas</h2>
<p>We proudly serve: {{SERVICE_AREAS}}</p>

<h2>About Our {{FORM_TYPE}} Service</h2>
<p>Our team at {{BUSINESS_NAME}} is committed to providing excellent service in {{CITY}} and surrounding areas.</p>
```

## Example About Page Content

```html
<h1>About {{BUSINESS_NAME}}</h1>

<p>Welcome to {{BUSINESS_NAME}}, {{CITY}}'s premier junk removal company. Located at {{ADDRESS}}, we've been serving the {{STATE}} area with reliable, professional junk removal services.</p>

<h2>Why Choose {{BUSINESS_NAME}}?</h2>
<ul>
<li>Local {{CITY}} business with deep community roots</li>
<li>Professional and insured team</li>
<li>Serving {{SERVICE_AREAS}} and surrounding areas</li>
<li>Competitive pricing and transparent quotes</li>
</ul>

<h2>Get In Touch</h2>
<p>Ready to get started? Contact {{CONTACT_NAME}} at {{BUSINESS_NAME}}:</p>
<ul>
<li>Call us: {{PHONE}}</li>
<li>Email: {{EMAIL}}</li>
<li>Business Email: {{BUSINESS_EMAIL}}</li>
</ul>
```

## Example Contact Page Content

```html
<h1>Contact {{BUSINESS_NAME}}</h1>

<h2>Get Your Free Quote Today</h2>
<p>{{BUSINESS_NAME}} is ready to help with all your junk removal needs in {{CITY}}, {{STATE}}.</p>

<div class="contact-info">
    <h3>Contact Information</h3>
    <p><strong>Business Name:</strong> {{BUSINESS_NAME}}</p>
    <p><strong>Contact Person:</strong> {{CONTACT_NAME}}</p>
    <p><strong>Phone:</strong> {{PHONE}}</p>
    <p><strong>Email:</strong> {{EMAIL}}</p>
    <p><strong>Business Email:</strong> {{BUSINESS_EMAIL}}</p>
    <p><strong>Address:</strong> {{ADDRESS}}</p>
</div>

<div class="service-areas">
    <h3>We Serve These Areas</h3>
    <p>{{SERVICE_AREAS}}</p>
</div>
```

## Example Services Page Content

```html
<h1>{{BUSINESS_NAME}} Services in {{CITY}}, {{STATE}}</h1>

<p>At {{BUSINESS_NAME}}, we provide comprehensive junk removal services throughout {{CITY}} and the surrounding {{STATE}} area. Our {{FORM_TYPE}} approach ensures you get exactly what you need.</p>

<h2>Our Junk Removal Services</h2>

<h3>Residential Junk Removal</h3>
<p>{{BUSINESS_NAME}} helps {{CITY}} homeowners clear out unwanted items from:</p>
<ul>
<li>Basements and attics</li>
<li>Garages and sheds</li>
<li>Yard waste and debris</li>
<li>Old furniture and appliances</li>
</ul>

<h3>Commercial Junk Removal</h3>
<p>We assist {{CITY}} businesses with:</p>
<ul>
<li>Office cleanouts</li>
<li>Construction debris</li>
<li>Retail space clearing</li>
<li>Property management support</li>
</ul>

<h2>Why {{CITY}} Chooses {{BUSINESS_NAME}}</h2>
<ul>
<li>Licensed and insured in {{STATE}}</li>
<li>Serving {{SERVICE_AREAS}}</li>
<li>Eco-friendly disposal methods</li>
<li>Same-day service available</li>
</ul>

<h2>Contact Us Today</h2>
<p>Ready to schedule your junk removal in {{CITY}}? Call {{BUSINESS_NAME}} at {{PHONE}} or email us at {{EMAIL}}.</p>
```

## Example Site Title and Tagline

**Site Title:** `{{BUSINESS_NAME}} - {{CITY}} Junk Removal`

**Tagline:** `Professional junk removal services in {{CITY}}, {{STATE}} - Call {{PHONE}}`

## Example Widget Content

### Text Widget Example
```html
<h4>Quick Contact</h4>
<p><strong>{{BUSINESS_NAME}}</strong><br>
{{ADDRESS}}<br>
Phone: {{PHONE}}<br>
Email: {{EMAIL}}</p>
```

### Custom HTML Widget Example
```html
<div class="contact-widget">
    <h3>Serving {{CITY}}</h3>
    <p>{{BUSINESS_NAME}} proudly serves {{SERVICE_AREAS}} with professional junk removal services.</p>
    <a href="tel:{{PHONE}}" class="call-button">Call {{PHONE}}</a>
</div>
```

## Example Footer Content

```html
<div class="footer-info">
    <h4>{{BUSINESS_NAME}}</h4>
    <p>Professional junk removal serving {{CITY}}, {{STATE}}</p>
    <p>{{ADDRESS}}</p>
    <p>Phone: {{PHONE}} | Email: {{EMAIL}}</p>
    <p>Service Areas: {{SERVICE_AREAS}}</p>
</div>
```

## Navigation Menu Examples

### Main Menu
- Home
- About {{BUSINESS_NAME}}
- Services in {{CITY}}
- Contact {{BUSINESS_NAME}}
- Service Areas

### Footer Menu
- {{BUSINESS_NAME}} Services
- About Us
- Contact
- {{CITY}} Service Area

## Important Notes

1. **Exact Format**: Placeholders must use the exact format `{{PLACEHOLDER}}` with double curly braces
2. **Case Sensitive**: Use uppercase for placeholder names
3. **No Spaces**: Don't add spaces inside the braces: `{{ BUSINESS_NAME }}` won't work
4. **Test First**: Always test with a simple template before using complex content

## Form Type Specific Content

You can create different content based on the form type:

```html
<!-- This will show "Fast Build", "Standard Build", or "Website Vip" -->
<h2>{{FORM_TYPE}} Package</h2>

<!-- You can use conditional content in your theme/plugins -->
<p>You've selected our {{FORM_TYPE}} service package for {{BUSINESS_NAME}}.</p>
```

## Advanced Placeholder Usage

### In Meta Descriptions
```html
<meta name="description" content="{{BUSINESS_NAME}} provides professional junk removal services in {{CITY}}, {{STATE}}. Call {{PHONE}} for fast, reliable service in {{SERVICE_AREAS}}.">
```

### In Schema Markup
```json
{
  "@type": "LocalBusiness",
  "name": "{{BUSINESS_NAME}}",
  "address": {
    "@type": "PostalAddress",
    "streetAddress": "{{ADDRESS}}",
    "addressLocality": "{{CITY}}",
    "addressRegion": "{{STATE}}"
  },
  "telephone": "{{PHONE}}",
  "email": "{{EMAIL}}"
}
```

### In Custom Fields
If your template uses custom fields (ACF, etc.), you can use placeholders there too:
- **Business Hours Field**: "{{BUSINESS_NAME}} is open Monday-Friday 8AM-6PM"
- **Service Description**: "Professional {{FORM_TYPE}} service in {{CITY}}"
- **Contact Info**: "Call {{BUSINESS_NAME}} at {{PHONE}}" 