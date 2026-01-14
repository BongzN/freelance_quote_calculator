<?php
/**
 * Plugin Name: Freelance Quote Calculator
 * Description: A quote calculator with conditional logic and JSONPlaceholder API integration. Test Project for Vetro Media.
 * Version: 1.0
 * Author: Bongani Nombamba
 *
 * Notes:
 * - Outputs a form via shortcode [quote_calculator]
 * - Uses AJAX (admin-ajax.php) + nonce for secure submission
 * - Fetches Account Managers from JSONPlaceholder /users (front-end)
 * - Posts quote data to JSONPlaceholder /posts (server-side) and shows a success message
 */

if (!defined('ABSPATH')) {
    exit; // Prevent direct file access.
}

// Load service class (business logic isolated for testability/maintainability).
require_once plugin_dir_path(__FILE__) . 'src/Services/PricingService.php';

use FQC\Services\PricingService;

/**
 * Enqueue front-end assets (CSS + JS).
 * Tailwind CDN is included for basic typography defaults, but plugin CSS enforces:
 * - 20px spacing between fields
 * - 0px border radius on inputs/buttons/cards
 */
function fqc_enqueue_assets() {
    // Tailwind CDN (assessment-friendly: no build step required).
    wp_enqueue_script(
        'fqc-tailwind',
        'https://cdn.tailwindcss.com',
        [],
        null,
        false
    );

    // Plugin CSS overrides radius/spacing consistently regardless of theme.
    wp_enqueue_style(
        'fqc-style',
        plugin_dir_url(__FILE__) . 'assets/css/quote-calculator.css',
        [],
        '1.2'
    );

    // Front-end behavior: show/hide conditional fields, fetch users, submit quote.
    wp_enqueue_script(
        'fqc-js',
        plugin_dir_url(__FILE__) . 'assets/js/quote-calculator.js',
        ['jquery'],
        '1.2',
        true
    );

    // Expose AJAX URL and nonce to JS without hard-coding.
    wp_localize_script('fqc-js', 'fqcData', [
        'ajaxUrl' => admin_url('admin-ajax.php'),
        'nonce'   => wp_create_nonce('fqc_nonce'),
    ]);
}
add_action('wp_enqueue_scripts', 'fqc_enqueue_assets');

/**
 * Shortcode renderer: [quote_calculator]
 */
