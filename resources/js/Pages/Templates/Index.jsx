import React, { useState, useCallback, useMemo } from "react";
import {
    Page,
    Layout,
    LegacyCard,
    Grid,
    Text,
    Badge,
    Button,
    TextField,
    Icon,
    Modal,
    EmptyState,
    Select,
    InlineStack,
    BlockStack,
    Card,
    Box,
    Tag,
    IndexFilters,
    useSetIndexFiltersMode,
    ChoiceList,
} from "@shopify/polaris";
import {
    SearchIcon,
    ViewIcon,
    FilterIcon,
    SortIcon,
    ArrowRightIcon,
} from "@shopify/polaris-icons";
import { Head, router } from "@inertiajs/react";
import { useAppBridge } from "@shopify/app-bridge-react";

// Static assets for logos (placeholders or CDN links usually)
const LOGOS = {
    slack: "https://cdn.worldvectorlogo.com/logos/slack-new-logo.svg",
    klaviyo: "https://cdn.worldvectorlogo.com/logos/klaviyo.svg",
    email: "https://cdn.shopify.com/s/files/1/0262/4071/2726/files/email-icon.png", // Generic email
    shopify: "https://cdn.worldvectorlogo.com/logos/shopify.svg",
    google: "https://cdn.worldvectorlogo.com/logos/google-drive.svg",
};

