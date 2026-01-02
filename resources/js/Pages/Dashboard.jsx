import React from "react";
import { Head, router } from "@inertiajs/react";
import {
    Page,
    Layout,
    BlockStack,
    Text,
    Button,
    Card,
    InlineStack,
    Badge,
    IndexTable,
    useIndexResourceState,
    Box,
    Icon,
    IndexFilters,
    useSetIndexFiltersMode,
    ChoiceList,
    TextField,
    LegacyCard,
    Pagination,
} from "@shopify/polaris";
import {
    PlusIcon,
    ArrowRightIcon,
    LayoutColumns3Icon,
    ClockIcon,
    AlertCircleIcon,
    SearchIcon,
} from "@shopify/polaris-icons";
import { useState, useCallback, useMemo } from "react";

export default function Dashboard({
    shop,
    stats,
    executions = [],
    flows = [],
}) {
    // --- IndexFilters Logic ---
    const [itemStrings, setItemStrings] = useState([
        "All",
        "Failed",
        "Success",
    ]);
    const [selected, setSelected] = useState(0);
    const { mode, setMode } = useSetIndexFiltersMode();
    const [queryValue, setQueryValue] = useState("");
    const [statusFilter, setStatusFilter] = useState([]);

    // Pagination State
    const [currentPage, setCurrentPage] = useState(1);
    const PAGE_SIZE = 3;

    const tabs = itemStrings.map((item, index) => ({
        content: item,
        index,
        onAction: () => {},
        id: `${item}-${index}`,
        isLocked: index === 0,
    }));

    const handleFiltersQueryChange = useCallback((value) => {
        setQueryValue(value);
        setCurrentPage(1);
    }, []);
    const handleStatusFilterChange = useCallback((value) => {
        setStatusFilter(value);
        setCurrentPage(1);
    }, []);
    const handleTabChange = useCallback((index) => {
        setSelected(index);
        setCurrentPage(1);
    }, []);

    const filters = [
        {
            key: "status",
            label: "Status",
            filter: (
                <ChoiceList
                    title="Execution Status"
                    titleHidden
                    choices={[
                        { label: "Success", value: "success" },
                        { label: "Failed", value: "failed" },
                        { label: "Running", value: "running" },
                    ]}
                    selected={statusFilter || []}
                    onChange={handleStatusFilterChange}
                    allowMultiple
                />
            ),
            shortcut: true,
        },
    ];

    const appliedFilters = [];
    if (statusFilter.length > 0) {
        appliedFilters.push({
            key: "status",
            label: `Status is ${statusFilter.join(", ")}`,
            onRemove: () => {
                setStatusFilter([]);
                setCurrentPage(1);
            },
        });
    }

    // --- Data Filtering Logic ---
    const filteredExecutions = useMemo(() => {
        return executions.filter((exec) => {
            // Search filter
            if (
                queryValue &&
                !exec.flow?.name
                    ?.toLowerCase()
                    .includes(queryValue.toLowerCase()) &&
                !exec.event?.toLowerCase().includes(queryValue.toLowerCase())
            ) {
                return false;
            }
            // Tab filter
            if (selected === 1 && exec.status !== "failed") return false;
            if (selected === 2 && exec.status !== "success") return false;
            // Status filter
            if (statusFilter.length > 0 && !statusFilter.includes(exec.status))
                return false;

            return true;
        });
    }, [executions, queryValue, selected, statusFilter]);

    // Slice for pagination
    const paginatedExecutions = useMemo(() => {
        const start = (currentPage - 1) * PAGE_SIZE;
        return filteredExecutions.slice(start, start + PAGE_SIZE);
    }, [filteredExecutions, currentPage]);

    const resourceName = {
        singular: "execution",
        plural: "executions",
    };

    const { selectedResources, allResourcesSelected, handleSelectionChange } =
        useIndexResourceState(paginatedExecutions);

    // Helper for status badge
    const StatusBadge = ({ status }) => {
        let tone = "subdued";
        if (status === "success" || status === "SUCCESS") tone = "success";
        if (status === "failed" || status === "FAILED") tone = "critical";
        if (status === "running") tone = "info";

        return <Badge tone={tone}>{status.toUpperCase()}</Badge>;
    };

    return (
        <Page
            title="Airo Automations"
            subtitle={`Welcome back, ${shop.name}`}
            primaryAction={{
                content: "New Workflow",
                icon: PlusIcon,
                onAction: () =>
                    router.visit("/workflows" + window.location.search),
            }}
        >
            <Head title="Dashboard" />

            <BlockStack gap="600">
                {/* Stats Grid */}
                <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <StatCard
                        title="Total Flows"
                        value={stats.total_flows}
                        icon={LayoutColumns3Icon}
                        color="bg-blue-50 text-blue-600"
                        subtext={`${stats.active_flows} Active`}
                    />
                    <StatCard
                        title="Total Executions"
                        value={stats.total_executions}
                        icon={ClockIcon}
                        color="bg-purple-50 text-purple-600"
                    />
                    <StatCard
                        title="Success Rate"
                        value={
                            stats.total_executions > 0
                                ? Math.round(
                                      ((stats.total_executions -
                                          stats.failed_executions) /
                                          stats.total_executions) *
                                          100
                                  ) + "%"
                                : "N/A"
                        }
                        icon={AlertCircleIcon}
                        color={
                            stats.failed_executions > 0
                                ? "bg-amber-50 text-amber-600"
                                : "bg-green-50 text-green-600"
                        }
                    />
                </div>

                <Layout>
                    <Layout.Section>
                        <Card padding="0">
                            <Box
                                padding="400"
                                borderBlockEndWidth="025"
                                borderColor="border"
                            >
                                <InlineStack
                                    align="space-between"
                                    blockAlign="center"
                                >
                                    <Text variant="headingMd" as="h3">
                                        Recent Executions
                                    </Text>
                                    <Button
                                        variant="plain"
                                        onClick={() =>
                                            router.visit(
                                                "/workflows" +
                                                    window.location.search
                                            )
                                        }
                                        icon={ArrowRightIcon}
                                    >
                                        View All
                                    </Button>
                                </InlineStack>
                            </Box>

                            {/* Enhanced IndexTable with Filters */}
                            <IndexFilters
                                queryValue={queryValue}
                                queryPlaceholder="Search by workflow or event..."
                                onQueryChange={handleFiltersQueryChange}
                                onQueryClear={() => {
                                    setQueryValue("");
                                    setCurrentPage(1);
                                }}
                                tabs={tabs}
                                selected={selected}
                                onSelect={handleTabChange}
                                filters={filters}
                                appliedFilters={appliedFilters}
                                onClearAll={() => {
                                    setQueryValue("");
                                    setStatusFilter([]);
                                    setCurrentPage(1);
                                }}
                                mode={mode}
                                setMode={setMode}
                                canCreateNewView={false}
                            />

                            {filteredExecutions.length === 0 ? (
                                <Box padding="800">
                                    <BlockStack
                                        align="center"
                                        inlineAlign="center"
                                        gap="400"
                                    >
                                        <img
                                            src="https://cdn.shopify.com/s/files/1/0262/4071/2726/files/emptystate-files.png"
                                            alt="No executions"
                                            style={{ width: 100, opacity: 0.5 }}
                                        />
                                        <Text tone="subdued" alignment="center">
                                            No matches found for your filters.
                                        </Text>
                                    </BlockStack>
                                </Box>
                            ) : (
                                <IndexTable
                                    resourceName={resourceName}
                                    itemCount={filteredExecutions.length}
                                    selectedItemsCount={
                                        allResourcesSelected
                                            ? "All"
                                            : selectedResources.length
                                    }
                                    condensed
                                    onSelectionChange={handleSelectionChange}
                                    headings={[
                                        { title: "Execution" },
                                        { title: "Status" },
                                    ]}
                                >
                                    {paginatedExecutions.map((exec, index) => (
                                        <IndexTable.Row
                                            id={exec.id}
                                            key={exec.id}
                                            selected={selectedResources.includes(
                                                exec.id
                                            )}
                                            position={index}
                                            onClick={() =>
                                                router.visit(
                                                    `/executions/${exec.id}` +
                                                        window.location.search
                                                )
                                            }
                                        >
                                            <div
                                                style={{
                                                    padding: "12px 16px",
                                                    width: "100%",
                                                    cursor: "pointer",
                                                }}
                                            >
                                                <BlockStack gap="100">
                                                    <Text
                                                        as="span"
                                                        variant="bodySm"
                                                        tone="subdued"
                                                    >
                                                        #{exec.id} •{" "}
                                                        {new Date(
                                                            exec.created_at
                                                        ).toLocaleString()}
                                                    </Text>
                                                    <InlineStack
                                                        align="space-between"
                                                        blockAlign="center"
                                                    >
                                                        <Text
                                                            as="span"
                                                            variant="bodyMd"
                                                            fontWeight="semibold"
                                                        >
                                                            {exec.flow?.name ||
                                                                "Deleted Flow"}
                                                        </Text>
                                                        <StatusBadge
                                                            status={exec.status}
                                                        />
                                                    </InlineStack>
                                                    <InlineStack
                                                        align="start"
                                                        gap="100"
                                                    >
                                                        <Badge
                                                            tone="info"
                                                            size="small"
                                                        >
                                                            {exec.event}
                                                        </Badge>
                                                        {exec.actions_completed >
                                                            0 && (
                                                            <Text
                                                                as="span"
                                                                variant="bodyXs"
                                                                tone="subdued"
                                                            >
                                                                {
                                                                    exec.actions_completed
                                                                }{" "}
                                                                actions
                                                            </Text>
                                                        )}
                                                    </InlineStack>
                                                </BlockStack>
                                            </div>
                                        </IndexTable.Row>
                                    ))}
                                </IndexTable>
                            )}

                            {/* Pagination Controls */}
                            {filteredExecutions.length > PAGE_SIZE && (
                                <Box padding="400">
                                    <InlineStack align="center">
                                        <Pagination
                                            hasPrevious={currentPage > 1}
                                            onPrevious={() =>
                                                setCurrentPage(
                                                    (prev) => prev - 1
                                                )
                                            }
                                            hasNext={
                                                currentPage * PAGE_SIZE <
                                                filteredExecutions.length
                                            }
                                            onNext={() =>
                                                setCurrentPage(
                                                    (prev) => prev + 1
                                                )
                                            }
                                        />
                                    </InlineStack>
                                </Box>
                            )}
                        </Card>
                    </Layout.Section>

                    <Layout.Section variant="oneThird">
                        <Card>
                            <BlockStack gap="400">
                                <InlineStack
                                    align="space-between"
                                    blockAlign="center"
                                >
                                    <Text variant="headingMd" as="h3">
                                        Active Flows
                                    </Text>
                                    <Button
                                        variant="plain"
                                        onClick={() =>
                                            router.visit(
                                                "/workflows" +
                                                    window.location.search
                                            )
                                        }
                                    >
                                        Manage
                                    </Button>
                                </InlineStack>
                                {flows.length === 0 ? (
                                    <Text tone="subdued">
                                        No flows created yet.
                                    </Text>
                                ) : (
                                    <BlockStack gap="200">
                                        {flows.map((flow) => (
                                            <div
                                                key={flow.id}
                                                className="p-3 rounded-lg bg-gray-50 border border-gray-100 flex justify-between items-center group hover:bg-gray-100 transition-colors cursor-pointer"
                                                onClick={() =>
                                                    router.visit(
                                                        `/workflows/${flow.id}` +
                                                            window.location
                                                                .search
                                                    )
                                                }
                                            >
                                                <BlockStack gap="050">
                                                    <Text
                                                        fontWeight="bold"
                                                        variant="bodyMd"
                                                    >
                                                        {flow.name}
                                                    </Text>
                                                    <Text
                                                        variant="bodyXs"
                                                        tone="subdued"
                                                    >
                                                        {new Date(
                                                            flow.updated_at
                                                        ).toLocaleDateString()}
                                                    </Text>
                                                </BlockStack>
                                                <div className="opacity-0 group-hover:opacity-100 transition-opacity">
                                                    <Icon
                                                        source={ArrowRightIcon}
                                                        tone="subdued"
                                                    />
                                                </div>
                                            </div>
                                        ))}
                                    </BlockStack>
                                )}
                                <Button
                                    fullWidth
                                    variant="primary"
                                    icon={PlusIcon}
                                    onClick={() =>
                                        router.visit(
                                            "/workflows" +
                                                window.location.search
                                        )
                                    }
                                >
                                    Create Flow
                                </Button>
                            </BlockStack>
                        </Card>
                    </Layout.Section>
                </Layout>
            </BlockStack>
        </Page>
    );
}

const StatCard = ({ title, value, icon, color, subtext }) => (
    <div className="bg-white p-6 rounded-xl shadow-sm border border-gray-200 flex items-start justify-between">
        <div>
            <p className="text-gray-500 text-sm font-medium">{title}</p>
            <h3 className="text-2xl font-bold mt-1 text-gray-900">{value}</h3>
            {subtext && <p className="text-xs text-gray-400 mt-1">{subtext}</p>}
        </div>
        <div className={`p-3 rounded-lg ${color}`}>
            <Icon source={icon} tone="inherit" />
        </div>
    </div>
);
