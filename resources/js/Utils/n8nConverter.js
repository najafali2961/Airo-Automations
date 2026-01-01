export function convertToN8N(nodes, edges) {
    const n8nNodes = nodes.map((node) => {
        let n8nType = 'n8n-nodes-base.noOp';
        let parameters = {};
        const position = [node.position.x, node.position.y];

        // Map React Flow types to N8N types
        if (node.type === 'shopifyTrigger') {
            n8nType = 'n8n-nodes-base.webhook';
            parameters = {
                httpMethod: 'POST',
                path: node.data.topic ? node.data.topic.replace('/', '-') : 'default-trigger',
                options: {},
            };
        } else if (node.type === 'action') {
            // Example mapping for generic actions
            if (node.data.actionType === 'http_request') {
                n8nType = 'n8n-nodes-base.httpRequest';
                parameters = {
                    url: 'https://example.com', // Placeholder
                    method: 'GET',
                };
            } else {
                // Fallback for other actions -> NoOp or Debug
                n8nType = 'n8n-nodes-base.set'; // Just set some data
                parameters = {
                    values: { string: [{ name: 'action', value: node.data.actionType }] }
                };
            }
        }

        return {
            parameters,
            name: node.data.label || node.id,
            type: n8nType,
            typeVersion: 1,
            position,
            id: node.id, // Keep ID for edge mapping reference
        };
    });

    const connections = {};

    edges.forEach((edge) => {
        const sourceNode = n8nNodes.find((n) => n.id === edge.source);
        const targetNode = n8nNodes.find((n) => n.id === edge.target);

        if (sourceNode && targetNode) {
            if (!connections[sourceNode.name]) {
                connections[sourceNode.name] = { main: [] };
            }
            // N8N uses "main" => [ [ { node: "TargetName", type: "main", index: 0 } ] ]
            // We assume single output for now
            if (!connections[sourceNode.name].main[0]) {
                connections[sourceNode.name].main[0] = [];
            }

            connections[sourceNode.name].main[0].push({
                node: targetNode.name,
                type: 'main',
                index: 0,
            });
        }
    });

    // Clean up IDs from nodes (N8N doesn't use UUIDs in the node object usually, it keys by Name, but keeping ID is fine or we can remove)
    const finalNodes = n8nNodes.map(({ id, ...rest }) => rest);

    return {
        nodes: finalNodes,
        connections,
    };
}
