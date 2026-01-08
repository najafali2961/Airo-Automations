import React, { useState, useCallback, useMemo, useEffect } from "react";
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
    Modal,
    TextField,
    FormLayout,
    Select,
    Card,
    Grid,
} from "@shopify/polaris";
import { getIconAndColor } from "../../Components/WorkflowBuilder/utils";

export default function Connectors({ connectors }) {
    const [activeToast, setActiveToast] = useState(false);
    const [toastMessage, setToastMessage] = useState("");
    const [isDisconnectingKey, setIsDisconnectingKey] = useState(null);

    // SMTP Modal State
    const [smtpModalOpen, setSmtpModalOpen] = useState(false);
    const [smtpConfig, setSmtpConfig] = useState({
        host: "",
        port: "587",
        username: "",
        password: "",
        encryption: "tls",
        from_address: "",
        from_name: "",
    });
    const [smtpLoading, setSmtpLoading] = useState(false);

    const toggleToast = useCallback(
        () => setActiveToast((active) => !active),
        []
    );

    // ============================================================================================
    //  Google Connection Logic
    // ============================================================================================
    useEffect(() => {
        const handleMessage = (event) => {
            if (
                event.data === "google_auth_success" ||
                event.data === "slack_auth_success" ||
                event.data === "klaviyo_auth_success"
            ) {
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

    const handleConnect = async (connector) => {
        if (connector.key === "smtp") {
            // Open SMTP Modal
            // We should fetch existing config if any
            try {
                const host = new URLSearchParams(window.location.search).get(
                    "host"
                );
                const res = await window.axios.get(
                    `/api/smtp/config?host=${host}`
                );
                if (res.data) {
                    setSmtpConfig({ ...res.data, password: "" }); // Don't show password
                }
            } catch (e) {
                console.error(e);
            }
            setSmtpModalOpen(true);
            return;
        }

        if (connector.auth_type === "oauth" && connector.auth_url) {
            try {
                const host = new URLSearchParams(window.location.search).get(
                    "host"
                );

                // Fetch signed URL if needed
                if (
                    connector.key === "google" ||
                    connector.key === "slack" ||
                    connector.key === "klaviyo"
                ) {
                    const apiUrl = `/api/${connector.key}/auth-url?host=${host}`;

                    const response = await window.axios.get(apiUrl);
                    if (response.data.url) {
                        window.open(
                            response.data.url,
                            `${connector.key}_auth_popup`,
                            "width=600,height=700,status=yes,scrollbars=yes"
                        );
                    } else {
                        setToastMessage("Failed to get auth URL");
                        setActiveToast(true);
                    }
                } else {
                    // Generic oauth
                    window.location.href = connector.auth_url;
                }
            } catch (error) {
                console.error(error);
                setToastMessage("Error initiating connection");
                setActiveToast(true);
            }
        }
    };

    // Disconnect Modal State
    const [disconnectModalOpen, setDisconnectModalOpen] = useState(false);
    const [itemToDisconnect, setItemToDisconnect] = useState(null);

    const openDisconnectModal = (key) => {
        setItemToDisconnect(key);
        setDisconnectModalOpen(true);
    };

    const confirmDisconnect = () => {
        if (!itemToDisconnect) return;

        const connectorKey = itemToDisconnect;
        setIsDisconnectingKey(connectorKey);
        setDisconnectModalOpen(false);

        let url = "";
        if (connectorKey === "google") url = "/auth/google/disconnect";
        if (connectorKey === "smtp") url = "/smtp/disconnect";
        if (connectorKey === "klaviyo") url = "/api/klaviyo/disconnect";

        if (!url) {
            setIsDisconnectingKey(null);
            return;
        }

        router.post(
            url,
            {},
            {
                onSuccess: () => {
                    setToastMessage("Disconnected successfully");
                    setActiveToast(true);
                    setIsDisconnectingKey(null);
                    setItemToDisconnect(null);
                    if (connectorKey === "smtp") {
                        setSmtpConfig({
                            host: "",
                            port: "587",
                            username: "",
                            password: "",
                            encryption: "tls",
                            from_address: "",
                            from_name: "",
                        });
                    }
                },
                onError: () => {
                    setToastMessage("Failed to disconnect");
                    setActiveToast(true);
                    setIsDisconnectingKey(null);
                    setItemToDisconnect(null);
                },
            }
        );
    };

    // ============================================================================================
    //  SMTP Save Logic
    // ============================================================================================
    const handleSmtpSave = () => {
        setSmtpLoading(true);
        router.post("/smtp/save", smtpConfig, {
            onSuccess: () => {
                setSmtpModalOpen(false);
                setToastMessage("SMTP Configuration Saved");
                setActiveToast(true);
                setSmtpLoading(false);
                router.reload();
            },
            onError: (errors) => {
                setSmtpLoading(false);
                setToastMessage("Failed to save SMTP config");
                setActiveToast(true);
                console.log(errors);
            },
        });
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

    const renderConnectorCard = (connector) => {
        const { key, title, description, status, is_active } = connector;
        const isConnected = status === "Connected";
        const { icon, color, isUrl } = getIconAndColor(key); // Use utility for consistent icon/color

        return (
            <div key={key} className="relative group">
                <div
                    className={`
                        bg-white rounded-2xl border transition-all duration-300 overflow-hidden flex flex-col h-full
                        ${
                            isConnected
                                ? "border-gray-200 shadow-sm hover:border-blue-300 hover:shadow-md"
                                : "border-gray-200 shadow-sm hover:border-gray-300"
                        }
                    `}
                >
                    {/* Header Stripe */}
                    <div className={`h-1.5 w-full ${color}`} />

                    <div className="p-5 flex flex-col h-full">
                        <div className="flex justify-between items-start mb-4">
                            <div className="flex items-center justify-center w-12 h-12 rounded-xl bg-gray-50 border border-gray-100 p-2">
                                {isUrl ? (
                                    <img
                                        src={icon}
                                        alt=""
                                        className="w-full h-full object-contain"
                                    />
                                ) : (
                                    <span className="text-2xl">{icon}</span>
                                )}
                            </div>
                            <div
                                className={`
                                px-2 py-1 rounded-full text-xs font-bold border
                                ${
                                    isConnected
                                        ? "bg-emerald-50 text-emerald-700 border-emerald-100"
                                        : "bg-gray-50 text-gray-600 border-gray-100"
                                }
                            `}
                            >
                                {isConnected ? "Active" : "Disconnected"}
                            </div>
                        </div>

                        <div className="mb-4 flex-1">
                            <Text variant="headingMd" as="h3">
                                {title}
                            </Text>
                            <Text variant="bodySm" tone="subdued" as="p">
                                {description}
                            </Text>
                        </div>

                        <div className="mt-auto pt-4 border-t border-gray-50">
                            {isConnected ? (
                                <InlineStack gap="200">
                                    {key === "smtp" && (
                                        <Button
                                            onClick={() =>
                                                handleConnect(connector)
                                            }
                                            size="slim"
                                        >
                                            Edit Config
                                        </Button>
                                    )}
                                    <Button
                                        variant="plain"
                                        tone="critical"
                                        onClick={() => openDisconnectModal(key)}
                                        loading={isDisconnectingKey === key}
                                        size="slim"
                                    >
                                        Disconnect
                                    </Button>
                                </InlineStack>
                            ) : (
                                <Button
                                    variant="primary"
                                    fullWidth
                                    onClick={() => handleConnect(connector)}
                                    disabled={!is_active}
                                >
                                    {key === "smtp"
                                        ? "Configure"
                                        : "Connect App"}
                                </Button>
                            )}
                        </div>
                    </div>
                </div>
            </div>
        );
    };

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
                            <div className="p-4">
                                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                                    {filteredConnectors.map(
                                        renderConnectorCard
                                    )}
                                </div>
                            </div>
                        </LegacyCard>
                    </Layout.Section>
                </Layout>

                {/* Disconnect Confirmation Modal */}
                <Modal
                    open={disconnectModalOpen}
                    onClose={() => setDisconnectModalOpen(false)}
                    title="Disconnect App?"
                    primaryAction={{
                        content: "Disconnect",
                        onAction: confirmDisconnect,
                        destructive: true,
                        loading: !!isDisconnectingKey,
                    }}
                    secondaryActions={[
                        {
                            content: "Cancel",
                            onAction: () => setDisconnectModalOpen(false),
                        },
                    ]}
                >
                    <Modal.Section>
                        <Text>
                            Are you sure you want to disconnect? This might
                            break active workflows using this connection.
                        </Text>
                    </Modal.Section>
                </Modal>

                {/* SMTP Configuration Modal */}
                <Modal
                    open={smtpModalOpen}
                    onClose={() => setSmtpModalOpen(false)}
                    title="SMTP Configuration"
                    primaryAction={{
                        content: "Save",
                        onAction: handleSmtpSave,
                        loading: smtpLoading,
                    }}
                    secondaryActions={[
                        {
                            content: "Cancel",
                            onAction: () => setSmtpModalOpen(false),
                        },
                    ]}
                >
                    <Modal.Section>
                        <FormLayout>
                            <FormLayout.Group>
                                <TextField
                                    label="Host"
                                    value={smtpConfig.host}
                                    onChange={(val) =>
                                        setSmtpConfig({
                                            ...smtpConfig,
                                            host: val,
                                        })
                                    }
                                    autoComplete="off"
                                    placeholder="smtp.gmail.com"
                                />
                                <TextField
                                    label="Port"
                                    value={smtpConfig.port}
                                    onChange={(val) =>
                                        setSmtpConfig({
                                            ...smtpConfig,
                                            port: val,
                                        })
                                    }
                                    autoComplete="off"
                                    placeholder="587"
                                />
                            </FormLayout.Group>
                            <FormLayout.Group>
                                <TextField
                                    label="Username"
                                    value={smtpConfig.username}
                                    onChange={(val) =>
                                        setSmtpConfig({
                                            ...smtpConfig,
                                            username: val,
                                        })
                                    }
                                    autoComplete="off"
                                />
                                <TextField
                                    label="Password"
                                    value={smtpConfig.password}
                                    onChange={(val) =>
                                        setSmtpConfig({
                                            ...smtpConfig,
                                            password: val,
                                        })
                                    }
                                    type="password"
                                    autoComplete="off"
                                />
                            </FormLayout.Group>
                            <Select
                                label="Encryption"
                                options={[
                                    { label: "TLS", value: "tls" },
                                    { label: "SSL", value: "ssl" },
                                    { label: "None", value: null },
                                ]}
                                onChange={(val) =>
                                    setSmtpConfig({
                                        ...smtpConfig,
                                        encryption: val,
                                    })
                                }
                                value={smtpConfig.encryption}
                            />
                            <FormLayout.Group>
                                <TextField
                                    label="From Email"
                                    value={smtpConfig.from_address}
                                    onChange={(val) =>
                                        setSmtpConfig({
                                            ...smtpConfig,
                                            from_address: val,
                                        })
                                    }
                                    autoComplete="off"
                                    placeholder="hello@example.com"
                                />
                                <TextField
                                    label="From Name"
                                    value={smtpConfig.from_name}
                                    onChange={(val) =>
                                        setSmtpConfig({
                                            ...smtpConfig,
                                            from_name: val,
                                        })
                                    }
                                    autoComplete="off"
                                    placeholder="My Shop"
                                />
                            </FormLayout.Group>
                        </FormLayout>
                    </Modal.Section>
                </Modal>

                {activeToast && (
                    <Toast content={toastMessage} onDismiss={toggleToast} />
                )}
            </Page>
        </Frame>
    );
}
