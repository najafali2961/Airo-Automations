<?php

namespace App\Flow\Actions\Google;

use App\Flow\Actions\BaseAction;
use App\Models\Execution;
use App\Models\Node;
use App\Services\GoogleService;
use Google\Service\Sheets;
use Google\Service\Sheets\Spreadsheet;
use Google\Service\Sheets\SpreadsheetProperties;

class CreateSheetAction extends BaseAction
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
            $title = $settings['title'] ?? 'New Spreadsheet';

            $client = $this->googleService->getClient($execution->flow->user);
            $service = new Sheets($client);

            $spreadsheet = new Spreadsheet([
                'properties' => new SpreadsheetProperties([
                    'title' => $title
                ])
            ]);

            $spreadsheet = $service->spreadsheets->create($spreadsheet);
            
            $this->log($execution, $node->id, 'info', 'Created spreadsheet: ' . $title, ['spreadsheet_id' => $spreadsheet->spreadsheetId, 'url' => $spreadsheet->spreadsheetUrl]);

        } catch (\Exception $e) {
            $this->log($execution, $node->id, 'error', 'Failed to create spreadsheet: ' . $e->getMessage());
        }
    }
}
