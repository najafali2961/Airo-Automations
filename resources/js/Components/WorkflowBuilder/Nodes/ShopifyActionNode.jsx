import React, { memo } from "react";
import { Handle, Position } from "@xyflow/react";
import { Icon, Text } from "@shopify/polaris";
import { ExportIcon } from "@shopify/polaris-icons";

export default memo(({ data, selected }) => {
    return (
        <div
            className={`w-64 shadow-md rounded-md bg-white border-2 ${
                selected ? "border-blue-500" : "border-transparent"
            }`}
        >
            <Handle
                type="target"
                position={Position.Top}
                className="w-3 h-3 bg-gray-500"
            />

            <div className="bg-blue-50 p-2 rounded-t-md border-b border-blue-100 flex items-center gap-2">
                <div className="w-5 h-5 text-blue-700">
                    <Icon source={ExportIcon} />
                </div>
                <Text variant="bodyMd" fontWeight="bold" tone="info">
                    Action
                </Text>
            </div>
            <div className="p-3">
                <Text variant="bodyMd">{data.label}</Text>
                <Text variant="bodySm" tone="subdued">
                    {data.description || "Performs a task"}
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
