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
        Log::info("--- START SMTP ACTION (VERSION 5.2 - AUTO FIX) ---");
        // ... (Keep existing user retrieval logic) ...
        $user = $execution->flow->user;
        
        if (!$user) {
             $shopId = $execution->flow->shop_id; 
             if ($shopId) {
                  $user = \App\Models\User::find($shopId);
                  if (!$user && method_exists(\App\Models\User::class, 'withTrashed')) {
                       $user = \App\Models\User::withTrashed()->find($shopId);
                  }
             }
        }
        if (!$user) {
             throw new \Exception("User not found for execution context.");
        }
        $smtpConfig = $user->smtpConfig;
        if (!$smtpConfig) {
             throw new \Exception("SMTP Configuration not found.");
        }
        $config = [
            'transport' => 'smtp',
            'host' => $smtpConfig->host,
            'port' => $smtpConfig->port,
            'username' => $smtpConfig->username,
            'password' => $smtpConfig->password,
            'encryption' => $smtpConfig->encryption,
            'timeout' => null,
        ];
        $factory = app('mail.manager');
        Config::set('mail.mailers.smtp_dynamic', $config);
        Config::set('mail.from.address', $smtpConfig->from_address);
        Config::set('mail.from.name', $smtpConfig->from_name);
        $mailer = $factory->mailer('smtp_dynamic');
        
        $to = $this->resolveToAddress($node, $payload, $user);
        
        if (empty($to)) {
             $strat = $node->settings['recipient_type'] ?? 'NULL';
             throw new \Exception("No recipient email address resolved. Strategy: {$strat}.");
        }
        // Variable Replacement
        $rawSubject = $node->settings['subject'] ?? 'No Subject';
        $rawBody = $node->settings['body'] ?? '';
        
        $subject = $this->replaceVariables($rawSubject, $payload);
        $body = $this->replaceVariables($rawBody, $payload);
        try {
             $mailer->send([], [], function ($message) use ($to, $subject, $body, $smtpConfig) {
                $message->to($to)
                        ->subject($subject)
                        ->from($smtpConfig->from_address, $smtpConfig->from_name)
                        ->html($body);
            });
            Log::info("SMTP Email sent to $to via " . $smtpConfig->host);
        } catch (\Exception $e) {
            Log::error("SMTP Error: " . $e->getMessage());
            throw $e;
        }
    }
    private function resolveToAddress($node, $payload, $user = null)
    {
        $settings = $node->settings ?? [];
        
        $strategy = $settings['recipient_type'] ?? null;
        // Intelligent Default: 
        // If the user hasn't touched the dropdown, 'recipient_type' might be missing.
        // But if they typed in the 'to' field, they definitely mean 'custom'.
        if (empty($strategy)) {
             if (!empty($settings['to'])) {
                 $strategy = 'custom';
             } else {
                 $strategy = 'shop_email';
             }
        }
        Log::info("SMTP Address Resolution Strategy: {$strategy}");
        if ($strategy === 'custom') {
            $email = $settings['to'] ?? '';
            // Support variables in 'To' field too!
            $email = $this->replaceVariables($email, $payload);
            $email = trim(explode(',', $email)[0]);
            if (!empty($email)) return $email;
        }
        if ($strategy === 'customer_email') {
            $email = $this->findCustomerEmail($payload);
            if ($email) return $email;
        }
        
        if ($strategy === 'order_email') {
            if (!empty($payload['email']) && filter_var($payload['email'], FILTER_VALIDATE_EMAIL)) return $payload['email'];
             if (!empty($payload['order']['email']) && filter_var($payload['order']['email'], FILTER_VALIDATE_EMAIL)) return $payload['order']['email'];
             // Fallback to customer email calculation
             $email = $this->findCustomerEmail($payload);
             if ($email) return $email;
        }
        if ($strategy === 'shop_email' || empty($strategy)) {
             if ($user && !empty($user->email)) return $user->email;
        }
        // Final Fallback
        if ($user && !empty($user->email)) return $user->email;
        return $settings['to'] ?? '';
    }
    private function findCustomerEmail($payload)
    {
        if (!empty($payload['email']) && filter_var($payload['email'], FILTER_VALIDATE_EMAIL)) return $payload['email'];
        if (!empty($payload['customer']['email']) && filter_var($payload['customer']['email'], FILTER_VALIDATE_EMAIL)) return $payload['customer']['email'];
        return $this->recursiveFindEmail($payload, 0, 3);
    }
    
    private function recursiveFindEmail($data, $depth = 0, $maxDepth = 3) {
        if ($depth > $maxDepth) return null;
        if (!is_array($data) && !is_object($data)) return null;
        foreach ($data as $key => $value) {
            if ($key === 'email' && is_string($value) && filter_var($value, FILTER_VALIDATE_EMAIL)) return $value;
            if (is_array($value) || is_object($value)) {
                $found = $this->recursiveFindEmail((array)$value, $depth + 1, $maxDepth);
                if ($found) return $found;
            }
        }
        return null;
    }
    private function replaceVariables($text, $payload)
    {
        if (empty($text)) return '';
        
        $flattened = \Illuminate\Support\Arr::dot($payload);
        
        // Add some convenience keys
        if (isset($payload['title'])) $flattened['product.title'] = $payload['title'];
        if (isset($payload['name'])) $flattened['order.name'] = $payload['name'];
        if (isset($payload['id'])) $flattened['id'] = $payload['id'];
        
        Log::info("Available Variables for Replacement: " . implode(', ', array_keys($flattened)));
        
        foreach ($flattened as $key => $value) {
            if (is_array($value) || is_object($value)) continue;
            if (is_bool($value)) $value = $value ? 'true' : 'false';
            
            $text = str_replace("{{ " . $key . " }}", (string)$value, $text);
            $text = str_replace("{{" . $key . "}}", (string)$value, $text);
        }
        return $text;
    }
}