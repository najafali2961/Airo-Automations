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
        <div className="h-screen flex flex-col bg-gray-50" style={{ height: '100vh', display: 'flex', flexDirection: 'column' }}>
            <Head title="Workflow Editor" />
            
            {/* Top Toolbar */}
            <div className="flex-none bg-white border-b border-gray-200 shadow-sm z-10">
                <Toolbar
                    title={workflowName}
                    onSave={handleSave}
                    isSaving={saving}
                />
            </div>

            {/* Main Workspace */}
            <div className="flex-1 flex overflow-hidden" style={{ flex: 1, display: 'flex', overflow: 'hidden' }}>
                
                {/* Sidebar */}
                <div className="flex-none w-64 bg-white border-r border-gray-200 z-10" style={{ width: '16rem', zIndex: 10 }}>
                    <Sidebar />
                </div>

                {/* Canvas Area */}
                <div className="flex-1 relative bg-gray-50" style={{ flex: 1, position: 'relative' }}>
                    <div className="absolute inset-0" style={{ position: 'absolute', top: 0, right: 0, bottom: 0, left: 0 }}>
                         <Builder
                            ref={builderRef}
                            initialNodes={initialNodes}
                            initialEdges={initialEdges}
                            onNodeSelect={setSelectedNode}
                        />
                    </div>
                </div>

                {/* Config Panel (Right Side) */}
                {selectedNode && (
                    <div className="flex-none w-80 bg-white border-l border-gray-200 overflow-y-auto shadow-lg z-20 transition-all duration-300">
                        <ConfigPanel
                            node={selectedNode}
                            onUpdate={(id, config) => {
                                if (builderRef.current) {
                                    builderRef.current.updateNode(id, config);
                                }
                            }}
                        />
                    </div>
                )}
            </div>
        </div>
    );
}
