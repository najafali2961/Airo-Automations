import axios from "axios";

export default function ExecutionViewer({ workflowId }) {
    const [executions, setExecutions] = useState([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);

    useEffect(() => {
        const fetchExecutions = async () => {
            try {
                setLoading(true);
                // Fetch from our local backend proxy
                const response = await axios.get(
                    `/workflows/${workflowId}/executions`
                );
                // N8N returns { data: [...] } structure
                setExecutions(response.data.data || []);
            } catch (err) {
                console.error(err);
                setError("Failed to load executions");
            } finally {
                setLoading(false);
            }
        };

        if (workflowId) {
            fetchExecutions();
        }
    }, [workflowId]);

    const rows = executions.map((exec) => [
        <Badge tone={exec.status === "success" ? "success" : "critical"}>
            {exec.status}
        </Badge>,
        exec.id,
        exec.startedAt ? format(new Date(exec.startedAt), "PPpp") : "-",
        `${exec.waitDuration || 0}ms`,
    ]);

    if (loading)
        return (
            <div className="p-4 flex justify-center">
                <Spinner accessibilityLabel="Loading executions" size="large" />
            </div>
        );
    if (error)
        return (
            <Text tone="critical" as="p">
                {error}
            </Text>
        );

    return (
        <div className="mt-4">
            <LegacyCard title="Execution History" sectioned>
                {executions.length === 0 ? (
                    <Text tone="subdued" as="p">
                        No executions recorded yet.
                    </Text>
                ) : (
                    <DataTable
                        columnContentTypes={["text", "text", "text", "text"]}
                        headings={["Status", "ID", "Started", "Duration"]}
                        rows={rows}
                    />
                )}
            </LegacyCard>
        </div>
    );
}
