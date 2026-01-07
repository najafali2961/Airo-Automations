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
            // Fetch from Shopify using GraphQL (more reliable for fields)
            $gid = "gid://shopify/Customer/{$customerId}";
            $query = <<<gql
            query {
                customer(id: "$gid") {
                    email
                }
            }
gql;

            // Log attempt
            // Log::info("Fetching customer email via GraphQL: $gid");

            $response = $user->api()->graph($query);
            
            if (!$response['errors']) {
                 $body = $response['body']; // Graph response body
                 
                 // GraphQL structure: data -> customer -> email
                 $data = $body['data']['customer'] ?? null; // accessing array from decoded body

                 // Note: Osiset graph() return body might be object. Convert to array if needed.
                 // Usually it returns an array response.
                 
                 // Using json_decode/encode trick if it's an object, or direct access.
                 // Let's assume array access is safe or cast.
                 $fetchedEmail = null;
                 if (is_array($data)) {
                     $fetchedEmail = $data['email'] ?? null;
                 } elseif (is_object($data)) {
                     $fetchedEmail = $data->email ?? null;
                 } else {
                     // Try accessing via array on body if it was already array
                     // If body is object:
                     if (is_object($body) && isset($body->data->customer->email)) {
                         $fetchedEmail = $body->data->customer->email;
                     } elseif (is_array($body) && isset($body['data']['customer']['email'])) {
                         $fetchedEmail = $body['data']['customer']['email'];
                     }
                 }
                 
                 if ($fetchedEmail) {
                     Log::info("Successfully fetched email from Shopify (GraphQL): $fetchedEmail");
                     return $fetchedEmail;
                 }
                 
                 Log::warning("Fetched customer via GraphQL but email was null/missing. GID: $gid");
            } else {
                Log::error("Shopify GraphQL Error fetching customer: " . json_encode($response['errors']));
            }

        } catch (\Exception $e) {
            Log::error("Exception fetching customer from Shopify: " . $e->getMessage());
        }

        return null;
    }
}
