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
        $data = json_decode($json, true);

        $exclude = [
            'pageTitle', 'queryPlaceholder', 'page-not-found', 'empty-state',
            'pagination', 'footerLink', 'eye-tooltip', 'categoriesSelection',
            'tagsSelection', 'taggedWith', 'preview', 'filters', 'resourceName'
        ];

        foreach ($data as $key => $item) {
            if (in_array($key, $exclude)) continue;
            if (!is_array($item)) continue;
            if (!isset($item['title'])) continue;

            Template::updateOrCreate(
                ['slug' => $key],
                [
                    'name' => $item['title'],
                    'description' => $item['description'] ?? '',
                    'workflow_data' => ['nodes' => [], 'edges' => []],
                    'tags' => [],
                ]
            );
        }
    }
}
