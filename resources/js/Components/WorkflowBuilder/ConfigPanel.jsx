import React, { useState, useEffect } from "react";
import {
    LegacyCard,
    FormLayout,
    TextField,
    Select,
    Text,
    Box,
    BlockStack
} from "@shopify/polaris";

export default function ConfigPanel({ node, onUpdate }) {
    const [config, setConfig] = useState(node?.data?.config || {});

    useEffect(() => {
        setConfig(node?.data?.config || {});
    }, [node]);

    const handleInputChange = (field, value) => {
        const updatedConfig = { ...config, [field]: value };
        setConfig(updatedConfig);
        if (node) {
            onUpdate(node.id, updatedConfig);
        }
    };

    if (!node) {
        return (
            <Box padding="400" background="bg-surface" minHeight="100%">
                <Text tone="subdued" as="p">
                    Select a node to configure
                </Text>
            </Box>
        );
    }

    const renderConfigFields = () => {
        const type = node.data?.type || node.type; // Handle both structure styles if needed

        switch (type) {
            case "trigger":
            case "shopifyTrigger":
                return (
                    <FormLayout>
                        <Select
                            label="Trigger Type"
                            options={[
                                { label: "Select...", value: "" },
                                { label: "All Orders", value: "orders/create" },
                                { label: "Paid Orders", value: "orders/paid" },
                                {
                                    label: "Draft Orders",
                                    value: "draft_orders/create",
                                },
                            ]}
                            value={config.triggerType || ""}
                            onChange={(val) =>
                                handleInputChange("triggerType", val)
                            }
                        />
                    </FormLayout>
                );

            case "action":
            case "send_email":
                return (
                    <FormLayout>
                        <Select
                            label="Action Type"
                            options={[
                                { label: "Send Email", value: "send_email" },
                                { label: "Send Slack", value: "send_slack" },
                            ]}
                            value={config.type || "send_email"}
                            onChange={(val) => handleInputChange("type", val)}
                        />
                        {(!config.type || config.type === "send_email") && (
                            <>
                                <TextField
                                    label="From Email"
                                    value={config.from || ""}
                                    onChange={(val) =>
                                        handleInputChange("from", val)
                                    }
                                    autoComplete="off"
                                    placeholder="sender@example.com"
                                />
                                <TextField
                                    label="To Email"
                                    value={config.to || ""}
                                    onChange={(val) =>
                                        handleInputChange("to", val)
                                    }
                                    autoComplete="off"
                                    placeholder="recipient@example.com"
                                />
                                <TextField
                                    label="Subject"
                                    value={config.subject || ""}
                                    onChange={(val) =>
                                        handleInputChange("subject", val)
                                    }
                                    autoComplete="off"
                                />
                                <TextField
                                    label="Body"
                                    value={config.body || ""}
                                    onChange={(val) =>
                                        handleInputChange("body", val)
                                    }
                                    autoComplete="off"
                                    multiline={4}
                                />
                            </>
                        )}
                        {config.type === "send_slack" && (
                            <>
                                <TextField
                                    label="Channel"
                                    value={config.channel || ""}
                                    onChange={(val) =>
                                        handleInputChange("channel", val)
                                    }
                                    autoComplete="off"
                                    placeholder="#general"
                                />
                                <TextField
                                    label="Message"
                                    value={config.message || ""}
                                    onChange={(val) =>
                                        handleInputChange("message", val)
                                    }
                                    autoComplete="off"
                                    multiline={4}
                                />
                            </>
                        )}
                    </FormLayout>
                );

            case "condition":
                return (
                    <FormLayout>
                        <TextField
                            label="Field"
                            value={config.field || ""}
                            onChange={(val) => handleInputChange("field", val)}
                            autoComplete="off"
                            placeholder="e.g. total_price"
                        />
                        <Select
                            label="Operator"
                            options={[
                                { label: "Equals", value: "equals" },
                                { label: "Not Equals", value: "notEquals" },
                                { label: "Greater Than", value: "greaterThan" },
                                { label: "Less Than", value: "lessThan" },
                                { label: "Contains", value: "contains" },
                            ]}
                            value={config.operator || "equals"}
                            onChange={(val) =>
                                handleInputChange("operator", val)
                            }
                        />
                        <TextField
                            label="Value"
                            value={config.value || ""}
                            onChange={(val) => handleInputChange("value", val)}
                            autoComplete="off"
                        />
                    </FormLayout>
                );

            default:
                return (
                    <Text as="p">No configuration available for {type}</Text>
                );
        }
    };

    return (
        <Box padding="400" minHeight="100%" background="bg-surface">
            <BlockStack gap="400">
                <Box borderBlockEndWidth="025" borderColor="border" paddingBlockEnd="400">
                    <BlockStack gap="100">
                        <Text variant="headingMd" as="h3">
                            {node.data?.label || node.type}
                        </Text>
                        <Text variant="bodySm" tone="subdued">
                            {node.data?.type || node.type}
                        </Text>
                    </BlockStack>
                </Box>
                {renderConfigFields()}
            </BlockStack>
        </Box>
    );
}
