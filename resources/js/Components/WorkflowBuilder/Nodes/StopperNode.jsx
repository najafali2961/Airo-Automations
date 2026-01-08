import React, { memo } from "react";
import { Handle, Position } from "@xyflow/react";
import { Text, Icon } from "@shopify/polaris";
import { XIcon } from "@shopify/polaris-icons";

export default memo(({ data, selected }) => {
    return (
        <div className="relative group">
            <Handle
                type="target"
                position={Position.Top}
                className="!w-3 !h-3 !bg-red-500 !border-2 !border-white hover:!scale-125 transition-all"
            />

            <div
                className={`
                    w-16 h-16 rounded-full flex items-center justify-center
                    bg-red-500 shadow-md border-4 border-double border-red-200
                    transition-all duration-300
                    ${
                        selected
                            ? "ring-2 ring-offset-2 ring-red-500 scale-110"
                            : "hover:scale-105"
                    }
                `}
            >
                <div className="text-white">
                    <Icon source={XIcon} tone="inherit" />
                </div>
            </div>

            <div className="absolute -bottom-6 left-1/2 -translate-x-1/2 w-max opacity-0 group-hover:opacity-100 transition-opacity">
                <Text variant="bodyXs" fontWeight="bold" tone="critical">
                    STOP
                </Text>
            </div>
        </div>
    );
});
