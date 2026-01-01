import React from "react";
import { Button, Text } from "@shopify/polaris";

export default function Toolbar({ title, onSave, isSaving }) {
    return (
        <div className="flex justify-between items-center p-4 border-b border-gray-200 bg-white">
            <div className="flex flex-col">
                <Text variant="headingLg" as="h2">
                    {title || "Untitled Workflow"}
                </Text>
            </div>
            <div>
                <Button
                    variant="primary"
                    onClick={onSave}
                    loading={isSaving}
                    disabled={isSaving}
                >
                    {isSaving ? "Saving..." : "Save Workflow"}
                </Button>
            </div>
        </div>
    );
}
