<?php

namespace App\Flow\Actions\Google;

use App\Flow\Actions\BaseAction;
use App\Models\Execution;
use App\Models\Node;
use App\Services\GoogleService;
use Google\Service\Docs;

class CreateDocAction extends BaseAction
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
            $titleSource = $settings['title_source'] ?? 'custom';
            $title = 'New Document';

            if ($titleSource === 'product_title') {
                $title = $payload['title'] ?? ($payload['product']['title'] ?? 'Unknown Product');
            } elseif ($titleSource === 'order_name') {
                $title = $payload['name'] ?? 'Unknown Order';
            } else {
                $title = $settings['title'] ?? 'New Document';
            }
            $content = $settings['content'] ?? '';

            $client = $this->googleService->getClient($execution->flow->user);
            $service = new Docs($client);

            $doc = new \Google\Service\Docs\Document([
                'title' => $title
            ]);

            $createdDoc = $service->documents->create($doc);
            $documentId = $createdDoc->getDocumentId();

            if (!empty($content)) {
                $requests = [
                    new \Google\Service\Docs\Request([
                        'insertText' => [
                            'text' => $content,
                            'location' => [
                                'index' => 1,
                            ],
                        ],
                    ]),
                ];

                $batchUpdate = new \Google\Service\Docs\BatchUpdateDocumentRequest([
                    'requests' => $requests
                ]);

                $service->documents->batchUpdate($documentId, $batchUpdate);
            }

            $this->log($execution, $node->id, 'info', 'Created document: ' . $title, ['document_id' => $documentId]);

        } catch (\Exception $e) {
            $this->log($execution, $node->id, 'error', 'Failed to create doc: ' . $e->getMessage());
        }
    }
}
