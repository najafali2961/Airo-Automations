<?php

namespace App\Flow\Actions\Google;

use App\Flow\Actions\BaseAction;
use App\Models\Execution;
use App\Models\Node;
use App\Services\GoogleService;
use Google\Service\Drive;
use Google\Service\Drive\DriveFile;
use Illuminate\Support\Facades\Http;

class UploadFileAction extends BaseAction
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
            $fileUrl = $settings['file_url'] ?? null;
            $fileName = $settings['file_name'] ?? 'Uploaded File';
            $folderId = $settings['folder_id'] ?? null; // ID of the parent folder

            if (!$fileUrl) {
                $this->log($execution, $node->id, 'error', 'Missing file URL for upload.');
                return;
            }

            // Fetch file content
            $response = Http::get($fileUrl);
            if (!$response->successful()) {
                 $this->log($execution, $node->id, 'error', 'Failed to fetch file from URL: ' . $fileUrl);
                 return;
            }
            $fileContent = $response->body();

            $client = $this->googleService->getClient($execution->flow->user);
            $service = new Drive($client);

            $fileMetadata = new DriveFile([
                'name' => $fileName,
            ]);
            
            if ($folderId) {
                $fileMetadata->setParents([$folderId]);
            }

            // Create file
            $file = $service->files->create($fileMetadata, [
                'data' => $fileContent,
                'uploadType' => 'multipart',
                'fields' => 'id, name, webViewLink'
            ]);

            $this->log($execution, $node->id, 'info', 'Uploaded file to Drive: ' . $fileName, [
                'file_id' => $file->id,
                'url' => $file->webViewLink
            ]);

        } catch (\Exception $e) {
            $this->log($execution, $node->id, 'error', 'Failed to upload file to Drive: ' . $e->getMessage());
        }
    }
}
