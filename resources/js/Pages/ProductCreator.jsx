import React, { useState, useEffect, useRef } from "react";
import {
    Page,
    Layout,
    LegacyCard,
    FormLayout,
    TextField,
    Button,
    Text,
    Banner,
    BlockStack,
    Box,
    ProgressBar,
} from "@shopify/polaris";
import { Head, router } from "@inertiajs/react";
import axios from "axios";

export default function ProductCreator() {
    // Form State
    const [form, setForm] = useState({
        title: "",
        description: "",
        price: "",
        sku: "",
        quantity: "0",
        vendor: "",
        type: "",
    });

    const [loading, setLoading] = useState(false);
    const [jobId, setJobId] = useState(null);
    const [logs, setLogs] = useState([]);
    const [error, setError] = useState(null);
    const pollingRef = useRef(null);

    // Handlers
    const handleChange = (field) => (value) => {
        setForm({ ...form, [field]: value });
    };

    const handleSubmit = async () => {
        setLoading(true);
        setError(null);
        setLogs([]);
        setJobId(null);

        try {
            const response = await axios.post("/product/create", form);
            setJobId(response.data.jobId);
        } catch (err) {
            setError(
                "Failed to start job: " +
                    (err.response?.data?.message || err.message)
            );
            setLoading(false);
        }
    };

    // Polling Logic
    useEffect(() => {
        if (!jobId) return;

        const poll = async () => {
            try {
                const response = await axios.get(`/product/poll/${jobId}`);
                const newLogs = response.data.logs || [];
                setLogs(newLogs);

                // Check if done
                const lastLog = newLogs[newLogs.length - 1];
                if (
                    lastLog &&
                    (lastLog.message === "DONE" ||
                        lastLog.message.includes("failed"))
                ) {
                    setLoading(false);
                    clearInterval(pollingRef.current);
                }
            } catch (err) {
                console.error("Polling error", err);
            }
        };

        // Poll every 1 second
        pollingRef.current = setInterval(poll, 1000);
        poll(); // initial call

        return () => clearInterval(pollingRef.current);
    }, [jobId]);

    return (
        <Page title="Interactive Product Creator">
            <Head title="Product Creator" />
            <Layout>
                <Layout.Section>
                    <LegacyCard sectioned title="Create New Product">
                        <FormLayout>
                            <TextField
                                label="Title"
                                value={form.title}
                                onChange={handleChange("title")}
                                autoComplete="off"
                            />
                            <TextField
                                label="Description"
                                value={form.description}
                                onChange={handleChange("description")}
                                multiline={4}
                                autoComplete="off"
                            />
                            <FormLayout.Group>
                                <TextField
                                    label="Price"
                                    type="number"
                                    value={form.price}
                                    onChange={handleChange("price")}
                                    prefix="$"
                                    autoComplete="off"
                                />
                                <TextField
                                    label="Quantity"
                                    type="number"
                                    value={form.quantity}
                                    onChange={handleChange("quantity")}
                                    autoComplete="off"
                                />
                            </FormLayout.Group>
                            <FormLayout.Group>
                                <TextField
                                    label="SKU"
                                    value={form.sku}
                                    onChange={handleChange("sku")}
                                    autoComplete="off"
                                />
                                <TextField
                                    label="Vendor"
                                    value={form.vendor}
                                    onChange={handleChange("vendor")}
                                    autoComplete="off"
                                />
                            </FormLayout.Group>

                            {error && <Banner tone="critical">{error}</Banner>}

                            <Button
                                primary
                                onClick={handleSubmit}
                                loading={loading}
                                disabled={loading}
                            >
                                Create Product
                            </Button>
                        </FormLayout>
                    </LegacyCard>
                </Layout.Section>

                <Layout.Section secondary>
                    <LegacyCard title="Execution Logs" sectioned>
                        <BlockStack gap="400">
                            {loading && <ProgressBar size="small" />}

                            <Box
                                background="bg-surface-secondary"
                                padding="400"
                                borderRadius="200"
                                style={{
                                    height: "300px",
                                    overflowY: "auto",
                                    fontFamily: "monospace",
                                    fontSize: "12px",
                                }}
                            >
                                {logs.length === 0 ? (
                                    <Text tone="subdued">
                                        Logs will appear here...
                                    </Text>
                                ) : (
                                    logs.map((log, i) => (
                                        <div
                                            key={i}
                                            style={{ marginBottom: "8px" }}
                                        >
                                            <Text tone="subdued" as="span">
                                                [
                                                {new Date(
                                                    log.timestamp
                                                ).toLocaleTimeString()}
                                                ]{" "}
                                            </Text>
                                            <Text
                                                as="span"
                                                color={
                                                    log.message.includes(
                                                        "failed"
                                                    )
                                                        ? "critical"
                                                        : "success"
                                                }
                                            >
                                                {log.message}
                                            </Text>
                                        </div>
                                    ))
                                )}
                            </Box>
                        </BlockStack>
                    </LegacyCard>
                </Layout.Section>
            </Layout>
        </Page>
    );
}
