<?php

namespace App\Flow\Actions\Klaviyo;

use App\Flow\Actions\BaseAction;
use App\Models\Execution;
use App\Models\Node;
use App\Services\KlaviyoService;
use App\Services\VariableService;
use Illuminate\Support\Facades\Log;

class AddToKlaviyoListAction extends BaseAction
{
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
            
            if (!$user || !$user->klaviyoCredential) {
                $this->log($execution, $node->id, 'error', 'User not connected to Klaviyo.');
                return;
            }

            $settings = $this->getSettings($node, $payload);

            // Process Variables
            $listId = $this->variableService->replace($settings['list_id'] ?? '', $payload);
            $email = $this->variableService->replace($settings['email'] ?? '', $payload);

            if (empty($listId)) {
                $this->log($execution, $node->id, 'error', 'List ID is required.');
                return;
            }

            if (empty($email)) {
                $this->log($execution, $node->id, 'error', 'Email is required.');
                return;
            }

            // 1. Get Profile ID (Create/Update if needs be)
            $profileId = $this->klaviyoService->getProfileIdByEmail($user->klaviyoCredential, $email);

            if (!$profileId) {
                $this->log($execution, $node->id, 'error', "Could not find or create Klaviyo profile for email: $email");
                return;
            }

            // 2. Add to List
            $response = $this->klaviyoService->addProfileToList($user->klaviyoCredential, $listId, $profileId);

            if ($response->successful()) {
                $this->log($execution, $node->id, 'info', "Added $email (ID: $profileId) to List $listId successfully.");
            } else {
                $error = $response->body();
                $this->log($execution, $node->id, 'error', "Failed to add to list. Klaviyo Error: $error");
            }

        } catch (\Exception $e) {
            $this->log($execution, $node->id, 'error', 'Failed to add to Klaviyo list: ' . $e->getMessage());
        }
    }
}
