import React, { useState, useRef, useCallback } from "react";
import {
    TextField,
    Button,
    Popover,
    ActionList,
    Icon,
    Box,
    BlockStack,
    Text,
    Scrollable,
} from "@shopify/polaris";
import { SearchIcon, MagicIcon } from "@shopify/polaris-icons";

export default function VariableInput({
    label,
    value,
    onChange,
    variables = [],
    multiline = false,
    placeholder,
    required,
    type = "text",
}) {
    const [popoverActive, setPopoverActive] = useState(false);
    const [searchQuery, setSearchQuery] = useState("");
    const inputRef = useRef(null);

    const togglePopover = useCallback(
        () => setPopoverActive((active) => !active),
        []
    );

    const handleVariableSelect = (variableValue) => {
        // Simple insertion at end for now, can be enhanced to use cursor position
        // if we manage the ref's selectionStart/End manually.
        // Doing full cursor management in React is tricky without a dedicated library,
        // so we'll append if cursor isn't tracked, or insert if we can.

        const textToInsert = `{{ ${variableValue} }}`;

        // Optimistic: Append to end if not advanced.
        // Real implementation for cursor:
        // const input = inputRef.current?.input; // Access Polaris internal input? Hard.
        // Let's just append for now, users can move it.
        const newValue = (value || "") + textToInsert;
        onChange(newValue);
        setPopoverActive(false);
        setSearchQuery("");
    };

    const filteredVariables = variables.filter((v) =>
        v.label.toLowerCase().includes(searchQuery.toLowerCase())
    );

    const activator = (
        <Button
            onClick={togglePopover}
            icon={MagicIcon}
            title="Insert Variable"
        />
    );

    return (
        <div className="relative">
            <TextField
                label={label}
                value={value}
                onChange={onChange}
                multiline={multiline}
                placeholder={placeholder}
                type={type}
                autoComplete="off"
                required={required}
                connectedRight={
                    <Popover
                        active={popoverActive}
                        activator={activator}
                        onClose={togglePopover}
                        autofocusTarget="first-node"
                    >
                        <Box
                            padding="200"
                            minWidth="200px"
                            maxWidth="300px"
                            minHeight="200px"
                        >
                            <BlockStack gap="200">
                                <TextField
                                    prefix={<Icon source={SearchIcon} />}
                                    placeholder="Search variables..."
                                    value={searchQuery}
                                    onChange={setSearchQuery}
                                    autoComplete="off"
                                    labelHidden
                                    label="Search Variables"
                                    size="slim"
                                />
                                <div
                                    style={{
                                        maxHeight: "200px",
                                        overflowY: "auto",
                                    }}
                                >
                                    {filteredVariables.length > 0 ? (
                                        <ActionList
                                            items={filteredVariables.map(
                                                (v) => ({
                                                    content: v.label,
                                                    helpText: v.value,
                                                    onAction: () =>
                                                        handleVariableSelect(
                                                            v.value
                                                        ),
                                                })
                                            )}
                                        />
                                    ) : (
                                        <Box padding="200">
                                            <Text
                                                tone="subdued"
                                                alignment="center"
                                            >
                                                {variables.length === 0
                                                    ? "No variables available for the selected trigger."
                                                    : "No matching variables found."}
                                            </Text>
                                        </Box>
                                    )}
                                </div>
                            </BlockStack>
                        </Box>
                    </Popover>
                }
            />
        </div>
    );
}
