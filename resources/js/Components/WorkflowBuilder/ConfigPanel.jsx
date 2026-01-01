import React, { useState, useEffect, useMemo } from "react";
import {
    Box,
    BlockStack,
    Text,
    FormLayout,
    TextField,
    Select,
    Banner,
    Divider
} from "@shopify/polaris";

export default function ConfigPanel({ node, nodeTypes, credentials = [], onUpdate }) {
    // Merge node.data.config into a single 'parameters' object for N8N compatibility
    // N8N nodes store data in 'parameters'. Our previous version used 'config'
    // We will normalize this.
    const [parameters, setParameters] = useState(node?.data?.parameters || node?.data?.config || {});
    const [selectedCredential, setSelectedCredential] = useState(node?.data?.credentials?.id || "");

    useEffect(() => {
        setParameters(node?.data?.parameters || node?.data?.config || {});
        setSelectedCredential(node?.data?.credentials?.id || "");
    }, [node]);



    const handleParameterChange = (key, value) => {
        const newParams = { ...parameters, [key]: value };
        setParameters(newParams);
        
        if (node && onUpdate) {
            // Update both 'config' (legacy) and 'parameters' (n8n-style)
            onUpdate(node.id, { 
                parameters: newParams,
                config: newParams 
            });
        }
    };
    
    const handleCredentialChange = (value) => {
        setSelectedCredential(value);
        if (node && onUpdate) {
            onUpdate(node.id, {
                credentials: { id: value }
            });
        }
    };

    // Find the node definition
    const nodeDefinition = useMemo(() => {
        if (!node || !nodeTypes) return null;
        // Search by name (n8n-nodes-base.shopify)
        const typeRaw = node.data?.n8nType || node.type;
        return nodeTypes.find(t => t.name === typeRaw) || null;
    }, [node, nodeTypes]);

    // Debug logging
    useEffect(() => {
         if (nodeDefinition?.credentials) {
             console.log("ConfigPanel: Node requires credentials", nodeDefinition.credentials);
             console.log("ConfigPanel: Available credentials", credentials);
         }
    }, [nodeDefinition, credentials]);

    if (!node) {
        return (
            <Box padding="400" minHeight="100%" background="bg-surface">
                <Text tone="subdued" as="p">
                    Select a node to configure
                </Text>
            </Box>
        );
    }

    // --- Helper Logic for Display Options ---
    const shouldShowProperty = (property) => {
        if (!property.displayOptions) return true;
        const { show, hide } = property.displayOptions;

        // "Show" logic
        if (show) {
            const allMatch = Object.keys(show).every(dependencyKey => {
                const requiredValues = show[dependencyKey];
                const actualValue = parameters[dependencyKey];
                return requiredValues.includes(actualValue);
            });
            if (!allMatch) return false;
        }

        // "Hide" logic
        if (hide) {
             const anyMatch = Object.keys(hide).some(dependencyKey => {
                const hiddenValues = hide[dependencyKey];
                const actualValue = parameters[dependencyKey];
                return hiddenValues.includes(actualValue);
            });
            if (anyMatch) return false;
        }

        return true;
    };

    // --- Recursive Field Renderer ---
    const renderProperty = (property) => {
        if (!shouldShowProperty(property)) return null;

        const key = property.name;
        const label = property.displayName || property.name;
        const value = parameters[key] ?? property.default ?? "";

        // Options (Select)
        if (property.type === 'options') {
            const options = property.options?.map(opt => ({
                label: opt.name,
                value: opt.value
            })) || [];
            
            return (
                <Select
                    key={key}
                    label={label}
                    options={options}
                    value={value}
                    onChange={(val) => handleParameterChange(key, val)}
                    helpText={property.description}
                />
            );
        }

        // Boolean
        if (property.type === 'boolean') {
             return (
                 <Select
                    key={key}
                    label={label}
                    options={[{ label: 'True', value: 'true'}, { label: 'False', value: 'false'}]}
                    value={String(value)}
                    onChange={(val) => handleParameterChange(key, val === 'true')}
                    helpText={property.description}
                 />
             );
        }

        // String / Default
        const isMultiline = property.typeOptions?.rows > 1;
        
        return (
            <TextField
                key={key}
                label={label}
                value={value}
                onChange={(val) => handleParameterChange(key, val)}
                multiline={isMultiline ? property.typeOptions.rows : false}
                placeholder={property.placeholder}
                helpText={property.description}
                autoComplete="off"
            />
        );
    };

    return (
        <Box padding="400" minHeight="100%" background="bg-surface">
            <BlockStack gap="400">
                {/* Header */}
                <Box borderBlockEndWidth="025" borderColor="border" paddingBlockEnd="400">
                    <BlockStack gap="100">
                        <Text variant="headingMd" as="h3">
                            {nodeDefinition?.displayName || node.data?.label || node.type}
                        </Text>
                        <Text variant="bodySm" tone="subdued">
                           {nodeDefinition?.name || node.data?.type || node.type}
                        </Text>
                    </BlockStack>
                </Box>

                {/* Form Fields */}
                <FormLayout>
                    {/* Credentials Selector */}
                    {nodeDefinition?.credentials && (
                        <Box paddingBlockEnd="200">
                             <Text variant="bodySm" fontWeight="medium">Authentication</Text>
                             <Select
                                label="Connect Credential"
                                labelHidden
                                options={[
                                    { label: 'Select Credential', value: '' },
                                    ...credentials
                                        .filter(c => nodeDefinition.credentials.some(nc => nc.name === c.type))
                                        .map(c => ({ label: c.name, value: c.id }))
                                ]}
                                value={selectedCredential}
                                onChange={handleCredentialChange}
                                helpText="Select the authentication to use for this node."
                             />
                             <Divider />
                        </Box>
                    )}

                    {/* Dynamic Parameters */}
                    {nodeDefinition?.properties ? (
                        nodeDefinition.properties.map(prop => renderProperty(prop))
                    ) : (
                        <Banner tone="warning">
                            <p>No configuration properties found for this node type.</p>
                        </Banner>
                    )}
                    
                    {/* Fallback for "Generic" nodes or custom data entry if definition exists but is empty? */}
                </FormLayout>
            </BlockStack>
        </Box>
    );
}
