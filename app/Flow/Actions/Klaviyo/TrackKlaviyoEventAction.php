<?php

namespace App\Flow\Actions\Klaviyo;

use App\Flow\Actions\BaseAction;
use App\Models\Execution;
use App\Models\Node;
use App\Services\KlaviyoService;
use App\Services\VariableService;
use Illuminate\Support\Facades\Log;

class TrackKlaviyoEventAction extends BaseAction
{
    use HasShopifyCustomerFallback;

    protected $klaviyoService;
    protected $variableService;

    public function __construct(KlaviyoService $klaviyoService, VariableService $variableService)
    {
        $this->klaviyoService = $klaviyoService;
        $this->variableService = $variableService;
    }

    public function handle(Node $node, array $payload, Execution $execution): void
    {
        try {
            $user = $execution->flow->user;
            
            if (!$user) {
                $this->log($execution, $node->id, 'error', 'User context missing.');
                return;
            }

            $settings = $this->getSettings($node, $payload);

            // Process Variables
            $eventName = $this->variableService->replace($settings['event_name'] ?? '', $payload);
            
            // Resolve Email with Fallback
            $email = $this->resolveEmail($user, $payload, $settings['email'] ?? '', $this->variableService);
            
            $value = $this->variableService->replace($settings['value'] ?? '', $payload);
            
            // For JSON properties, we substitute vars first, then json_decode
            $propertiesRaw = $this->variableService->replace($settings['properties'] ?? '', $payload);
            
            // Clean up
            if (!empty($eventName) && str_contains($eventName, '{{')) $eventName = null;
            if (!empty($email) && str_contains($email, '{{')) $email = null;

            if (empty($eventName)) {
                $this->log($execution, $node->id, 'error', 'Event Name is required.');
                return;
            }

            if (empty($email)) {
                $this->log($execution, $node->id, 'error', 'Email is required (could not be resolved from payload or Shopify).');
                return;
            }

            $properties = [];
            if (!empty($propertiesRaw)) {
                $properties = json_decode($propertiesRaw, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    $this->log($execution, $node->id, 'warning', 'Invalid JSON in properties. sending as raw string value if simple string, or skipping.');
                    // If it's just a string, we can maybe put it in a key? 
                    // Let's assume user expects object. If fail, maybe log error.
                    $this->log($execution, $node->id, 'error', 'Event Properties must be valid JSON.');
                    return;
                }
            }

            // Add Value to properties if present
            if (!empty($value)) {
                $properties['$value'] = $value;
            }

            $profileProperties = ['email' => $email];

            $response = $this->klaviyoService->trackEvent(
                $user, 
                $eventName, 
                $profileProperties, 
                $properties
            );

            if ($response->successful()) {
                $this->log($execution, $node->id, 'info', "Tracked event '$eventName' for $email successfully.");
            } else {
                $error = $response->body();
                $this->log($execution, $node->id, 'error', "Failed to track event. Klaviyo Error: $error");
            }

        } catch (\Exception $e) {
            $this->log($execution, $node->id, 'error', 'Failed to track Klaviyo event: ' . $e->getMessage());
        }
    }
}
