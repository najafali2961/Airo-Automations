import React from "react";
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
} from "@shopify/polaris";
import { Head, Link, router } from "@inertiajs/react";
import { PlusIcon, EditIcon, DeleteIcon } from "@shopify/polaris-icons";

export default function Index({ flows = [] }) {
    // Status Badge Helper
    const StatusBadge = ({ active }) => (
        <Badge tone={active ? "success" : "subdued"}>
            {active ? "Active" : "Draft"}
        </Badge>
    );

    return (
        <Page
            title="Workflows"
            backAction={{
                content: "Dashboard",
                onAction: () => router.visit("/"),
            }}
            primaryAction={{
                content: "Create Workflow",
                icon: PlusIcon,
                onAction: () => router.visit("/workflows/create"),
            }}
        >
            <Head title="Workflows" />

            <Layout>
                <Layout.Section>
                    <LegacyCard>
                        {flows.length === 0 ? (
                            <EmptyState
                                heading="Create your first workflow"
                                action={{
                                    content: "Create Workflow",
                                    icon: PlusIcon,
                                    onAction: () =>
                                        router.visit("/workflows/create"),
                                }}
                                image="https://cdn.shopify.com/s/files/1/0262/4071/2726/files/emptystate-files.png"
                            >
                                <p>
                                    Automate your Shopify store with custom
                                    flows.
                                </p>
                            </EmptyState>
                        ) : (
                            <IndexTable
                                resourceName={{
                                    singular: "flow",
                                    plural: "flows",
                                }}
                                itemCount={flows.length}
                                headings={[
                                    { title: "Name" },
                                    { title: "Status" },
                                    { title: "Executions" },
                                    { title: "Last Updated" },
                                    { title: "Actions" },
                                ]}
                                selectable={false}
                            >
                                {flows.map((flow, index) => (
                                    <IndexTable.Row
                                        id={flow.id}
                                        key={flow.id}
                                        position={index}
                                    >
                                        <IndexTable.Cell>
                                            <Text fontWeight="bold" as="span">
                                                <Link
                                                    href={`/workflows/${flow.id}`}
                                                    className="hover:underline"
                                                >
                                                    {flow.name}
                                                </Link>
                                            </Text>
                                        </IndexTable.Cell>
                                        <IndexTable.Cell>
                                            <StatusBadge active={flow.active} />
                                        </IndexTable.Cell>
                                        <IndexTable.Cell>
                                            {flow.execution_count || 0}
                                        </IndexTable.Cell>
                                        <IndexTable.Cell>
                                            {new Date(
                                                flow.updated_at
                                            ).toLocaleDateString()}
                                        </IndexTable.Cell>
                                        <IndexTable.Cell>
                                            <InlineStack gap="200">
                                                <Button
                                                    size="slim"
                                                    icon={EditIcon}
                                                    onClick={() =>
                                                        router.visit(
                                                            `/workflows/${flow.id}`
                                                        )
                                                    }
                                                />
                                                <Button
                                                    size="slim"
                                                    tone="critical"
                                                    icon={DeleteIcon}
                                                    onClick={() => {
                                                        if (
                                                            confirm(
                                                                "Are you sure you want to delete this flow?"
                                                            )
                                                        ) {
                                                            router.delete(
                                                                `/workflows/${flow.id}`
                                                            );
                                                        }
                                                    }}
                                                />
                                            </InlineStack>
                                        </IndexTable.Cell>
                                    </IndexTable.Row>
                                ))}
                            </IndexTable>
                        )}
                    </LegacyCard>
                </Layout.Section>
            </Layout>
        </Page>
    );
}
