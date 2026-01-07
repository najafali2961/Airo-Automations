<?php

namespace App\Flow\Actions\Klaviyo;

use Illuminate\Support\Facades\Log;

trait HasShopifyCustomerFallback
{
    /**
     * Resolve email from payload, or fetch from Shopify if missing.
     */
    protected function resolveEmail($user, array $payload, string $emailSetting, $variableService)
    {
        // 1. Try standard replacement
        $email = $variableService->replace($emailSetting, $payload);

        // Check if email is valid (basic check and check if it still has braces)
        // If it looks like an email and doesn't have {{ }}, return it.
        if (!empty($email) && !str_contains($email, '{{') && filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $email;
        }

        Log::info("Email not found in payload via replacement (Value: '$email'). Attempting to fetch from Shopify...");

        // 2. Try to find customer ID
        $customerId = null;

        // A. Direct payload check (if payload is customer)
        if (isset($payload['id']) && (isset($payload['default_address']) || isset($payload['verified_email']) || isset($payload['first_name']))) {
            $customerId = $payload['id'];
        }
        
        // B. Check 'customer' key in payload
        if (!$customerId && isset($payload['customer']['id'])) {
            $customerId = $payload['customer']['id'];
        }

        // C. Try to resolve {{ customer.id }} via VariableService (smart aliasing)
        if (!$customerId) {
            $resolvedId = $variableService->replace('{{ customer.id }}', $payload);
             if (!empty($resolvedId) && !str_contains($resolvedId, '{{') && is_numeric($resolvedId)) {
                 $customerId = $resolvedId;
             }
        }
        
        if (!$customerId) {
            Log::warning("Could not find Customer ID to fetch email.");
            return null;
        }

        try {
            // Fetch from Shopify
            // Reverting to full path as relative path caused errors. 
            // Adding explicit fields to ensure we get email.
            $response = $user->api()->rest('GET', "/admin/api/2024-04/customers/{$customerId}.json", ['fields' => 'id,email,first_name,last_name,phone']);
            
            if (!$response['errors']) {
                 $body = $response['body'];
                 
                 // Log the structure to be sure
                 // Log::info("Shopify Customer Response: " . json_encode($body));
                 
                 $fetchedEmail = $body['customer']['email'] ?? null;
                 
                 if ($fetchedEmail) {
                     Log::info("Successfully fetched email from Shopify: $fetchedEmail");
                     return $fetchedEmail;
                 }
                 
                 Log::warning("Fetched customer but email was null/missing from Shopify response (Customer ID: $customerId).");
            } else {
                Log::error("Shopify API Error fetching customer: " . json_encode($response['errors']));
            }
            
        } catch (\Exception $e) {
            Log::error("Exception fetching customer from Shopify: " . $e->getMessage());
        }

        return null;
    }
}
