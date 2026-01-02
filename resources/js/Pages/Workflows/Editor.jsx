import React, {
    useState,
    useRef,
    useEffect,
    useMemo,
    useCallback,
} from "react";
import {
    Page,
    Layout,
    BlockStack,
    Box,
    Text,
    Button,
    Card,
} from "@shopify/polaris";
import { Head, router } from "@inertiajs/react";
import { SaveBar, useAppBridge } from "@shopify/app-bridge-react";
import axios from "axios";
import Sidebar from "../../Components/WorkflowBuilder/Sidebar";
import Builder from "../../Components/WorkflowBuilder/Canvas";
import Toolbar from "../../Components/WorkflowBuilder/Toolbar";
import ConfigPanel from "../../Components/WorkflowBuilder/ConfigPanel";

export default function WorkflowEditor({ shop, flow }) {
    // Note: Controller passes 'flow', we were using 'workflow' prop name. Adjusted to 'flow'.
    const shopify = useAppBridge();
    const [saving, setSaving] = useState(false);
    const [isDirty, setIsDirty] = useState(false);
    const [selectedNode, setSelectedNode] = useState(null);
    const [workflowName, setWorkflowName] = useState(
        flow?.name || "New Workflow"
    );
    const [loadingTypes, setLoadingTypes] = useState(false);
    const builderRef = useRef(null);

    // Transform DB nodes/edges to React Flow format
    const initialNodes = useMemo(() => {
        if (!flow?.nodes) return [];
        return flow.nodes.map((n) => ({
            id: String(n.id), // Ensure string
            type: n.type,
            position: { x: n.position_x || 0, y: n.position_y || 0 },
            data: {
                label: n.label,
                settings: n.settings, // Database JSON cast
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
                    setIsDirty(false);
                    shopify.toast.show("Workflow saved");
                    // Optionally update local flow state or URL if it was a new creation
                    if (!flow?.id && response.data.flow?.id) {
                        router.visit(`/workflows/${response.data.flow.id}`, {
                            replace: true,
                        });
                    }
                }
            } catch (error) {
                console.error("Save failed", error);
                setSaving(false);
                shopify.toast.show("Failed to save", {
                    isError: true,
                });
            }
        }
    };

    const handleDiscard = () => {
        if (confirm("Discard unsaved changes?")) {
            router.reload();
        }
    };

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

    const handleFlowChange = useCallback(() => {
        setIsDirty(true);
    }, []);

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

            {isDirty && (
                <SaveBar id="my-save-bar">
                    <button
                        variant="primary"
                        onClick={handleSave}
                        disabled={saving}
                    />
                    <button onClick={handleDiscard} />
                </SaveBar>
            )}

            <div style={{ flex: "0 0 auto" }}>
                <Page
                    fullWidth
                    backAction={{
                        content: "Workflows",
                        onAction: () => router.visit("/workflows"),
                    }}
                    title={workflowName}
                    secondaryActions={[
                        {
                            content: "Run Test",
                            onAction: handleExecute,
                            disabled: isDirty,
                        },
                    ]}
                ></Page>
            </div>

            <div style={{ flex: 1, overflow: "hidden", position: "relative" }}>
                <div
                    style={{ position: "absolute", inset: 0, display: "flex" }}
                >
                    {/* Sidebar */}
                    <div
                        style={{
                            width: "280px",
                            height: "100%",
                            borderRight: "1px solid #e1e3e5",
                            background: "white",
                            overflowY: "auto",
                        }}
                    >
                        <Sidebar loading={loadingTypes} />
                    </div>

                    {/* Canvas */}
                    <div
                        style={{
                            flex: 1,
                            height: "100%",
                            position: "relative",
                            background: "#f1f2f3",
                        }}
                    >
                        <div style={{ position: "absolute", inset: 0 }}>
                            <Builder
                                ref={builderRef}
                                initialNodes={initialNodes}
                                initialEdges={initialEdges}
                                onNodeSelect={setSelectedNode}
                                onExecute={handleExecute}
                                onFlowChange={handleFlowChange}
                            />
                        </div>
                    </div>

                    {/* Config Panel */}
                    {selectedNode && (
                        <div
                            style={{
                                width: "320px",
                                height: "100%",
                                borderLeft: "1px solid #e1e3e5",
                                background: "white",
                                overflowY: "auto",
                                boxShadow: "-2px 0 8px rgba(0,0,0,0.05)",
                                zIndex: 10,
                            }}
                        >
                            <ConfigPanel
                                node={selectedNode}
                                onUpdate={(id, config) => {
                                    if (builderRef.current) {
                                        builderRef.current.updateNode(
                                            id,
                                            config
                                        );
                                        setIsDirty(true);
                                    }
                                }}
                            />
                        </div>
                    )}
                </div>
            </div>
        </div>
    );
}
