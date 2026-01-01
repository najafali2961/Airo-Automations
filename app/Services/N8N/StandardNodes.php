<?php

namespace App\Services\N8N;

class StandardNodes
{
    public static function get()
    {
        return [
            // Core
            ['name' => 'n8n-nodes-base.webhook', 'displayName' => 'Webhook', 'group' => ['trigger']],
            ['name' => 'n8n-nodes-base.scheduleTrigger', 'displayName' => 'Schedule', 'group' => ['trigger']],
            ['name' => 'n8n-nodes-base.httpRequest', 'displayName' => 'HTTP Request', 'group' => ['transform']],
            ['name' => 'n8n-nodes-base.set', 'displayName' => 'Set', 'group' => ['transform']],
            ['name' => 'n8n-nodes-base.code', 'displayName' => 'Code', 'group' => ['transform']],
            ['name' => 'n8n-nodes-base.splitInBatches', 'displayName' => 'Split In Batches', 'group' => ['transform']],
            ['name' => 'n8n-nodes-base.merge', 'displayName' => 'Merge', 'group' => ['transform']],
            ['name' => 'n8n-nodes-base.if', 'displayName' => 'If', 'group' => ['transform']],
            ['name' => 'n8n-nodes-base.switch', 'displayName' => 'Switch', 'group' => ['transform']],
            ['name' => 'n8n-nodes-base.wait', 'displayName' => 'Wait', 'group' => ['transform']],

            // Shopify & E-commerce
            ['name' => 'n8n-nodes-base.shopify', 'displayName' => 'Shopify', 'group' => ['transform', 'app']],
            ['name' => 'n8n-nodes-base.shopifyTrigger', 'displayName' => 'Shopify Trigger', 'group' => ['trigger', 'app']],
            ['name' => 'n8n-nodes-base.woocommerce', 'displayName' => 'WooCommerce', 'group' => ['transform', 'app']],
            ['name' => 'n8n-nodes-base.stripe', 'displayName' => 'Stripe', 'group' => ['transform', 'app']],
            ['name' => 'n8n-nodes-base.stripeTrigger', 'displayName' => 'Stripe Trigger', 'group' => ['trigger', 'app']],

            // Productivity & Communication
            ['name' => 'n8n-nodes-base.slack', 'displayName' => 'Slack', 'group' => ['transform', 'app']],
            ['name' => 'n8n-nodes-base.emailReadImap', 'displayName' => 'Email Read (IMAP)', 'group' => ['trigger', 'app']],
            ['name' => 'n8n-nodes-base.emailSend', 'displayName' => 'Email Send (SMTP)', 'group' => ['transform', 'app']],
            ['name' => 'n8n-nodes-base.gmail', 'displayName' => 'Gmail', 'group' => ['transform', 'app']],
            ['name' => 'n8n-nodes-base.gmailTrigger', 'displayName' => 'Gmail Trigger', 'group' => ['trigger', 'app']],
            ['name' => 'n8n-nodes-base.googleSheets', 'displayName' => 'Google Sheets', 'group' => ['transform', 'app']],
            ['name' => 'n8n-nodes-base.airtable', 'displayName' => 'Airtable', 'group' => ['transform', 'app']],
            ['name' => 'n8n-nodes-base.notion', 'displayName' => 'Notion', 'group' => ['transform', 'app']],
            ['name' => 'n8n-nodes-base.discord', 'displayName' => 'Discord', 'group' => ['transform', 'app']],
            ['name' => 'n8n-nodes-base.telegram', 'displayName' => 'Telegram', 'group' => ['transform', 'app']],
            
            // Social Media
            ['name' => 'n8n-nodes-base.facebookGraphApi', 'displayName' => 'Facebook Graph API', 'group' => ['transform', 'app']],
            ['name' => 'n8n-nodes-base.instagram', 'displayName' => 'Instagram', 'group' => ['transform', 'app']],
            ['name' => 'n8n-nodes-base.twitter', 'displayName' => 'Twitter (X)', 'group' => ['transform', 'app']],
            ['name' => 'n8n-nodes-base.linkedin', 'displayName' => 'LinkedIn', 'group' => ['transform', 'app']],

            // AI & ML
            ['name' => 'n8n-nodes-base.openAi', 'displayName' => 'OpenAI', 'group' => ['transform', 'app']],
            ['name' => 'n8n-nodes-base.huggingFace', 'displayName' => 'Hugging Face', 'group' => ['transform', 'app']],
            ['name' => 'n8n-nodes-base.langChain', 'displayName' => 'LangChain', 'group' => ['transform', 'app']],

            // CRM & Marketing
            ['name' => 'n8n-nodes-base.hubspot', 'displayName' => 'HubSpot', 'group' => ['transform', 'app']],
            ['name' => 'n8n-nodes-base.mailchimp', 'displayName' => 'Mailchimp', 'group' => ['transform', 'app']],
            ['name' => 'n8n-nodes-base.klaviyo', 'displayName' => 'Klaviyo', 'group' => ['transform', 'app']],
            ['name' => 'n8n-nodes-base.salesforce', 'displayName' => 'Salesforce', 'group' => ['transform', 'app']],
            ['name' => 'n8n-nodes-base.activeCampaign', 'displayName' => 'ActiveCampaign', 'group' => ['transform', 'app']],

            // Database
            ['name' => 'n8n-nodes-base.mysql', 'displayName' => 'MySQL', 'group' => ['transform', 'app']],
            ['name' => 'n8n-nodes-base.postgres', 'displayName' => 'PostgreSQL', 'group' => ['transform', 'app']],
            ['name' => 'n8n-nodes-base.mongoDb', 'displayName' => 'MongoDB', 'group' => ['transform', 'app']],
            ['name' => 'n8n-nodes-base.redis', 'displayName' => 'Redis', 'group' => ['transform', 'app']],
            ['name' => 'n8n-nodes-base.supabase', 'displayName' => 'Supabase', 'group' => ['transform', 'app']],
            
             // File & Utils
            ['name' => 'n8n-nodes-base.awsS3', 'displayName' => 'AWS S3', 'group' => ['transform', 'app']],
            ['name' => 'n8n-nodes-base.googleDrive', 'displayName' => 'Google Drive', 'group' => ['transform', 'app']],
            ['name' => 'n8n-nodes-base.dropbox', 'displayName' => 'Dropbox', 'group' => ['transform', 'app']],
            ['name' => 'n8n-nodes-base.ftp', 'displayName' => 'FTP', 'group' => ['transform', 'app']],
        ];
    }
}