function fqc_shortcode() {
    ob_start();
    ?>
    <form id="fqc-form" class="fqc-form">
        <?php
        // Adds a hidden nonce field in the form for CSRF protection.
        wp_nonce_field('fqc_nonce', 'fqc_nonce_field');
        ?>

        <div class="fqc-field">
            <label for="service" class="fqc-label">Service Type <span class="fqc-required">*</span></label>
            <select id="service" name="service" class="fqc-input" required>
                <option value="">Select Service</option>
                <option value="web">Web Development</option>
                <option value="design">Graphic Design</option>
                <option value="writing">Content Writing</option>
            </select>
        </div>

        <div class="fqc-field">
            <label for="account-manager" class="fqc-label">Account Manager <span class="fqc-required">*</span></label>
            <select id="account-manager" name="account_manager" class="fqc-input" required>
                <option value="">Loading...</option>
            </select>
        </div>

        <!-- Web Development Fields -->
        <div class="conditional web">
            <div class="fqc-section-title">Web Development Details</div>

            <div class="fqc-field">
                <label class="fqc-label">Number of Pages <span class="fqc-required">*</span></label>
                <input type="number" name="pages" min="1" class="fqc-input" />
            </div>

            <div class="fqc-field">
                <label class="fqc-label">Timeline <span class="fqc-required">*</span></label>
                <select name="timeline" class="fqc-input">
                    <option value="1">1 month</option>
                    <option value="2">2 months</option>
                    <option value="3">3+ months</option>
                </select>
            </div>

            <div class="fqc-field">
                <label class="fqc-label">E-commerce Needed? <span class="fqc-required">*</span></label>
                <div class="fqc-inline">
                    <label class="fqc-radio"><input type="radio" name="ecommerce" value="yes"> Yes</label>
                    <label class="fqc-radio"><input type="radio" name="ecommerce" value="no"> No</label>
                </div>
            </div>
        </div>

        <!-- Graphic Design Fields -->
        <div class="conditional design">
            <div class="fqc-section-title">Graphic Design Details</div>

            <div class="fqc-field">
                <label class="fqc-label">Project Type <span class="fqc-required">*</span></label>
                <select name="design_type" class="fqc-input">
                    <option value="logo">Logo</option>
                    <option value="branding">Branding</option>
                    <option value="print">Print</option>
                </select>
            </div>

            <div class="fqc-field">
                <label class="fqc-label">Number of Revisions</label>
                <input type="number" name="revisions" min="0" class="fqc-input" />
            </div>
        </div>

        <!-- Content Writing Fields -->
        <div class="conditional writing">
            <div class="fqc-section-title">Content Writing Details</div>

            <div class="fqc-field">
                <label class="fqc-label">Word Count <span class="fqc-required">*</span></label>
                <input type="number" name="word_count" min="100" class="fqc-input" />
            </div>

            <div class="fqc-field">
                <label class="fqc-label">SEO Optimization Needed? <span class="fqc-required">*</span></label>
                <div class="fqc-inline">
                    <label class="fqc-radio"><input type="radio" name="seo" value="yes"> Yes</label>
                    <label class="fqc-radio"><input type="radio" name="seo" value="no"> No</label>
                </div>
            </div>
        </div>

        <button type="submit" id="fqc-submit" class="fqc-button">Calculate Quote</button>

        <!-- Results appear here as a bordered card  -->
        <div id="quote-result" class="fqc-result"></div>

        <!-- Reset button appears at all times; user can start a new quote easily -->
        <button type="button" id="fqc-reset" class="fqc-button fqc-button-secondary">Reset / New Quote</button>
    </form>
    <?php
    return ob_get_clean();
}
add_shortcode('quote_calculator', 'fqc_shortcode');

/**
 * AJAX: Calculate quote + POST to JSONPlaceholder /posts
 * Security:
 * - Uses check_ajax_referer to validate nonce.
 * - Sanitizes all incoming data.
 */
function fqc_submit() {
    check_ajax_referer('fqc_nonce', 'nonce');

    $service = isset($_POST['service']) ? sanitize_text_field($_POST['service']) : '';
    $data    = array_map('sanitize_text_field', $_POST);

    // Basic server-side validation (prevents invalid/empty submissions).
    if (empty($service)) {
        wp_send_json_error(['message' => 'Please select a service type.']);
    }

    // Calculate quote using service class (keeps controller slim).
    $pricing = new PricingService();
    $quote   = $pricing->calculate($service, $data);

    if ($quote <= 0) {
        wp_send_json_error(['message' => 'Please fill in the required fields for this service.']);
    }

    // POST payload to JSONPlaceholder /posts (mock success expected).
    $api_response = wp_remote_post(
        'https://jsonplaceholder.typicode.com/posts',
        [
            'headers' => ['Content-Type' => 'application/json'],
            'timeout' => 10,
            'body'    => wp_json_encode([
                'service'          => $service,
                'account_manager'  => $data['account_manager'] ?? '',
                'quote'            => $quote,
                'submitted_at'     => gmdate('c'),
            ]),
        ]
    );

    // Handle transport errors (DNS, SSL, connection issues).
    if (is_wp_error($api_response)) {
        wp_send_json_error(['message' => 'API request failed. Please try again.']);
    }

    $status_code = wp_remote_retrieve_response_code($api_response);
    if ($status_code < 200 || $status_code >= 300) {
        wp_send_json_error(['message' => 'API returned an unexpected response.']);
    }

    // I intentionally do NOT return API Response ID to the UI per requirement.
    wp_send_json_success([
        'quote'   => $quote,
        'message' => 'Quote submitted successfully.',
    ]);
}
add_action('wp_ajax_fqc_submit', 'fqc_submit');
add_action('wp_ajax_nopriv_fqc_submit', 'fqc_submit');
