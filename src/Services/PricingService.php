<?php
namespace FQC\Services;

/**
 * PricingService
 *
 * Contains the pricing logic for each service.
 * Isolated into a service class so it can be unit-tested and extended easily.
 */
class PricingService {

    /**
     * Calculate quote based on selected service and sanitized form data.
     *
     * @param string $service
     * @param array  $data
     * @return float
     */
    public function calculate(string $service, array $data): float {
        switch ($service) {
            case 'web':
                return $this->web($data);

            case 'design':
                return $this->design($data);

            case 'writing':
                return $this->writing($data);

            default:
                return 0;
        }
    }

    /**
     * Web Development pricing:
     * - R500 per page
     * - +R2000 if ecommerce is "yes"
     * - +R500 per month of timeline (1/2/3)
     */
    protected function web(array $data): float {
        $pages    = (int) ($data['pages'] ?? 0);
        $timeline = (int) ($data['timeline'] ?? 0);
        $ecomm    = $data['ecommerce'] ?? '';

        if ($pages <= 0 || $timeline <= 0 || !in_array($ecomm, ['yes', 'no'], true)) {
            return 0; // Invalid/missing required fields.
        }

        $price = $pages * 500;

        if ($ecomm === 'yes') {
            $price += 2000;
        }

        $price += $timeline * 500;

        return (float) $price;
    }

    /**
     * Graphic Design pricing:
     * - Base price depends on design_type
     * - +R300 per revision
     */
    protected function design(array $data): float {
        $base_prices = [
            'logo'     => 1500,
            'branding' => 3000,
            'print'    => 1000,
        ];

        $type      = $data['design_type'] ?? '';
        $revisions = (int) ($data['revisions'] ?? 0);

        if (!isset($base_prices[$type]) || $revisions < 0) {
            return 0;
        }

        return (float) ($base_prices[$type] + ($revisions * 300));
    }

    /**
     * Content Writing pricing:
     * - R150 per 100 words
     * - +R500 if SEO is "yes"
     */
    protected function writing(array $data): float {
        $words = (int) ($data['word_count'] ?? 0);
        $seo   = $data['seo'] ?? '';

        if ($words < 100 || !in_array($seo, ['yes', 'no'], true)) {
            return 0;
        }

        $price = ($words / 100) * 150;

        if ($seo === 'yes') {
            $price += 500;
        }

        return (float) $price;
    }
}
