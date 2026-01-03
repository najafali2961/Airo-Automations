import React, { useState, useCallback, useEffect } from "react";
import { Head, router, Link } from "@inertiajs/react";
import {
    Page,
    LegacyCard,
    IndexTable,
    Text,
    Badge,
    Pagination,
    BlockStack,
    Box,
    InlineStack,
    IndexFilters,
    useSetIndexFiltersMode,
    useIndexResourceState,
    ChoiceList,
    useBreakpoints,
} from "@shopify/polaris";

export default function Index({ executions, filters: serverFilters = {} }) {
    const { data } = executions;

    // Filter states
    const [queryValue, setQueryValue] = useState(serverFilters.query || "");

    const ensureStringArray = (val, fallback = []) => {
        if (!val) return fallback;
        const arr = Array.isArray(val) ? val : [val];
        return arr.filter(
            (item) => typeof item === "string" && item.length > 0
        );
    };

    const [statusSelected, setStatusSelected] = useState(() =>
        ensureStringArray(serverFilters.status)
    );

    const [sortSelected, setSortSelected] = useState(() => {
        const arr = ensureStringArray(serverFilters.sort);
        return arr.length > 0 ? arr : ["created_at desc"];
    });

    const { mode, setMode } = useSetIndexFiltersMode();

    const getTabIndex = (status) => {
        if (status === "success") return 1;
        if (status === "failed") return 2;
        return 0;
    };

    const [selectedTab, setSelectedTab] = useState(() =>
        getTabIndex(
            Array.isArray(serverFilters.status)
                ? serverFilters.status[0]
                : serverFilters.status
        )
    );

    const tabs = [
        { content: "All", index: 0, id: "all" },
        { content: "Success", index: 1, id: "success" },
        { content: "Failed", index: 2, id: "failed" },
    ];

    const sortOptions = [
        {
            label: "Date",
            value: "created_at desc",
            directionLabel: "Newest first",
        },
        {
            label: "Date",
            value: "created_at asc",
            directionLabel: "Oldest first",
        },
    ];

    const resourceName = { singular: "execution", plural: "executions" };
    const { selectedResources, allResourcesSelected, handleSelectionChange } =
        useIndexResourceState(data);

    const navigate = useCallback((params) => {
        const cleanParams = Object.fromEntries(
            Object.entries(params).filter(
                ([_, v]) =>
                    v != null &&
                    v !== "" &&
                    !(Array.isArray(v) && v.length === 0)
            )
        );
        router.get(window.location.pathname, cleanParams, {
            preserveState: true,
            replace: true,
        });
    }, []);

    const handleFiltersQueryChange = useCallback(
        (value) => {
            setQueryValue(value);
            navigate({
                query: value,
                status: statusSelected[0],
                sort: sortSelected[0],
            });
        },
        [statusSelected, sortSelected, navigate]
    );

    const handleStatusChange = useCallback(
        (value) => {
            const safeValue = ensureStringArray(value);
            setStatusSelected(safeValue);
            setSelectedTab(getTabIndex(safeValue[0]));
            navigate({
                query: queryValue,
                status: safeValue[0],
                sort: sortSelected[0],
            });
        },
        [queryValue, sortSelected, navigate]
    );

    const handleSortChange = useCallback(
        (value) => {
            const safeValue = ensureStringArray(value, ["created_at desc"]);
            setSortSelected(safeValue);
            navigate({
                query: queryValue,
                status: statusSelected[0],
                sort: safeValue[0],
            });
        },
        [queryValue, statusSelected, navigate]
    );

    const handleFiltersClearAll = useCallback(() => {
        setQueryValue("");
        setStatusSelected([]);
        setSelectedTab(0);
        navigate({});
    }, [navigate]);

    const filters = [
        {
            key: "status",
            label: "Status",
            filter: (
                <ChoiceList
                    title="Status"
                    titleHidden
                    choices={[
                        { label: "Success", value: "success" },
                        { label: "Failed", value: "failed" },
                        { label: "Running", value: "running" },
                    ]}
                    selected={statusSelected}
                    onChange={handleStatusChange}
                />
            ),
            shortcut: true,
        },
    ];

    const appliedFilters = [];
    if (statusSelected && statusSelected.length > 0 && statusSelected[0]) {
        appliedFilters.push({
            key: "status",
            label: `Status: ${statusSelected[0]}`,
            onRemove: () => handleStatusChange([]),
        });
    }

    const StatusBadge = ({ status }) => {
        let tone = "subdued";
        if (status === "success") tone = "success";
        if (status === "failed") tone = "critical";
        if (status === "running") tone = "info";
        if (status === "partial") tone = "attention";
        return <Badge tone={tone}>{status.toUpperCase()}</Badge>;
    };

    const rowMarkup = data.map((execution, index) => (
        <IndexTable.Row
            id={execution.id}
            key={execution.id}
            position={index}
            selected={selectedResources.includes(execution.id)}
            onClick={() => router.visit(`/executions/${execution.id}`)}
        >
            <IndexTable.Cell>
                <StatusBadge status={execution.status} />
            </IndexTable.Cell>
            <IndexTable.Cell>
                <Text variant="bodyMd" fontWeight="bold" as="span">
                    <Link
                        href={`/executions/${execution.id}`}
                        className="hover:underline"
                        style={{ color: "inherit", textDecoration: "none" }}
                        onClick={(e) => e.stopPropagation()}
                    >
                        {execution.flow?.name || "Deleted Flow"}
                    </Link>
                </Text>
            </IndexTable.Cell>
            <IndexTable.Cell>
                <Badge tone="info">{execution.event}</Badge>
            </IndexTable.Cell>
            <IndexTable.Cell>
                <Text variant="bodyMd" tone="subdued" as="span">
                    {(execution.external_event_id || "").substring(0, 12)}...
                </Text>
            </IndexTable.Cell>
            <IndexTable.Cell>
                <Text variant="bodyMd" as="span">
                    {new Date(execution.created_at).toLocaleString()}
                </Text>
            </IndexTable.Cell>
        </IndexTable.Row>
    ));

    return (
        <Page
            title="Activity Logs"
            subtitle="Monitor every single detail of your workflow executions."
            backAction={{
                content: "Dashboard",
                onAction: () => router.visit("/" + window.location.search),
            }}
        >
            <Head title="Executions" />
            <BlockStack gap="500">
                <LegacyCard>
                    <IndexFilters
                        sortOptions={sortOptions}
                        sortSelected={sortSelected}
                        queryValue={queryValue}
                        queryPlaceholder="Search executions..."
                        onQueryChange={handleFiltersQueryChange}
                        onQueryClear={() => handleFiltersQueryChange("")}
                        onSort={handleSortChange}
                        tabs={tabs}
                        selected={selectedTab}
                        onSelect={(index) => {
                            const status =
                                tabs[index].id === "all"
                                    ? []
                                    : [tabs[index].id];
                            handleStatusChange(status);
                        }}
                        filters={filters}
                        appliedFilters={appliedFilters}
                        onClearAll={handleFiltersClearAll}
                        mode={mode}
                        setMode={setMode}
                    />
                    <IndexTable
                        condensed={useBreakpoints().smDown}
                        resourceName={resourceName}
                        itemCount={data.length}
                        selectedItemsCount={
                            allResourcesSelected
                                ? "All"
                                : selectedResources.length
                        }
                        onSelectionChange={handleSelectionChange}
                        headings={[
                            { title: "Status" },
                            { title: "Workflow" },
                            { title: "Event" },
                            { title: "Event ID" },
                            { title: "Date" },
                        ]}
                    >
                        {rowMarkup}
                    </IndexTable>
                    <Box padding="400">
                        <InlineStack align="center">
                            <Pagination
                                hasPrevious={!!executions.prev_page_url}
                                onPrevious={() =>
                                    router.visit(executions.prev_page_url)
                                }
                                hasNext={!!executions.next_page_url}
                                onNext={() =>
                                    router.visit(executions.next_page_url)
                                }
                            />
                        </InlineStack>
                    </Box>
                </LegacyCard>
            </BlockStack>
        </Page>
    );
}
