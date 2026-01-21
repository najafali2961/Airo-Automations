import React, {
    useState,
    useRef,
    useEffect,
    useMemo,
    useCallback,
} from "react";
import {
    Page,
    BlockStack,
    InlineStack,
    Button,
    Modal,
    Text,
    Badge,
    Icon,
} from "@shopify/polaris";
import { ArrowLeftIcon } from "@shopify/polaris-icons";
import { Head, router } from "@inertiajs/react";
import { SaveBar, useAppBridge } from "@shopify/app-bridge-react";
import axios from "axios";

import Sidebar from "../../Components/WorkflowBuilder/Sidebar";
import Builder from "../../Components/WorkflowBuilder/Canvas";
import ConfigPanel from "../../Components/WorkflowBuilder/ConfigPanel";
import NodeSelector from "../../Components/WorkflowBuilder/NodeSelector";

export default function WorkflowEditor({
    shop,
    flow,
    definitions,
    connectors,
}) {
    const shopify = useAppBridge();
    const [saving, setSaving] = useState(false);
    const [isDirty, setIsDirty] = useState(false);
    const [selectedNode, setSelectedNode] = useState(null);
    const [workflowName, setWorkflowName] = useState(
        flow?.name || "New Workflow",
    );
    const builderRef = useRef(null);

    // New state for interactions
    const [insertContext, setInsertContext] = useState(null);
    const [isSelectorOpen, setIsSelectorOpen] = useState(false);
    const [isEditingName, setIsEditingName] = useState(false);
    const nameInputRef = useRef(null);

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
                    // Fix for Shopify casing if needed, though 'shopify' is standard
                }
            }

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
        return flow.edges.map((e) => ({
            id: `e${e.source_node_id}-${e.target_node_id}`,
            source: String(e.source_node_id),
            target: String(e.target_node_id),
            type: "custom",
            label: e.label || "then",
            sourceHandle: e.source_handle || e.sourceHandle || null,
        }));
    }, [flow]);

    const handleSave = async () => {
        setSaving(true);
        if (builderRef.current) {
            // Validate before saving
            const isValid = builderRef.current.validate();
            if (!isValid) {
                shopify.toast.show(
                    "Cannot save: Layout has errors (check red nodes)",
                    { isError: true },
                );
                setSaving(false);
                return;
            }

            const { nodes, edges } = builderRef.current.getFlow();

            try {
                const response = await axios.post("/workflows/save", {
                    id: flow?.id,
                    name: workflowName,
                    nodes,
                    edges,
                });

                if (response.data.success) {
                    setSaving(false);
                    isDirtyRef.current = false; // Immediate update for navigation guard
                    setIsDirty(false); // This will trigger hide
                    shopify.toast.show("Workflow saved");

                    if (!flow?.id && response.data.flow?.id) {
                        router.visit(
                            `/workflows/${response.data.flow.id}` +
                                window.location.search,
                            {
                                replace: true,
                            },
                        );
                    }
                }
            } catch (error) {
                console.error("Save failed", error);
                setSaving(false);
                shopify.toast.show("Failed to save", { isError: true });
            }
        }
    };

    const handleDiscard = () => {
        // Silently discard changes and revert to initial state
        if (builderRef.current) {
            builderRef.current.setFlow(initialNodes, initialEdges);
        }
        setIsDirty(false);
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
                setIsDirty(true); // Mark as dirty after inserting a node
                setIsSelectorOpen(false); // Close the selector after selection
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

    const handleExecute = async () => {
        if (!flow?.id || isDirty) {
            shopify.toast.show("Please save changes before executing", {
                isError: true,
            });
            return;
        }

        try {
            const response = await axios.post(`/workflows/${flow.id}/execute`);
            shopify.toast.show("Execution Started");
        } catch (error) {
            console.error("Execution failed", error);
            shopify.toast.show("Execution failed", { isError: true });
        }
    };

    const handleToggleActive = () => {
        if (!flow?.id) return;

        router.post(
            `/workflows/${flow.id}/toggle-active`,
            {},
            {
                preserveState: true,
                onSuccess: (page) => {
                    const message =
                        page.props.flash?.success || "Status updated";
                    shopify.toast.show(message);
                },
                onError: () => {
                    shopify.toast.show("Failed to change status", {
                        isError: true,
                    });
                },
            },
        );
    };

    // Use ref to track dirty state for event listeners to avoid closure staleness
    const isDirtyRef = useRef(false);
    useEffect(() => {
        isDirtyRef.current = isDirty;
    }, [isDirty]);

    const [showExitModal, setShowExitModal] = useState(false);
    const [pendingUrl, setPendingUrl] = useState(null);

    // Show/hide SaveBar based on isDirty
    useEffect(() => {
        if (!shopify?.saveBar) {
            console.warn("App Bridge saveBar not available");
            return;
        }

        if (isDirty) {
            shopify.saveBar.show("my-workflow-save-bar");
        } else {
            shopify.saveBar.hide("my-workflow-save-bar");
        }
    }, [isDirty, shopify]);

    const handleFlowChange = useCallback(() => {
        setIsDirty(true);
    }, []);

    // Intercept Inertia visits preventing navigation if unsaved
    useEffect(() => {
        const removeInertiaListener = router.on("before", (event) => {
            if (isDirtyRef.current) {
                event.preventDefault();
                setPendingUrl(event.detail.visit.url);
                setShowExitModal(true);
            }
        });

        return () => {
            removeInertiaListener();
        };
    }, []);

    const confirmExit = () => {
        isDirtyRef.current = false; // Prevent check loop
        setIsDirty(false);
        setShowExitModal(false);
        if (pendingUrl) {
            router.visit(pendingUrl);
        }
    };

    // Live Update of Variables
    // When selectedNode changes, find the trigger in the LIVE flow to get variables
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

        // If selected node is itself a trigger
        if (selectedNode.type === "trigger") {
            connectedTrigger = selectedNode;
        } else {
            // Simple Breadth-First/Path walking to find the root trigger
            // For now, we assume a linear chain or simple tree upwards
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

                // Find edges where target is current id
                const incomingEdges = edges.filter((e) => e.target === id);
                for (const edge of incomingEdges) {
                    queue.push(edge.source);
                }
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
                    // Check settings.topic (legacy/nested) OR root-level topic (standard)
                    (connectedTrigger.data.settings?.topic &&
                        (tr.settings?.topic ===
                            connectedTrigger.data.settings?.topic ||
                            tr.topic ===
                                connectedTrigger.data.settings?.topic)) ||
                    tr.label === connectedTrigger.data.label,
            );
            if (t) {
                setAvailableVariables(t.variables || []);
                return;
            }
        }
        setAvailableVariables([]);
    }, [selectedNode, definitions, initialNodes]);

    // Auto-focus name input when editing starts
    useEffect(() => {
        if (isEditingName && nameInputRef.current) {
            nameInputRef.current.focus();
        }
    }, [isEditingName]);

    return (
        <div
            style={{
                height: "100vh",
                display: "flex",
                flexDirection: "column",
                background: "#f6f6f7",
            }}
        >
            <Head title="Workflow Editor" />

            {/* Always render SaveBar — visibility controlled via App Bridge */}
            <SaveBar id="my-workflow-save-bar">
                <button
                    variant="primary"
                    onClick={handleSave}
                    disabled={saving}
                >
                    Save
                </button>
                <button onClick={handleDiscard}>Discard</button>
            </SaveBar>

            <Modal
                open={showExitModal}
                onClose={() => setShowExitModal(false)}
                title="Unsaved Changes"
                primaryAction={{
                    content: "Leave",
                    onAction: confirmExit,
                    destructive: true,
                }}
                secondaryActions={[
                    {
                        content: "Stay",
                        onAction: () => setShowExitModal(false),
                    },
                ]}
            >
                <Modal.Section>
                    <Text>
                        You have unsaved changes. If you leave now, your changes
                        will be lost.
                    </Text>
                </Modal.Section>
            </Modal>

            <NodeSelector
                open={isSelectorOpen}
                onClose={() => setIsSelectorOpen(false)}
                definitions={definitions}
                onSelect={handleNodeSelect}
            />

            <div className="flex-none h-16 px-6 border-b border-gray-200 bg-white/90 backdrop-blur-xl z-30 flex items-center justify-between shadow-[0_1px_3px_rgba(0,0,0,0.02)]">
                <div className="flex items-center gap-5">
                    <button
                        onClick={() =>
                            router.visit(`/workflows${window.location.search}`)
                        }
                        className="group flex items-center justify-center w-9 h-9 rounded-lg border border-gray-200 bg-white text-gray-500 shadow-sm hover:bg-gray-50 hover:border-gray-300 hover:text-gray-800 hover:shadow transition-all duration-200 active:scale-95"
                    >
                        <span className="transform transition-transform group-hover:-translate-x-0.5">
                            <Icon source={ArrowLeftIcon} />
                        </span>
                    </button>

                    <div className="h-6 w-px bg-gray-200/80" />

                    <div className="flex items-center gap-3">
                        {isEditingName ? (
                            <input
                                ref={nameInputRef}
                                type="text"
                                value={workflowName}
                                onChange={(e) =>
                                    setWorkflowName(e.target.value)
                                }
                                onBlur={() => setIsEditingName(false)}
                                onKeyDown={(e) =>
                                    e.key === "Enter" && setIsEditingName(false)
                                }
                                className="text-lg font-bold bg-transparent border-b-2 border-blue-500 rounded-none px-0 py-0.5 focus:outline-none focus:ring-0 text-gray-900 placeholder-gray-400 w-64 transition-all"
                            />
                        ) : (
                            <h1
                                onClick={() => setIsEditingName(true)}
                                className="text-lg font-bold text-gray-900 cursor-pointer hover:text-gray-600 transition-colors flex items-center gap-2 group selectable-none"
                            >
                                {workflowName}
                                <div className="opacity-0 group-hover:opacity-100 transition-opacity bg-gray-100 p-1 rounded-md text-gray-500">
                                    <svg
                                        className="w-3 h-3"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        stroke="currentColor"
                                    >
                                        <path
                                            strokeLinecap="round"
                                            strokeLinejoin="round"
                                            strokeWidth={2}
                                            d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"
                                        />
                                    </svg>
                                </div>
                            </h1>
                        )}

                        <div
                            className={`px-2.5 py-0.5 rounded-full text-[10px] font-bold tracking-wider uppercase border shadow-sm ${
                                flow?.active
                                    ? "bg-emerald-50 text-emerald-700 border-emerald-200"
                                    : "bg-amber-50 text-amber-700 border-amber-200"
                            }`}
                        >
                            {flow?.active ? "Active" : "Draft"}
                        </div>
                    </div>
                </div>

                <div className="flex items-center gap-4">
                    <div className="flex items-center gap-2 px-3 py-1.5 rounded-full bg-white border border-gray-200 shadow-sm mr-2">
                        {saving ? (
                            <>
                                <div className="w-2 h-2 rounded-full bg-blue-500 animate-pulse" />
                                <span className="text-xs font-medium text-gray-600">
                                    Saving...
                                </span>
                            </>
                        ) : isDirty ? (
                            <>
                                <div className="w-2 h-2 rounded-full bg-amber-500" />
                                <span className="text-xs font-medium text-gray-600">
                                    Unsaved changes
                                </span>
                            </>
                        ) : (
                            <>
                                <div className="w-2 h-2 rounded-full bg-emerald-500" />
                                <span className="text-xs font-medium text-gray-600">
                                    Saved
                                </span>
                            </>
                        )}
                    </div>

                    <div className="flex items-center gap-2">
                        {/* Using standard Polaris buttons for functionality, but wrapped for layout */}
                        <Button
                            size="slim"
                            onClick={handleToggleActive}
                            tone={flow?.active ? "critical" : "success"}
                            disabled={!flow?.id || isDirty}
                        >
                            {flow?.active ? "Deactivate" : "Activate"}
                        </Button>

                        <Button
                            size="slim"
                            variant="tertiary"
                            onClick={() => builderRef.current?.autoLayout()}
                        >
                            Format Flow
                        </Button>
                    </div>
                </div>
            </div>

            {/* Main Content Area */}
            <div style={{ flex: 1, display: "flex", overflow: "hidden" }}>
                {/* Sidebar */}
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

                {/* Canvas */}
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

                {/* Config Panel */}
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
                            connectors={connectors} // Pass directly
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
    );
}
