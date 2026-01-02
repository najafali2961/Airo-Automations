import React from "react";
import { Page, Layout, Card, Text, Button, BlockStack } from "@shopify/polaris";

class ErrorBoundary extends React.Component {
    constructor(props) {
        super(props);
        this.state = { hasError: false, error: null };
    }

    static getDerivedStateFromError(error) {
        return { hasError: true, error };
    }

    componentDidCatch(error, errorInfo) {
        console.error("Uncaught error:", error, errorInfo);
    }

    handleReload = () => {
        window.location.reload();
    };

    render() {
        if (this.state.hasError) {
            return (
                <Page>
                    <Layout>
                        <Layout.Section>
                            <Card>
                                <BlockStack gap="400">
                                    <Text variant="headingMd" as="h2">
                                        Something went wrong
                                    </Text>
                                    <Text as="p">
                                        {this.state.error?.message ||
                                            "An unexpected error occurred."}
                                    </Text>
                                    <Button
                                        onClick={this.handleReload}
                                        variant="primary"
                                    >
                                        Reload Page
                                    </Button>
                                </BlockStack>
                            </Card>
                        </Layout.Section>
                    </Layout>
                </Page>
            );
        }

        return this.props.children;
    }
}

export default ErrorBoundary;
