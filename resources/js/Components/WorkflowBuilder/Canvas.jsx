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
    const { screenToFlowPosition, getNodes, getEdges } = useReactFlow();

    // Lift state up or manage generic validation here
    // For simplicity, we write validation status directly into node data
    const [nodes, setNodes, onNodesChange] = useNodesState(
        initialNodes || initialNodesDefault
    );
    const [edges, setEdges, onEdgesChange] = useEdgesState(initialEdges || []);

    // -------------------------------------------------------------------------
    // Validation Logic
    // -------------------------------------------------------------------------
    const validateFlow = useCallback((currentNodes, currentEdges) => {
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

        // If we found changes in validation state, we need to return them
        // But we can't simply setNodes here if we are inside a state update loop.
        // So we return the new nodes, and the caller (useEffect) handles the update?
        // Actually, best pattern is to run this effect when nodes/edges structure changes.
        return { updatedNodes, allValid };
    }, []);

    // We run validation in a useEffect that watches the structure (ids/connections),
    // BUT we need to be careful not to cycle endlessly.
    // We'll trust that 'validateFlow' returns stable objects if nothing changed.
    useEffect(() => {
        // We need a stable check. JSON stringify is expensive but comprehensive for small graphs.
        // A better way is checking if 'updatedNodes' is different ref or deep equal.
        // Let's rely on the manual check inside validateFlow

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
    // ^ Dependency is tricky. length + edge structure.
    // If we include 'nodes' it loops because we setNodes.
    // We depend on nodes.length and edge connections to trigger re-validation.

    // -------------------------------------------------------------------------
    // Deletion Logic
    // -------------------------------------------------------------------------
    const handleDeleteNode = useCallback(
        (nodeId) => {
            setNodes((nds) => nds.filter((n) => n.id !== nodeId));
            setEdges((eds) =>
                eds.filter((e) => e.source !== nodeId && e.target !== nodeId)
            );
            if (onFlowChange) onFlowChange();
            if (onNodeSelect) onNodeSelect(null); // Deselect if deleted
        },
        [setNodes, setEdges, onFlowChange, onNodeSelect]
    );

    // Prepare node with handlers
    // We need to inject 'onDelete' and validation props into data.
    // But 'data' is static in the node object unless we update it.
    // We already update nodes for validation. Let's also ensure onDelete is there.
    // Wait, we can't store functions in 'data' if we persistence (save to DB).
    // ReactFlow warns about functions in data if not careful, but for runtime it's fine.
    // When saving, we strip it.

    // Instead of putting onDelete in data (which causes serialization issues or updates),
    // let's pass it via a custom Node Wrapper or use `onNodeClick` and a separate UI.
    // BUT the requirement is "trash icon at the right side of the node".
    // So the Node component needs the callback.
    // We can inject it in `useNodesState` or a `useEffect`.

    useEffect(() => {
        setNodes((nds) =>
            nds.map((node) => {
                if (node.data.onDelete) return node; // already has it
                return {
                    ...node,
                    data: {
                        ...node.data,
                        onDelete: () => handleDeleteNode(node.id),
                    },
                };
            })
        );
    }, [handleDeleteNode, setNodes, nodes.length]); // Run when nodes are added

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
        if (initialNodes) {
            // Hydrate with method
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
            });
            setEdges((eds) => addEdge(newEdge, eds));
            if (onFlowChange) onFlowChange();
        },
        [setEdges, onFlowChange, prepareEdge]
    );

    useImperativeHandle(forwardedRef, () => ({
        getFlow: () => {
            // Return pure data stripping functions
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
                    n8nType: nodeDef.n8nType,
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
                    n8nType: nodeDef.n8nType,
                    onDelete: () => handleDeleteNode(newNodeId),
                },
            };

            setNodes((nds) => nds.concat(newNode));
            if (onFlowChange) onFlowChange();
        },
    }));

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

            const newNodeId = getId();
            const newNode = {
                id: newNodeId,
                type,
                position,
                data: {
                    label: label,
                    appName: appName,
                    n8nType: n8nType,
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
