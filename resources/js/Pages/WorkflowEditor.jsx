import React, { useState, useRef } from "react";
import { Page, Layout } from "@shopify/polaris";
import { Head, router } from "@inertiajs/react";
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
        // We need to update the node in the Builder state.
        // Since Builder manages its own state via useNodesState,
        // passing props down to update it is tricky without context or exposed method.
        // For now, we assume ConfigPanel is modifying the node object in a way that ReactFlow picks up
        // OR we need to lift nodes state up to this Editor component.
        // Given the constraints and typical React Flow patterns, lifting state is better
        // but for this refactor I will re-implement the Canvas to accept nodes/setNodes if possible,
        // or expose a setNodes method on the ref.
        // Simpler approach for now: The ConfigPanel effectively is a "view"
        // but updating it requires pushing change back to ReactFlow.
        // Let's actually assume we will move the state up in a future refactor if needed,
        // but for now let's use the pattern where we don't update the visual node immediately
        // unless we force a refresh.
        // Actually, the previous Canvas.jsx exposed getFlow, maybe we need setFlow or updateNode.
        // EDIT: I will rely on the ConfigPanel to call a method if I pass one, but
        // the ConfigPanel in my previous step calls onUpdate.
        // I need to wire this onUpdate to the Builder's setter.
        // Since I can't easily access Builder's internal setNodes from here without lifting state...
        // Recommendation: Rewrite Canvas to accept nodes/edges as props (controlled component).
        // But for time, I'll rely on the user dragging/dropping for structure
        // and only using ConfigPanel for data.
        // Wait, the user's provided code had ConfigPanel INSIDE Canvas.
        // I separated them. Let's put ConfigPanel side-by-side.
        // To make onUpdate work, I really should lift state.
        // Let's modify this file to just render the structure and let Canvas handle the internal wiring if I move ConfigPanel inside Canvas?
        // No, I prefer separation.
        // I will re-write Canvas.jsx in the next step to be a controlled component if I can,
        // OR just leave the ConfigPanel logic for later refinement.
        // BUT, for a working app, I need to update the node data.
        // Let's stick to the current plan:
        // functionality is tricky without lifting state.
        // I'll skip implementing the live-update from ConfigPanel for this exact second
        // and trust the visual builder for structure.
        // ... Actually, I should just fix it.
        // I will check the Canvas code again.
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

            <div style={{ display: "flex", flexGrow: 1, overflow: "hidden" }}>
                <div style={{ flexGrow: 1, position: "relative" }}>
                    <Builder
                        ref={builderRef}
                        initialNodes={initialNodes}
                        initialEdges={initialEdges}
                        onNodeSelect={setSelectedNode}
                    />
                </div>

                <ConfigPanel
                    node={selectedNode}
                    onUpdate={(id, config) => {
                        if (builderRef.current) {
                            builderRef.current.updateNode(id, config);
                        }
                        // Also update localized selectedNode state if needed, but usually redundant if re-selected
                    }}
                />
            </div>
        </div>
    );
}
