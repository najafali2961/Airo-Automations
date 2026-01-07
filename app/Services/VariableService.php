<?php

namespace App\Services;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;

class VariableService
{
    /**
     * Globally replace variables in a string with values from the payload.
     * Implements "Smart Aliasing" to ensure {{ order.id }} works even if payload only has 'id'.
     *
     * @param string $template
     * @param array $payload
     * @return string
     */
    public function replace(string $template, array $payload): string
    {
        if (empty($template)) {
            return '';
        }

        // 1. Flatten the payload
        // This turns ['order' => ['id' => 1]] into ['order.id' => 1]
        $flattened = Arr::dot($payload);
        
        // 2. Create Smart Aliases
        // If the payload is from a webhook that isn't nested (e.g. just { id: 101, email: ... })
        // we map it to common prefixes so users can use standard variable names.
        $aliases = [];
        foreach ($flattened as $key => $value) {
            // Map 'id' -> 'order.id', 'product.id', 'customer.id', 'shop.id'
            // This is "optimistic" and "global" as requested.
            // It means {{ product.id }} will output the ID of whatever triggered this, 
            // effectively making it a polymorphic ID.
            // Map common payload keys to resource prefixes
            // This allows {{ product.id }} to work even if the payload is just { id: 123... }
            $resources = [
                'order', 'product', 'customer', 'shop', 'cart', 'checkout', 
                'fulfillment', 'refund', 'draft_order', 'collection', 
                'transaction', 'inventory_level', 'inventory_item', 
                'location', 'theme'
            ];

            foreach ($resources as $resource) {
                $aliases["{$resource}.$key"] = $value;
            }
        }
        
        // --- HELPER LOGIC START ---
        
        // 1. Generate line_items_summary
        // Look for line_items in the payload (it might be flattened as line_items.0.title etc, but usually top level array in raw payload)
        // Since we have $payload available:
        $lineItems = $payload['line_items'] ?? $payload['order']['line_items'] ?? $payload['checkout']['line_items'] ?? null;
        
        if (is_array($lineItems) && count($lineItems) > 0) {
            $summaryLines = [];
            foreach ($lineItems as $item) {
                // Handle different payload shapes (sometimes nested)
                if (!is_array($item)) continue;
                
                $qty = $item['quantity'] ?? 1;
                $title = $item['title'] ?? $item['name'] ?? 'Unknown Item';
                $price = $item['price'] ?? $item['price_set']['shop_money']['amount'] ?? '0.00';
                $currency = $item['price_set']['shop_money']['currency_code'] ?? '';
                
                $summaryLines[] = "{$qty}x {$title} ({$currency} {$price})";
            }
            $summaryString = implode("\n", $summaryLines);
            
            // Add to aliases
            $aliases['line_items_summary'] = $summaryString;
            $aliases['order.line_items_summary'] = $summaryString;
            $aliases['cart.line_items_summary'] = $summaryString;
            $aliases['checkout.line_items_summary'] = $summaryString;
        }

        // 2. Email Fallback
        // If 'email' is missing, try to find it from other common fields and backfill it
        if (!isset($payload['email']) && !isset($flattened['email'])) {
             $fallbackEmail = $payload['contact_email'] 
                ?? $payload['customer']['email'] 
                ?? $payload['order']['email'] 
                ?? null;
                
             if ($fallbackEmail) {
                 $aliases['email'] = $fallbackEmail;
                 $aliases['order.email'] = $fallbackEmail;
                 $aliases['cart.email'] = $fallbackEmail;
                 $aliases['checkout.email'] = $fallbackEmail;
                 $aliases['customer.email'] = $fallbackEmail;
                 Log::info("[VariableService_v2.5] Email Fallback applied: $fallbackEmail");
             } else {
                 Log::info("[VariableService_v2.5] Email Fallback FAILED. No email found in payload.");
             }
        }
        
        // --- HELPER LOGIC END ---

        // Merge aliases (keeping original keys too)
        $variables = array_merge($flattened, $aliases);
        
        // Explicitly sort by key length (descending) to avoid partial replacement issues
        // e.g. ensuring {{ order.id }} is replaced before {{ id }} if braces were missing, 
        // though strictly with braces it's less of an issue, it's safer.
        uksort($variables, function($a, $b) {
            return strlen($b) <=> strlen($a);
        });

        // 3. Perform Replacement
        foreach ($variables as $key => $value) {
            if (is_array($value) || is_object($value)) continue;
            
            // Format booleans
            if (is_bool($value)) $value = $value ? 'true' : 'false';
            
            $valString = (string)$value;
            
            // Handle variations with spaces
            $template = str_replace("{{ " . $key . " }}", $valString, $template);
            $template = str_replace("{{" . $key . "}}", $valString, $template);
        }
        
        Log::info("[VariableService_v2.5] Template processed -> " . substr($template, 0, 100) . "...");

        return $template;
    }
}
