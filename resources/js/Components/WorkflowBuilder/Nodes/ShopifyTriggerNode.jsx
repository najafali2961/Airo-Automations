import React, { memo } from "react";
import { Handle, Position } from "@xyflow/react";
import { Icon, Text, LegacyCard } from "@shopify/polaris";
import { ImportIcon } from "@shopify/polaris-icons";

export default memo(({ data, selected }) => {
    return (
        <div
            className={`w-64 shadow-md rounded-md bg-white border-2 ${
                selected ? "border-blue-500" : "border-transparent"
            }`}
        >
            <div className="bg-green-100 p-2 rounded-t-md border-b border-green-200 flex items-center gap-2">
                <div className="w-5 h-5 text-green-700">
                    <Icon source={ImportIcon} />
                </div>
                <Text variant="bodyMd" fontWeight="bold" tone="success">
                    Trigger
                </Text>
            </div>
            <div className="p-3">
                <Text variant="bodyMd">{data.label}</Text>
                <Text variant="bodySm" tone="subdued">
                    {data.description || "Starts when an event occurs"}
                </Text>
            </div>
            <Handle
                type="source"
                id="then"
                position={Position.Bottom}
                className="w-3 h-3 bg-gray-500"
            />
        </div>
    );
});
