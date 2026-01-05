<?php

namespace App\Flow\Traits;

use Illuminate\Support\Arr;

trait InteractsWithVariables
{
    /**
     * Replace {{ variable.path }} with values from data.
     */
    protected function replaceVariables(string $content, array $data): string
    {
        return preg_replace_callback('/\{\{\s*([\w\.]+)\s*\}\}/', function ($matches) use ($data) {
            $key = $matches[1];
            $value = Arr::get($data, $key);
            
            if (is_array($value)) {
                return json_encode($value);
            }
            
            return (string) ($value ?? $matches[0]); // Return original if not found
        }, $content);
    }

    /**
     * Recursively replace variables in an array of settings.
     */
    protected function resolveRawSettings(array $settings, array $data): array
    {
        foreach ($settings as $key => $value) {
            if (is_string($value)) {
                $settings[$key] = $this->replaceVariables($value, $data);
            } elseif (is_array($value)) {
                $settings[$key] = $this->resolveRawSettings($value, $data);
            }
        }
        return $settings;
    }
}
