import React from "react";
import {
    Page,
    Layout,
    LegacyCard,
    DataTable,
    Badge,
    Button,
    Text,
    EmptyState,
} from "@shopify/polaris";
import { Head, Link, router } from "@inertiajs/react";

export default function Index({ workflows }) {
    const rows = workflows.map((wf) => [
        <Text fontWeight="bold" as="span">
            <Link onClick={() => router.visit(`/workflows/${wf.id}`)}>
                {wf.name}
            </Link>
        </Text>,
        <Badge tone={wf.status ? "success" : "subdued"}>
            {wf.status ? "Active" : "Draft"}
        </Badge>,
        wf.n8n_id || "Not Synced",
        new Date(wf.created_at).toLocaleDateString(),
        <div style={{ display: "flex", gap: "0.5rem" }}>
            <Button
                size="slim"
                onClick={() =>
                    router.post(
                        `/workflows/${wf.id}/${
                            wf.status ? "deactivate" : "activate"
                        }`
                    )
                }
            >
                {wf.status ? "Deactivate" : "Activate"}
            </Button>
            <Button
                size="slim"
                onClick={() => router.visit(`/workflows/${wf.id}`)}
            >
                Edit
            </Button>
            <Button
                size="slim"
                tone="critical"
                onClick={() => router.delete(`/workflows/${wf.id}`)}
            >
                Delete
            </Button>
        </div>,
    ]);

    return (
        <Page
            title="Workflows"
            primaryAction={{
                content: "Create Workflow",
                onAction: () => router.visit("/workflows/new"),
            }}
        >
            <Head title="Workflows" />

            <Layout>
                <Layout.Section>
                    <LegacyCard>
                        {workflows.length === 0 ? (
                            <EmptyState
                                heading="Create your first workflow"
                                action={{
                                    content: "Create Workflow",
                                    onAction: () =>
                                        router.visit("/workflows/new"),
                                }}
                                image="https://cdn.shopify.com/s/files/1/0262/4071/2726/files/emptystate-files.png"
                            >
                                <p>
                                    Automate your business with custom
                                    workflows.
                                </p>
                            </EmptyState>
                        ) : (
                            <DataTable
                                columnContentTypes={[
                                    "text",
                                    "text",
                                    "text",
                                    "text",
                                    "text",
                                ]}
                                headings={[
                                    "Name",
                                    "Status",
                                    "N8N ID",
                                    "Created",
                                    "Actions",
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
