import React, { useState, useRef } from "react";
import { Page, Layout } from "@shopify/polaris";
import { Head, router } from "@inertiajs/react";
import Sidebar from "../Components/WorkflowBuilder/Sidebar";
import Builder from "../Components/WorkflowBuilder/Canvas";
import Toolbar from "../Components/WorkflowBuilder/Toolbar";
import ConfigPanel from "../Components/WorkflowBuilder/ConfigPanel";

export default function WorkflowEditor({ shop, workflow }) {
    const [saving, setSaving] = useState(false);
    const [selectedNode, setSelectedNode] = useState(null);
    const [workflowName, setWorkflowName] = useState(
        workflow?.name || "New Workflow"
    );
    const builderRef = useRef(null);

    // Initial state from DB or Default
    const initialNodes = workflow?.ui_data?.nodes;
    const initialEdges = workflow?.ui_data?.edges;

    const handleSave = () => {
        setSaving(true);
        if (builderRef.current) {
            const { nodes, edges } = builderRef.current.getFlow();

            router.post(
                "/workflows/save",
                {
                    id: workflow?.id,
                    name: workflowName,
                    workflow_ui: { nodes, edges },
                },
                {
                    onSuccess: () => setSaving(false),
                    onError: (errors) => {
                        console.error("Save failed", errors);
                        setSaving(false);
                    },
                }
            );
        }
    };

    const handleNodeUpdate = (nodeId, newConfig) => {
        // Placeholder for future state lifting
    };

    return (
        <div
            style={{
                height: "100vh",
                display: "flex",
                flexDirection: "column",
            }}
        >
            <Head title="Workflow Editor" />
            <Toolbar
                title={workflowName}
                onSave={handleSave}
                isSaving={saving}
            />

            <div
                style={{
                    display: "flex",
                    flexGrow: 1,
                    overflow: "hidden",
                    padding: "1rem",
                    gap: "1rem",
                    backgroundColor: "#f6f6f7", // Polaris page bg
                }}
            >
                 <div
                    style={{
                        width: "250px", // Fixed width for sidebar
                        display: "flex",
                        flexDirection: "column",
                        border: "1px solid #e1e3e5",
                        borderRadius: "8px",
                        boxShadow: "0px 1px 3px rgba(0, 0, 0, 0.1)",
                        backgroundColor: "white",
                        overflow: "hidden",
                    }}
                >
                    <Sidebar />
                </div>

                <div
                    style={{
                        flexGrow: 1,
                        position: "relative",
                        border: "1px solid #e1e3e5",
                        borderRadius: "8px",
                        boxShadow: "0px 1px 3px rgba(0, 0, 0, 0.1)",
                        overflow: "hidden",
                        backgroundColor: "white",
                    }}
                >
                    <Builder
                        ref={builderRef}
                        initialNodes={initialNodes}
                        initialEdges={initialEdges}
                        onNodeSelect={setSelectedNode}
                    />
                </div>

                <div
                    style={{
                        width: "350px", // Fixed width for panel
                        display: "flex",
                        flexDirection: "column",
                        border: "1px solid #e1e3e5",
                        borderRadius: "8px",
                        boxShadow: "0px 1px 3px rgba(0, 0, 0, 0.1)",
                        backgroundColor: "white",
                        overflowY: "auto",
                    }}
                >
                    <ConfigPanel
                        node={selectedNode}
                        onUpdate={(id, config) => {
                            if (builderRef.current) {
                                builderRef.current.updateNode(id, config);
                            }
                        }}
                    />
                </div>
            </div>
        </div>
    );
}
