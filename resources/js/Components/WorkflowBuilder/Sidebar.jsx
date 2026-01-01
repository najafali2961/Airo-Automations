import React, { useEffect, useState } from "react";
import { LegacyCard, Text, Icon, Spinner, TextField } from "@shopify/polaris";
import { ImportIcon, ExportIcon, SearchIcon } from "@shopify/polaris-icons";
import axios from "axios";

export default function Sidebar() {
    const [nodes, setNodes] = useState([]);
    const [loading, setLoading] = useState(true);
    const [searchTerm, setSearchTerm] = useState("");

    useEffect(() => {
        const fetchNodes = async () => {
            try {
                // Fetch N8N node types from our backend proxy
                const response = await axios.get("/workflows/node-types");
                // Expecting array of node types
                setNodes(response.data || []);
            } catch (error) {
                console.error("Failed to load node types", error);
            } finally {
                setLoading(false);
            }
        };
        fetchNodes();
    }, []);

    const onDragStart = (event, nodeType, label, n8nType) => {
        event.dataTransfer.setData("application/reactflow", nodeType);
        event.dataTransfer.setData("application/reactflow/label", label);
        event.dataTransfer.setData("application/reactflow/n8nType", n8nType);
        event.dataTransfer.effectAllowed = "move";
    };

    // Filter and Group
    const filteredNodes = nodes.filter((n) =>
        n.displayName.toLowerCase().includes(searchTerm.toLowerCase())
    );

    const triggers = filteredNodes.filter(
        (n) =>
            n.name.toLowerCase().includes("trigger") ||
            n.group.includes("trigger")
    );
    const actions = filteredNodes.filter(
        (n) =>
            !n.name.toLowerCase().includes("trigger") &&
            !n.group.includes("trigger")
    );

    if (loading)
        return (
            <div className="p-4">
                <Spinner size="small" />
            </div>
        );

    return (
        <div className="w-64 border-r border-gray-200 bg-white p-4 flex flex-col gap-4 h-full overflow-y-auto">
            <Text variant="headingMd" as="h3">
                Nodes
            </Text>
            <TextField
                prefix={<Icon source={SearchIcon} />}
                value={searchTerm}
                onChange={setSearchTerm}
                placeholder="Search nodes..."
                autoComplete="off"
            />

            <div className="flex flex-col gap-3">
                {/* Triggers Section */}
                {triggers.length > 0 && (
                    <>
                        <Text variant="bodySm" tone="subdued">
                            Triggers
                        </Text>
                        {triggers.map((n) => (
                            <div
                                key={n.name}
                                className="p-3 border border-gray-300 rounded cursor-grab hover:bg-gray-50 flex items-center gap-2"
                                onDragStart={(event) =>
                                    onDragStart(
                                        event,
                                        "trigger", // React Flow type (we map all to generic trigger or action wrapper)
                                        n.displayName,
                                        n.name // Original N8N Type
                                    )
                                }
                                draggable
                            >
                                <div className="w-5 h-5 text-green-600">
                                    <Icon source={ImportIcon} />
                                </div>
                                <Text variant="bodyMd">{n.displayName}</Text>
                            </div>
                        ))}
                    </>
                )}

                {/* Actions Section */}
                <Text variant="bodySm" tone="subdued">
                    Actions
                </Text>
                {actions.length === 0 && (
                    <Text tone="subdued">No actions found</Text>
                )}
                {actions.map((n) => (
                    <div
                        key={n.name}
                        className="p-3 border border-gray-300 rounded cursor-grab hover:bg-gray-50 flex items-center gap-2"
                        onDragStart={(event) =>
                            onDragStart(
                                event,
                                "action", // React Flow type
                                n.displayName,
                                n.name // Original N8N Type
                            )
                        }
                        draggable
                    >
                        <div className="w-5 h-5 text-blue-600">
                            <Icon source={ExportIcon} />
                        </div>
                        <Text variant="bodyMd">{n.displayName}</Text>
                    </div>
                ))}
            </div>
        </div>
    );
}
