import React, { useEffect, useState } from "react";
import {
    FormLayout,
    TextField,
    Text,
    Select,
    LegacyCard,
} from "@shopify/polaris";

export default function PropertiesPanel({ selectedNode, onChange }) {
    const [data, setData] = useState({});

    useEffect(() => {
        if (selectedNode) {
            setData(selectedNode.data || {});
        }
    }, [selectedNode]);

    const handleChange = (field, value) => {
        const newData = { ...data, [field]: value };
        setData(newData);
        if (selectedNode) {
            onChange(selectedNode.id, newData);
        }
    };

    if (!selectedNode) {
        return (
            <div className="w-80 border-l border-gray-200 bg-white p-4 h-full">
                <Text tone="subdued" as="p">
                    Select a node to view properties.
                </Text>
            </div>
        );
    }

    return (
        <div className="w-80 border-l border-gray-200 bg-white p-4 h-full overflow-y-auto">
            <Text variant="headingMd" as="h3">
                Properties
            </Text>
            <div className="mt-4">
                <FormLayout>
                    {/* Common Fields */}
                    <TextField
                        label="Label"
                        value={data.label}
                        onChange={(val) => handleChange("label", val)}
                        autoComplete="off"
                    />

                    {/* Trigger Specific Fields */}
                    {selectedNode.type === "shopifyTrigger" && (
                        <Select
                            label="Shopify Topic"
                            options={[
                                {
                                    label: "Order Created",
                                    value: "orders/create",
                                },
                                { label: "Order Paid", value: "orders/paid" },
                                {
                                    label: "Product Created",
                                    value: "products/create",
                                },
                                {
                                    label: "Customer Created",
                                    value: "customers/create",
                                },
                            ]}
                            value={data.topic}
                            onChange={(val) => handleChange("topic", val)}
                        />
                    )}

                    {/* Action Specific Fields */}
                    {selectedNode.type === "action" && (
                        <Select
                            label="Action Type"
                            options={[
                                {
                                    label: "Add Customer Tag",
                                    value: "add_customer_tag",
                                },
                                { label: "Send Email", value: "send_email" },
                                {
                                    label: "HTTP Request",
                                    value: "http_request",
                                },
                            ]}
                            value={data.actionType}
                            onChange={(val) => handleChange("actionType", val)}
                        />
                    )}

                    <div className="pt-4 border-t border-gray-100 mt-4">
                        <Text variant="bodySm" tone="subdued">
                            Node ID: {selectedNode.id}
                        </Text>
                        <Text variant="bodySm" tone="subdued">
                            Type: {selectedNode.type}
                        </Text>
                    </div>
                </FormLayout>
            </div>
        </div>
    );
}
