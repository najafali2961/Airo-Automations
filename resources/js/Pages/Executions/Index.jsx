import React from "react";
import { Head, router } from "@inertiajs/react";
import {
    Page,
    Card,
    IndexTable,
    Text,
    Badge,
    Pagination,
    BlockStack,
    Box,
    InlineStack,
} from "@shopify/polaris";

export default function Index({ executions }) {
    const { data, links, meta } = executions;

    const StatusBadge = ({ status }) => {
        let tone = "subdued";
        if (status === "success") tone = "success";
        if (status === "failed") tone = "critical";
        if (status === "running") tone = "info";
        if (status === "partial") tone = "attention";

        return <Badge tone={tone}>{status.toUpperCase()}</Badge>;
    };

    const resourceName = {
        singular: "execution",
        plural: "executions",
    };

    const rowMarkup = data.map((execution, index) => (
        <IndexTable.Row
            id={execution.id}
            key={execution.id}
            position={index}
            onClick={() =>
                router.visit(
                    `/executions/${execution.id}` + window.location.search
                )
            }
        >
            <IndexTable.Cell>
                <StatusBadge status={execution.status} />
            </IndexTable.Cell>
            <IndexTable.Cell>
                <Text variant="bodyMd" fontWeight="bold" as="span">
                    {execution.flow?.name || "Deleted Flow"}
                </Text>
            </IndexTable.Cell>
            <IndexTable.Cell>
                <Badge tone="info">{execution.event}</Badge>
            </IndexTable.Cell>
            <IndexTable.Cell>
                <Text variant="bodyMd" tone="subdued" as="span">
                    {execution.external_event_id.substring(0, 12)}...
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
        >
            <Head title="Executions" />

            <BlockStack gap="500">
                <Card padding="0">
                    <IndexTable
                        resourceName={resourceName}
                        itemCount={data.length}
                        headings={[
                            { title: "Status" },
                            { title: "Workflow" },
                            { title: "Event" },
                            { title: "Event ID" },
                            { title: "Date" },
                        ]}
                        selectable={false}
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
                </Card>
            </BlockStack>
        </Page>
    );
}
