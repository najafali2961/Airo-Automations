
if ($action) {
    echo "Found action: " . $action->label . "\n";
    $fields = $action->fields;
    $updated = false;
    foreach ($fields as &$field) {
        if ($field['name'] === 'to') {
            $field['showIf'] = ['field' => 'recipient_type', 'value' => 'custom'];
            $updated = true;
        }
    }
    unset($field);
    
    if ($updated) {
        $action->fields = $fields;
        $action->save();
        echo "Successfully updated SMTP action fields.\n";
    } else {
        echo "Field 'to' not found in definition.\n";
    }
} else {
    echo "SMTP action not found.\n";
}
exit();