export default function Index({ templates = [] }) {
    const shopify = useAppBridge();
    const { mode, setMode } = useSetIndexFiltersMode();

    const [queryValue, setQueryValue] = useState("");
    const [selectedCategory, setSelectedCategory] = useState(undefined); // Array for ChoiceList
    const [sortSelected, setSortSelected] = useState(["name asc"]);
    const [selectedTab, setSelectedTab] = useState(0);

    const [selectedTemplate, setSelectedTemplate] = useState(null);
    const [isModalOpen, setIsModalOpen] = useState(false);

    // --- Sorting Options ---
    const sortOptions = [
        { label: "Name (A-Z)", value: "name asc", directionLabel: "A-Z" },
        { label: "Name (Z-A)", value: "name desc", directionLabel: "Z-A" },
        { label: "Newest", value: "created_at desc", directionLabel: "Newest" },
        { label: "Oldest", value: "created_at asc", directionLabel: "Oldest" },
    ];

    // --- Helper to get connectors from template ---
    const getConnectors = (template) => {
        const text = (template.name + " " + template.description).toLowerCase();
        const connectors = [];
        if (text.includes("slack")) connectors.push("slack");
        if (text.includes("klaviyo")) connectors.push("klaviyo");
        if (text.includes("email") || text.includes("gmail"))
            connectors.push("email");
        if (
            text.includes("sheet") ||
            text.includes("doc") ||
            text.includes("drive")
        )
            connectors.push("google");
        if (connectors.length === 0) connectors.push("shopify"); // Default
        return connectors;
    };

    // --- Tabs Configuration (Connectors) ---
    const tabs = [
        {
            id: "all",
            content: "All Templates",
            panelID: "all-content",
            connector: null,
        },
        {
            id: "shopify",
            content: "Shopify",
            panelID: "shopify-content",
            connector: "shopify",
        },
        {
            id: "slack",
            content: "Slack",
            panelID: "slack-content",
            connector: "slack",
        },
        {
            id: "klaviyo",
            content: "Klaviyo",
            panelID: "klaviyo-content",
            connector: "klaviyo",
        },
        {
            id: "email",
            content: "Email",
            panelID: "email-content",
            connector: "email",
        },
        {
            id: "google",
            content: "Google Drive",
            panelID: "google-content",
            connector: "google",
        },
    ];

    // --- Filter Options: Categories ---
    const categories = useMemo(() => {
        const cats = new Set(templates.map((t) => t.category).filter(Boolean));
        return [
            "Automated",
            "Notifications",
            "Marketing",
            "Inventory",
            ...Array.from(cats),
        ]
            .filter((v, i, a) => a.indexOf(v) === i) // Unique
            .map((c) => ({
                label: c.charAt(0).toUpperCase() + c.slice(1),
                value: c,
            }));
    }, [templates]);

    // --- Filtering Logic ---
    const filteredTemplates = useMemo(() => {
        return templates.filter((template) => {
            // 1. Tab Filter (Connector)
            const currentTab = tabs[selectedTab];
            const templateConnectors = getConnectors(template);
            const matchesTab =
                !currentTab.connector ||
                templateConnectors.includes(currentTab.connector);

            // 2. Search Filter
            const matchesSearch =
                template.name
                    .toLowerCase()
                    .includes(queryValue.toLowerCase()) ||
                (template.description &&
                    template.description
                        .toLowerCase()
                        .includes(queryValue.toLowerCase()));

            // 3. Category Filter
            const matchesCategory =
                !selectedCategory ||
                selectedCategory.length === 0 ||
                selectedCategory.includes(template.category);

            return matchesTab && matchesSearch && matchesCategory;
        });
    }, [templates, queryValue, selectedCategory, selectedTab]);

    // --- Sorting Logic ---
    const sortedTemplates = useMemo(() => {
        return [...filteredTemplates].sort((a, b) => {
            const [key, direction] = sortSelected[0].split(" ");
            let valA = a[key] || "";
            let valB = b[key] || "";

            if (typeof valA === "string") valA = valA.toLowerCase();
            if (typeof valB === "string") valB = valB.toLowerCase();

            if (valA < valB) return direction === "asc" ? -1 : 1;
            if (valA > valB) return direction === "asc" ? 1 : -1;
            return 0;
        });
    }, [filteredTemplates, sortSelected]);

    const handleTemplateClick = (template) => {
        setSelectedTemplate(template);
        setIsModalOpen(true);
    };

    const handleActivate = () => {
        if (!selectedTemplate) return;

        // Explicitly use Inertia's post method for proper token handling
        router.post(
            `/templates/${selectedTemplate.slug}/activate`,
            {},
            {
                onSuccess: () => {
                    setIsModalOpen(false);
                    shopify.toast.show(
                        "Template activated - Redirecting to Editor..."
                    );
                },
                onError: (errors) => {
                    // If it's a 405 or other error, it usually ends up here if JSON response
                    // But if it's a full page HTML error (like standard Laravel 405), Inertia might show a modal.
                    // We log it here for debugging if needed.
                    console.error("Activation failed:", errors);
                    shopify.toast.show("Failed to activate template", {
                        isError: true,
                    });
                },
            }
        );
    };

    // --- Filters UI Configuration ---
    const filters = [
        {
            key: "category",
            label: "Category",
            filter: (
                <ChoiceList
                    title="Category"
                    titleHidden
                    choices={categories}
                    selected={selectedCategory || []}
                    onChange={setSelectedCategory}
                    allowMultiple
                />
            ),
            shortcut: true,
        },
    ];

    const appliedFilters = [];
    if (selectedCategory && selectedCategory.length > 0) {
        appliedFilters.push({
            key: "category",
            label: `Category: ${selectedCategory.join(", ")}`,
            onRemove: () => setSelectedCategory([]),
        });
    }

    return (
        <Page
            title="Template Gallery"
            subtitle="Explore pre-built automation workflows."
            primaryAction={{
                content: "Create from scratch",
                url: "/workflows/create",
            }}
        >
            <Head title="Templates" />

            <Layout>
                <Layout.Section>
                    <LegacyCard>
                        <IndexFilters
                            sortOptions={sortOptions}
                            sortSelected={sortSelected}
                            queryValue={queryValue}
                            queryPlaceholder="Search templates..."
                            onQueryChange={setQueryValue}
                            onQueryClear={() => setQueryValue("")}
                            onSort={setSortSelected}
                            filters={filters}
                            appliedFilters={appliedFilters}
                            onClearAll={() => {
                                setQueryValue("");
                                setSelectedCategory([]);
                            }}
                            mode={mode}
                            setMode={setMode}
                            tabs={tabs}
                            selected={selectedTab}
                            onSelect={setSelectedTab}
                            canCreateNewView={false}
                            hideFilters={false}
                            filteringAccessibilityLabel="Search and filter templates"
                            cancelAction={{
                                onAction: () => {},
                                disabled: false,
                                loading: false,
                            }}
                            primaryAction={{
                                type: "save",
                                onAction: () => {},
                                disabled: false,
                                loading: false,
                            }}
                        />
                    </LegacyCard>
                </Layout.Section>

                <Layout.Section>
                    {sortedTemplates.length === 0 ? (
                        <EmptyState
                            heading="No matching templates found"
                            image="https://cdn.shopify.com/s/files/1/0262/4071/2726/files/emptystate-files.png"
                        >
                            <p>Try adjusting your search terms or filters.</p>
                        </EmptyState>
                    ) : (
                        <Grid>
                            {sortedTemplates.map((template) => {
                                const connectors = getConnectors(template);
                                return (
                                    <Grid.Cell
                                        key={template.id}
                                        columnSpan={{
                                            xs: 6,
                                            sm: 6,
                                            md: 4,
                                            lg: 4,
                                            xl: 4,
                                        }}
                                    >
                                        <Card>
                                            <BlockStack gap="400">
                                                <InlineStack
                                                    align="space-between"
                                                    blockAlign="center"
                                                >
                                                    <InlineStack gap="100">
                                                        {connectors.map((c) => (
                                                            <div
                                                                key={c}
                                                                style={{
                                                                    width: 24,
                                                                    height: 24,
                                                                    borderRadius:
                                                                        "50%",
                                                                    background:
                                                                        "#f1f2f3",
                                                                    padding: 4,
                                                                    display:
                                                                        "flex",
                                                                    alignItems:
                                                                        "center",
                                                                    justifyContent:
                                                                        "center",
                                                                }}
                                                            >
                                                                {LOGOS[c] ? (
                                                                    <img
                                                                        src={
                                                                            LOGOS[
                                                                                c
                                                                            ]
                                                                        }
                                                                        alt={c}
                                                                        style={{
                                                                            width: "100%",
                                                                            height: "100%",
                                                                            objectFit:
                                                                                "contain",
                                                                        }}
                                                                    />
                                                                ) : (
                                                                    <Icon
                                                                        source={
                                                                            ViewIcon
                                                                        }
                                                                        size="small"
                                                                    />
                                                                )}
                                                            </div>
                                                        ))}
                                                    </InlineStack>
                                                    {template.category && (
                                                        <Badge tone="info">
                                                            {template.category}
                                                        </Badge>
                                                    )}
                                                </InlineStack>

                                                <BlockStack gap="200">
                                                    <Text
                                                        as="h3"
                                                        variant="headingSm"
                                                        truncate
                                                    >
                                                        {template.name}
                                                    </Text>
                                                    <div
                                                        style={{
                                                            minHeight: "2.5rem",
                                                        }}
                                                    >
                                                        <Text
                                                            as="p"
                                                            variant="bodySm"
                                                            tone="subdued"
                                                            breakWord
                                                        >
                                                            {template.description
                                                                ? template
                                                                      .description
                                                                      .length >
                                                                  70
                                                                    ? template.description.substring(
                                                                          0,
                                                                          70
                                                                      ) + "..."
                                                                    : template.description
                                                                : ""}
                                                        </Text>
                                                    </div>
                                                </BlockStack>

                                                <Button
                                                    onClick={() =>
                                                        handleTemplateClick(
                                                            template
                                                        )
                                                    }
                                                    fullWidth
                                                    variant="secondary"
                                                    icon={ArrowRightIcon}
                                                >
                                                    Preview
                                                </Button>
                                            </BlockStack>
                                        </Card>
                                    </Grid.Cell>
                                );
                            })}
                        </Grid>
                    )}
                </Layout.Section>
            </Layout>

            {/* Preview Modal */}
            <Modal
                open={isModalOpen}
                onClose={() => setIsModalOpen(false)}
                title={selectedTemplate?.name}
                primaryAction={{
                    content: "Use Template",
                    onAction: handleActivate,
                }}
                secondaryActions={[
                    {
                        content: "Cancel",
                        onAction: () => setIsModalOpen(false),
                    },
                ]}
                size="large"
            >
                <Modal.Section>
                    <Layout>
                        <Layout.Section>
                            <BlockStack gap="400">
                                <Text as="p" variant="bodyMd">
                                    {selectedTemplate?.description}
                                </Text>

                                <LegacyCard title="Connectors" sectioned>
                                    <InlineStack gap="400">
                                        {selectedTemplate &&
                                            getConnectors(selectedTemplate).map(
                                                (c) => (
                                                    <InlineStack
                                                        key={c}
                                                        gap="200"
                                                        blockAlign="center"
                                                    >
                                                        <div
                                                            style={{
                                                                width: 24,
                                                                height: 24,
                                                            }}
                                                        >
                                                            <img
                                                                src={
                                                                    LOGOS[c] ||
                                                                    ""
                                                                }
                                                                style={{
                                                                    width: "100%",
                                                                    height: "100%",
                                                                    objectFit:
                                                                        "contain",
                                                                }}
                                                                alt={c}
                                                                onError={(e) =>
                                                                    (e.target.style.display =
                                                                        "none")
                                                                }
                                                            />
                                                        </div>
                                                        <Text
                                                            variant="bodyMd"
                                                            transform="capitalize"
                                                        >
                                                            {c}
                                                        </Text>
                                                    </InlineStack>
                                                )
                                            )}
                                    </InlineStack>
                                </LegacyCard>

                                {selectedTemplate?.tags &&
                                    selectedTemplate.tags.length > 0 && (
                                        <BlockStack gap="200">
                                            <Text variant="headingSm">
                                                Tags
                                            </Text>
                                            <InlineStack gap="200">
                                                {selectedTemplate.tags.map(
                                                    (tag) => (
                                                        <Tag key={tag}>
                                                            {tag}
                                                        </Tag>
                                                    )
                                                )}
                                            </InlineStack>
                                        </BlockStack>
                                    )}
                            </BlockStack>
                        </Layout.Section>

                        <Layout.Section variant="oneThird">
                            <div
                                style={{
                                    padding: "2rem",
                                    background: "#f6f6f7",
                                    borderRadius: "8px",
                                    border: "1px dashed #dbe1e6",
                                    height: "100%",
                                    minHeight: "300px",
                                    display: "flex",
                                    flexDirection: "column",
                                    alignItems: "center",
                                    justifyContent: "center",
                                }}
                            >
                                <BlockStack
                                    align="center"
                                    inlineAlign="center"
                                    gap="400"
                                >
                                    <Icon
                                        source={ViewIcon}
                                        tone="subdued"
                                        size="large"
                                    />
                                    <Text
                                        variant="bodySm"
                                        tone="subdued"
                                        alignment="center"
                                    >
                                        Logic Preview
                                    </Text>
                                </BlockStack>
                            </div>
                        </Layout.Section>
                    </Layout>
                </Modal.Section>
            </Modal>
        </Page>
    );
}
