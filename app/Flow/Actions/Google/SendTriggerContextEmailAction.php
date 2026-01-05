<?php

namespace App\Flow\Actions\Google;

use App\Flow\Actions\BaseAction;
use App\Models\Execution;
use App\Models\Node;
use App\Services\GoogleService;
use Google\Service\Gmail;
use Google\Service\Gmail\Message;

class SendTriggerContextEmailAction extends BaseAction
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
            $subject = $settings['subject'] ?? 'Notification for ' . $execution->event;
            
            if (!$to) {
                $this->log($execution, $node->id, 'error', 'Missing "to" email address.');
                return;
            }

            // Generate Smart Table from Payload
            $body = "<h2>Automated Notification: {$execution->event}</h2>";
            $body .= "<p>Here are the details from the trigger:</p>";
            $body .= "<table border='1' cellpadding='5' cellspacing='0' style='border-collapse: collapse; width: 100%;'>";
            $body .= "<thead><tr style='background-color: #f2f2f2;'><th>Key</th><th>Value</th></tr></thead><tbody>";
            
            // Flatten payload for simple display
            $flattened = \Illuminate\Support\Arr::dot($payload);
            
            foreach ($flattened as $key => $value) {
                // Force string conversion for safety
                try {
                    if (is_array($value) || is_object($value)) {
                        $value = json_encode($value);
                    } else {
                        $value = (string) $value;
                    }
                } catch (\Throwable $e) {
                   $value = '[Complex Data]'; 
                }

                if (is_array($value)) {
                    $this->log($execution, $node->id, 'error', 'CRITICAL: Value is still array after conversion. Key: ' . $key);
                    continue; 
                }

                // Skip huge or irrelevant keys
                try {
                    if (strlen($value) > 500) $value = substr($value, 0, 500) . '...';
                } catch (\Throwable $e) {
                    $this->log($execution, $node->id, 'error', 'Strlen failed. Type: ' . gettype($value));
                    continue;
                }
                
                $body .= "<tr><td><strong>{$key}</strong></td><td>{$value}</td></tr>";
            }
            
            $body .= "</tbody></table>";
            
            $client = $this->googleService->getClient($execution->flow->user);
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

            $this->log($execution, $node->id, 'info', 'Smart email sent to ' . $to);

        } catch (\Exception $e) {
            $this->log($execution, $node->id, 'error', 'Failed to send smart email: ' . $e->getMessage());
        }
    }
}
