import dagre from "dagre";
import { Position } from "@xyflow/react";

const nodeWidth = 240;
const nodeHeight = 150; // Approximated height including padding

export const getLayoutedElements = (nodes, edges, direction = "TB") => {
    const dagreGraph = new dagre.graphlib.Graph();
    dagreGraph.setDefaultEdgeLabel(() => ({}));

    const isHorizontal = direction === "LR";
    dagreGraph.setGraph({
        rankdir: direction,
        ranksep: 100,
        nodesep: 100
    });


    nodes.forEach((node) => {
        // Use dimensions if available, otherwise fallback
        const width = node.measured?.width || nodeWidth;
        const height = node.measured?.height || nodeHeight;
        dagreGraph.setNode(node.id, { width, height });
    });

    edges.forEach((edge) => {
        dagreGraph.setEdge(edge.source, edge.target);
    });

    dagre.layout(dagreGraph);

    const newNodes = nodes.map((node) => {
        const nodeWithPosition = dagreGraph.node(node.id);
        const newNode = {
            ...node,
            targetPosition: isHorizontal ? Position.Left : Position.Top,
            sourcePosition: isHorizontal ? Position.Right : Position.Bottom,
            // We are shifting the dagre node position (anchor=center center) to the top left
            // so it matches the React Flow node anchor point (top left).
            position: {
                x: nodeWithPosition.x - (node.measured?.width || nodeWidth) / 2,
                y:
                    nodeWithPosition.y -
                    (node.measured?.height || nodeHeight) / 2,
            },
        };

        return newNode;
    });

    return { nodes: newNodes, edges };
};
