import React from "react";
import { Text, Icon } from "@shopify/polaris";
import { DeleteIcon, AlertCircleIcon } from "@shopify/polaris-icons";
import { getIconAndColor } from "./utils";

export default function NodeCard({
    label,
    subtext,
    selected,
    children,
    type = "action",
    appName,
    className = "",
    onDelete,
    isValid = true,
    validationMessage = "",
}) {
    const { icon, color, isUrl } = getIconAndColor(appName || label || type);
    const isTrigger = type === "trigger";

    return (
        <div
            className={`
                group relative min-w-[240px] rounded-xl bg-white 
                transition-all duration-300 ease-out
                ${
                    selected
                        ? "ring-2 ring-offset-2 ring-blue-600 shadow-xl scale-[1.02]"
                        : "border border-gray-200/60 shadow-md hover:shadow-lg hover:-translate-y-0.5"
                }
                ${className}
            `}
        >
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

            {/* Header Stripe */}
            <div className={`h-1.5 w-full rounded-t-xl ${color}`} />

            <div className="p-4">
                {/* Header Section */}
                <div className="flex items-start justify-between mb-3">
                    <div className="flex items-center gap-3">
                        <div
                            className={`
                            flex items-center justify-center w-10 h-10 rounded-lg 
                            ${selected ? "bg-gray-100" : "bg-gray-50"} 
                            shadow-sm border border-gray-100 text-xl overflow-hidden
                        `}
                        >
                            {isUrl ? (
                                <img
                                    src={icon}
                                    alt=""
                                    className="w-6 h-6 object-contain"
                                />
                            ) : (
                                icon
                            )}
                        </div>
                        <div>
                            <Text variant="bodyMd" fontWeight="bold" as="h3">
                                <span className="text-gray-800">{label}</span>
                            </Text>
                            <Text variant="bodyXs" tone="subdued">
                                {type.toUpperCase()}
                            </Text>
                        </div>
                    </div>
                </div>

                {/* Content/Status */}
                <div className="bg-gray-50/50 rounded-lg p-2.5 border border-gray-100/50">
                    <Text
                        variant="bodySm"
                        tone={!isValid ? "critical" : "subdued"}
                        breakWord
                    >
                        {!isValid
                            ? validationMessage
                            : subtext || "Configure this step..."}
                    </Text>
                </div>

                {children}
            </div>

            {/* Selection Indicator */}
            {selected && (
                <div className="absolute -top-1 -right-1 w-3 h-3 bg-blue-500 rounded-full border-2 border-white animate-pulse" />
            )}
        </div>
    );
}
