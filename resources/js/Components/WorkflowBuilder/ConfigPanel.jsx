import React, { useState, useEffect, useMemo } from "react";
import {
    Box,
    BlockStack,
    Text,
    FormLayout,
    TextField,
    Select,
    Button,
    InlineStack,
    Divider,
    Banner,
} from "@shopify/polaris";
import VariableInput from "./VariableInput";

export default function ConfigPanel({
    node,
    definitions,
    onUpdate,
    onClose,
    connectors,
    triggerVariables,
}) {
    const [settings, setSettings] = useState(node.data.settings || {});

    // Find the definition for this node
    const definition = useMemo(() => {
        if (!definitions || !definitions.apps) return null;

        for (const app of definitions.apps) {
            if (node.type === "trigger") {
                const trigger = app.triggers?.find(
                    (t) =>
                        t.settings?.topic === settings.topic ||
                        t.label === node.data.label
                );
                if (trigger) return trigger;
            } else if (node.type === "action") {
                const action = app.actions?.find(
                    (a) =>
                        a.settings?.action === settings.action ||
                        a.label === node.data.label
                );
                if (action) return action;
            }
        }
        return null;
    }, [node, definitions, settings.topic, settings.action]);

    useEffect(() => {
        setSettings(node.data.settings || {});
    }, [node]);

    const updateSetting = (key, value) => {
        const newSettings = { ...settings, [key]: value };
        setSettings(newSettings);

        onUpdate(node.id, {
            settings: newSettings,
            label: node.data.label, // Keep label stable unless specifically changed
        });
    };

    if (!node) return null;

    // Validation Logic
    const isConnected = useMemo(() => {
        if (!definition || !connectors) return true;
        // Determine app name from definition group or explicit app property (not always set in frontend defs)
        // Backend maps 'google', 'slack' to group names or app property.
        // My FlowController adds 'app' implicitly via group logic?
        // Wait, definition in FlowController sets 'group'.
        // But I can infer from category or label.
        // Actually, FlowController sets 'n8nType' and 'group'.
        // Let's rely on 'group' (category) often matching app name or being 'communication' etc.
        // Better: Check definition.icon or name?
        // Code in FlowController: $actionDef['group'] = $action['category'], $actionDef['fields']...
        // It DOES NOT pass 'app' key explicitly to the action definition in the final array.
        // However, I grouped them into 'apps' array by name.
        // But ConfigPanel just searches `definitions.apps`.

        // Find which app this definition belongs to
        const parentApp = definitions.apps.find(
            (app) =>
                app.actions?.some((a) => a.label === definition.label) ||
                app.triggers?.some((t) => t.label === definition.label)
        );

        if (parentApp) {
            const appName = parentApp.name.toLowerCase(); // 'google', 'slack', 'smtp'
            if (connectors[appName] === false) return false;
        }
        return true;
    }, [definition, connectors, definitions]);

    const renderFields = () => {
        // If it's a condition node, use hardcoded logic for now (it's a standard node)
        if (node.type === "condition") {
            return (
                <ConditionSettings
                    settings={settings}
                    onChange={updateSetting}
                />
            );
        }

        // For triggers and actions, use definitions
        if (!definition) {
            // Fallback for nodes without a clear definition match (initial state)
            if (node.type === "trigger") {
                return (
                    <Select
                        label="Select Shopify Event"
                        options={getAllTriggers(definitions)}
                        value={settings.topic}
                        onChange={(val) => updateSetting("topic", val)}
                    />
                );
            }
            return (
                <Text tone="subdued">
                    No configuration available for this node.
                </Text>
            );
        }

        return (
            <BlockStack gap="400">
                {!isConnected && (
                    <Banner tone="critical" title="Connection Required">
                        <p>
                            You must connect {definition.group || "this app"} in
                            the Connectors page before using this action.
                        </p>
                    </Banner>
                )}

                {definition.description && (
                    <Text variant="bodySm" tone="subdued">
                        {definition.description}
                    </Text>
                )}

                {definition.fields?.map((field) => (
                    <div key={field.name}>
                        {field.type === "select" ? (
                            <Select
                                label={field.label}
                                options={field.options}
                                value={
                                    settings[field.name] || field.default || ""
                                }
                                onChange={(val) =>
                                    updateSetting(field.name, val)
                                }
                                disabled={!isConnected}
                                required={field.required}
                            />
                        ) : (
                            <VariableInput
                                label={field.label}
                                value={settings[field.name] || ""}
                                onChange={(val) =>
                                    updateSetting(field.name, val)
                                }
                                placeholder={field.placeholder}
                                multiline={
                                    field.type === "textarea" ? 4 : false
                                }
                                type={
                                    field.type === "number" ? "number" : "text"
                                }
                                disabled={!isConnected}
                                required={field.required}
                                variables={triggerVariables || []}
                            />
                        )}
                    </div>
                ))}

                {!definition.fields ||
                    (definition.fields.length === 0 && (
                        <Text tone="subdued">
                            This action has no configurable fields.
                        </Text>
                    ))}
            </BlockStack>
        );
    };

    return (
        <Box padding="400" background="bg-surface" minHeight="100%">
            <BlockStack gap="400">
                <InlineStack align="space-between" blockAlign="center">
                    <BlockStack gap="050">
                        <Text variant="headingMd" as="h3">
                            {node.data.label || node.type}
                        </Text>
                        <Text variant="bodyXs" tone="subdued">
                            ID: {node.id}
                        </Text>
                    </BlockStack>
                    {onClose && (
                        <Button variant="plain" onClick={onClose} size="slim">
                            Close
                        </Button>
                    )}
                </InlineStack>
                <Divider />
                <FormLayout>{renderFields()}</FormLayout>
            </BlockStack>
        </Box>
    );
}

