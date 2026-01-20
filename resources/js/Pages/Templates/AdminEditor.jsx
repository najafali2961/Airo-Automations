import React, {
    useState,
    useRef,
    useEffect,
    useMemo,
    useCallback,
} from "react";
import {
    Page,
    Button,
    Modal,
    Text,
    Badge,
    Icon,
    Frame,
    Toast,
} from "@shopify/polaris";
import { ArrowLeftIcon } from "@shopify/polaris-icons";
import { Head, router } from "@inertiajs/react";
import axios from "axios";

import Sidebar from "../../Components/WorkflowBuilder/Sidebar";
import Builder from "../../Components/WorkflowBuilder/Canvas";
import ConfigPanel from "../../Components/WorkflowBuilder/ConfigPanel";
import NodeSelector from "../../Components/WorkflowBuilder/NodeSelector";

export default function AdminTemplateEditor({
    template,
    flow, // structure { id, name, nodes, edges }
    definitions,
    connectors, // []
}) {
    const [saving, setSaving] = useState(false);
    const [isDirty, setIsDirty] = useState(false);
    const [selectedNode, setSelectedNode] = useState(null);
    const [workflowName, setWorkflowName] = useState(
        flow?.name || "New Template",
    );
    const builderRef = useRef(null);
    const [toast, setToast] = useState(null);

    // New state for interactions
    const [insertContext, setInsertContext] = useState(null);
    const [isSelectorOpen, setIsSelectorOpen] = useState(false);

    // Transform DB nodes/edges to React Flow format
    const initialNodes = useMemo(() => {
        if (!flow?.nodes) return [];
        return flow.nodes.map((n) => {
            // Infer app name for existing nodes
            let appName = null;
            if (definitions?.apps) {
                const parentApp = definitions.apps.find(
                    (app) =>
                        app.triggers?.some((t) => t.label === n.label) ||
                        app.actions?.some((a) => a.label === n.label),
                );
                if (parentApp) {
                    appName = parentApp.name.toLowerCase();
                }
            }

            // Handle pre-formatted React Flow nodes (from DB/this editor)
            if (n.position && n.data) {
                return {
                    ...n,
                    id: String(n.id),
                    data: {
                        ...n.data,
                        appName: appName || n.data.appName,
                    },
                };
            }

            // Handle Legacy Flat DB structure
            return {
                id: String(n.id),
                type: n.type,
                position: { x: n.position_x || 0, y: n.position_y || 0 },
                data: {
                    label: n.label,
                    settings: n.settings,
                    appName: appName,
                },
            };
        });
    }, [flow, definitions]);

    const initialEdges = useMemo(() => {
        if (!flow?.edges) return [];
        return flow.edges.map((e) => {
            // Check for React Flow format (source/target) or DB format (source_node_id/target_node_id)
            const source = e.source || String(e.source_node_id);
            const target = e.target || String(e.target_node_id);

            return {
                id: e.id || `e${source}-${target}`, // Use existing ID if available
                source: source,
                target: target,
                type: "custom",
                label: e.label || "then",
                sourceHandle: e.source_handle || e.sourceHandle || null,
            };
        });
    }, [flow]);

    const handleSave = async () => {
        setSaving(true);
        if (builderRef.current) {
            // Validate before saving
            const isValid = builderRef.current.validate();
            if (!isValid) {
                setToast({
                    content: "Cannot save: Layout has errors",
                    error: true,
                });
                setSaving(false);
                return;
            }

            const { nodes, edges } = builderRef.current.getFlow();

            try {
                // Save to Admin API
                await axios.post(
                    `/admin-tools/template-editor/${template.id}/save`,
                    {
                        name: workflowName,
                        nodes,
                        edges,
                    },
                );

                setSaving(false);
                setIsDirty(false);
                setToast({ content: "Template saved successfully" });
            } catch (error) {
                console.error("Save failed", error);
                setSaving(false);
                setToast({ content: "Failed to save template", error: true });
            }
        }
    };

    const handleAddStep = useCallback((edgeId, sourceNodeId, targetNodeId) => {
        setInsertContext({
            edgeId,
            source: sourceNodeId,
            target: targetNodeId,
        });
        setIsSelectorOpen(true);
    }, []);

    const handleNodeSelect = useCallback(
        (nodeDef) => {
            if (builderRef.current && insertContext) {
                builderRef.current.insertNodeBetween(nodeDef, insertContext);
                setIsDirty(true);
                setIsSelectorOpen(false);
            }
        },
        [insertContext],
    );

    const handleSidebarNodeClick = useCallback((nodeDef) => {
        if (builderRef.current) {
            builderRef.current.addNode(nodeDef);
            setIsDirty(true);
        }
    }, []);

    const handleFlowChange = useCallback(() => {
        setIsDirty(true);
    }, []);

    // Variable Logic (Simplified from Editor.jsx)
    const [availableVariables, setAvailableVariables] = useState([]);
    useEffect(() => {
        if (!selectedNode) {
            setAvailableVariables([]);
            return;
        }

        const flow = builderRef.current
            ? builderRef.current.getFlow()
            : { nodes: initialNodes, edges: initialEdges || [] };
        const { nodes, edges } = flow;

        // Traverse backwards to find the connected trigger
        let currentId = selectedNode.id;
        let connectedTrigger = null;

        if (selectedNode.type === "trigger") {
            connectedTrigger = selectedNode;
        } else {
            const visited = new Set();
            const queue = [currentId];
            while (queue.length > 0) {
                const id = queue.shift();
                if (visited.has(id)) continue;
                visited.add(id);

                const node = nodes.find((n) => n.id === id);
                if (node && node.type === "trigger") {
                    connectedTrigger = node;
                    break;
                }
                const incomingEdges = edges.filter((e) => e.target === id);
                for (const edge of incomingEdges) queue.push(edge.source);
            }
        }

        if (!connectedTrigger) {
            setAvailableVariables([]);
            return;
        }

        // Find definition
        for (const app of definitions.apps) {
            const t = app.triggers?.find(
                (tr) =>
                    (connectedTrigger.data.settings?.topic &&
                        tr.settings?.topic ===
                            connectedTrigger.data.settings?.topic) ||
                    tr.label === connectedTrigger.data.label,
            );
            if (t) {
                setAvailableVariables(t.variables || []);
                return;
            }
        }
        setAvailableVariables([]);
    }, [selectedNode, definitions, initialNodes]);

    return (
        <Frame>
            <div
                style={{
                    height: "100vh",
                    display: "flex",
                    flexDirection: "column",
                    background: "#f6f6f7",
                }}
            >
                <Head title={`Edit Template: ${template.name}`} />
                {toast && (
                    <Toast
                        content={toast.content}
                        error={toast.error}
                        onDismiss={() => setToast(null)}
                    />
                )}

                <NodeSelector
                    open={isSelectorOpen}
                    onClose={() => setIsSelectorOpen(false)}
                    definitions={definitions}
                    onSelect={handleNodeSelect}
                />

                <div className="flex-none border-b border-gray-200 bg-white/80 backdrop-blur-md z-20 px-6 py-4">
                    <div className="flex items-center justify-between">
                        <div className="flex items-center gap-4">
                            <h1 className="text-lg font-bold text-gray-900">
                                {workflowName}{" "}
                                <span className="text-gray-400 font-normal">
                                    (Template)
                                </span>
                            </h1>
                            {isDirty && (
                                <Badge tone="attention">Unsaved Changes</Badge>
                            )}
                        </div>

                        <div className="flex items-center gap-3">
                            <Button
                                size="slim"
                                onClick={() => builderRef.current?.autoLayout()}
                            >
                                Format Flow
                            </Button>

                            <Button
                                variant="primary"
                                onClick={handleSave}
                                loading={saving}
                            >
                                Save Template
                            </Button>
                        </div>
                    </div>
                </div>

                <div style={{ flex: 1, display: "flex", overflow: "hidden" }}>
                    <div
                        style={{
                            width: "280px",
                            background: "white",
                            borderRight: "1px solid #e1e3e5",
                            display: "flex",
                            flexDirection: "column",
                            overflowY: "auto",
                        }}
                    >
                        <Sidebar
                            definitions={definitions}
                            connectors={connectors}
                            onNodeClick={handleSidebarNodeClick}
                        />
                    </div>

                    <div
                        style={{
                            flex: 1,
                            position: "relative",
                            background: "#f1f2f3",
                        }}
                    >
                        <Builder
                            ref={builderRef}
                            initialNodes={initialNodes}
                            initialEdges={initialEdges}
                            onNodeSelect={setSelectedNode}
                            onFlowChange={handleFlowChange}
                            onAddConnectorClick={handleAddStep}
                        />
                    </div>

                    {selectedNode && (
                        <div
                            style={{
                                width: "350px",
                                background: "white",
                                borderLeft: "1px solid #e1e3e5",
                                overflowY: "auto",
                                boxShadow: "-2px 0 5px rgba(0,0,0,0.05)",
                                zIndex: 10,
                            }}
                        >
                            <ConfigPanel
                                node={selectedNode}
                                definitions={definitions}
                                connectors={connectors}
                                triggerVariables={availableVariables}
                                onUpdate={(id, data) => {
                                    if (builderRef.current) {
                                        builderRef.current.updateNode(id, data);
                                        setIsDirty(true);
                                    }
                                }}
                                onClose={() => setSelectedNode(null)}
                            />
                        </div>
                    )}
                </div>
            </div>
        </Frame>
    );
}
