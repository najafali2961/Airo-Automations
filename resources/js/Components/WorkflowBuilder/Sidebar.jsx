import React from "react";
import { LegacyCard, Text, Icon } from "@shopify/polaris";
import { ImportIcon, ExportIcon } from "@shopify/polaris-icons";

export default function Sidebar() {
    const onDragStart = (event, nodeType, label) => {
        event.dataTransfer.setData("application/reactflow", nodeType);
        event.dataTransfer.setData("application/reactflow/label", label);
        event.dataTransfer.effectAllowed = "move";
    };

    return (
        <div className="w-64 border-r border-gray-200 bg-white p-4 flex flex-col gap-4 h-full overflow-y-auto">
            <Text variant="headingMd" as="h3">
                Nodes
            </Text>

            <div className="flex flex-col gap-3">
                <Text variant="bodySm" tone="subdued">
                    Triggers
                </Text>
                <div
                    className="p-3 border border-gray-300 rounded cursor-grab hover:bg-gray-50 flex items-center gap-2"
                    onDragStart={(event) =>
                        onDragStart(event, "shopifyTrigger", "Shopify Trigger")
                    }
                    draggable
                >
                    <div className="w-5 h-5 text-green-600">
                        <Icon source={ImportIcon} />
                    </div>
                    <Text variant="bodyMd">Shopify Trigger</Text>
                </div>

                <Text variant="bodySm" tone="subdued">
                    Actions
                </Text>
                <div
                    className="p-3 border border-gray-300 rounded cursor-grab hover:bg-gray-50 flex items-center gap-2"
                    onDragStart={(event) =>
                        onDragStart(event, "action", "Action")
                    }
                    draggable
                >
                    <div className="w-5 h-5 text-blue-600">
                        <Icon source={ExportIcon} />
                    </div>
                    <Text variant="bodyMd">Generic Action</Text>
                </div>
            </div>
        </div>
    );
}
