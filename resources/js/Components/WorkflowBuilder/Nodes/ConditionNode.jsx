import React, { memo } from "react";
import { Handle, Position } from "@xyflow/react";
import { Icon, Text } from "@shopify/polaris";
import { FilterIcon } from "@shopify/polaris-icons";

export default memo(({ data, selected }) => {
    return (
        <div
            className={`w-64 shadow-md rounded-md bg-white border-2 ${
                selected ? "border-orange-500" : "border-transparent"
            }`}
        >
            <Handle
                type="target"
                position={Position.Top}
                className="w-3 h-3 bg-gray-500"
            />

            <div className="bg-orange-50 p-2 rounded-t-md border-b border-orange-100 flex items-center gap-2">
                <div className="w-5 h-5 text-orange-700">
                    <Icon source={FilterIcon} />
                </div>
                <Text variant="bodyMd" fontWeight="bold" tone="warning">
                    Condition
                </Text>
            </div>
            <div className="p-3">
                <Text variant="bodyMd">{data.label || "If Condition"}</Text>
                {data.config?.field && (
                    <Text variant="bodySm" tone="subdued">
                        {data.config.field} {data.config.operator}{" "}
                        {data.config.value}
                    </Text>
                )}
            </div>

            <Handle
                type="source"
                position={Position.Bottom}
                className="w-3 h-3 bg-gray-500"
            />
        </div>
    );
});
