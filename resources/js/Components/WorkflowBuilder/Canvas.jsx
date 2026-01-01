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
    useReactFlow,
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

const InnerBuilder = ({
    initialNodes,
    initialEdges,
    onNodeSelect,
    forwardedRef,
}) => {
    const reactFlowWrapper = useRef(null);
    const { screenToFlowPosition } = useReactFlow();

    const [nodes, setNodes, onNodesChange] = useNodesState(
        initialNodes || initialNodesDefault
    );
    const [edges, setEdges, onEdgesChange] = useEdgesState(initialEdges || []);
    const [selectedNode, setSelectedNode] = useState(null);

    useImperativeHandle(forwardedRef, () => ({
        getFlow: () => ({ nodes, edges }),
        updateNode: (id, data) => {
            setNodes((nds) =>
                nds.map((node) => {
                    if (node.id === id) {
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

    const onDragOver = useCallback((event) => {
        event.preventDefault();
        event.dataTransfer.dropEffect = "move";
    }, []);

    const onDrop = useCallback(
        (event) => {
            event.preventDefault();

            const type = event.dataTransfer.getData("application/reactflow");
            const label = event.dataTransfer.getData(
                "application/reactflow/label"
            );
            const n8nType = event.dataTransfer.getData(
                "application/reactflow/n8nType"
            );

            if (typeof type === "undefined" || !type) {
                return;
            }

            const position = screenToFlowPosition({
                x: event.clientX,
                y: event.clientY,
            });

            const newNode = {
                id: getId(),
                type,
                position,
                data: { label: label, n8nType: n8nType },
            };

            setNodes((nds) => nds.concat(newNode));
        },
        [screenToFlowPosition, setNodes]
    );

    return (
        <div
            className="w-full h-full bg-gray-50 relative"
            ref={reactFlowWrapper}
            style={{ width: "100%", height: "100%", position: "relative" }}
        >
            <ReactFlow
                nodes={nodes}
                edges={edges}
                onNodesChange={onNodesChange}
                onEdgesChange={onEdgesChange}
                onConnect={onConnect}
                onNodeClick={onNodeClick}
                onPaneClick={onPaneClick}
                onDrop={onDrop}
                onDragOver={onDragOver}
                nodeTypes={nodeTypes}
                fitView
            >
                <Controls />
                <MiniMap />
                <Background variant="dots" gap={12} size={1} />
            </ReactFlow>
        </div>
    );
};

const Builder = forwardRef((props, ref) => (
    <ReactFlowProvider>
        <InnerBuilder {...props} forwardedRef={ref} />
    </ReactFlowProvider>
));

export default Builder;
