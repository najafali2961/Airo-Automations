<?php

namespace App\Flow\Engine;

use Illuminate\Support\Arr;

class ConditionEvaluator
{
    /**
     * Evaluate rules defined in a node's settings against the payload data.
     */
    public function evaluate(array $settings, array $data): bool
    {
        $rules = $settings['rules'] ?? [];
        $logic = $settings['logic'] ?? 'AND';

        if (empty($rules)) {
            return true;
        }

        $results = [];
        foreach ($rules as $rule) {
            $value = $this->getValue($data, $rule['object'] ?? null, $rule['field'] ?? '');
            $operator = $rule['operator'] ?? '=';
            $target = $rule['value'] ?? null;

            $results[] = $this->compare($value, $operator, $target);
        }

        if ($logic === 'AND') {
            return !in_array(false, $results, true);
        } else {
            return in_array(true, $results, true);
        }
    }

    protected function getValue(array $data, ?string $object, string $field)
    {
        // Simple dot notation access via Arr::get (data_get alias)
        // If object is specified, we might want to check it, but usually the payload
        // is the resource itself (e.g. the Order object).
        return data_get($data, $field);
    }

    protected function compare($value, string $operator, $target): bool
    {
        switch ($operator) {
            case '=':
            case 'equals':
                return $value == $target;
            case '!=':
            case 'not_equals':
                return $value != $target;
            case '>':
            case 'greater_than':
                return $value > $target;
            case '<':
            case 'less_than':
                return $value < $target;
            case '>=':
                return $value >= $target;
            case '<=':
                return $value <= $target;
            case 'contains':
                return is_string($value) && str_contains($value, (string)$target);
            case 'not_contains':
                return is_string($value) && !str_contains($value, (string)$target);
            case 'starts_with':
                return is_string($value) && str_starts_with($value, (string)$target);
            case 'ends_with':
                return is_string($value) && str_ends_with($value, (string)$target);
            case 'empty':
                return empty($value);
            case 'not_empty':
                return !empty($value);
            default:
                return false;
        }
    }
}