function getAllTriggers(definitions) {
    const options = [{ label: "Select Event", value: "" }];
    definitions?.apps?.forEach((app) => {
        app.triggers?.forEach((t) => {
            options.push({ label: t.label, value: t.settings.topic });
        });
    });
    return options;
}

const ConditionSettings = ({ settings, onChange }) => {
    const rules = settings.rules || [{ field: "", operator: "=", value: "" }];

    const updateRule = (index, key, val) => {
        const newRules = [...rules];
        newRules[index] = { ...newRules[index], [key]: val };
        onChange("rules", newRules);
    };

    const addRule = () => {
        onChange("rules", [...rules, { field: "", operator: "=", value: "" }]);
    };

    const removeRule = (index) => {
        const newRules = rules.filter((_, i) => i !== index);
        onChange(
            "rules",
            newRules.length > 0
                ? newRules
                : [{ field: "", operator: "=", value: "" }]
        );
    };

    const commonFields = [
        { label: "Custom Path", value: "" },
        { label: "Order Total Price", value: "total_price" },
        { label: "Order Subtotal", value: "subtotal_price" },
        { label: "Order Tags", value: "tags" },
        { label: "Customer Tags", value: "customer.tags" },
        { label: "Line Items Count", value: "line_items.length" },
        { label: "Shipping Country", value: "shipping_address.country_code" },
        { label: "Product Title", value: "title" },
        { label: "Product Type", value: "product_type" },
        { label: "Customer Email", value: "email" },
    ];

    const operators = [
        { label: "Equals", value: "=" },
        { label: "Not Equals", value: "!=" },
        { label: "Greater Than", value: ">" },
        { label: "Less Than", value: "<" },
        { label: "Greater or Equal", value: ">=" },
        { label: "Less or Equal", value: "<=" },
        { label: "Contains", value: "contains" },
        { label: "Not Contains", value: "not_contains" },
    ];

    return (
        <BlockStack gap="400">
            <Select
                label="Match Rules Using Logic"
                options={[
                    { label: "All rules must match (AND)", value: "AND" },
                    { label: "Any rule can match (OR)", value: "OR" },
                ]}
                value={settings.logic || "AND"}
                onChange={(v) => onChange("logic", v)}
            />

            <Divider />

            {rules.map((rule, index) => (
                <Box
                    key={index}
                    padding="300"
                    background="bg-surface-secondary"
                    borderRadius="200"
                >
                    <BlockStack gap="300">
                        <InlineStack align="space-between">
                            <Text variant="headingSm">Rule {index + 1}</Text>
                            {rules.length > 1 && (
                                <Button
                                    tone="critical"
                                    variant="plain"
                                    onClick={() => removeRule(index)}
                                    size="slim"
                                >
                                    Remove
                                </Button>
                            )}
                        </InlineStack>

                        <Select
                            label="Quick Fields"
                            options={commonFields}
                            value={
                                commonFields.find((f) => f.value === rule.field)
                                    ?.value || ""
                            }
                            onChange={(v) => {
                                if (v) updateRule(index, "field", v);
                            }}
                        />

                        <TextField
                            label="Field Path"
                            value={rule.field || ""}
                            onChange={(v) => updateRule(index, "field", v)}
                            placeholder="e.g. total_price"
                            autoComplete="off"
                        />

                        <Select
                            label="Operator"
                            options={operators}
                            value={rule.operator || "="}
                            onChange={(v) => updateRule(index, "operator", v)}
                        />

                        <TextField
                            label="Value"
                            value={rule.value || ""}
                            onChange={(v) => updateRule(index, "value", v)}
                            autoComplete="off"
                        />
                    </BlockStack>
                </Box>
            ))}

            <Button onClick={addRule} fullWidth>
                Add Another Rule
            </Button>
        </BlockStack>
    );
};
