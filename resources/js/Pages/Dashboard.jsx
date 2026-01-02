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
} from "@shopify/polaris";
import {
    PlusIcon,
    ArrowRightIcon,
    LayoutColumns3Icon,
    ClockIcon,
    AlertCircleIcon,
} from "@shopify/polaris-icons";

export default function Dashboard({
    shop,
    stats,
    executions = [],
    flows = [],
}) {
    // Helper for status badge
    const StatusBadge = ({ status }) => {
        let tone = "subdued";
        if (status === "success") tone = "success";
        if (status === "failed") tone = "critical";
        if (status === "running") tone = "info";

        return <Badge tone={tone}>{status.toUpperCase()}</Badge>;
    };

    return (
        <Page
            title="Overview"
            subtitle={`Welcome back, ${shop.name}`}
            primaryAction={{
                content: "New Workflow",
                icon: PlusIcon,
                onAction: () => router.visit("/workflows"),
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
                                        url="/workflows"
                                        icon={ArrowRightIcon}
                                    >
                                        View All
                                    </Button>
                                </InlineStack>
                            </Box>

                            {/* Illustration / Empty State if needed, or just list */}
                            {executions.length === 0 ? (
                                <Box padding="800">
                                    <BlockStack
                                        align="center"
                                        inlineAlign="center"
                                        gap="400"
                                    >
                                        <img
                                            src="https://cdn.shopify.com/s/files/1/0262/4071/2726/files/emptystate-files.png"
                                            alt="No executions"
                                            style={{ width: 150, opacity: 0.5 }}
                                        />
                                        <Text tone="subdued" alignment="center">
                                            No recent activity found.
                                        </Text>
                                    </BlockStack>
                                </Box>
                            ) : (
                                <IndexTable
                                    resourceName={{
                                        singular: "execution",
                                        plural: "executions",
                                    }}
                                    itemCount={executions.length}
                                    headings={[
                                        { title: "Status" },
                                        { title: "Flow" },
                                        { title: "Event" },
                                        { title: "Date" },
                                    ]}
                                    selectable={false}
                                >
                                    {executions.map((exec, index) => (
                                        <IndexTable.Row
                                            id={exec.id}
                                            key={exec.id}
                                            position={index}
                                        >
                                            <IndexTable.Cell>
                                                <StatusBadge
                                                    status={exec.status}
                                                />
                                            </IndexTable.Cell>
                                            <IndexTable.Cell>
                                                <Text fontWeight="bold">
                                                    {exec.flow?.name ||
                                                        "Unknown Flow"}
                                                </Text>
                                            </IndexTable.Cell>
                                            <IndexTable.Cell>
                                                <Badge tone="info">
                                                    {exec.event}
                                                </Badge>
                                            </IndexTable.Cell>
                                            <IndexTable.Cell>
                                                {new Date(
                                                    exec.created_at
                                                ).toLocaleString()}
                                            </IndexTable.Cell>
                                        </IndexTable.Row>
                                    ))}
                                </IndexTable>
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
                                    <Button variant="plain" url="/workflows">
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
                                                        `/workflows/${flow.id}`
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
                                    onClick={() => router.visit("/workflows")}
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
