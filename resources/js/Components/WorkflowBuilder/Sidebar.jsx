import React, { useState, useMemo } from "react";
import {
    Box,
    BlockStack,
    Text,
    Icon,
    InlineStack,
    Button,
    Divider,
    TextField,
} from "@shopify/polaris";
import {
    AlertCircleIcon,
    ArrowRightIcon,
    CheckIcon,
    AppsIcon,
    ChevronRightIcon,
    ChevronLeftIcon,
    SearchIcon,
} from "@shopify/polaris-icons";

export default function Sidebar({ definitions }) {
    const [view, setView] = useState("categories"); // categories | app | search
    const [selectedApp, setSelectedApp] = useState(null);
    const [searchQuery, setSearchQuery] = useState("");

    const standardNodes = [
        {
            type: "condition",
            label: "Condition",
            icon: CheckIcon,
            color: "#E29100",
            description: "Branch your workflow based on rules",
            group: "Logic",
        },
    ];

    const apps = definitions?.apps || [];

    // Global Search Filtering
    const searchResults = useMemo(() => {
        if (!searchQuery) return null;
        const query = searchQuery.toLowerCase();
        const results = {
            apps: [],
            nodes: [],
        };

        // Search logic nodes
        standardNodes.forEach((node) => {
            if (
                node.label.toLowerCase().includes(query) ||
                node.description?.toLowerCase().includes(query)
            ) {
                results.nodes.push({ ...node, category: "Logic" });
            }
        });

        // Search apps and their nodes
        apps.forEach((app) => {
            let matchedApp = false;
            if (app.name.toLowerCase().includes(query)) {
                results.apps.push(app);
                matchedApp = true;
            }

            app.triggers?.forEach((t) => {
                if (
                    t.label.toLowerCase().includes(query) ||
                    t.description?.toLowerCase().includes(query)
                ) {
                    results.nodes.push({
                        ...t,
                        type: "trigger",
                        category: app.name,
                        icon: AlertCircleIcon,
                        color: "#008060",
                    });
                }
            });

            app.actions?.forEach((a) => {
                if (
                    a.label.toLowerCase().includes(query) ||
                    a.description?.toLowerCase().includes(query)
                ) {
                    results.nodes.push({
                        ...a,
                        type: "action",
                        category: app.name,
                        icon: ArrowRightIcon,
                        color: "#0070f3",
                    });
                }
            });
        });

        return results;
    }, [searchQuery, apps]);

    const handleSearchChange = (value) => {
        setSearchQuery(value);
        if (value && view !== "search") {
            setView("search");
        } else if (!value && view === "search") {
            setView("categories");
        }
    };

    const handleAppClick = (app) => {
        setSelectedApp(app);
        setView("app");
    };

    const handleBack = () => {
        setView("categories");
        setSelectedApp(null);
        setSearchQuery("");
    };

    const onDragStart = (event, node) => {
        event.dataTransfer.setData("application/reactflow", node.type);
        event.dataTransfer.setData("application/reactflow/label", node.label);
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
            key={`${node.category}-${node.label}`}
            draggable
            onDragStart={(e) => onDragStart(e, node)}
            className="cursor-grab group"
        >
            <Box
                padding="300"
                background="bg-surface"
                borderRadius="200"
                shadow="100"
                borderColor="border"
                borderWidth="025"
                className="transition-all hover:border-blue-500 hover:shadow-md"
            >
                <InlineStack gap="300" align="start" blockAlign="center">
                    <div
                        style={{
                            color: node.color || "#5c5f62",
                            display: "flex",
                        }}
                    >
                        <Icon
                            source={node.icon || ArrowRightIcon}
                            tone="inherit"
                        />
                    </div>
                    <BlockStack gap="050">
                        <InlineStack gap="200" align="start">
                            <Text fontWeight="bold" variant="bodySm">
                                {node.label}
                            </Text>
                            {node.category && (
                                <Box
                                    background="bg-fill-secondary"
                                    paddingInline="100"
                                    borderRadius="100"
                                >
                                    <Text variant="bodyXs" tone="subdued">
                                        {node.category}
                                    </Text>
                                </Box>
                            )}
                        </InlineStack>
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
        <Box
            padding="400"
            background="bg-surface"
            minHeight="100%"
            borderInlineEndWidth="025"
            borderColor="border"
        >
            <BlockStack gap="400">
                <TextField
                    prefix={<Icon source={SearchIcon} tone="subdued" />}
                    placeholder="Search triggers, actions..."
                    value={searchQuery}
                    onChange={handleSearchChange}
                    autoComplete="off"
                    clearButton
                    onClearButtonClick={() => handleSearchChange("")}
                />

                {view === "categories" && (
                    <BlockStack gap="500">
                        <BlockStack gap="300">
                            <Text variant="headingSm" tone="subdued">
                                Logic
                            </Text>
                            {standardNodes.map((n) =>
                                renderNodeItem({ ...n, category: "Logic" })
                            )}
                        </BlockStack>

                        <Divider />

                        <BlockStack gap="300">
                            <Text variant="headingSm" tone="subdued">
                                Apps
                            </Text>
                            <Box className="grid grid-cols-1 gap-2">
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
                                            className="transition-all hover:bg-gray-50"
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
                                                        borderRadius="150"
                                                        padding="100"
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
                            </Box>
                        </BlockStack>
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
                        <InlineStack gap="300" blockAlign="center">
                            <Box
                                background="bg-fill-success"
                                borderRadius="150"
                                padding="100"
                            >
                                <Icon source={AppsIcon} tone="base" />
                            </Box>
                            <Text variant="headingMd">{selectedApp.name}</Text>
                        </InlineStack>

                        {selectedApp.triggers?.length > 0 && (
                            <BlockStack gap="300">
                                <Text variant="headingSm" tone="subdued">
                                    Triggers
                                </Text>
                                {selectedApp.triggers.map((trigger) =>
                                    renderNodeItem({
                                        ...trigger,
                                        icon: AlertCircleIcon,
                                        color: "#008060",
                                        category: selectedApp.name,
                                    })
                                )}
                            </BlockStack>
                        )}

                        {selectedApp.actions?.length > 0 && (
                            <BlockStack gap="300">
                                <Text variant="headingSm" tone="subdued">
                                    Actions
                                </Text>
                                {selectedApp.actions.map((action) =>
                                    renderNodeItem({
                                        ...action,
                                        icon: ArrowRightIcon,
                                        color: "#0070f3",
                                        category: selectedApp.name,
                                    })
                                )}
                            </BlockStack>
                        )}
                    </BlockStack>
                )}

                {view === "search" && searchResults && (
                    <BlockStack gap="400">
                        <InlineStack align="space-between">
                            <Text variant="headingSm" tone="subdued">
                                Search Results
                            </Text>
                            <Button variant="plain" onClick={handleBack}>
                                Clear
                            </Button>
                        </InlineStack>

                        {searchResults.nodes.length === 0 &&
                            searchResults.apps.length === 0 && (
                                <Box padding="400" textAlign="center">
                                    <Text tone="subdued">
                                        No results found for "{searchQuery}"
                                    </Text>
                                </Box>
                            )}

                        {searchResults.apps.length > 0 && (
                            <BlockStack gap="200">
                                <Text
                                    variant="bodyXs"
                                    fontWeight="bold"
                                    tone="subdued"
                                >
                                    APPS
                                </Text>
                                {searchResults.apps.map((app) => (
                                    <div
                                        key={app.name}
                                        onClick={() => handleAppClick(app)}
                                        className="cursor-pointer"
                                    >
                                        <Box
                                            padding="200"
                                            background="bg-surface"
                                            borderRadius="100"
                                            borderWidth="025"
                                            borderColor="border"
                                        >
                                            <InlineStack
                                                gap="200"
                                                blockAlign="center"
                                            >
                                                <Icon
                                                    source={AppsIcon}
                                                    tone="subdued"
                                                />
                                                <Text>{app.name}</Text>
                                            </InlineStack>
                                        </Box>
                                    </div>
                                ))}
                            </BlockStack>
                        )}

                        {searchResults.nodes.length > 0 && (
                            <BlockStack gap="300">
                                <Text
                                    variant="bodyXs"
                                    fontWeight="bold"
                                    tone="subdued"
                                >
                                    TRIGGERS & ACTIONS
                                </Text>
                                {searchResults.nodes.map(renderNodeItem)}
                            </BlockStack>
                        )}
                    </BlockStack>
                )}
            </BlockStack>
        </Box>
    );
}
