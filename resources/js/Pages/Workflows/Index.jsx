import React, { useState, useCallback } from "react";
import {
    Page,
    Layout,
    LegacyCard,
    IndexTable,
    Badge,
    Button,
    Text,
    EmptyState,
    InlineStack,
    Box,
    Icon,
    Modal,
    IndexFilters,
    useSetIndexFiltersMode,
    useIndexResourceState,
    ChoiceList,
    TextField,
    RangeSlider,
    useBreakpoints,
} from "@shopify/polaris";
import { Head, Link, router } from "@inertiajs/react";
import { PlusIcon, EditIcon, DeleteIcon } from "@shopify/polaris-icons";
import { useAppBridge } from "@shopify/app-bridge-react";

export default function Index({ flows = [] }) {
    const shopify = useAppBridge();
    const [flowToDelete, setFlowToDelete] = useState(null);
    const { mode, setMode } = useSetIndexFiltersMode();
    const sleep = (ms) => new Promise((resolve) => setTimeout(resolve, ms));

    // Views State
    const [itemStrings, setItemStrings] = useState(["All", "Active", "Draft"]);
    const [selected, setSelected] = useState(0);

    const deleteView = (index) => {
        const newItemStrings = [...itemStrings];
        newItemStrings.splice(index, 1);
        setItemStrings(newItemStrings);
        setSelected(0);
    };

    const duplicateView = async (name) => {
        setItemStrings([...itemStrings, name]);
        setSelected(itemStrings.length);
        await sleep(1);
        return true;
    };

    const tabs = itemStrings.map((item, index) => ({
        content: item,
        index,
        onAction: () => {},
        id: `${item}-${index}`,
        isLocked: index === 0,
        actions:
            index === 0
                ? []
                : [
                      {
                          type: "rename",
                          onAction: () => {},
                          onPrimaryAction: async (value) => {
                              const newItemsStrings = tabs.map((item, idx) => {
                                  if (idx === index) return value;
                                  return item.content;
                              });
                              await sleep(1);
                              setItemStrings(newItemsStrings);
                              return true;
                          },
                      },
                      {
                          type: "duplicate",
                          onPrimaryAction: async (value) => {
                              await sleep(1);
                              duplicateView(value);
                              return true;
                          },
                      },
                      {
                          type: "edit",
                      },
                      {
                          type: "delete",
                          onPrimaryAction: async () => {
                              await sleep(1);
                              deleteView(index);
                              return true;
                          },
                      },
                  ],
    }));

    const onCreateNewView = async (value) => {
        await sleep(500);
        setItemStrings([...itemStrings, value]);
        setSelected(itemStrings.length);
        return true;
    };

    // Sort Options
    const sortOptions = [
        { label: "Name", value: "name asc", directionLabel: "A-Z" },
        { label: "Name", value: "name desc", directionLabel: "Z-A" },
        { label: "Updated", value: "updated_at asc", directionLabel: "Oldest" },
        {
            label: "Updated",
            value: "updated_at desc",
            directionLabel: "Newest",
        },
    ];
    const [sortSelected, setSortSelected] = useState(["updated_at desc"]);

    // Filters State
    const [status, setStatus] = useState(undefined);
    const [queryValue, setQueryValue] = useState("");

    const handleStatusChange = useCallback((value) => setStatus(value), []);
    const handleFiltersQueryChange = useCallback(
        (value) => setQueryValue(value),
        []
    );
    const handleStatusRemove = useCallback(() => setStatus(undefined), []);
    const handleQueryValueRemove = useCallback(() => setQueryValue(""), []);
    const handleFiltersClearAll = useCallback(() => {
        handleStatusRemove();
        handleQueryValueRemove();
    }, [handleStatusRemove, handleQueryValueRemove]);

    const filters = [
        {
            key: "status",
            label: "Status",
            filter: (
                <ChoiceList
                    title="Status"
                    titleHidden
                    choices={[
                        { label: "Active", value: "active" },
                        { label: "Draft", value: "draft" },
                    ]}
                    selected={status || []}
                    onChange={handleStatusChange}
                    allowMultiple
                />
            ),
            shortcut: true,
        },
    ];

    const appliedFilters = [];
    if (status && status.length > 0) {
        appliedFilters.push({
            key: "status",
            label: `Status: ${status.join(", ")}`,
            onRemove: handleStatusRemove,
        });
    }

    // Handlers
    const onHandleCancel = () => {};
    const onHandleSave = async () => {
        await sleep(1);
        return true;
    };

    const primaryAction =
        selected === 0
            ? {
                  type: "save-as",
                  onAction: onCreateNewView,
                  disabled: false,
                  loading: false,
              }
            : {
                  type: "save",
                  onAction: onHandleSave,
                  disabled: false,
                  loading: false,
              };

    const handleDelete = () => {
        if (flowToDelete) {
            router.delete(
                `/workflows/${flowToDelete.id}` + window.location.search
            );
            setFlowToDelete(null);
        }
    };

    // Data Filtering Logic (Client-side for now)
    const filteredFlows = flows.filter((flow) => {
        // Query Search
        if (
            queryValue &&
            !flow.name.toLowerCase().includes(queryValue.toLowerCase())
        ) {
            return false;
        }
        // Tab Filtering
        if (itemStrings[selected] === "Active" && !flow.active) return false;
        if (itemStrings[selected] === "Draft" && flow.active) return false;

        // Filter Options
        if (status && status.length > 0) {
            const flowStatus = flow.active ? "active" : "draft";
            if (!status.includes(flowStatus)) return false;
        }

        return true;
    });

    // Sorting Logic
    const sortedFlows = [...filteredFlows].sort((a, b) => {
        const [key, direction] = sortSelected[0].split(" ");
        let valA = a[key] || "";
        let valB = b[key] || "";

        if (key === "updated_at") {
            valA = new Date(valA).getTime();
            valB = new Date(valB).getTime();
        } else if (typeof valA === "string") {
            valA = valA.toLowerCase();
            valB = valB.toLowerCase();
        }

        if (valA < valB) return direction === "asc" ? -1 : 1;
        if (valA > valB) return direction === "asc" ? 1 : -1;
        return 0;
    });

    const resourceName = {
        singular: "workflow",
        plural: "workflows",
    };

    const { selectedResources, allResourcesSelected, handleSelectionChange } =
        useIndexResourceState(sortedFlows);

    // Status Badge Helper
    const StatusBadge = ({ active }) => (
        <Badge tone={active ? "success" : "subdued"}>
            {active ? "Active" : "Draft"}
        </Badge>
    );

    const handleToggleActive = (id) => {
        router.post(
            `/workflows/${id}/toggle-active`,
            {},
            {
                preserveScroll: true,
                onSuccess: (page) => {
                    const message =
                        page.props.flash?.success || "Status updated";
                    shopify.toast.show(message);
                },
            }
        );
    };

    const rowMarkup = sortedFlows.map((flow, index) => (
        <IndexTable.Row
            id={flow.id}
            key={flow.id}
            selected={selectedResources.includes(flow.id)}
            position={index}
        >
            <IndexTable.Cell>
                <Text fontWeight="bold" as="span">
                    <Link
                        href={`/workflows/${flow.id}` + window.location.search}
                        className="hover:underline"
                    >
                        {flow.name}
                    </Link>
                </Text>
            </IndexTable.Cell>
            <IndexTable.Cell>
                <StatusBadge active={flow.active} />
            </IndexTable.Cell>
            <IndexTable.Cell>{flow.execution_count || 0}</IndexTable.Cell>
            <IndexTable.Cell>
                {new Date(flow.updated_at).toLocaleDateString()}
            </IndexTable.Cell>
            <IndexTable.Cell>
                <InlineStack gap="200" wrap={false}>
                    <Button
                        size="slim"
                        onClick={() => handleToggleActive(flow.id)}
                        variant="secondary"
                        tone={flow.active ? "critical" : "success"}
                    >
                        {flow.active ? "Pause" : "Activate"}
                    </Button>
                    <Button
                        size="slim"
                        icon={EditIcon}
                        onClick={() =>
                            router.visit(
                                `/workflows/${flow.id}` + window.location.search
                            )
                        }
                    />
                    <Button
                        size="slim"
                        tone="critical"
                        icon={DeleteIcon}
                        onClick={() => setFlowToDelete(flow)}
                    />
                </InlineStack>
            </IndexTable.Cell>
        </IndexTable.Row>
    ));

    return (
        <Page
            title="Workflows"
            backAction={{
                content: "Dashboard",
                onAction: () => router.visit("/" + window.location.search),
            }}
            primaryAction={{
                content: "Create Workflow",
                icon: PlusIcon,
                onAction: () =>
                    router.visit("/workflows/create" + window.location.search),
            }}
        >
            <Head title="Workflows" />

            <Modal
                open={!!flowToDelete}
                onClose={() => setFlowToDelete(null)}
                title="Delete Workflow"
                primaryAction={{
                    content: "Delete",
                    onAction: handleDelete,
                    destructive: true,
                }}
                secondaryActions={[
                    {
                        content: "Cancel",
                        onAction: () => setFlowToDelete(null),
                    },
                ]}
            >
                <Modal.Section>
                    <Text>
                        Are you sure you want to delete{" "}
                        <strong>{flowToDelete?.name}</strong>? This action
                        cannot be undone.
                    </Text>
                </Modal.Section>
            </Modal>

            <Layout>
                <Layout.Section>
                    <LegacyCard>
                        <IndexFilters
                            sortOptions={sortOptions}
                            sortSelected={sortSelected}
                            queryValue={queryValue}
                            queryPlaceholder="Searching in all"
                            onQueryChange={handleFiltersQueryChange}
                            onQueryClear={() => setQueryValue("")}
                            onSort={setSortSelected}
                            primaryAction={primaryAction}
                            cancelAction={{
                                onAction: onHandleCancel,
                                disabled: false,
                                loading: false,
                            }}
                            tabs={tabs}
                            selected={selected}
                            onSelect={setSelected}
                            canCreateNewView
                            onCreateNewView={onCreateNewView}
                            filters={filters}
                            appliedFilters={appliedFilters}
                            onClearAll={handleFiltersClearAll}
                            mode={mode}
                            setMode={setMode}
                        />
                        <IndexTable
                            condensed={useBreakpoints().smDown}
                            resourceName={resourceName}
                            itemCount={sortedFlows.length}
                            selectedItemsCount={
                                allResourcesSelected
                                    ? "All"
                                    : selectedResources.length
                            }
                            onSelectionChange={handleSelectionChange}
                            headings={[
                                { title: "Name" },
                                { title: "Status" },
                                { title: "Executions" },
                                { title: "Last Updated" },
                                { title: "Actions" },
                            ]}
                        >
                            {rowMarkup}
                        </IndexTable>
                    </LegacyCard>
                </Layout.Section>
            </Layout>
        </Page>
    );
}
