<?php

namespace App\Flow\Actions\Google;

use App\Flow\Actions\BaseAction;
use App\Models\Execution;
use App\Models\Node;
use App\Services\GoogleService;
use Google\Service\Sheets;
use Google\Service\Sheets\ValueRange;

class AddToSheetAction extends BaseAction
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
            $spreadsheetId = $settings['spreadsheet_id'] ?? null;
            $range = $settings['range'] ?? 'Sheet1!A1';
            $values = $settings['values'] ?? []; // Array of strings

            if (!$spreadsheetId) {
                $this->log($execution, $node->id, 'error', 'Missing Spreadsheet ID.');
                return;
            }
            
            // If values is a comma separated string, explode it
            if (is_string($values)) {
                $values = explode(',', $values);
            }

            // Ensure values is an array of arrays (rows)
            if (!is_array($values)) {
               $values = [$values];
            }
             // If simple array [val1, val2], wrap in [ [val1, val2] ] for appending a row
            if (count($values) > 0 && !is_array($values[0])) {
                $values = [$values];
            }


            $client = $this->googleService->getClient();
            $service = new Sheets($client);

            $body = new ValueRange([
                'values' => $values
            ]);
            
            $params = [
                'valueInputOption' => 'USER_ENTERED'
            ];

            $result = $service->spreadsheets_values->append($spreadsheetId, $range, $body, $params);

            $this->log($execution, $node->id, 'info', 'Added ' . $result->getUpdates()->getUpdatedRows() . ' rows to sheet.');

        } catch (\Exception $e) {
            $this->log($execution, $node->id, 'error', 'Failed to add to sheet: ' . $e->getMessage());
        }
    }
}
