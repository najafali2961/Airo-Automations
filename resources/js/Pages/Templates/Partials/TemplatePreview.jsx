import React, { useMemo } from "react";
import {
    ReactFlow,
    Background,
    Controls,
    MiniMap,
    ReactFlowProvider,
} from "@xyflow/react";
import "@xyflow/react/dist/style.css";

import TriggerNode from "../../../Components/WorkflowBuilder/Nodes/TriggerNode";
import ActionNode from "../../../Components/WorkflowBuilder/Nodes/ActionNode";
import ConditionNode from "../../../Components/WorkflowBuilder/Nodes/ConditionNode";
import StopperNode from "../../../Components/WorkflowBuilder/Nodes/StopperNode";
import CustomEdge from "../../../Components/WorkflowBuilder/CustomEdge";

const TemplatePreviewInner = ({ flowData }) => {
    // Memoize node types
    const nodeTypes = useMemo(
        () => ({
            trigger: TriggerNode,
            action: ActionNode,
            condition: ConditionNode,
            stopper: StopperNode,
            shopifyTrigger: TriggerNode,
        }),
        [],
    );

    const edgeTypes = useMemo(
        () => ({
            custom: CustomEdge,
        }),
        [],
    );

    // Parse and prepare nodes/edges
    const { nodes, edges } = useMemo(() => {
        if (!flowData) return { nodes: [], edges: [] };

        // Handle if flowData is wrapped in 'data' prop or is the direct object
        // The structure usually matches what's saved in DB: { nodes: [], edges: [] }
        const rawNodes = flowData.nodes || [];
        const rawEdges = flowData.edges || [];

        const preparedNodes = rawNodes.map((n) => {
            // Check for React Flow format (nested data) vs DB format
            if (n.position && n.data) {
                return {
                    ...n,
                    id: String(n.id),
                    dragHandle: null, // Disable dragging
                    draggable: false,
                    connectable: false,
                    selectable: false,
                    data: {
                        ...n.data,
                        onDelete: undefined, // Disable delete
                    },
                };
            }

            // Legacy Flat DB structure rehydration
            return {
                id: String(n.id),
                type: n.type,
                position: { x: n.position_x || 0, y: n.position_y || 0 },
                draggable: false,
                connectable: false,
                selectable: false,
                data: {
                    label: n.label,
                    settings: n.settings,
                    appName: null, // Would need definitions to infer, ok to be null for preview
                    onDelete: undefined,
                },
            };
        });

        const preparedEdges = rawEdges.map((e) => {
            const source = e.source || String(e.source_node_id);
            const target = e.target || String(e.target_node_id);

            return {
                id: e.id || `e${source}-${target}`,
                source: source,
                target: target,
                type: "custom",
                label: e.label || "then",
                sourceHandle: e.source_handle || e.sourceHandle || null,
                animated: false,
                focusable: false,
                data: {
                    // No interactive handlers
                },
            };
        });

        return { nodes: preparedNodes, edges: preparedEdges };
    }, [flowData]);

    return (
        <div
            style={{
                width: "100%",
                height: "100%",
                minHeight: "300px",
                background: "#f9fafb",
            }}
        >
            <ReactFlow
                nodes={nodes}
                edges={edges}
                nodeTypes={nodeTypes}
                edgeTypes={edgeTypes}
                fitView
                nodesDraggable={false}
                nodesConnectable={false}
                elementsSelectable={false}
                zoomOnScroll={false}
                panOnScroll={true}
                minZoom={0.5}
                maxZoom={1.5}
                proOptions={{ hideAttribution: true }}
            >
                <Background gap={20} size={1} color="#e5e7eb" />
                <Controls showInteractive={false} />
            </ReactFlow>
        </div>
    );
};

export default function TemplatePreview(props) {
    return (
        <ReactFlowProvider>
            <TemplatePreviewInner {...props} />
        </ReactFlowProvider>
    );
}
