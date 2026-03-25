<?php
/**
 * Payment and shipping configuration for MinC.
 *
 * Update the account details below before deploying to production.
 */

if (!function_exists('getMincPaymentConfig')) {
    function getMincPaymentConfig() {
        return [
            'shipping' => [
                'standard_fee' => 150.00,
                'free_threshold' => 1000.00,
                'coverage_label' => 'Angeles City, Pampanga',
                'coverage_note' => 'Shipping is currently available only within Angeles City, Pampanga. Delivery-app handling such as Grab or Lalamove is managed manually when needed.'
            ],
            'bank_transfer' => [
                'enabled' => true,
                'bank_name' => 'Update Bank Name',
                'account_name' => 'Update Account Name',
                'account_number' => 'Update Account Number',
                'branch' => 'Update Branch',
                'reference_label' => 'Bank Reference / Transaction ID'
            ],
            'gcash' => [
                'enabled' => true,
                'account_name' => 'Update GCash Account Name',
                'account_number' => 'Update GCash Number',
                'reference_label' => 'GCash Reference Number'
            ],
            'paymaya' => [
                'enabled' => true,
                'account_name' => 'Update Maya Account Name',
                'account_number' => 'Update Maya Number',
                'reference_label' => 'Maya Reference Number'
            ]
        ];
    }
}
