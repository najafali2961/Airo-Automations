import React, {
    useState,
    useRef,
    useCallback,
    useMemo,
    forwardRef,
    useImperativeHandle,
    useEffect,
} from "react";
import {
    ReactFlow,
    addEdge,
    useNodesState,
    useEdgesState,
    Controls,
    Background,
    useReactFlow,
    ReactFlowProvider,
    MiniMap,
} from "@xyflow/react";
import "@xyflow/react/dist/style.css";

import ConfigPanel from "./ConfigPanel";
import TriggerNode from "./Nodes/TriggerNode";
import ActionNode from "./Nodes/ActionNode";
import ConditionNode from "./Nodes/ConditionNode";
import CustomEdge from "./CustomEdge";

const initialNodesDefault = [
    {
        id: "trigger-1",
        type: "trigger",
        position: { x: 250, y: 5 },
        data: { label: "Order Created", type: "order_created" },
    },
];

let id = 1;
const getId = () => `node_${Date.now()}_${id++}`;

const InnerBuilder = ({
    initialNodes,
    initialEdges,
    onNodeSelect,
    onExecute,
    onFlowChange,
    onAddConnectorClick,
    forwardedRef,
}) => {
    const reactFlowWrapper = useRef(null);
    const { screenToFlowPosition } = useReactFlow();

    const [nodes, setNodes, onNodesChange] = useNodesState(
        initialNodes || initialNodesDefault
    );
    const [edges, setEdges, onEdgesChange] = useEdgesState(initialEdges || []);

    // Custom change handlers to track dirty state
    const handleNodesChange = useCallback(
        (changes) => {
            onNodesChange(changes);
            const isSignificant = changes.some(
                (c) => c.type !== "dimensions" && c.type !== "select"
            );
            if (isSignificant && onFlowChange) {
                onFlowChange();
            }
        },
        [onFlowChange, onNodesChange]
    );

    const handleEdgesChange = useCallback(
        (changes) => {
            onEdgesChange(changes);
            const isSignificant = changes.some((c) => c.type !== "select");
            if (isSignificant && onFlowChange) {
                onFlowChange();
            }
        },
        [onFlowChange, onEdgesChange]
    );

    const prepareEdge = useCallback(
        (edge) => ({
            ...edge,
            type: "custom",
            data: { ...edge.data, onAdd: onAddConnectorClick },
        }),
        [onAddConnectorClick]
    );

    useEffect(() => {
        if (initialNodes) setNodes(initialNodes);
        if (initialEdges) {
            setEdges(initialEdges.map((e) => prepareEdge(e)));
        }
    }, [initialNodes, initialEdges, prepareEdge, setNodes, setEdges]);

    const onConnect = useCallback(
        (params) => {
            const newEdge = prepareEdge({
                ...params,
                id: `e${params.source}-${params.target}`,
            });
            setEdges((eds) => addEdge(newEdge, eds));
            if (onFlowChange) onFlowChange();
        },
        [setEdges, onFlowChange, prepareEdge]
    );

    useImperativeHandle(forwardedRef, () => ({
        getFlow: () => ({ nodes, edges }),
        setFlow: (newNodes, newEdges) => {
            setNodes(newNodes);
            setEdges(newEdges.map((e) => prepareEdge(e)));
        },
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
        insertNodeBetween: (nodeDef, context) => {
            const { edgeId, source, target } = context;
            const sourceNode = nodes.find((n) => n.id === source);
            const targetNode = nodes.find((n) => n.id === target);

            if (!sourceNode || !targetNode) return;

            const newNodeId = getId();
            // Simple positioning: halfway + slight offset to avoid strict overlap if layout is weird
            const position = {
                x: (sourceNode.position.x + targetNode.position.x) / 2,
                y: (sourceNode.position.y + targetNode.position.y) / 2,
            };

            const newNode = {
                id: newNodeId,
                type: nodeDef.type,
                position,
                data: {
                    label: nodeDef.label,
                    settings: nodeDef.settings || {},
                    n8nType: nodeDef.n8nType,
                },
            };

            // Remove old edge
            setEdges((eds) => eds.filter((e) => e.id !== edgeId));

            // Add new node
            setNodes((nds) => nds.concat(newNode));

            // Add two new edges
            const edge1 = prepareEdge({
                id: `e${source}-${newNodeId}`,
                source: source,
                target: newNodeId,
                label: "then",
            });

            const edge2 = prepareEdge({
                id: `e${newNodeId}-${target}`,
                source: newNodeId,
                target: target,
                label: "then",
            });

            setEdges((eds) => [...eds, edge1, edge2]);
            if (onFlowChange) onFlowChange();
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

    const edgeTypes = useMemo(
        () => ({
            custom: CustomEdge,
        }),
        []
    );

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
            const appName = event.dataTransfer.getData(
                "application/reactflow/appName"
            );
            const defaultsStr = event.dataTransfer.getData(
                "application/reactflow/defaults"
            );

            if (typeof type === "undefined" || !type) return;

            let defaults = {};
            try {
                if (defaultsStr) defaults = JSON.parse(defaultsStr);
            } catch (e) {
                console.error("Failed to parse node defaults", e);
            }

            const position = screenToFlowPosition({
                x: event.clientX,
                y: event.clientY,
            });

            const newNode = {
                id: getId(),
                type,
                position,
                data: {
                    label: label,
                    appName: appName,
                    n8nType: n8nType,
                    settings: defaults,
                },
            };

            setNodes((nds) => nds.concat(newNode));
            if (onFlowChange) onFlowChange();
        },
        [screenToFlowPosition, setNodes, onFlowChange]
    );

    const onDragOver = useCallback((event) => {
        event.preventDefault();
        event.dataTransfer.dropEffect = "move";
    }, []);

    const onNodeClick = useCallback(
        (_, node) => {
            if (onNodeSelect) onNodeSelect(node);
        },
        [onNodeSelect]
    );

    const onPaneClick = useCallback(() => {
        if (onNodeSelect) onNodeSelect(null);
    }, [onNodeSelect]);

    return (
        <div
            className="w-full h-full bg-gray-50 relative"
            ref={reactFlowWrapper}
            style={{ width: "100%", height: "100%", position: "relative" }}
        >
            <ReactFlow
                nodes={nodes}
                edges={edges}
                onNodesChange={handleNodesChange}
                onEdgesChange={handleEdgesChange}
                onConnect={onConnect}
                onInit={(instance) => {
                    // Fit view initially if needed
                    instance.fitView();
                }}
                onDrop={onDrop}
                onDragOver={onDragOver}
                edgeTypes={edgeTypes}
                nodeTypes={nodeTypes}
                onNodeClick={onNodeClick}
                onPaneClick={onPaneClick}
                fitView
            >
                <Background variant="dots" gap={20} size={2} color="#cbd5e1" />
                <Controls />
                <MiniMap />
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
