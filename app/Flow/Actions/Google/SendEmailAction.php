<?php

namespace App\Flow\Actions\Google;

use App\Flow\Actions\BaseAction;
use App\Models\Execution;
use App\Models\Node;
use App\Services\GoogleService;
use Google\Service\Gmail;
use Google\Service\Gmail\Message;

class SendEmailAction extends BaseAction
{
    protected $googleService;

    public function __construct(GoogleService $googleService)
    {
        $this->googleService = $googleService;
    }

    public function handle(Node $node, array $payload, Execution $execution): void
    {
        try {
            $settings = $this->getSettings($node, $payload);
            $to = $settings['to'] ?? null;
            $subject = $settings['subject'] ?? 'No Subject';
            $body = $settings['body'] ?? '';

            $recipientType = $settings['recipient_type'] ?? 'custom';

            if ($recipientType === 'customer_email') {
                // Try to find customer email in payload
                $to = $this->findCustomerEmail($payload);
                if (!$to) {
                     $this->log($execution, $node->id, 'error', 'Could not find customer email in trigger data.');
                     return;
                }
            } elseif ($recipientType === 'shop_email') {
                $to = \Illuminate\Support\Facades\Auth::user()->email ?? $execution->flow->user->email;
            } else {
                // Custom
                $to = $settings['to'] ?? null;
            }

            if (!$to) {
                $this->log($execution, $node->id, 'error', 'Missing "to" email address.');
                return;
            }

            $flowUser = $execution->flow->user;
            
            if (!$flowUser) {
                $shopId = $execution->flow->shop_id;
                $this->log($execution, $node->id, 'warning', 'Relation failed. Trying direct User::find(' . $shopId . ')');
                $flowUser = \App\Models\User::find($shopId);
            }

            if (!$flowUser) {
                $this->log($execution, $node->id, 'error', 'User not found even with direct find. Shop ID: ' . $execution->flow->shop_id);
            } else {
                 $this->log($execution, $node->id, 'info', 'User found (' . ($execution->flow->user ? 'relation' : 'direct') . '). ID: ' . $flowUser->id . ' | Token: ' . ($flowUser->google_access_token ? 'YES' : 'NO'));
            }

            $client = $this->googleService->getClient($flowUser);
            $service = new Gmail($client);

            $strSubject = 'Subject: ' . $subject . "\r\n";
            $strTo = 'To: ' . $to . "\r\n";
            $strContentType = 'Content-Type: text/html; charset=utf-8' . "\r\n";
            $strMime = 'MIME-Version: 1.0' . "\r\n";
            $strBody = $body . "\r\n";
            
            $rawMessageString = $strTo . $strSubject . $strMime . $strContentType . "\r\n" . $strBody;
            $rawMessage = base64_encode($rawMessageString);
            $rawMessage = str_replace(['+', '/', '='], ['-', '_', ''], $rawMessage); // Url Safe

            $msg = new Message();
            $msg->setRaw($rawMessage);
            
            $service->users_messages->send('me', $msg);

            $this->log($execution, $node->id, 'info', 'Email sent successfully to ' . $to);

        } catch (\Exception $e) {
            $this->log($execution, $node->id, 'error', 'Failed to send email: ' . $e->getMessage());
        }
    }

    protected function findCustomerEmail(array $payload)
    {
        return $payload['email'] ?? 
               ($payload['customer']['email'] ?? 
               ($payload['contact_email'] ?? null));
    }
}
