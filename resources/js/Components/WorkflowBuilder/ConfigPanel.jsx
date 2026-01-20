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
import { router } from "@inertiajs/react";
import VariableInput from "./VariableInput";
import ResourceSelect from "./ResourceSelect";
import { getIconAndColor } from "./utils";

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
                        (settings.topic && t.topic === settings.topic) ||
                        (settings.topic &&
                            t.settings?.topic === settings.topic) ||
                        t.label === node.data.label,
                );
                if (trigger) return trigger;
            } else if (node.type === "action") {
                const action = app.actions?.find(
                    (a) =>
                        a.settings?.action === settings.action ||
                        a.label === node.data.label,
                );
                if (action) return action;
            }
        }
        return null;
    }, [node, definitions, settings.topic, settings.action]);

    useEffect(() => {
        setSettings(node.data.settings || {});
    }, [node]);

    // Self-Healing Effect:
    // If we matched a trigger definition (e.g. by label) but the node settings are missing the topic,
    // auto-patch the settings to include the topic from the definition.
    useEffect(() => {
        if (
            node.type === "trigger" &&
            definition &&
            definition.topic &&
            !settings.topic
        ) {
            console.log(
                "ConfigPanel: Auto-repairing missing topic",
                definition.topic,
            );
            updateSetting("topic", definition.topic);
        }
    }, [definition, settings.topic, node.type]);

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
        // Actually, FlowController sets 'group'.
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
                app.triggers?.some((t) => t.label === definition.label),
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
                    triggerVariables={triggerVariables}
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
                    <div className="rounded-xl border border-red-200 bg-red-50 p-4 animate-in fade-in slide-in-from-top-2 duration-300">
                        <BlockStack gap="300">
                            <div className="flex items-start gap-3">
                                <div className="p-2 bg-red-100 rounded-lg text-red-600">
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        width="20"
                                        height="20"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        strokeWidth="2"
                                        strokeLinecap="round"
                                        strokeLinejoin="round"
                                    >
                                        <circle cx="12" cy="12" r="10" />
                                        <line x1="12" x2="12" y1="8" y2="12" />
                                        <line
                                            x1="12"
                                            x2="12.01"
                                            y1="16"
                                            y2="16"
                                        />
                                    </svg>
                                </div>
                                <div className="flex-1">
                                    <Text variant="headingSm" tone="critical">
                                        Connection Inactive
                                    </Text>
                                    <Text variant="bodySm" tone="critical">
                                        You need to connect{" "}
                                        <strong>
                                            {definition.group || "this app"}
                                        </strong>{" "}
                                        before you can configure this action.
                                    </Text>
                                </div>
                            </div>

                            <Button
                                variant="primary"
                                tone="critical"
                                onClick={() => router.visit("/connectors")}
                            >
                                Connect Now
                            </Button>
                        </BlockStack>
                    </div>
                )}

                {definition.description && (
                    <Text variant="bodySm" tone="subdued">
                        {definition.description}
                    </Text>
                )}

                {definition.fields?.map((field) => {
                    // Logic for conditional visibility (showIf)
                    if (field.showIf) {
                        const {
                            field: depField,
                            value: depValue,
                            operator,
                        } = field.showIf;
                        // Resolve current value of the dependency, falling back to its default
                        const depDef = definition.fields.find(
                            (f) => f.name === depField,
                        );
                        const effectiveVal =
                            settings[depField] ?? depDef?.default;

                        let isVisible = true;
                        if (operator === "!=") {
                            isVisible = effectiveVal !== depValue;
                        } else {
                            // Default to equals
                            isVisible = effectiveVal === depValue;
                        }

                        if (!isVisible) return null;
                    }

                    return (
                        <div
                            key={field.name}
                            className="animate-in fade-in slide-in-from-top-1 duration-200"
                        >
                            {field.type === "select" ? (
                                <Select
                                    label={field.label}
                                    options={field.options}
                                    value={
                                        settings[field.name] ||
                                        field.default ||
                                        ""
                                    }
                                    onChange={(val) =>
                                        updateSetting(field.name, val)
                                    }
                                    disabled={!isConnected}
                                    required={field.required}
                                />
                            ) : field.type === "resource_select" ? (
                                <ResourceSelect
                                    label={field.label}
                                    value={settings[field.name] || ""}
                                    onChange={(val) =>
                                        updateSetting(field.name, val)
                                    }
                                    service={(function () {
                                        // 1. Try explicit app name from node data or definition
                                        if (node.data.appName)
                                            return node.data.appName;
                                        if (
                                            definition.app &&
                                            definition.app !== "shopify"
                                        )
                                            return definition.app;

                                        // 2. Fallback based on group/category mappings
                                        const group = definition.group;
                                        if (group === "marketing")
                                            return "klaviyo";
                                        if (group === "productivity")
                                            return "google";
                                        if (group === "communication") {
                                            if (
                                                definition.icon ===
                                                "MessageSquare"
                                            )
                                                return "slack";
                                            if (
                                                definition.icon === "Mail" &&
                                                definition.label.includes(
                                                    "Gmail",
                                                )
                                            )
                                                return "google";
                                            if (
                                                definition.icon === "Mail" &&
                                                definition.label.includes(
                                                    "SMTP",
                                                )
                                            )
                                                return "smtp";
                                        }

                                        // 3. Last resort
                                        return group;
                                    })()}
                                    resource={field.resource}
                                    placeholder={field.placeholder}
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
                                        field.type === "number"
                                            ? "number"
                                            : "text"
                                    }
                                    disabled={!isConnected}
                                    required={field.required}
                                    variables={triggerVariables || []}
                                />
                            )}
                        </div>
                    );
                })}

                {!definition.fields ||
                    (definition.fields.length === 0 && (
                        <Text tone="subdued">
                            This action has no configurable fields.
                        </Text>
                    ))}
            </BlockStack>
        );
    };

    const { icon, color, isUrl } = getIconAndColor(
        node.data.appName || node.data.label || node.type,
    );

    return (
        <div className="h-full flex flex-col bg-white">
            {/* Header */}
            <div className="relative">
                <div className={`h-2 w-full ${color}`} />
                <div className="p-4 border-b border-gray-100 flex justify-between items-start">
                    <div className="flex gap-3 items-center">
                        <div className="flex items-center justify-center w-12 h-12 rounded-xl bg-gray-50 border border-gray-100 text-2xl shadow-sm overflow-hidden">
                            {isUrl ? (
                                <img
                                    src={icon}
                                    alt=""
                                    className="w-8 h-8 object-contain"
                                />
                            ) : (
                                icon
                            )}
                        </div>
                        <div>
                            <Text variant="headingMd" as="h2">
                                {node.data.label || node.type}
                            </Text>
                            <Text variant="bodyXs" tone="subdued">
                                {node.type.toUpperCase()} • ID: {node.id}
                            </Text>
                        </div>
                    </div>
                    {onClose && (
                        <button
                            onClick={onClose}
                            className="text-gray-400 hover:text-gray-600 p-1 rounded hover:bg-gray-50 transition-colors"
                        >
                            ✕
                        </button>
                    )}
                </div>
            </div>

            {/* Scrollable Content */}
            <div className="flex-1 overflow-y-auto p-4 custom-scrollbar">
                <Box paddingBlockEnd="400">
                    <BlockStack gap="500">
                        <FormLayout>{renderFields()}</FormLayout>
                    </BlockStack>
                </Box>
            </div>
        </div>
    );
}

function getAllTriggers(definitions) {
    const options = [{ label: "Select Event", value: "" }];
    definitions?.apps?.forEach((app) => {
        app.triggers?.forEach((t) => {
            options.push({
                label: t.label,
                value: t.topic || t.settings?.topic,
            });
        });
    });
    return options;
}

const ConditionSettings = ({ settings, onChange, triggerVariables }) => {
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
                : [{ field: "", operator: "=", value: "" }],
        );
    };

    const commonFields = [
        { label: "Custom Path", value: "" },
        ...(triggerVariables || []).map((v) => ({
            label: v.label,
            value: v.value,
        })),
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
