Freelance Quote Calculator (

Author: Bongani Nombamba
Version: 1.0
Type: WordPress Plugin
Project: Technical Assessment – Vetro Media

------------------------------------------------------------------------

Installation Steps

1. Download the plugin ZIP file.

2. In WordPress Admin, go to Plugins → Add New → Upload Plugin.

3. Upload the ZIP and click Install Now.

4. Click Activate.

5. Create or edit a page and add the shortcode: [quote_calculator]

6. Publish the page and open it on the front-end to use the calculator.


------------------------------------------------------------------------

How the Pricing Logic Works

Pricing is calculated server-side (PHP) in a dedicated service class (PricingService) based on the selected service type and provided inputs.

Web Development

R500 per page

+ R2000 if E-commerce is required

+ R500 per timeline month (1 / 2 / 3+ months)

Example: 5 pages + e-commerce + 2 months
= (5 × 500) + 2000 + (2 × 500) = R5500

Graphic Design

Base pricing by project type:

Logo: R1500

Branding: R3000

Print: R1000

+ R300 per revision

Example: Branding + 3 revisions
= 3000 + (3 × 300) = R3900

Content Writing

R150 per 100 words

+ R500 if SEO optimization is required

Example: 1000 words + SEO
= (1000/100 × 150) + 500 = 1500 + 500 = R2000

------------------------------------------------------------------------

Assumptions Made

Pricing values are demonstration only and intended to show conditional logic.

JSONPlaceholder is used as a mock API, so responses are simulated.

Quote data is submitted to the API on successful calculation, but the UI does not display an API response ID (kept clean for UX).

Styling is intentionally minimal and consistent to work with most themes.
------------------------------------------------------------------------

Time Spent

75–6 hours, including:

Plugin setup and shortcode form

Conditional UI logic (JS)

Server-side pricing logic (service class)

AJAX + nonce security + sanitisation

API integration (GET users + POST quote)

UI polish and documentation



