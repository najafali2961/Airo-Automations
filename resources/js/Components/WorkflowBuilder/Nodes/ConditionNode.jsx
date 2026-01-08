import React, { memo } from "react";
import { Handle, Position } from "@xyflow/react";
import { Text } from "@shopify/polaris";
import NodeCard from "../NodeCard";

export default memo(({ data, selected }) => {
    return (
        <>
            <Handle
                type="target"
                position={Position.Top}
                className="!w-4 !h-4 !bg-orange-400 !border-4 !border-white !rounded-full !-top-2 hover:!w-5 hover:!h-5 hover:!bg-orange-500 transition-all"
            />

            <NodeCard
                label={data.label || "Condition"}
                selected={selected}
                type="condition"
                subtext={
                    data.settings?.rules?.length
                        ? `${data.settings.rules.length} rule(s) (${
                              data.settings.logic || "AND"
                          })`
                        : "Configure rules..."
                }
            >
                <div className="mt-4 flex justify-between items-center bg-gray-50 rounded-lg p-2 border border-gray-100">
                    <div className="relative flex-1 flex flex-col items-center">
                        <Text variant="bodyXs" tone="success" fontWeight="bold">
                            YES
                        </Text>
                        <Handle
                            type="source"
                            id="true"
                            position={Position.Bottom}
                            className="!w-3 !h-3 !bg-green-500 !border-2 !border-white !-bottom-5"
                            style={{ left: "50%" }}
                        />
                    </div>
                    <div className="w-px h-6 bg-gray-300 mx-2" />
                    <div className="relative flex-1 flex flex-col items-center">
                        <Text
                            variant="bodyXs"
                            tone="critical"
                            fontWeight="bold"
                        >
                            NO
                        </Text>
                        <Handle
                            type="source"
                            id="false"
                            position={Position.Bottom}
                            className="!w-3 !h-3 !bg-red-500 !border-2 !border-white !-bottom-5"
                            style={{ left: "50%" }}
                        />
                    </div>
                </div>
            </NodeCard>
        </>
    );
});
