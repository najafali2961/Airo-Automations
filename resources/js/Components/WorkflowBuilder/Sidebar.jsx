import React from "react";
import { Box, BlockStack, Text, Icon, InlineStack } from "@shopify/polaris";
import {
    AlertCircleIcon,
    ArrowRightIcon,
    CheckIcon,
} from "@shopify/polaris-icons";

export default function Sidebar() {
    const nodes = [
        {
            type: "trigger",
            label: "Trigger",
            icon: AlertCircleIcon,
            color: "bg-fill-success",
        },
        {
            type: "condition",
            label: "Condition",
            icon: CheckIcon,
            color: "bg-fill-warning",
        },
        {
            type: "action",
            label: "Action",
            icon: ArrowRightIcon,
            color: "bg-fill-info",
        },
    ];

    const onDragStart = (event, node) => {
        event.dataTransfer.setData("application/reactflow", node.type);
        event.dataTransfer.setData("application/reactflow/label", node.label);
        event.dataTransfer.effectAllowed = "move";
    };

    return (
        <Box padding="300" background="bg-surface" minHeight="100%">
            <BlockStack gap="400">
                <Text variant="headingSm">Nodes</Text>
                {nodes.map((node) => (
                    <div
                        key={node.type}
                        draggable
                        onDragStart={(e) => onDragStart(e, node)}
                        style={{ cursor: "grab" }}
                    >
                        <Box
                            padding="300"
                            background="bg-surface"
                            borderRadius="200"
                            shadow="100"
                            borderColor="border"
                            borderWidth="025"
                        >
                            <InlineStack
                                gap="300"
                                align="start"
                                blockAlign="center"
                            >
                                <Box
                                    background={node.color}
                                    borderRadius="100"
                                    padding="050"
                                >
                                    <Icon source={node.icon} tone="base" />
                                </Box>
                                <Text fontWeight="bold">{node.label}</Text>
                            </InlineStack>
                        </Box>
                    </div>
                ))}
            </BlockStack>
        </Box>
    );
}
