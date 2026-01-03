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
            <div className="p-3 border-b border-gray-100">
                <Text variant="bodyMd">{data.label || "If Condition"}</Text>
                {data.settings?.rules?.length > 0 && (
                    <div className="mt-1">
                        <Text variant="bodyXs" tone="subdued">
                            {data.settings.rules.length === 1
                                ? `${data.settings.rules[0].field} ${data.settings.rules[0].operator} ${data.settings.rules[0].value}`
                                : `${data.settings.rules.length} rules (${
                                      data.settings.logic || "AND"
                                  })`}
                        </Text>
                    </div>
                )}
            </div>

            <div className="flex justify-between items-center p-2 bg-gray-50 rounded-b-md">
                <div className="relative flex-1 flex flex-col items-center">
                    <Text variant="bodyXs" tone="success" fontWeight="bold">
                        TRUE
                    </Text>
                    <Handle
                        type="source"
                        id="true"
                        position={Position.Bottom}
                        className="w-3 h-3 bg-green-500"
                        style={{ left: "25%" }}
                    />
                </div>
                <div className="w-px h-6 bg-gray-200" />
                <div className="relative flex-1 flex flex-col items-center">
                    <Text variant="bodyXs" tone="critical" fontWeight="bold">
                        FALSE
                    </Text>
                    <Handle
                        type="source"
                        id="false"
                        position={Position.Bottom}
                        className="w-3 h-3 bg-red-500"
                        style={{ left: "75%" }}
                    />
                </div>
            </div>
        </div>
    );
});
