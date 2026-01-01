import React, { useState, useRef } from "react";
import { 
    Page, 
    Layout, 
    BlockStack, 
    InlineStack, 
    Box, 
    Text, 
    Button 
} from "@shopify/polaris";
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
        <div style={{ height: '100vh', overflow: 'hidden', backgroundColor: '#f6f6f7' }}>
            <Head title="Workflow Editor" />
            
            <BlockStack gap="0">
                {/* Top Toolbar */}
                <Box 
                    background="bg-surface" 
                    borderBlockEndWidth="025" 
                    borderColor="border" 
                    padding="300"
                >
                    <Toolbar
                        title={workflowName}
                        onSave={handleSave}
                        isSaving={saving}
                    />
                </Box>

                {/* Main Workspace */}
                <Box style={{ height: 'calc(100vh - 65px)', position: 'relative' }}>
                    <div style={{ display: 'flex', height: '100%', alignItems: 'stretch', width: '100%' }}>
                        
                        {/* Sidebar */}
                        <Box 
                            borderInlineEndWidth="025" 
                            borderColor="border" 
                            background="bg-surface"
                            minWidth="280px"
                            maxWidth="280px"
                            style={{ height: '100%', overflowY: 'auto' }}
                        >
                            <Sidebar />
                        </Box>

                        {/* Canvas Area */}
                        <Box 
                            background="bg-surface-secondary" 
                            padding="400"
                            style={{ flex: 1, height: '100%', position: 'relative', overflow: 'hidden' }}
                        >
                            <div style={{ 
                                height: '100%', 
                                width: '100%', 
                                position: 'relative', 
                                backgroundColor: 'var(--p-color-bg-surface)', 
                                borderRadius: 'var(--p-border-radius-300)', 
                                boxShadow: 'var(--p-shadow-100)',
                                border: '1px solid var(--p-color-border)',
                                overflow: 'hidden'
                            }}>
                                <div style={{ position: 'absolute', inset: 0 }}>
                                    <Builder
                                        ref={builderRef}
                                        initialNodes={initialNodes}
                                        initialEdges={initialEdges}
                                        onNodeSelect={setSelectedNode}
                                    />
                                </div>
                            </div>
                        </Box>

                        {/* Config Panel */}
                        {selectedNode && (
                             <Box 
                                borderInlineStartWidth="025" 
                                borderColor="border" 
                                background="bg-surface"
                                minWidth="320px"
                                maxWidth="320px"
                                shadow="300"
                                style={{ height: '100%', overflowY: 'auto', zIndex: 10 }}
                            >
                                <ConfigPanel
                                    node={selectedNode}
                                    onUpdate={(id, config) => {
                                        if (builderRef.current) {
                                            builderRef.current.updateNode(id, config);
                                        }
                                    }}
                                />
                            </Box>
                        )}
                    </div>
                </Box>
            </BlockStack>
        </div>
    );
}
