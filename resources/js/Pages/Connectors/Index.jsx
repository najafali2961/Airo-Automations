import React from "react";
import { Head, router } from "@inertiajs/react";
import {
    Page,
    Layout,
    Card,
    ResourceList,
    ResourceItem,
    Text,
    Avatar,
    Badge,
    Button,
    BlockStack,
    InlineStack,
} from "@shopify/polaris";

export default function Connectors({ connectors }) {
    // Helper to render status badge
    const StatusBadge = ({ status }) => {
        let tone = "subdued";
        if (status === "Connected") tone = "success";
        else if (status === "Disconnected") tone = "critical";

        return <Badge tone={tone}>{status.toUpperCase()}</Badge>;
    };

    return (
        <Page title="Connectors" subtitle="Manage your external integrations">
            <Head title="Connectors" />

            <Layout>
                <Layout.Section>
                    <Card padding="0">
                        <ResourceList
                            resourceName={{
                                singular: "connector",
                                plural: "connectors",
                            }}
                            items={connectors}
                            renderItem={(item) => {
                                const {
                                    key,
                                    title,
                                    description,
                                    icon,
                                    status,
                                    is_active,
                                    auth_url,
                                } = item;
                                const media = (
                                    <Avatar source={icon} alt={title} />
                                ); // Using Avatar as simple image holder

                                return (
                                    <ResourceItem
                                        id={key}
                                        media={media}
                                        accessibilityLabel={`View details for ${title}`}
                                        persistActions
                                    >
                                        <InlineStack
                                            align="space-between"
                                            blockAlign="center"
                                        >
                                            <BlockStack gap="050">
                                                <Text
                                                    variant="headingMd"
                                                    fontWeight="bold"
                                                >
                                                    {title}
                                                </Text>
                                                <Text
                                                    variant="bodySm"
                                                    tone="subdued"
                                                >
                                                    {description}
                                                </Text>
                                            </BlockStack>

                                            <InlineStack
                                                gap="400"
                                                blockAlign="center"
                                            >
                                                <StatusBadge status={status} />

                                                {status === "Disconnected" &&
                                                    is_active && (
                                                        <Button
                                                            variant="primary"
                                                            onClick={() => {
                                                                const separator =
                                                                    auth_url.includes(
                                                                        "?"
                                                                    )
                                                                        ? "&"
                                                                        : "?";
                                                                const finalUrl =
                                                                    auth_url +
                                                                    separator +
                                                                    window.location.search.substring(
                                                                        1
                                                                    );

                                                                window.open(
                                                                    finalUrl,
                                                                    "_blank"
                                                                );
                                                            }}
                                                        >
                                                            Connect
                                                        </Button>
                                                    )}

                                                {status === "Connected" && (
                                                    <Button disabled>
                                                        Connected
                                                    </Button>
                                                )}

                                                {!is_active && (
                                                    <Button disabled>
                                                        Coming Soon
                                                    </Button>
                                                )}
                                            </InlineStack>
                                        </InlineStack>
                                    </ResourceItem>
                                );
                            }}
                        />
                    </Card>
                </Layout.Section>
            </Layout>
        </Page>
    );
}
