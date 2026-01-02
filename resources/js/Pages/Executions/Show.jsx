import React from "react";
import { Head, router } from "@inertiajs/react";
import {
    Page,
    Layout,
    Card,
    BlockStack,
    Text,
    Badge,
    Button,
    InlineStack,
    Box,
    Divider,
} from "@shopify/polaris";
import { ArrowLeftIcon } from "@shopify/polaris-icons";

export default function Show({ execution }) {
    const { flow, logs = [] } = execution;

    const StatusBadge = ({ status }) => {
        let tone = "subdued";
        if (status === "success") tone = "success";
        if (status === "failed") tone = "critical";
        if (status === "running") tone = "info";

        return <Badge tone={tone}>{status.toUpperCase()}</Badge>;
    };

    return (
        <Page
            backAction={{
                content: "Back to activity",
                onAction: () =>
                    router.visit("/executions" + window.location.search),
            }}
            title={`Execution #${execution.id}`}
            subtitle={flow?.name || "Deleted Workflow"}
            titleMetadata={<StatusBadge status={execution.status} />}
        >
            <Head title={`Execution #${execution.id}`} />

            <Layout>
                <Layout.Section>
                    <BlockStack gap="500">
                        {/* Summary Card */}
                        <Card>
                            <BlockStack gap="400">
                                <Text variant="headingMd" as="h3">
                                    Execution Summary
                                </Text>
                                <div className="grid grid-cols-2 gap-4">
                                    <BlockStack gap="100">
                                        <Text variant="bodySm" tone="subdued">
                                            Event Topic
                                        </Text>
                                        <Text
                                            variant="bodyMd"
                                            fontWeight="bold"
                                        >
                                            {execution.event}
                                        </Text>
                                    </BlockStack>
                                    <BlockStack gap="100">
                                        <Text variant="bodySm" tone="subdued">
                                            Triggered At
                                        </Text>
                                        <Text variant="bodyMd">
                                            {new Date(
                                                execution.created_at
                                            ).toLocaleString()}
                                        </Text>
                                    </BlockStack>
                                    <BlockStack gap="100">
                                        <Text variant="bodySm" tone="subdued">
                                            External ID
                                        </Text>
                                        <Text variant="bodyMd" breakWord>
                                            {execution.external_event_id}
                                        </Text>
                                    </BlockStack>
                                    <BlockStack gap="100">
                                        <Text variant="bodySm" tone="subdued">
                                            Status
                                        </Text>
                                        <StatusBadge
                                            status={execution.status}
                                        />
                                    </BlockStack>
                                </div>
                                {execution.error_message && (
                                    <Box
                                        padding="300"
                                        background="bg-surface-critical-subdued"
                                        borderRadius="200"
                                    >
                                        <BlockStack gap="100">
                                            <Text
                                                variant="bodySm"
                                                tone="critical"
                                                fontWeight="bold"
                                            >
                                                Error Message
                                            </Text>
                                            <Text
                                                variant="bodyMd"
                                                tone="critical"
                                            >
                                                {execution.error_message}
                                            </Text>
                                        </BlockStack>
                                    </Box>
                                )}
                            </BlockStack>
                        </Card>

                        {/* Step-by-Step Logs */}
                        <Card padding="0">
                            <Box
                                padding="400"
                                borderBlockEndWidth="025"
                                borderColor="border"
                            >
                                <Text variant="headingMd" as="h3">
                                    Step-by-Step Activity
                                </Text>
                            </Box>

                            <div className="divide-y divide-gray-100">
                                {logs.length === 0 ? (
                                    <Box padding="800">
                                        <Text alignment="center" tone="subdued">
                                            No detailed logs found for this
                                            execution.
                                        </Text>
                                    </Box>
                                ) : (
                                    logs.map((log) => (
                                        <div
                                            key={log.id}
                                            className="p-4 hover:bg-gray-50 transition-colors"
                                        >
                                            <InlineStack
                                                gap="400"
                                                align="space-between"
                                                blockAlign="start"
                                            >
                                                <BlockStack gap="100" flex="1">
                                                    <InlineStack
                                                        gap="200"
                                                        blockAlign="center"
                                                    >
                                                        <Text
                                                            variant="bodySm"
                                                            tone="subdued"
                                                        >
                                                            [
                                                            {new Date(
                                                                log.created_at
                                                            ).toLocaleTimeString()}
                                                            ]
                                                        </Text>
                                                        {log.node_id && (
                                                            <Badge size="small">
                                                                Node:{" "}
                                                                {log.node_id}
                                                            </Badge>
                                                        )}
                                                        <Badge
                                                            size="small"
                                                            tone={
                                                                log.level ===
                                                                "error"
                                                                    ? "critical"
                                                                    : log.level ===
                                                                      "warning"
                                                                    ? "attention"
                                                                    : "info"
                                                            }
                                                        >
                                                            {log.level.toUpperCase()}
                                                        </Badge>
                                                    </InlineStack>
                                                    <Text
                                                        variant="bodyMd"
                                                        fontWeight={
                                                            log.level ===
                                                            "error"
                                                                ? "bold"
                                                                : "normal"
                                                        }
                                                    >
                                                        {log.message}
                                                    </Text>
                                                    {log.data && (
                                                        <Box
                                                            padding="300"
                                                            background="bg-surface-secondary"
                                                            borderRadius="200"
                                                            className="mt-2"
                                                        >
                                                            <pre className="text-xs overflow-auto max-h-40 whitespace-pre-wrap">
                                                                {JSON.stringify(
                                                                    log.data,
                                                                    null,
                                                                    2
                                                                )}
                                                            </pre>
                                                        </Box>
                                                    )}
                                                </BlockStack>
                                            </InlineStack>
                                        </div>
                                    ))
                                )}
                            </div>
                        </Card>
                    </BlockStack>
                </Layout.Section>

                <Layout.Section variant="oneThird">
                    <Card>
                        <BlockStack gap="400">
                            <Text variant="headingMd" as="h3">
                                Trigger Payload
                            </Text>
                            <Box
                                padding="300"
                                background="bg-surface-secondary"
                                borderRadius="200"
                            >
                                <pre className="text-xs overflow-auto max-h-[500px] whitespace-pre-wrap">
                                    {JSON.stringify(execution.payload, null, 2)}
                                </pre>
                            </Box>
                        </BlockStack>
                    </Card>
                </Layout.Section>
            </Layout>
        </Page>
    );
}
