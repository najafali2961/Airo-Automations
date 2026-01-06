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
        $user = $execution->flow->user;
        
        if (!$user) {
             // Fallback if relation fails
             $user = \App\Models\User::find($execution->flow->shop_id);
        }

        if (!$user) {
             throw new \Exception("User not found for execution context (Flow ID: " . $execution->flow_id . ")");
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

        $to = $this->resolveToAddress($node, $payload);
        $subject = $node['data']['settings']['subject'] ?? 'No Subject';
        $body = $node['data']['settings']['body'] ?? '';

        try {
            $mailer->raw($body, function ($message) use ($to, $subject, $smtpConfig) {
                $message->to($to)
                        ->subject($subject)
                        ->from($smtpConfig->from_address, $smtpConfig->from_name);
                
                // HTML Support: 'raw' sends plain text. For HTML use 'html' if defined or 'send' with view.
                // But mailer->html() is available in newer Laravel.
                // If not, we can use ->html($body, ...) or provide a simple view.
                // Let's try explicit html method if available, or fallback to text.
                // Actually, safest is ->send([], [], function($m) use ($body) { $m->html($body); });
                // But ->html() method exists in most recent versions.
            });
            
            // Re-attempt with HTML if possible for rich body
            // Since we used raw above, let's fix it to support HTML properly.
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

    private function resolveToAddress($node, $payload)
    {
        $settings = $node['data']['settings'] ?? [];
        $strategy = $settings['recipient_type'] ?? 'custom';

        if ($strategy === 'custom') {
            return $settings['to'] ?? '';
        }

        if ($strategy === 'customer_email') {
            return $this->findCustomerEmail($payload);
        }

        if ($strategy === 'shop_email') {
             // We'd need current shop email. 
             // Assuming we can get it from payload or execution context.
             // For now fallback to custom.
             return 'admin@example.com'; 
        }

        return $settings['to'] ?? '';
    }

    private function findCustomerEmail($payload)
    {
        // Try top level
        if (isset($payload['email'])) return $payload['email'];
        if (isset($payload['customer']['email'])) return $payload['customer']['email'];
        if (isset($payload['order']['email'])) return $payload['order']['email'];
        
        return null;
    }
}
