<?php

namespace Database\Seeders;

use App\Models\Connector;
use Illuminate\Database\Seeder;

class ConnectorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $connectors = [
            [
                'name' => 'Google',
                'slug' => 'google',
                'icon' => 'google',
                'description' => 'Connect your Google Drive,Doc,Sheet and Mail to save generated files directly to your cloud storage.',
                'is_active' => true,
            ],
            [
                'name' => 'Slack',
                'slug' => 'slack',
                'icon' => 'slack',
                'description' => 'Send notifications to Slack channels when automations run.',
                'is_active' => true,
            ],
            [
                'name' => 'Klaviyo',
                'slug' => 'klaviyo',
                'icon' => 'klaviyo',
                'description' => 'Sync customer segments and trigger flows in Klaviyo.',
                'is_active' => true,
            ],
            [
                'name' => 'SMTP',
                'slug' => 'smtp',
                'icon' => 'mail',
                'description' => 'Send emails using your own SMTP server credentials.',
                'is_active' => true,
            ],
        ];

        foreach ($connectors as $connector) {
            Connector::updateOrCreate(
                ['slug' => $connector['slug']],
                $connector
            );
        }
    }
}
