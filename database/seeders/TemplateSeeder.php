<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Template;
use Illuminate\Support\Facades\File;

class TemplateSeeder extends Seeder
{
    public function run()
    {
        $path = base_path('templates.json');
        if (!File::exists($path)) {
            return;
        }

        $json = File::get($path);
        // Decode as array
        $templates = json_decode($json, true);

        if (!is_array($templates)) {
            return;
        }

        foreach ($templates as $item) {
            // Basic validation
            if (!isset($item['slug']) || !isset($item['name'])) {
                continue;
            }

            Template::updateOrCreate(
                ['slug' => $item['slug']],
                [
                    'name' => $item['name'],
                    'description' => $item['description'] ?? '',
                    'category' => $item['category'] ?? null,
                    'tags' => $item['tags'] ?? [],
                    'connectors' => $item['connectors'] ?? ['shopify'],
                    // Preserve existing workflow data if we were updating, but for seeding fresh we default to empty.
                    // Ideally we might want to seed real nodes/edges eventually.
                    'workflow_data' => ['nodes' => [], 'edges' => []],
                ]
            );
        }
    }
}
