import React, {
    useState,
    useRef,
    useCallback,
    useMemo,
    forwardRef,
    useImperativeHandle,
} from "react";
import {
    ReactFlow,
    ReactFlowProvider,
    addEdge,
    useNodesState,
    useEdgesState,
    Controls,
    Background,
    MiniMap,
} from "@xyflow/react";
import "@xyflow/react/dist/style.css";

import ConfigPanel from "./ConfigPanel";
import TriggerNode from "./Nodes/TriggerNode";
import ActionNode from "./Nodes/ActionNode";
import ConditionNode from "./Nodes/ConditionNode";

const initialNodesDefault = [
    {
        id: "trigger-1",
        type: "trigger",
        position: { x: 250, y: 5 },
        data: { label: "Order Created", type: "order_created" },
    },
];

let id = 1;
const getId = () => `node_${id++}`;

const Builder = forwardRef(
    ({ initialNodes, initialEdges, onNodeSelect }, ref) => {
        const reactFlowWrapper = useRef(null);
        const [nodes, setNodes, onNodesChange] = useNodesState(
            initialNodes || initialNodesDefault
        );
        const [edges, setEdges, onEdgesChange] = useEdgesState(
            initialEdges || []
        );
        const [selectedNode, setSelectedNode] = useState(null);

        useImperativeHandle(ref, () => ({
            getFlow: () => ({ nodes, edges }),
            updateNode: (id, data) => {
                setNodes((nds) =>
                    nds.map((node) => {
                        if (node.id === id) {
                            // Merge data deeply or shallowly? Shallow merge of 'data' prop is usually enough
                            // N8N properties are usually in data.parameters or similar.
                            return { ...node, data: { ...node.data, ...data } };
                        }
                        return node;
                    })
                );
            },
        }));
        const nodeTypes = useMemo(
            () => ({
                trigger: TriggerNode,
                action: ActionNode,
                condition: ConditionNode,
                // Keep backward compatibility if needed, or map old types
                shopifyTrigger: TriggerNode,
            }),
            []
        );

        const onConnect = useCallback(
            (params) => setEdges((eds) => addEdge(params, eds)),
            []
        );

        const onNodeClick = (event, node) => {
            setSelectedNode(node);
            if (onNodeSelect) onNodeSelect(node);
        };

        const onPaneClick = () => {
            setSelectedNode(null);
            if (onNodeSelect) onNodeSelect(null);
        };

        return (
            <div
                className="flex h-full w-full bg-gray-50"
                style={{ width: "100%", height: "100%" }}
            >
                <div
                    className="flex-grow h-full relative"
                    ref={reactFlowWrapper}
                    style={{ width: "100%", height: "100%" }}
                >
                    <ReactFlow
                        nodes={nodes}
                        edges={edges}
                        onNodesChange={onNodesChange}
                        onEdgesChange={onEdgesChange}
                        onConnect={onConnect}
                        onNodeClick={onNodeClick}
                        onPaneClick={onPaneClick}
                        nodeTypes={nodeTypes}
                        fitView
                    >
                        <Controls />
                        <MiniMap />
                        <Background variant="dots" gap={12} size={1} />
                    </ReactFlow>
                </div>
            </div>
        );
    }
);

export default Builder;
