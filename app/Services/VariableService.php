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

        // Merge aliases (keeping original keys too)
        $variables = array_merge($flattened, $aliases);
        
        // Log::info("VariableService Keys: " . implode(', ', array_keys($variables)));
        
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
        
        Log::info("VariableService: Template processed -> " . substr($template, 0, 100) . "...");

        return $template;
    }
}
