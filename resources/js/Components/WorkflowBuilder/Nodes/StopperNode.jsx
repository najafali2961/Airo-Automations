import React, { memo } from "react";
import { Handle, Position } from "@xyflow/react";
import { Text, Icon } from "@shopify/polaris";
import { XIcon, DeleteIcon, AlertCircleIcon } from "@shopify/polaris-icons";

export default memo(({ data, selected }) => {
    const { isValid = true, validationMessage = "", onDelete } = data;

    return (
        <div className="relative group">
            <Handle
                type="target"
                position={Position.Top}
                className="!w-3 !h-3 !bg-red-500 !border-2 !border-white hover:!scale-125 transition-all"
            />

            {/* Validation Badge */}
            {!isValid && (
                <div
                    className="absolute -top-3 -left-3 z-10 bg-red-100 text-red-600 rounded-full p-1 border border-red-200 shadow-sm"
                    title={validationMessage}
                >
                    <Icon source={AlertCircleIcon} tone="critical" />
                </div>
            )}

            {/* Delete Button */}
            {onDelete && (
                <div
                    className="absolute -top-2 -right-2 z-10 opacity-0 group-hover:opacity-100 transition-opacity"
                    onClick={(e) => {
                        e.stopPropagation();
                        onDelete();
                    }}
                >
                    <div className="bg-white rounded-full p-1.5 shadow-md border border-gray-200 hover:bg-red-50 hover:text-red-600 cursor-pointer">
                        <Icon source={DeleteIcon} tone="critical" />
                    </div>
                </div>
            )}

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
