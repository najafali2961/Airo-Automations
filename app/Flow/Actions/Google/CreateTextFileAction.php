<?php

namespace App\Flow\Actions\Google;

use App\Flow\Actions\BaseAction;
use App\Models\Execution;
use App\Models\Node;
use App\Services\GoogleService;
use Google\Service\Drive;
use Google\Service\Drive\DriveFile;

class CreateTextFileAction extends BaseAction
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
            $fileName = $settings['file_name'] ?? 'New Text File.txt';
            $content = $settings['content'] ?? '';
            $folderId = $settings['folder_id'] ?? null;

            $client = $this->googleService->getClient($execution->flow->user);
            $service = new Drive($client);

            $fileMetadata = new DriveFile([
                'name' => $fileName,
                'mimeType' => 'text/plain'
            ]);

            if ($folderId) {
                $fileMetadata->setParents([$folderId]);
            }

            $file = $service->files->create($fileMetadata, [
                'data' => $content,
                'mimeType' => 'text/plain',
                'uploadType' => 'multipart',
                'fields' => 'id, name, webViewLink'
            ]);

            $this->log($execution, $node->id, 'info', 'Created Text File: ' . $fileName, [
                'file_id' => $file->id,
                'url' => $file->webViewLink
            ]);

        } catch (\Exception $e) {
            $this->log($execution, $node->id, 'error', 'Failed to create text file: ' . $e->getMessage());
        }
    }
}
