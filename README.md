Freelance Quote Calculator (

Author: Bongani Nombamba
Version: 1.0
Type: WordPress Plugin
Project: Technical Assessment – Vetro Media

------------------------------------------------------------------------

Purpose

This plugin demonstrates:

-   Conditional front-end logic without page reloads
-   Secure WordPress AJAX handling
-   External REST API integration
-   Service-based PHP architecture
-   Clean separation of business logic and presentation

It is designed as a maintainable, testable codebase rather than a
one-off script.

------------------------------------------------------------------------

Installation

1.  Upload the plugin ZIP to /wp-content/plugins/ or via WordPress Admin
    → Plugins → Add New → Upload.
2.  Activate Freelance Quote Calculator.
3.  Insert the shortcode into any page:

[quote_calculator]

------------------------------------------------------------------------

High-Level Architecture

The plugin is structured around three responsibilities:

1.  UI Rendering (shortcode)
2.  Business Logic (service class)
3.  Integration Layer (AJAX + external API)

Directory layout:

freelance-quote-calculator/ ├── freelance-quote-calculator.php
(Bootstrap, shortcode, AJAX controller) ├── src/ │ └── Services/ │ └──
PricingService.php (Business logic) └── assets/ ├──
css/quote-calculator.css └── js/quote-calculator.js

------------------------------------------------------------------------

Data Flow

1.  User selects a service (Web, Design, Writing).
2.  JavaScript shows relevant conditional fields.
3.  Account managers are fetched via:
    https://jsonplaceholder.typicode.com/users
4.  On submit:
    -   Data is sent to admin-ajax.php
    -   Nonce is verified server-side
    -   Inputs are sanitized
    -   PricingService calculates the quote
5.  Quote data is POSTed to: https://jsonplaceholder.typicode.com/posts
6.  Server returns a success response (quote only).
7.  Front-end renders result in a bordered summary card.

------------------------------------------------------------------------

Pricing Engine (Service Layer)

All pricing logic lives in:

src/Services/PricingService.php

This class is responsible for: - Validating required fields per
service - Applying pricing rules - Returning a numeric quote

Example interface:

calculate(string $service, array $data): float

This ensures: - Separation of concerns - Easy unit testing - Future
extensibility (e.g., new services, pricing rules)

------------------------------------------------------------------------

Security Considerations

-   Nonce verification using check_ajax_referer
-   All inputs sanitized via sanitize_text_field
-   Server-side validation of required fields
-   No reliance on client-side values for pricing
-   External API errors handled via wp_remote_post checks

------------------------------------------------------------------------

API Integration

GET

Fetch account managers: https://jsonplaceholder.typicode.com/users

POST

Submit quote payload: https://jsonplaceholder.typicode.com/posts

Payload structure:

{ “service”: “web”, “account_manager”: “Jane Doe”, “quote”: 4500,
“submitted_at”: “ISO8601 timestamp” }

Notes: - API is used as a mock integration target. - Response IDs are
intentionally not exposed in the UI.

------------------------------------------------------------------------

UI Rules 

-   20px vertical spacing between fields
-   0px border radius across inputs, buttons, and cards
-   Results displayed in a bordered card with bold black text
-   Reset / New Quote button clears state

CSS is scoped to the plugin to avoid theme interference.

------------------------------------------------------------------------

Validation Logic

Each service enforces required fields:

Web Development: - pages (int > 0) - timeline (1–3) - ecommerce (yes/no)

Graphic Design: - design_type must exist in allowed set - revisions >= 0

Content Writing: - word_count >= 100 - seo (yes/no)

Invalid or missing fields return server-side errors.

------------------------------------------------------------------------

Extensibility

To add a new service:

1.  Add new conditional fields in the shortcode form.
2.  Extend PricingService with a new method.
3.  Add case in calculate() switch.
4.  Adjust front-end JS to toggle new section.


------------------------------------------------------------------------

Testing Readiness

Pricing logic is isolated in a single service class, making it suitable
for:

-   PHPUnit / WP_UnitTestCase
-   Mocked input testing
-   CI integration

------------------------------------------------------------------------

Assumptions

-   JSONPlaceholder is a stand-in for a real API.
-   Pricing rules are demonstrative.
-   Focus is on architecture, security, and maintainability rather than
    design polish.

------------------------------------------------------------------------

Time Spent

7 hours: - Architecture design - AJAX & API
integration - Service-layer refactor - Validation and security - UI
rules enforcement - Documentation

------------------------------------------------------------------------

Author
Bongani Nombamba

