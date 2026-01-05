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
    ChevronLeftIcon,
    SearchIcon,
    StoreIcon,
    PersonIcon,
    OrderIcon,
    ProductIcon,
} from "@shopify/polaris-icons";

export default function Sidebar({ definitions }) {
    const [view, setView] = useState("categories"); // categories | app | search
    const [selectedApp, setSelectedApp] = useState(null);
    const [searchQuery, setSearchQuery] = useState("");

    const connectorApps = [
        {
            name: "Google",
            color: "#EA4335",
            iconUrl: "https://cdn-icons-png.flaticon.com/512/2991/2991148.png",
            triggers: [],
            actions: [],
        },
        {
            name: "Slack",
            color: "#4A154B",
            iconUrl: "https://cdn-icons-png.flaticon.com/512/2111/2111615.png",
            triggers: [],
            actions: [],
        },
        {
            name: "SMTP",
            color: "#D93025",
            iconUrl: "https://cdn-icons-png.flaticon.com/512/732/732200.png",
            triggers: [],
            actions: [],
        },
        {
            name: "Twilio",
            color: "#F22F46",
            iconUrl: "https://cdn-icons-png.flaticon.com/512/5968/5968841.png",
            triggers: [],
            actions: [],
        },
    ];

    const standardNodes = [
        {
            type: "condition",
            label: "Condition",
            icon: CheckIcon,
            color: "#E29100",
            description: "Branch workflow logic",
            group: "Logic",
        },
    ];

    const apps = (definitions?.apps || []).map((app) => {
        if (app.name === "Shopify") {
            return {
                ...app,
                color: "#95BF47", // Shopify Green
                iconUrl:
                    "https://upload.wikimedia.org/wikipedia/commons/0/0e/Shopify_logo_2018.svg",
            };
        }

        return app;
    });
    const allApps = [...apps, ...connectorApps];

    const searchResults = useMemo(() => {
        if (!searchQuery) return null;
        const query = searchQuery.toLowerCase();
        const results = { apps: [], nodes: [] };

        standardNodes.forEach((node) => {
            if (node.label.toLowerCase().includes(query)) {
                results.nodes.push({ ...node, category: "Logic" });
            }
        });

        allApps.forEach((app) => {
            if (app.name.toLowerCase().includes(query)) results.apps.push(app);
            app.triggers?.forEach((t) => {
                if (t.label.toLowerCase().includes(query)) {
                    results.nodes.push({
                        ...t,
                        type: "trigger",
                        category: app.name,
                        icon: t.label.includes("Order")
                            ? OrderIcon
                            : t.label.includes("Product")
                            ? ProductIcon
                            : t.label.includes("Customer")
                            ? PersonIcon
                            : AlertCircleIcon,
                        color: "#008060",
                    });
                }
            });
            app.actions?.forEach((a) => {
                if (a.label.toLowerCase().includes(query)) {
                    results.nodes.push({
                        ...a,
                        type: "action",
                        category: app.name,
                        icon: a.label.includes("Tag")
                            ? CheckIcon
                            : ArrowRightIcon,
                        color: "#0070f3",
                    });
                }
            });
        });
        return results;
    }, [searchQuery, apps, allApps]);

    const handleSearchChange = (value) => {
        setSearchQuery(value);
        if (value && view !== "search") setView("search");
        else if (!value && view === "search") setView("categories");
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
            className="cursor-grab"
        >
            <div className="bg-white border-2 border-gray-100 rounded-2xl p-4 transition-all hover:border-blue-500 hover:shadow-sm">
                <InlineStack
                    gap="300"
                    align="start"
                    blockAlign="center"
                    wrap={false}
                >
                    <div
                        style={{
                            color: node.color || "#5c5f62",
                            display: "flex",
                            flexShrink: 0,
                        }}
                    >
                        <Icon
                            source={node.icon || ArrowRightIcon}
                            tone="inherit"
                        />
                    </div>
                    <BlockStack gap="050" minWidth="0">
                        <Text fontWeight="bold" variant="bodySm" truncate>
                            {node.label}
                        </Text>
                        {node.description && (
                            <Text variant="bodyXs" tone="subdued" truncate>
                                {node.description}
                            </Text>
                        )}
                    </BlockStack>
                </InlineStack>
            </div>
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
                    placeholder="Search triggers..."
                    value={searchQuery}
                    onChange={handleSearchChange}
                    autoComplete="off"
                    clearButton
                />

                {view === "categories" && (
                    <BlockStack gap="500">
                        <BlockStack gap="300">
                            <Text variant="headingSm" tone="subdued">
                                Choose Integration
                            </Text>
                            <Box className="grid grid-cols-2 gap-3 max-h-[400px] overflow-y-auto pr-1">
                                {/* Changed to vertical grid */}
                                {allApps.map((app) => (
                                    <div
                                        key={app.name}
                                        onClick={() => handleAppClick(app)}
                                        className="cursor-pointer group"
                                    >
                                        <div className="bg-white border-2 border-gray-100 rounded-2xl p-4 flex flex-col items-center gap-2 transition-all group-hover:border-blue-500 group-hover:shadow-sm h-full justify-center">
                                            <div
                                                style={{
                                                    width: "32px",
                                                    height: "32px",
                                                    display: "flex",
                                                    alignItems: "center",
                                                    justifyContent: "center",
                                                }}
                                            >
                                                {app.iconUrl ? (
                                                    <img
                                                        src={app.iconUrl}
                                                        alt={app.name}
                                                        className="w-full h-full object-contain"
                                                    />
                                                ) : (
                                                    <div
                                                        style={{
                                                            color:
                                                                app.color ||
                                                                "#95a5a6",
                                                        }}
                                                    >
                                                        <Icon
                                                            source={
                                                                app.name ===
                                                                "Shopify"
                                                                    ? StoreIcon
                                                                    : AppsIcon
                                                            }
                                                            tone="inherit"
                                                        />
                                                    </div>
                                                )}
                                            </div>
                                            <Text
                                                variant="bodyXs"
                                                fontWeight="bold"
                                                alignment="center"
                                                tone="subdued"
                                                truncate
                                            >
                                                {app.name}
                                            </Text>
                                        </div>
                                    </div>
                                ))}
                            </Box>
                        </BlockStack>

                        <Divider />

                        <BlockStack gap="300">
                            <Text variant="headingSm" tone="subdued">
                                Common Units
                            </Text>
                            <Box className="grid grid-cols-1 gap-3">
                                {standardNodes.map((n) =>
                                    renderNodeItem({ ...n, category: "Logic" })
                                )}
                            </Box>
                        </BlockStack>
                    </BlockStack>
                )}

                {view === "app" && selectedApp && (
                    <BlockStack gap="400">
                        <div className="flex items-center justify-between">
                            <Button
                                icon={ChevronLeftIcon}
                                onClick={handleBack}
                                variant="plain"
                            >
                                Back
                            </Button>
                        </div>

                        <div className="bg-white border-2 border-gray-100 rounded-3xl p-6 flex flex-col items-center gap-3">
                            <div
                                style={{
                                    width: "40px",
                                    height: "40px",
                                    display: "flex",
                                    justifyContent: "center",
                                    alignItems: "center",
                                }}
                            >
                                {selectedApp.iconUrl ? (
                                    <img
                                        src={selectedApp.iconUrl}
                                        alt={selectedApp.name}
                                        className="w-full h-full object-contain"
                                    />
                                ) : (
                                    <div
                                        style={{
                                            color:
                                                selectedApp.color || "#95a5a6",
                                            transform: "scale(1.5)",
                                        }}
                                    >
                                        <Icon
                                            source={
                                                selectedApp.name === "Shopify"
                                                    ? StoreIcon
                                                    : AppsIcon
                                            }
                                            tone="inherit"
                                        />
                                    </div>
                                )}
                            </div>
                            <Text variant="headingMd" fontWeight="bold">
                                {selectedApp.name}
                            </Text>
                        </div>

                        {selectedApp.triggers?.length > 0 ||
                        selectedApp.actions?.length > 0 ? (
                            <BlockStack gap="400">
                                {selectedApp.triggers?.length > 0 && (
                                    <BlockStack gap="300">
                                        <Text
                                            variant="headingSm"
                                            tone="subdued"
                                        >
                                            Triggers
                                        </Text>
                                        <Box className="grid grid-cols-1 gap-3">
                                            {selectedApp.triggers.map((t) =>
                                                renderNodeItem({
                                                    ...t,
                                                    icon: t.label.includes(
                                                        "Order"
                                                    )
                                                        ? OrderIcon
                                                        : t.label.includes(
                                                              "Product"
                                                          )
                                                        ? ProductIcon
                                                        : t.label.includes(
                                                              "Customer"
                                                          )
                                                        ? PersonIcon
                                                        : AlertCircleIcon,
                                                    color: "#008060",
                                                    category: selectedApp.name,
                                                })
                                            )}
                                        </Box>
                                    </BlockStack>
                                )}

                                {selectedApp.actions?.length > 0 && (
                                    <BlockStack gap="300">
                                        <Text
                                            variant="headingSm"
                                            tone="subdued"
                                        >
                                            Actions
                                        </Text>
                                        <Box className="grid grid-cols-1 gap-3">
                                            {selectedApp.actions.map((a) =>
                                                renderNodeItem({
                                                    ...a,
                                                    icon: a.label.includes(
                                                        "Tag"
                                                    )
                                                        ? CheckIcon
                                                        : ArrowRightIcon,
                                                    color: "#0070f3",
                                                    category: selectedApp.name,
                                                })
                                            )}
                                        </Box>
                                    </BlockStack>
                                )}
                            </BlockStack>
                        ) : (
                            <Box padding="600" textAlign="center">
                                <BlockStack gap="400">
                                    <Text tone="subdued">Coming soon...</Text>
                                    <Button onClick={handleBack} size="slim">
                                        Notify Me
                                    </Button>
                                </BlockStack>
                            </Box>
                        )}
                    </BlockStack>
                )}

                {view === "search" && searchResults && (
                    <BlockStack gap="400">
                        <InlineStack align="space-between" blockAlign="center">
                            <Text variant="headingSm" tone="subdued">
                                Results
                            </Text>
                            <Button variant="plain" onClick={handleBack}>
                                Clear
                            </Button>
                        </InlineStack>

                        {searchResults.nodes.length === 0 &&
                        searchResults.apps.length === 0 ? (
                            <Box padding="400" textAlign="center">
                                <Text tone="subdued">Nothing found</Text>
                            </Box>
                        ) : (
                            <BlockStack gap="400">
                                {searchResults.apps.length > 0 && (
                                    <BlockStack gap="300">
                                        <Text
                                            variant="bodyXs"
                                            fontWeight="bold"
                                            tone="subdued"
                                        >
                                            APPS
                                        </Text>
                                        <div className="grid grid-cols-1 gap-2">
                                            {searchResults.apps.map((app) => (
                                                <div
                                                    key={app.name}
                                                    onClick={() =>
                                                        handleAppClick(app)
                                                    }
                                                    className="cursor-pointer"
                                                >
                                                    <div className="bg-white border-2 border-gray-100 rounded-2xl p-4 flex items-center gap-3 hover:border-blue-500">
                                                        <div
                                                            style={{
                                                                width: "24px",
                                                                height: "24px",
                                                                flexShrink: 0,
                                                            }}
                                                        >
                                                            {app.iconUrl ? (
                                                                <img
                                                                    src={
                                                                        app.iconUrl
                                                                    }
                                                                    alt={
                                                                        app.name
                                                                    }
                                                                    className="w-full h-full object-contain"
                                                                />
                                                            ) : (
                                                                <div
                                                                    style={{
                                                                        color:
                                                                            app.color ||
                                                                            "#95a5a6",
                                                                    }}
                                                                >
                                                                    <Icon
                                                                        source={
                                                                            app.name ===
                                                                            "Shopify"
                                                                                ? StoreIcon
                                                                                : AppsIcon
                                                                        }
                                                                        tone="inherit"
                                                                    />
                                                                </div>
                                                            )}
                                                        </div>
                                                        <Text fontWeight="bold">
                                                            {app.name}
                                                        </Text>
                                                    </div>
                                                </div>
                                            ))}
                                        </div>
                                    </BlockStack>
                                )}
                                {searchResults.nodes.length > 0 && (
                                    <BlockStack gap="300">
                                        <Text
                                            variant="bodyXs"
                                            fontWeight="bold"
                                            tone="subdued"
                                        >
                                            UNITS
                                        </Text>
                                        <div className="grid grid-cols-1 gap-3">
                                            {searchResults.nodes.map(
                                                renderNodeItem
                                            )}
                                        </div>
                                    </BlockStack>
                                )}
                            </BlockStack>
                        )}
                    </BlockStack>
                )}
            </BlockStack>
        </Box>
    );
}
