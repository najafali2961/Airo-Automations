import React from "react";
import { Head } from "@inertiajs/react";
import {
    Page,
    Layout,
    LegacyCard,
    DataTable,
    Badge,
    Text,
    Button,
    BlockStack,
    CalloutCard,
} from "@shopify/polaris";

export default function Dashboard({ shop, stats, executions, n8nUrl }) {
    // Prepare DataTable rows
    const rows = executions.map((exec) => [
        <Badge
            tone={
                exec.status === "processed"
                    ? "success"
                    : exec.status === "failed"
                    ? "critical"
                    : "attention"
            }
        >
            {exec.status}
        </Badge>,
        exec.topic,
        new Date(exec.created_at).toLocaleString(),
        <span style={{ fontFamily: "monospace" }}>
            {JSON.stringify(JSON.parse(exec.payload)).substring(0, 30)}...
        </span>,
    ]);

    return (
        <Page
            title="Automation Dashboard"
            subtitle={`Welcome back, ${shop.name}`}
            primaryAction={{
                content: "Open Workflow Editor",
                url: n8nUrl,
                external: true,
            }}
        >
            <Head title="Automation Dashboard" />

            <Layout>
                {/* Stats Section */}
                <Layout.Section>
                    <div
                        style={{
                            display: "grid",
                            gridTemplateColumns: "1fr 1fr 1fr",
                            gap: "1rem",
                        }}
                    >
                        <LegacyCard title="Total Executions" sectioned>
                            <Text variant="heading2xl" as="h3">
                                {stats.total_executions}
                            </Text>
                        </LegacyCard>
                        <LegacyCard title="Successful" sectioned>
                            <Text variant="heading2xl" as="h3" tone="success">
                                {stats.success_executions}
                            </Text>
                        </LegacyCard>
                        <LegacyCard title="Failed" sectioned>
                            <Text variant="heading2xl" as="h3" tone="critical">
                                {stats.failed_executions}
                            </Text>
                        </LegacyCard>
                    </div>
                </Layout.Section>

                {/* Workflow Builder Callout */}
                <Layout.Section>
                    <CalloutCard
                        title="Automation Builder"
                        illustration="https://cdn.shopify.com/s/assets/admin/checkout/settings-customizecart-705f57c725ac05be5a34ec20c05b94298cb8afd10aac7bd9c7ad02030f48cfa0.svg"
                        primaryAction={{
                            content: "Open Workflow Editor",
                            url: n8nUrl,
                            external: true,
                        }}
                    >
                        <p>
                            Create and manage your workflows visually using our
                            advanced builder.
                        </p>
                    </CalloutCard>
                </Layout.Section>

                {/* Executions Table */}
                <Layout.Section>
                    <LegacyCard title="Recent Activity">
                        {executions.length === 0 ? (
                            <LegacyCard.Section>
                                <Text tone="subdued" as="p">
                                    No executions yet.
                                </Text>
                            </LegacyCard.Section>
                        ) : (
                            <DataTable
                                columnContentTypes={[
                                    "text",
                                    "text",
                                    "text",
                                    "text",
                                ]}
                                headings={[
                                    "Status",
                                    "Topic",
                                    "Date",
                                    "Payload Snippet",
                                ]}
                                rows={rows}
                            />
                        )}
                    </LegacyCard>
                </Layout.Section>
            </Layout>
        </Page>
    );
}
