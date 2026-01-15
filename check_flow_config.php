<?php
$conf = config('flow');
echo "Config Type: " . gettype($conf) . "\n";
if (is_array($conf)) {
    echo "Keys: " . implode(', ', array_keys($conf)) . "\n";
    if (isset($conf['actions'])) {
        echo "Actions Count: " . count($conf['actions']) . "\n";
    }
} else {
    echo "Config is NULL or not an array.\n";
}
