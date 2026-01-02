import React, { useState } from "react";
import {
    Box,
    BlockStack,
    Text,
    Icon,
    InlineStack,
    Button,
    Divider,
} from "@shopify/polaris";
import {
    AlertCircleIcon,
    ArrowRightIcon,
    CheckIcon,
    AppsIcon,
    ChevronRightIcon,
    ChevronLeftIcon,
} from "@shopify/polaris-icons";

export default function Sidebar({ definitions }) {
    const [view, setView] = useState("categories"); // categories | app | list
    const [selectedApp, setSelectedApp] = useState(null);

    const standardNodes = [
        {
            type: "condition",
            label: "Condition",
            icon: CheckIcon,
            color: "bg-fill-warning",
            group: "Logic",
        },
    ];

    const apps = definitions?.apps || [];

    // When an app is selected, show its triggers and actions
    const handleAppClick = (app) => {
        setSelectedApp(app);
        setView("app");
    };

    const handleBack = () => {
        setView("categories");
        setSelectedApp(null);
    };

    const onDragStart = (event, node) => {
        event.dataTransfer.setData("application/reactflow", node.type);
        event.dataTransfer.setData("application/reactflow/label", node.label);
        // Pass extra data for Shopify nodes
        if (node.n8nType) {
            event.dataTransfer.setData(
                "application/reactflow/n8nType",
                node.n8nType
            );
            event.dataTransfer.setData(
                "application/reactflow/defaults",
                JSON.stringify(node.settings || {})
            );
        }
        event.dataTransfer.effectAllowed = "move";
    };

    const renderNodeItem = (node) => (
        <div
            key={node.label}
            draggable
            onDragStart={(e) => onDragStart(e, node)}
            style={{ cursor: "grab" }}
        >
            <Box
                padding="300"
                background="bg-surface"
                borderRadius="200"
                shadow="100"
                borderColor="border"
                borderWidth="025"
            >
                <InlineStack gap="300" align="start" blockAlign="center">
                    <div style={{ color: node.color || "#5c5f62" }}>
                        <Icon
                            source={node.icon || ArrowRightIcon}
                            tone="inherit"
                        />
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
            </Box>
        </div>
    );

    return (
        <Box padding="300" background="bg-surface" minHeight="100%">
            <BlockStack gap="400">
                {view === "categories" && (
                    <BlockStack gap="400">
                        <Text variant="headingSm">Logic</Text>
                        {standardNodes.map(renderNodeItem)}

                        <Divider />

                        <Text variant="headingSm">Apps</Text>
                        {apps.map((app) => (
                            <div
                                key={app.name}
                                onClick={() => handleAppClick(app)}
                                className="cursor-pointer group"
                            >
                                <Box
                                    padding="300"
                                    background="bg-surface"
                                    borderRadius="200"
                                    shadow="100"
                                    borderColor="border"
                                    borderWidth="025"
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
                                            <Box
                                                background="bg-fill-success"
                                                borderRadius="100"
                                                padding="050"
                                            >
                                                <Icon
                                                    source={AppsIcon}
                                                    tone="base"
                                                />
                                            </Box>
                                            <Text fontWeight="bold">
                                                {app.name}
                                            </Text>
                                        </InlineStack>
                                        <Icon
                                            source={ChevronRightIcon}
                                            tone="subdued"
                                        />
                                    </InlineStack>
                                </Box>
                            </div>
                        ))}
                    </BlockStack>
                )}

                {view === "app" && selectedApp && (
                    <BlockStack gap="400">
                        <Button
                            icon={ChevronLeftIcon}
                            onClick={handleBack}
                            variant="plain"
                        >
                            Back to Apps
                        </Button>
                        <Text variant="headingMd">{selectedApp.name}</Text>

                        {selectedApp.triggers?.length > 0 && (
                            <BlockStack gap="200">
                                <Text variant="headingSm" tone="subdued">
                                    Triggers
                                </Text>
                                {selectedApp.triggers.map((trigger) =>
                                    renderNodeItem({
                                        ...trigger,
                                        icon: AlertCircleIcon,
                                        color: "bg-fill-success",
                                    })
                                )}
                            </BlockStack>
                        )}

                        {selectedApp.actions?.length > 0 && (
                            <BlockStack gap="200">
                                <Text variant="headingSm" tone="subdued">
                                    Actions
                                </Text>
                                {selectedApp.actions.map((action) =>
                                    renderNodeItem({
                                        ...action,
                                        icon: ArrowRightIcon,
                                        color: "bg-fill-info",
                                    })
                                )}
                            </BlockStack>
                        )}
                    </BlockStack>
                )}
            </BlockStack>
        </Box>
    );
}
