import React, { useState, useCallback, useMemo } from "react";
import { Head, router } from "@inertiajs/react";
import {
    Page,
    Layout,
    LegacyCard,
    IndexTable,
    IndexFilters,
    useSetIndexFiltersMode,
    useIndexResourceState,
    Text,
    ChoiceList,
    Badge,
    Button,
    BlockStack,
    InlineStack,
    Avatar,
    Toast,
    Frame,
    useBreakpoints,
} from "@shopify/polaris";
import { DeleteIcon } from "@shopify/polaris-icons";

export default function Connectors({ connectors }) {
    const [activeToast, setActiveToast] = useState(false);
    const [toastMessage, setToastMessage] = useState("");
    const [isDisconnectingKey, setIsDisconnectingKey] = useState(null);

    const toggleToast = useCallback(
        () => setActiveToast((active) => !active),
        []
    );

    // ============================================================================================
    //  Google Connection Logic
    // ============================================================================================
    React.useEffect(() => {
        const handleMessage = (event) => {
            if (event.data === "google_auth_success") {
                router.reload({
                    onSuccess: () => {
                        setToastMessage("Successfully connected!");
                        setActiveToast(true);
                    },
                });
            }
        };
        window.addEventListener("message", handleMessage);
        return () => window.removeEventListener("message", handleMessage);
    }, []);

    const handleConnect = async (authUrl) => {
        try {
            const host = new URLSearchParams(window.location.search).get(
                "host"
            ); // Maintain host if present

            // Fetch signed URL if needed (from original code)
            // We'll use the API endpoint to get a fresh signed URL to avoid "stale signature" errors

            const response = await window.axios.get(
                `/api/google/auth-url?host=${host}`
            );
            if (response.data.url) {
                window.open(
                    response.data.url,
                    "google_auth_popup",
                    "width=600,height=700,status=yes,scrollbars=yes"
                );
            } else {
                setToastMessage("Failed to get auth URL");
                setActiveToast(true);
            }
        } catch (error) {
            console.error(error);
            setToastMessage("Error initiating connection");
            setActiveToast(true);
        }
    };

    const handleDisconnect = (connectorKey) => {
        setIsDisconnectingKey(connectorKey);
        router.post(
            "/auth/google/disconnect",
            {},
            {
                onSuccess: () => {
                    setToastMessage("Disconnected successfully");
                    setActiveToast(true);
                    setIsDisconnectingKey(null);
                },
                onError: () => {
                    setToastMessage("Failed to disconnect");
                    setActiveToast(true);
                    setIsDisconnectingKey(null);
                },
            }
        );
    };

    // ============================================================================================
    //  IndexTable & Filters Logic
    // ============================================================================================

    // 1. View Management
    const sleep = (ms) => new Promise((resolve) => setTimeout(resolve, ms));
    const [itemStrings, setItemStrings] = useState([
        "All",
        "Connected",
        "Disconnected",
    ]);
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
                      { type: "edit" },
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

    // 2. Sorting
    const sortOptions = [
        { label: "Title", value: "title asc", directionLabel: "A-Z" },
        { label: "Title", value: "title desc", directionLabel: "Z-A" },
        { label: "Status", value: "status asc", directionLabel: "A-Z" },
        { label: "Status", value: "status desc", directionLabel: "Z-A" },
    ];
    const [sortSelected, setSortSelected] = useState(["title asc"]);

    // 3. Filters
    const { mode, setMode } = useSetIndexFiltersMode();
    const [queryValue, setQueryValue] = useState("");
    const [statusFilter, setStatusFilter] = useState(undefined); // ['Connected'] or undefined

    const handleStatusFilterChange = useCallback(
        (value) => setStatusFilter(value),
        []
    );
    const handleFiltersQueryChange = useCallback(
        (value) => setQueryValue(value),
        []
    );
    const handleStatusFilterRemove = useCallback(
        () => setStatusFilter(undefined),
        []
    );
    const handleQueryValueRemove = useCallback(() => setQueryValue(""), []);
    const handleFiltersClearAll = useCallback(() => {
        handleStatusFilterRemove();
        handleQueryValueRemove();
    }, [handleStatusFilterRemove, handleQueryValueRemove]);

    const filters = [
        {
            key: "status",
            label: "Status",
            filter: (
                <ChoiceList
                    title="Status"
                    titleHidden
                    choices={[
                        { label: "Connected", value: "Connected" },
                        { label: "Disconnected", value: "Disconnected" },
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
    if (statusFilter && !isEmpty(statusFilter)) {
        const key = "status";
        appliedFilters.push({
            key,
            label: disambiguateLabel(key, statusFilter),
            onRemove: handleStatusFilterRemove,
        });
    }

    // 4. Data Processing (Filtering & Sorting)
    const filteredConnectors = useMemo(() => {
        let result = [...connectors];

        // A. Filter by View (Tab)
        const currentView = itemStrings[selected];
        if (currentView === "Connected") {
            result = result.filter((c) => c.status === "Connected");
        } else if (currentView === "Disconnected") {
            result = result.filter((c) => c.status === "Disconnected");
        }

        // B. Filter by Search
        if (queryValue) {
            const lowerQuery = queryValue.toLowerCase();
            result = result.filter(
                (item) =>
                    item.title.toLowerCase().includes(lowerQuery) ||
                    item.description.toLowerCase().includes(lowerQuery)
            );
        }

        // C. Filter by Specific Filters
        if (statusFilter && statusFilter.length > 0) {
            result = result.filter((item) =>
                statusFilter.includes(item.status)
            );
        }

        // D. Sorting
        const [sortKey, sortDir] = sortSelected[0].split(" ");
        result.sort((a, b) => {
            let valA = a[sortKey] || "";
            let valB = b[sortKey] || "";
            if (valA < valB) return sortDir === "asc" ? -1 : 1;
            if (valA > valB) return sortDir === "asc" ? 1 : -1;
            return 0;
        });

        return result;
    }, [
        connectors,
        itemStrings,
        selected,
        queryValue,
        statusFilter,
        sortSelected,
    ]);

    // 5. IndexTable Props
    const resourceName = { singular: "connector", plural: "connectors" };
    const { selectedResources, allResourcesSelected, handleSelectionChange } =
        useIndexResourceState(filteredConnectors);

    const onHandleSave = async () => {
        await sleep(1);
        return true;
    };
    const primaryAction =
        selected === 0
            ? { type: "save-as", onAction: onCreateNewView }
            : { type: "save", onAction: onHandleSave };

    // ============================================================================================
    //  Renderers
    // ============================================================================================

    function isEmpty(value) {
        if (Array.isArray(value)) return value.length === 0;
        return value === "" || value == null;
    }

    function disambiguateLabel(key, value) {
        if (key === "status") return `Status: ${value.join(", ")}`;
        return value;
    }

    const StatusBadge = ({ status }) => {
        let tone = "subdued";
        if (status === "Connected") tone = "success";
        else if (status === "Disconnected") tone = "critical";
        return <Badge tone={tone}>{status.toUpperCase()}</Badge>;
    };

    const rowMarkup = filteredConnectors.map(
        (
            { key, title, description, icon, status, is_active, auth_url },
            index
        ) => {
            const isSelected = selectedResources.includes(key);

            return (
                <IndexTable.Row
                    id={key}
                    key={key}
                    selected={isSelected}
                    position={index}
                >
                    <IndexTable.Cell>
                        <InlineStack gap="300" blockAlign="center">
                            <Avatar source={icon} alt={title} size="medium" />
                            <BlockStack>
                                <Text fontWeight="bold" as="span">
                                    {title}
                                </Text>
                                <Text variant="bodySm" tone="subdued" as="span">
                                    {description}
                                </Text>
                            </BlockStack>
                        </InlineStack>
                    </IndexTable.Cell>
                    <IndexTable.Cell>
                        <StatusBadge status={status} />
                    </IndexTable.Cell>
                    <IndexTable.Cell>
                        {status === "Disconnected" && is_active && (
                            <Button
                                variant="primary"
                                onClick={() => handleConnect(auth_url)}
                            >
                                Connect
                            </Button>
                        )}
                        {status === "Connected" && (
                            <Button
                                variant="primary"
                                tone="critical"
                                loading={isDisconnectingKey === key}
                                onClick={() => handleDisconnect(key)}
                            >
                                Disconnect
                            </Button>
                        )}
                        {!is_active && <Button disabled>Coming Soon</Button>}
                    </IndexTable.Cell>
                </IndexTable.Row>
            );
        }
    );

    return (
        <Frame>
            <Page
                title="Connectors"
                subtitle="Manage your external integrations"
                backAction={{
                    content: "Home",
                    onAction: () => router.visit("/"),
                }}
            >
                <Head title="Connectors" />
                <Layout>
                    <Layout.Section>
                        <LegacyCard>
                            <IndexFilters
                                sortOptions={sortOptions}
                                sortSelected={sortSelected}
                                queryValue={queryValue}
                                queryPlaceholder="Search connectors..."
                                onQueryChange={handleFiltersQueryChange}
                                onQueryClear={() => setQueryValue("")}
                                onSort={setSortSelected}
                                primaryAction={primaryAction}
                                cancelAction={{
                                    onAction: () => {},
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
                                itemCount={filteredConnectors.length}
                                selectedItemsCount={
                                    allResourcesSelected
                                        ? "All"
                                        : selectedResources.length
                                }
                                onSelectionChange={handleSelectionChange}
                                headings={[
                                    { title: "App" },
                                    { title: "Status" },
                                    { title: "Actions" },
                                ]}
                            >
                                {rowMarkup}
                            </IndexTable>
                        </LegacyCard>
                    </Layout.Section>
                </Layout>
                {activeToast && (
                    <Toast content={toastMessage} onDismiss={toggleToast} />
                )}
            </Page>
        </Frame>
    );
}
