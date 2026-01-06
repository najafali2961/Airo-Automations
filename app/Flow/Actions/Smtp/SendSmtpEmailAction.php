<?php

namespace App\Flow\Actions\Smtp;

use App\Flow\Contracts\ActionInterface;
use App\Models\Node;
use App\Models\Execution;
use App\Services\GoogleService;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class SendSmtpEmailAction implements ActionInterface
{
    public function handle(Node $node, array $payload, Execution $execution): void
    {
        Log::info("--- START SMTP ACTION (VERSION 5.0 - DEBUG) ---");
        // Debugging Node Structure to settle 'data' vs 'settings' debate
        Log::info("Node Settings Keys: " . implode(',', array_keys($node->settings ?? [])));
        Log::info("Node Data Keys (if any): " . implode(',', array_keys($node['data'] ?? [])));
        
        $user = $execution->flow->user;
        
        if (!$user) {
             $shopId = $execution->flow->shop_id; // Try explicit ID from Flow model
             
             if ($shopId) {
                  // Fallback 1: Normal Find
                  $user = \App\Models\User::find($shopId);
                  
                  // Fallback 2: Soft Deletes (if installed/uninstalled quickly)
                  if (!$user && method_exists(\App\Models\User::class, 'withTrashed')) {
                       $user = \App\Models\User::withTrashed()->find($shopId);
                       if ($user) Log::warning("User found via soft-delete lookup for Shop ID: $shopId");
                  }
             }
             
             if ($user) {
                  Log::warning("Recovered User Context manually for Shop ID: " . $shopId);
             }
        }

        if (!$user) {
             $flowId = $execution->flow_id;
             $shopIdFromFlow = $execution->flow->shop_id ?? 'NULL';
             // Log full context for debugging
             Log::error("CRITICAL: User Context Failure. Flow ID: {$flowId}, Shop ID: {$shopIdFromFlow}. Execution Payload keys: " . implode(',', array_keys($payload)));
             throw new \Exception("User not found for execution context (Flow ID: {$flowId}, Shop ID: {$shopIdFromFlow}). Please check if the Shop User exists in DB.");
        }

        $smtpConfig = $user->smtpConfig;

        if (!$smtpConfig) {
             throw new \Exception("SMTP Configuration not found. Please configure SMTP in Connectors.");
        }

        $config = [
            'transport' => 'smtp',
            'host' => $smtpConfig->host,
            'port' => $smtpConfig->port,
            'username' => $smtpConfig->username,
            'password' => $smtpConfig->password, // logic handles decryption if cast correctly
            'encryption' => $smtpConfig->encryption,
            'timeout' => null,
            // 'auth_mode' => null, // Optional
        ];

        // Dynamic Mailer
        // Laravel doesn't have a simple Mail::build() that returns a full mailer instance easily in all versions without setup.
        // But we can use the 'config' helper to set 'mail.mailers.smtp_dynamic' temporarily or build a transport.
        // A cleaner way in modern Laravel:
        
        $factory = app('mail.manager');
        // We'll define a custom mailer config on the fly
        Config::set('mail.mailers.smtp_dynamic', $config);
        Config::set('mail.from.address', $smtpConfig->from_address);
        Config::set('mail.from.name', $smtpConfig->from_name);

        $mailer = $factory->mailer('smtp_dynamic');
        
        // Pass $user to resolution logic for 'shop_email' fallback
        $to = $this->resolveToAddress($node, $payload, $user);
        
        if (empty($to)) {
             $strat = $node->settings['recipient_type'] ?? 'NULL';
             throw new \Exception("No recipient email address resolved. Strategy: {$strat}. Settings Count: " . count($node->settings ?? []));
        }

        $subject = $node['settings']['subject'] ?? 'No Subject';
        $body = $node['settings']['body'] ?? '';

        try {
            // Re-attempt with HTML if possible for rich body
             $mailer->send([], [], function ($message) use ($to, $subject, $body, $smtpConfig) {
                $message->to($to)
                        ->subject($subject)
                        ->from($smtpConfig->from_address, $smtpConfig->from_name)
                        ->html($body);
            });

            Log::info("SMTP Email sent to $to via " . $smtpConfig->host);
            
            // return [
            //    'success' => true,
            //    'message' => "Email sent to $to",
            //    'settings' => $config
            // ];

        } catch (\Exception $e) {
            Log::error("SMTP Error: " . $e->getMessage());
            throw $e;
        }
    }

    private function resolveToAddress($node, $payload, $user = null)
    {
        // Use object syntax for Eloquent model to be safe
        $settings = $node->settings ?? [];
        
        // Default to 'shop_email' if empty.
        $strategy = $settings['recipient_type'] ?? 'shop_email';

        Log::info("SMTP Address Resolution Strategy: {$strategy}");

        if ($strategy === 'custom') {
            $email = $settings['to'] ?? '';
            $email = trim(explode(',', $email)[0]);
            if (!empty($email)) return $email;
            Log::warning("Strategy 'custom' selected but 'to' field is empty.");
        }

        if ($strategy === 'customer_email') {
            $email = $this->findCustomerEmail($payload);
            if ($email) {
                 Log::info("Resolved Customer Email: {$email}");
                 return $email;
            }
            Log::warning("Strategy 'customer_email' failed. No email found in payload.");
        }

        if ($strategy === 'shop_email') {
             if ($user && !empty($user->email)) {
                  Log::info("Resolved Shop Admin Email: {$user->email}");
                  return $user->email;
             }
             // Fallback to a safe default? Or fail.
             Log::warning("Strategy 'shop_email' failed. User object missing or has no email.");
        }

        // Final Fallback: If no strategy is set OR if custom/customer logic yielded nothing.
        // We defaults to Shop Email (Admin) to prevent crashes.
        if ($user && !empty($user->email)) {
             Log::warning("Resolution Fallback: Primary strategy '{$strategy}' yielded no email. Defaulting to Shop Admin Email: {$user->email}");
             return $user->email;
        }

        return $settings['to'] ?? '';
    }

    private function findCustomerEmail($payload)
    {
        // 1. Direct Top-Level Checks (Fast Path)
        if (!empty($payload['email']) && filter_var($payload['email'], FILTER_VALIDATE_EMAIL)) return $payload['email'];
        if (!empty($payload['customer']['email']) && filter_var($payload['customer']['email'], FILTER_VALIDATE_EMAIL)) return $payload['customer']['email'];
        if (!empty($payload['order']['email']) && filter_var($payload['order']['email'], FILTER_VALIDATE_EMAIL)) return $payload['order']['email'];
        
        // 2. Recursive Search (Deep Path) - beneficial for weird webhooks
        // Limit depth to avoid performance hit
        return $this->recursiveFindEmail($payload, 0, 3);
    }
    
    private function recursiveFindEmail($data, $depth = 0, $maxDepth = 3) {
        if ($depth > $maxDepth) return null;
        if (!is_array($data) && !is_object($data)) return null;
        
        foreach ($data as $key => $value) {
            if ($key === 'email' && is_string($value) && filter_var($value, FILTER_VALIDATE_EMAIL)) {
                return $value;
            }
            
            if (is_array($value) || is_object($value)) {
                $found = $this->recursiveFindEmail((array)$value, $depth + 1, $maxDepth);
                if ($found) return $found;
            }
        }
        return null;
    }
}

