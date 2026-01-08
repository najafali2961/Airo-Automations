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
        <BaseEdge
            path={edgePath}
            markerEnd={markerEnd}
            style={{
                strokeWidth: 2,
                stroke: "#94a3b8",
                ...style,
            }}
        />
    );
}
