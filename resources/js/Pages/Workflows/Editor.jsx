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
} from "@shopify/polaris";
import { Head, router } from "@inertiajs/react";
import { SaveBar, useAppBridge } from "@shopify/app-bridge-react";
import axios from "axios";

import Sidebar from "../../Components/WorkflowBuilder/Sidebar";
import Builder from "../../Components/WorkflowBuilder/Canvas";
import ConfigPanel from "../../Components/WorkflowBuilder/ConfigPanel";
import NodeSelector from "../../Components/WorkflowBuilder/NodeSelector";

export default function WorkflowEditor({ shop, flow, definitions }) {
    const shopify = useAppBridge();
    const [saving, setSaving] = useState(false);
    const [isDirty, setIsDirty] = useState(false);
    const [selectedNode, setSelectedNode] = useState(null);
    const [workflowName, setWorkflowName] = useState(
        flow?.name || "New Workflow"
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
        return flow.nodes.map((n) => ({
            id: String(n.id),
            type: n.type,
            position: { x: n.position_x || 0, y: n.position_y || 0 },
            data: {
                label: n.label,
                settings: n.settings,
            },
        }));
    }, [flow]);

    const initialEdges = useMemo(() => {
        if (!flow?.edges) return [];
        return flow.edges.map((e) => ({
            id: `e${e.source_node_id}-${e.target_node_id}`,
            source: String(e.source_node_id),
            target: String(e.target_node_id),
            label: e.label || "then",
        }));
    }, [flow]);

    const handleSave = async () => {
        setSaving(true);
        if (builderRef.current) {
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
                            }
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
        [insertContext]
    );

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
            }
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

            <div
                style={{
                    flex: "0 0 auto",
                    borderBottom: "1px solid #e1e3e5",
                    background: "white",
                    padding: "1rem",
                }}
            >
                <BlockStack gap="400">
                    <InlineStack align="space-between" blockAlign="center">
                        <div className="flex items-center gap-2">
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
                                        e.key === "Enter" &&
                                        setIsEditingName(false)
                                    }
                                    className="text-xl font-bold border rounded px-2 py-1"
                                />
                            ) : (
                                <div
                                    onClick={() => setIsEditingName(true)}
                                    className="text-xl font-bold cursor-pointer hover:bg-gray-50 px-2 py-1 rounded flex items-center gap-2 group"
                                >
                                    {workflowName}
                                    <span className="opacity-0 group-hover:opacity-100 text-gray-400 text-sm">
                                        ✎
                                    </span>
                                </div>
                            )}
                        </div>
                        <div className="flex gap-2">
                            <Button
                                onClick={handleToggleActive}
                                tone={flow?.active ? "critical" : "success"}
                                disabled={!flow?.id || isDirty}
                            >
                                {flow?.active ? "Deactivate" : "Activate"}
                            </Button>
                            <Button
                                onClick={() =>
                                    router.visit(
                                        "/workflows" + window.location.search
                                    )
                                }
                            >
                                Back to App
                            </Button>
                            <Button
                                variant="primary"
                                onClick={handleExecute}
                                disabled={isDirty || saving}
                            >
                                Run Test
                            </Button>
                        </div>
                    </InlineStack>
                </BlockStack>
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
                    <Sidebar definitions={definitions} />
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
