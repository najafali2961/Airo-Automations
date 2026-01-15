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
    XIcon,
} from "@shopify/polaris-icons";

export default function Sidebar({ definitions, connectors, onNodeClick }) {
    const [view, setView] = useState("categories"); // categories | app | search
    const [selectedApp, setSelectedApp] = useState(null);
    const [searchQuery, setSearchQuery] = useState("");

    // Color/Icon mapping for known apps (visuals only, not existence)
    const appVisuals = {
        google: {
            color: "#EA4335",
            iconUrl: "https://cdn-icons-png.flaticon.com/512/2991/2991148.png",
        },
        slack: {
            color: "#4A154B",
            iconUrl: "https://cdn-icons-png.flaticon.com/512/2111/2111615.png",
        },
        smtp: {
            color: "#D93025",
            iconUrl: "https://cdn-icons-png.flaticon.com/512/732/732200.png",
        },
        twilio: {
            color: "#F22F46",
            iconUrl: "https://cdn-icons-png.flaticon.com/512/5968/5968841.png",
        },
        klaviyo: {
            color: "#25D366",
            iconUrl:
                "https://www.klaviyo.com/application-assets/klaviyo/production/static-assets/favicon.png",
        },
        shopify: {
            color: "#95BF47",
            iconUrl:
                "https://upload.wikimedia.org/wikipedia/commons/0/0e/Shopify_logo_2018.svg",
        },
    };

    const standardNodes = [
        {
            type: "condition",
            label: "Condition",
            icon: CheckIcon,
            color: "#E29100",
            description: "Branch workflow logic",
            group: "Logic",
        },
        {
            type: "stopper",
            label: "Stopper",
            icon: XIcon,
            color: "#D82C0D",
            description: "End workflow",
            group: "Logic",
        },
    ];

    // Strictly use definitions.apps from backend
    const apps = (definitions?.apps || []).map((app) => {
        const lowerName = app.name.toLowerCase();
        const visual = appVisuals[lowerName];

        if (visual) {
            return {
                ...app,
                color: app.color || visual.color,
                iconUrl: app.iconUrl || visual.iconUrl,
            };
        }

        return app;
    });

    const allApps = apps;

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
        event.dataTransfer.setData(
            "application/reactflow/appName",
            node.category || node.name
        ); // Pass app name
        event.dataTransfer.setData(
            "application/reactflow/description",
            node.description || ""
        );

        // Pass defaults (e.g. topic for triggers)
        const defaults = {};
        if (node.topic) {
            defaults.topic = node.topic;
        } else if (node.settings) {
            Object.assign(defaults, node.settings);
        }

        event.dataTransfer.setData(
            "application/reactflow/defaults",
            JSON.stringify(defaults)
        );

        event.dataTransfer.effectAllowed = "move";
    };

    const renderNodeItem = (node) => (
        <div
            key={`${node.category}-${node.label}`}
            draggable
            onDragStart={(e) => onDragStart(e, node)}
            onClick={() => onNodeClick && onNodeClick(node)}
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
                    <div style={{ minWidth: 0, flex: 1 }}>
                        <BlockStack gap="050">
                            <Text fontWeight="bold" variant="bodySm" truncate>
                                {node.label}
                            </Text>
                            {node.description && (
                                <Text variant="bodyXs" tone="subdued" truncate>
                                    {node.description}
                                </Text>
                            )}
                        </BlockStack>
                    </div>
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
                                {allApps.map((app) => {
                                    // Determine connection status
                                    const isConnected = !connectors // If connectors not loaded, assume true or hide
                                        ? true
                                        : app.name === "Shopify" // Shopify always connected
                                        ? true
                                        : connectors[app.name.toLowerCase()] !==
                                          false;

                                    return (
                                        <div
                                            key={app.name}
                                            onClick={() => handleAppClick(app)}
                                            className="cursor-pointer group"
                                        >
                                            <div
                                                className={`
                                                    bg-white border-2 rounded-2xl p-4 flex flex-col items-center gap-2 transition-all h-full justify-center relative
                                                    ${
                                                        isConnected
                                                            ? "border-gray-100 group-hover:border-blue-500 group-hover:shadow-sm"
                                                            : "border-orange-100 bg-orange-50/30 group-hover:border-orange-300"
                                                    }
                                                `}
                                            >
                                                {/* Status Dot */}
                                                <div
                                                    className={`
                                                        absolute top-3 right-3 w-2.5 h-2.5 rounded-full border border-white
                                                        ${
                                                            isConnected
                                                                ? "bg-emerald-400"
                                                                : "bg-orange-400 animate-pulse"
                                                        }
                                                    `}
                                                    title={
                                                        isConnected
                                                            ? "Connected"
                                                            : "Setup Required"
                                                    }
                                                />

                                                <div
                                                    style={{
                                                        width: "32px",
                                                        height: "32px",
                                                        display: "flex",
                                                        alignItems: "center",
                                                        justifyContent:
                                                            "center",
                                                    }}
                                                >
                                                    {app.iconUrl ? (
                                                        <img
                                                            src={app.iconUrl}
                                                            alt={app.name}
                                                            className={`w-full h-full object-contain ${
                                                                !isConnected &&
                                                                "grayscale opacity-80"
                                                            }`}
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
                                                    tone={
                                                        isConnected
                                                            ? "subdued"
                                                            : "caution"
                                                    }
                                                    truncate
                                                >
                                                    {app.name}
                                                </Text>
                                            </div>
                                        </div>
                                    );
                                })}
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
