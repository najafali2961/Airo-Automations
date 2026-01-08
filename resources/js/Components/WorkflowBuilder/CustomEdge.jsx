import React, { useState } from "react";
import { BaseEdge, EdgeLabelRenderer, getBezierPath } from "@xyflow/react";
import { Icon } from "@shopify/polaris";
import { PlusIcon } from "@shopify/polaris-icons";

export default function CustomEdge({
    id,
    sourceX,
    sourceY,
    targetX,
    targetY,
    sourcePosition,
    targetPosition,
    style = {},
    markerEnd,
    data,
    label,
}) {
    const [isEditing, setIsEditing] = useState(false);
    const [labelText, setLabelText] = useState(label || "then");

    React.useEffect(() => {
        setLabelText(label || "then");
    }, [label]);

    const [edgePath, labelX, labelY] = getBezierPath({
        sourceX,
        sourceY,
        sourcePosition,
        targetX,
        targetY,
        targetPosition,
    });

    const onEdgeClick = (evt) => {
        evt.stopPropagation();
        if (data?.onAdd) {
            data.onAdd(id, data.source, data.target);
        }
    };

    const handleLabelClick = (e) => {
        e.stopPropagation();
        setIsEditing(true);
    };

    const handleLabelBlur = () => {
        setIsEditing(false);
        if (data?.onLabelChange) {
            data.onLabelChange(id, labelText);
        }
    };

    const handleKeyDown = (e) => {
        if (e.key === "Enter") {
            handleLabelBlur();
        }
    };

    return (
        <>
            <BaseEdge
                path={edgePath}
                markerEnd={markerEnd}
                style={{
                    strokeWidth: 3,
                    stroke: "#b1b1b7",
                    ...style,
                }}
                className="flow-animation"
            />
            <EdgeLabelRenderer>
                <div
                    style={{
                        position: "absolute",
                        transform: `translate(-50%, -50%) translate(${labelX}px,${labelY}px)`,
                        fontSize: 12,
                        pointerEvents: "all",
                    }}
                    className="nodrag nopan"
                >
                    <div className="flex flex-col items-center gap-1">
                        {isEditing ? (
                            <input
                                autoFocus
                                className="px-2 py-1 rounded border border-blue-500 text-xs shadow-sm bg-white"
                                value={labelText}
                                onChange={(e) => setLabelText(e.target.value)}
                                onBlur={handleLabelBlur}
                                onKeyDown={handleKeyDown}
                                onClick={(e) => e.stopPropagation()}
                            />
                        ) : (
                            <div
                                onClick={handleLabelClick}
                                className="px-2 py-1 bg-white rounded-md border border-gray-200 shadow-sm text-gray-500 hover:text-blue-600 hover:border-blue-300 cursor-text transition-all text-xs font-medium select-none"
                            >
                                {label || "then"}
                            </div>
                        )}
                    </div>
                </div>
            </EdgeLabelRenderer>
        </>
    );
}
