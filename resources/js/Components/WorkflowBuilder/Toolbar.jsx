import React from "react";
import { Button, Text, InlineStack } from "@shopify/polaris";

export default function Toolbar({ title, onSave, isSaving }) {
    return (
        <InlineStack align="space-between" blockAlign="center">
            <Text variant="headingLg" as="h2">
                {title || "Untitled Workflow"}
            </Text>
            <Button
                variant="primary"
                onClick={onSave}
                loading={isSaving}
                disabled={isSaving}
            >
                {isSaving ? "Saving..." : "Save Workflow"}
            </Button>
        </InlineStack>
    );
}
