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

    public function handle(Execution $execution, Node $node, array $payload)
    {
        try {
            $settings = $this->getSettings($node, $payload);
            $to = $settings['to'] ?? null;
            $subject = $settings['subject'] ?? 'No Subject';
            $body = $settings['body'] ?? '';

            if (!$to) {
                $this->log($execution, $node->id, 'error', 'Missing "to" email address.');
                return;
            }

            $client = $this->googleService->getClient();
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
}
