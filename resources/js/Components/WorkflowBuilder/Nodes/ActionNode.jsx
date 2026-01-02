import React, { memo } from "react";
import { Handle, Position } from "@xyflow/react";
import { Text } from "@shopify/polaris";

export default memo(({ data, selected }) => {
    return (
        <div
            className={`min-w-[200px] shadow-sm rounded-lg bg-white border ${
                selected
                    ? "border-blue-500 ring-1 ring-blue-500"
                    : "border-gray-200"
            }`}
        >
            <Handle
                type="target"
                position={Position.Top}
                className="w-3 h-3 bg-gray-400 !border-2 !border-white"
            />
            <div className="p-3 flex items-center gap-3 border-b border-gray-100 bg-gray-50 rounded-t-lg">
                <span className="text-xl">✓</span>
                <Text variant="bodyMd" fontWeight="bold">
                    {data.label || "Action"}
                </Text>
            </div>
            <div className="p-3">
                <Text variant="bodySm" tone="subdued">
                    {data.settings?.action || "Configure..."}
                </Text>
            </div>
            <Handle
                type="source"
                position={Position.Bottom}
                className="w-3 h-3 bg-gray-400 !border-2 !border-white"
            />
        </div>
    );
});
