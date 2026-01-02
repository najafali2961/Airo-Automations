import React from "react";
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
}) {
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

    return (
        <>
            <BaseEdge path={edgePath} markerEnd={markerEnd} style={style} />
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
                    <button
                        className="flex items-center justify-center w-6 h-6 bg-white border border-gray-300 rounded-full shadow-sm hover:bg-blue-50 hover:border-blue-500 transition-colors"
                        onClick={onEdgeClick}
                        title="Add step"
                    >
                        <div style={{ width: 14 }}>
                            <Icon source={PlusIcon} tone="base" />
                        </div>
                    </button>
                </div>
            </EdgeLabelRenderer>
        </>
    );
}
