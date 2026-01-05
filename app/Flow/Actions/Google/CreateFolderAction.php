<?php

namespace App\Flow\Actions\Google;

use App\Flow\Actions\BaseAction;
use App\Models\Execution;
use App\Models\Node;
use App\Services\GoogleService;
use Google\Service\Drive;
use Google\Service\Drive\DriveFile;

class CreateFolderAction extends BaseAction
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
            $folderName = $settings['folder_name'] ?? 'New Folder';
            
            // Strategy for folder name
            $nameSource = $settings['name_source'] ?? 'custom';
            if ($nameSource === 'order_name') {
                $folderName = $payload['name'] ?? 'Unknown Order';
            } elseif ($nameSource === 'product_title') {
                $folderName = $payload['title'] ?? 'Unknown Product';
            } elseif ($nameSource === 'customer_name') {
                 $folderName = ($payload['first_name'] ?? '') . ' ' . ($payload['last_name'] ?? '');
                 $folderName = trim($folderName) ?: 'Unknown Customer';
            }

            $client = $this->googleService->getClient($execution->flow->user);
            $service = new Drive($client);

            $fileMetadata = new DriveFile([
                'name' => $folderName,
                'mimeType' => 'application/vnd.google-apps.folder'
            ]);

            $folder = $service->files->create($fileMetadata, [
                'fields' => 'id, name, webViewLink'
            ]);

            $this->log($execution, $node->id, 'info', 'Created Google Drive folder: ' . $folderName, [
                'folder_id' => $folder->id,
                'url' => $folder->webViewLink
            ]);

        } catch (\Exception $e) {
            $this->log($execution, $node->id, 'error', 'Failed to create folder: ' . $e->getMessage());
        }
    }
}
