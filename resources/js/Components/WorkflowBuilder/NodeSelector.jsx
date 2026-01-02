import React, { useState } from "react";
import {
    Modal,
    Box,
    BlockStack,
    Text,
    Icon,
    InlineStack,
    Divider,
} from "@shopify/polaris";
import {
    AlertCircleIcon,
    ArrowRightIcon,
    CheckIcon,
    AppsIcon,
    ChevronRightIcon,
} from "@shopify/polaris-icons";

export default function NodeSelector({ open, onClose, onSelect, definitions }) {
    const [view, setView] = useState("categories");
    const [selectedApp, setSelectedApp] = useState(null);

    const handleAppClick = (app) => {
        setSelectedApp(app);
        setView("app");
    };

    const handleBack = () => {
        setView("categories");
        setSelectedApp(null);
    };

    const handleSelect = (node) => {
        onSelect(node);
        onClose();
        // Reset view for next time
        setTimeout(() => handleBack(), 300);
    };

    const standardNodes = [
        {
            type: "condition",
            label: "Condition",
            icon: CheckIcon,
            group: "Logic",
            color: "text-amber-600",
        },
    ];

    const apps = definitions?.apps || [];

    const renderItem = (node, onClick) => (
        <div
            key={node.label}
            className="cursor-pointer hover:bg-gray-50 p-3 rounded-md border border-gray-200"
            onClick={onClick}
        >
            <InlineStack gap="300" align="start" blockAlign="center">
                <div style={{ color: node.color || "#5c5f62" }}>
                    <Icon source={node.icon} tone="inherit" />
                </div>
                <BlockStack gap="050">
                    <Text fontWeight="bold" variant="bodySm">
                        {node.label}
                    </Text>
                    {node.description && (
                        <Text variant="bodyXs" tone="subdued">
                            {node.description}
                        </Text>
                    )}
                </BlockStack>
            </InlineStack>
        </div>
    );

    return (
        <Modal
            open={open}
            onClose={onClose}
            title="Add to Workflow"
            footer={
                view === "app" ? (
                    <button
                        onClick={handleBack}
                        className="text-sm text-blue-600 font-medium px-4"
                    >
                        ← Back to Categories
                    </button>
                ) : null
            }
        >
            <Modal.Section>
                <div className="min-h-[300px]">
                    {view === "categories" && (
                        <BlockStack gap="400">
                            <Text variant="headingSm">Logic</Text>
                            {standardNodes.map((n) =>
                                renderItem(n, () => handleSelect(n))
                            )}

                            <Divider />
                            <Text variant="headingSm">Apps</Text>
                            {apps.map((app) => (
                                <div
                                    key={app.name}
                                    onClick={() => handleAppClick(app)}
                                    className="cursor-pointer hover:bg-gray-50 p-3 rounded-md border border-gray-200 group"
                                >
                                    <InlineStack
                                        align="space-between"
                                        blockAlign="center"
                                    >
                                        <InlineStack
                                            gap="300"
                                            align="start"
                                            blockAlign="center"
                                        >
                                            <Icon
                                                source={AppsIcon}
                                                tone="base"
                                            />
                                            <Text fontWeight="bold">
                                                {app.name}
                                            </Text>
                                        </InlineStack>
                                        <Icon
                                            source={ChevronRightIcon}
                                            tone="subdued"
                                        />
                                    </InlineStack>
                                </div>
                            ))}
                        </BlockStack>
                    )}

                    {view === "app" && selectedApp && (
                        <BlockStack gap="400">
                            <Text variant="headingMd">{selectedApp.name}</Text>
                            {selectedApp.triggers?.length > 0 && (
                                <BlockStack gap="200">
                                    <Text variant="headingSm" tone="subdued">
                                        Triggers
                                    </Text>
                                    {selectedApp.triggers.map((t) =>
                                        renderItem(
                                            {
                                                ...t,
                                                icon: AlertCircleIcon,
                                                color: "#008060",
                                            },
                                            () =>
                                                handleSelect({
                                                    ...t,
                                                    type: "trigger",
                                                })
                                        )
                                    )}
                                </BlockStack>
                            )}
                            {selectedApp.actions?.length > 0 && (
                                <BlockStack gap="200">
                                    <Text variant="headingSm" tone="subdued">
                                        Actions
                                    </Text>
                                    {selectedApp.actions.map((a) =>
                                        renderItem(
                                            {
                                                ...a,
                                                icon: ArrowRightIcon,
                                                color: "#0070f3",
                                            },
                                            () =>
                                                handleSelect({
                                                    ...a,
                                                    type: "action",
                                                })
                                        )
                                    )}
                                </BlockStack>
                            )}
                        </BlockStack>
                    )}
                </div>
            </Modal.Section>
        </Modal>
    );
}
