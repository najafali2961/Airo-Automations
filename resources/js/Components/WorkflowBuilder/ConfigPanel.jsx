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
} from "@shopify/polaris";

export default function ConfigPanel({ node, definitions, onUpdate, onClose }) {
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
                                required={field.required}
                            />
                        ) : (
                            <TextField
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
                                required={field.required}
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
    const rules = settings.rules || [{}];
    const rule = rules[0];

    const updateRule = (key, val) => {
        const newRule = { ...rule, [key]: val };
        onChange("rules", [newRule]);
    };

    return (
        <BlockStack gap="400">
            <Select
                label="Logic"
                options={[
                    { label: "AND", value: "AND" },
                    { label: "OR", value: "OR" },
                ]}
                value={settings.logic || "AND"}
                onChange={(v) => onChange("logic", v)}
            />
            <Divider />
            <Text variant="headingSm">Rule</Text>
            <TextField
                label="Field Path"
                value={rule.field || ""}
                onChange={(v) => updateRule("field", v)}
                placeholder="order.total_price"
                helpText="Use dot notation for nested fields"
            />
            <Select
                label="Operator"
                options={[
                    { label: "Equals", value: "=" },
                    { label: "Not Equals", value: "!=" },
                    { label: "Greater Than", value: ">" },
                    { label: "Less Than", value: "<" },
                    { label: "Contains", value: "contains" },
                ]}
                value={rule.operator || "="}
                onChange={(v) => updateRule("operator", v)}
            />
            <TextField
                label="Value"
                value={rule.value || ""}
                onChange={(v) => updateRule("value", v)}
            />
        </BlockStack>
    );
};
