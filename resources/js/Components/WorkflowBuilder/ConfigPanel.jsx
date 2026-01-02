import React, { useState, useEffect } from "react";
import {
    Box,
    BlockStack,
    Text,
    FormLayout,
    TextField,
    Select,
    Button,
    InlineStack,
    Banner,
} from "@shopify/polaris";

export default function ConfigPanel({ node, onUpdate }) {
    const [settings, setSettings] = useState(node.data.settings || {});

    useEffect(() => {
        setSettings(node.data.settings || {});
    }, [node]);

    const updateSetting = (key, value) => {
        const newSettings = { ...settings, [key]: value };
        setSettings(newSettings);
        // Helper to update displayed label if needed
        let newLabel = node.data.label;
        if (node.type === "trigger" && key === "event") newLabel = value;

        onUpdate(node.id, {
            settings: newSettings,
            label: newLabel,
        });
    };

    if (!node)
        return (
            <Box padding="400">
                <Text>Select a node</Text>
            </Box>
        );

    const renderContent = () => {
        switch (node.type) {
            case "trigger":
                return (
                    <TriggerSettings
                        settings={settings}
                        onChange={updateSetting}
                    />
                );
            case "condition":
                return (
                    <ConditionSettings
                        settings={settings}
                        onChange={updateSetting}
                    />
                );
            case "action":
                return (
                    <ActionSettings
                        settings={settings}
                        onChange={updateSetting}
                    />
                );
            default:
                return <Text>Unknown node type: {node.type}</Text>;
        }
    };

    return (
        <Box padding="400" background="bg-surface" minHeight="100%">
            <BlockStack gap="400">
                <Box
                    borderBlockEndWidth="025"
                    borderColor="border"
                    paddingBlockEnd="400"
                >
                    <Text variant="headingMd" as="h3">
                        {node.data.label || node.type}
                    </Text>
                    <Text variant="bodySm" tone="subdued">
                        ID: {node.id}
                    </Text>
                </Box>
                <FormLayout>{renderContent()}</FormLayout>
            </BlockStack>
        </Box>
    );
}

const TriggerSettings = ({ settings, onChange }) => (
    <Select
        label="Shopify Event"
        options={[
            { label: "Select Event", value: "" },
            { label: "Order Created", value: "orders/create" },
            { label: "Order Updated", value: "orders/updated" },
            { label: "Order Paid", value: "orders/paid" },
            { label: "Product Created", value: "products/create" },
            { label: "Product Updated", value: "products/update" },
            { label: "Customer Created", value: "customers/create" },
            { label: "Fulfillment Created", value: "fulfillments/create" },
        ]}
        value={settings.event}
        onChange={(val) => onChange("event", val)}
    />
);

const ActionSettings = ({ settings, onChange }) => (
    <>
        <Select
            label="Action Type"
            options={[
                { label: "Select Action", value: "" },
                { label: "Send Email", value: "send_email" },
                { label: "Apply Discount", value: "apply_discount" },
                { label: "Add Tag", value: "add_tag" },
                { label: "Update Note", value: "update_order_note" },
                { label: "Call Webhook", value: "call_webhook" },
            ]}
            value={settings.action}
            onChange={(val) => onChange("action", val)}
        />

        {settings.action === "send_email" && (
            <>
                <TextField
                    label="Recipient (Path)"
                    value={settings.recipient}
                    onChange={(v) => onChange("recipient", v)}
                    helpText="e.g. customer.email"
                />
                <TextField
                    label="Template"
                    value={settings.template}
                    onChange={(v) => onChange("template", v)}
                />
                <TextField
                    label="Subject"
                    value={settings.subject}
                    onChange={(v) => onChange("subject", v)}
                />
            </>
        )}

        {settings.action === "add_tag" && (
            <>
                <Select
                    label="Target"
                    options={[
                        { label: "Product", value: "product" },
                        { label: "Customer", value: "customer" },
                        { label: "Order", value: "order" },
                    ]}
                    value={settings.object_type}
                    onChange={(v) => onChange("object_type", v)}
                />
                <TextField
                    label="Tags (comma separated)"
                    value={settings.tags}
                    onChange={(v) => onChange("tags", v)}
                />
            </>
        )}

        {settings.action === "apply_discount" && (
            <>
                <TextField
                    label="Discount Code"
                    value={settings.code}
                    onChange={(v) => onChange("code", v)}
                />
                <TextField
                    label="Value"
                    type="number"
                    value={settings.discount_value}
                    onChange={(v) => onChange("discount_value", v)}
                />
            </>
        )}
    </>
);

const ConditionSettings = ({ settings, onChange }) => {
    // Simplified single rule editor for MVP
    const rules = settings.rules || [{}];
    const rule = rules[0];

    const updateRule = (key, val) => {
        const newRule = { ...rule, [key]: val };
        onChange("rules", [newRule]);
    };

    return (
        <Box>
            <BlockStack gap="200">
                <Select
                    label="Logic"
                    options={[
                        { label: "AND", value: "AND" },
                        { label: "OR", value: "OR" },
                    ]}
                    value={settings.logic || "AND"}
                    onChange={(v) => onChange("logic", v)}
                />
                <Text variant="headingSm">Rule 1</Text>
                <TextField
                    label="Field Path"
                    value={rule.field}
                    onChange={(v) => updateRule("field", v)}
                    placeholder="order.total_price"
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
                    value={rule.operator}
                    onChange={(v) => updateRule("operator", v)}
                />
                <TextField
                    label="Value"
                    value={rule.value}
                    onChange={(v) => updateRule("value", v)}
                />
            </BlockStack>
        </Box>
    );
};
