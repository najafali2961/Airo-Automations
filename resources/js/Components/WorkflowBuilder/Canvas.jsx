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
    getIncomers,
    getOutgoers,
} from "@xyflow/react";
import "@xyflow/react/dist/style.css";

import ConfigPanel from "./ConfigPanel";
import TriggerNode from "./Nodes/TriggerNode";
import ActionNode from "./Nodes/ActionNode";
import ConditionNode from "./Nodes/ConditionNode";
import StopperNode from "./Nodes/StopperNode";
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

import { getLayoutedElements } from "./layoutUtils";

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
    const { screenToFlowPosition, getNodes, getEdges, fitView } =
        useReactFlow();

    // ... (existing state) ...
    const [nodes, setNodes, onNodesChange] = useNodesState(
        initialNodes || initialNodesDefault
    );
    const [edges, setEdges, onEdgesChange] = useEdgesState(initialEdges || []);

    // ... (validation logic) ...
    const validateFlow = useCallback((currentNodes, currentEdges) => {
        // ... (existing body) ...
        let allValid = true;

        const updatedNodes = currentNodes.map((node) => {
            const incomers = getIncomers(node, currentNodes, currentEdges);
            const outgoers = getOutgoers(node, currentNodes, currentEdges);

            let isValid = true;
            let message = "";

            if (node.type === "trigger") {
                if (outgoers.length === 0) {
                    isValid = false;
                    message = "Trigger must be connected to an action";
                }
            } else if (node.type === "action") {
                if (incomers.length === 0) {
                    isValid = false;
                    message = "Action is disconnected";
                }
            } else if (node.type === "condition") {
                if (outgoers.length === 0) {
                    isValid = false;
                    message = "Condition must have at least one path";
                } else if (incomers.length === 0) {
                    isValid = false;
                    message = "Condition is disconnected";
                }
            } else if (node.type === "stopper") {
                if (incomers.length === 0) {
                    isValid = false;
                    message = "Stopper is disconnected";
                }
            }

            if (!isValid) allValid = false;

            // Only update if changed to avoid loop
            if (
                node.data.isValid !== isValid ||
                node.data.validationMessage !== message
            ) {
                return {
                    ...node,
                    data: {
                        ...node.data,
                        isValid,
                        validationMessage: message,
                    },
                };
            }
            return node;
        });
        return { updatedNodes, allValid };
    }, []);

    // ... (useEffects) ...
    // Validation Effect
    useEffect(() => {
        const { updatedNodes, allValid } = validateFlow(nodes, edges);

        const hasChanges = updatedNodes.some((n, i) => {
            return (
                n.data.isValid !== nodes[i].data.isValid ||
                n.data.validationMessage !== nodes[i].data.validationMessage
            );
        });

        if (hasChanges) {
            setNodes(updatedNodes);
        }
    }, [nodes.length, edges.length, JSON.stringify(edges), validateFlow]);

    // Deletion Logic
    const handleDeleteNode = useCallback(
        (nodeId) => {
            setNodes((nds) => nds.filter((n) => n.id !== nodeId));
            setEdges((eds) =>
                eds.filter((e) => e.source !== nodeId && e.target !== nodeId)
            );
            if (onFlowChange) onFlowChange();
            if (onNodeSelect) onNodeSelect(null);
        },
        [setNodes, setEdges, onFlowChange, onNodeSelect]
    );

    // Node Hydration
    useEffect(() => {
        setNodes((nds) =>
            nds.map((node) => {
                if (node.data.onDelete) return node;
                return {
                    ...node,
                    data: {
                        ...node.data,
                        onDelete: () => handleDeleteNode(node.id),
                    },
                };
            })
        );
    }, [handleDeleteNode, setNodes, nodes.length]);

    // Change Handlers
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

    const onLabelChange = useCallback(
        (edgeId, newLabel) => {
            setEdges((eds) =>
                eds.map((e) => {
                    if (e.id === edgeId) {
                        return { ...e, label: newLabel };
                    }
                    return e;
                })
            );
            if (onFlowChange) onFlowChange();
        },
        [setEdges, onFlowChange]
    );

    const prepareEdge = useCallback(
        (edge) => ({
            ...edge,
            type: "custom",
            data: {
                ...edge.data,
                onAdd: onAddConnectorClick,
                onLabelChange: onLabelChange,
            },
        }),
        [onAddConnectorClick, onLabelChange]
    );

    useEffect(() => {
        if (initialNodes) {
            const hydrated = initialNodes.map((n) => ({
                ...n,
                data: { ...n.data, onDelete: () => handleDeleteNode(n.id) },
            }));
            setNodes(hydrated);
        }
        if (initialEdges) {
            setEdges(initialEdges.map((e) => prepareEdge(e)));
        }
    }, [
        initialNodes,
        initialEdges,
        prepareEdge,
        setNodes,
        setEdges,
        handleDeleteNode,
    ]);

    const onConnect = useCallback(
        (params) => {
            const newEdge = prepareEdge({
                ...params,
                id: `e${params.source}-${params.target}`,
                label: params.sourceHandle || "then",
            });
            setEdges((eds) => addEdge(newEdge, eds));
            if (onFlowChange) onFlowChange();
        },
        [setEdges, onFlowChange, prepareEdge]
    );

    // Exposed Methods
    useImperativeHandle(forwardedRef, () => ({
        getFlow: () => {
            const cleanNodes = nodes.map((n) => {
                const { onDelete, ...restData } = n.data;
                return { ...n, data: restData };
            });
            return { nodes: cleanNodes, edges };
        },
        validate: () => {
            const { allValid } = validateFlow(nodes, edges);
            return allValid;
        },
        autoLayout: () => {
            const { nodes: layoutedNodes, edges: layoutedEdges } =
                getLayoutedElements(nodes, edges);
            setNodes([...layoutedNodes]);
            setEdges([...layoutedEdges]);
            setTimeout(() => {
                fitView();
            }, 0);
        },
        setFlow: (newNodes, newEdges) => {
            setNodes(
                newNodes.map((n) => ({
                    ...n,
                    data: { ...n.data, onDelete: () => handleDeleteNode(n.id) },
                }))
            );
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
                    onDelete: () => handleDeleteNode(newNodeId),
                },
            };

            setEdges((eds) => eds.filter((e) => e.id !== edgeId));
            setNodes((nds) => nds.concat(newNode));

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
        addNode: (nodeDef) => {
            const newNodeId = getId();
            const position = { x: 250, y: 100 + nodes.length * 50 };

            const newNode = {
                id: newNodeId,
                type: nodeDef.type,
                position,
                data: {
                    label: nodeDef.label,
                    appName: nodeDef.category || nodeDef.name,
                    settings: nodeDef.settings || {},
                    onDelete: () => handleDeleteNode(newNodeId),
                },
            };

            setNodes((nds) => nds.concat(newNode));
            if (onFlowChange) onFlowChange();
        },
    }));

    // ... (rest of render) ...
    const nodeTypes = useMemo(
        () => ({
            trigger: TriggerNode,
            action: ActionNode,
            condition: ConditionNode,
            stopper: StopperNode,
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
            const appName = event.dataTransfer.getData(
                "application/reactflow/appName"
            );
            const description = event.dataTransfer.getData(
                "application/reactflow/description"
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

            const newNodeId = getId();
            const newNode = {
                id: newNodeId,
                type,
                position,
                data: {
                    label: label,
                    appName: appName,
                    description: description,
                    settings: defaults,
                    onDelete: () => handleDeleteNode(newNodeId),
                },
            };

            setNodes((nds) => nds.concat(newNode));
            if (onFlowChange) onFlowChange();
        },
        [screenToFlowPosition, setNodes, onFlowChange, handleDeleteNode]
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
