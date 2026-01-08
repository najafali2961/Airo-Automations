import React, { useState, useEffect, useMemo } from "react";
import { Select, Spinner, Combobox, Listbox, Icon } from "@shopify/polaris";
import { SearchIcon } from "@shopify/polaris-icons";
// import { useAuthenticatedFetch } from "../../../hooks/useAuthenticatedFetch";  <-- Removed

// Note: Ensure useAuthenticatedFetch or similar is available, or use standard fetch with props if passed from parent.
// For now, I'll assume we can use standard fetch or axios if available, but since this is Inertia/Laravel, relative paths work with session cookies usually.
// Or better, use axios if it's in the project. The codebase seems to use Inertia.

export default function ResourceSelect({
    label,
    value,
    onChange,
    service,
    resource,
    placeholder = "Select...",
    disabled = false,
    required = false,
}) {
    const [options, setOptions] = useState([]);
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState(null);
    const [inputValue, setInputValue] = useState("");
    const [selectedOption, setSelectedOption] = useState(null);

    // Fetch resources on mount
    useEffect(() => {
        if (!service || !resource) return;

        const fetchResources = async () => {
            setLoading(true);
            setError(null);
            try {
                // Determine service name mapping if needed (e.g. 'google' -> 'google')
                // resource: 'channels' | 'lists' | 'drive_folders' | 'google_sheets'
                // Force lowercase service name to ensure backend compatibility
                const serviceName = (service || "").toLowerCase();

                console.log(
                    "[ResourceSelect] Fetching resources. Raw service:",
                    service,
                    "Lowercased:",
                    serviceName,
                    "Resource:",
                    resource
                );

                const url = `/api/integrations/${serviceName}/${resource}`;

                // Use window.axios if available for consistent auth/csrf handling
                const axios = window.axios;

                let data = [];
                if (axios) {
                    const res = await axios.get(url);
                    data = res.data;
                } else {
                    // Fallback to fetch if axios not found (unlikely in this setup)
                    const res = await fetch(url, {
                        headers: {
                            Accept: "application/json",
                            "X-Requested-With": "XMLHttpRequest",
                        },
                    });
                    if (!res.ok) {
                        const errText = await res.text();
                        throw new Error(
                            errText || `Failed to fetch ${resource}`
                        );
                    }
                    data = await res.json();
                }

                // Expected format: [{ label, value }]
                setOptions(data);

                // If we have a value, try to set selectedOption label for display
                if (value) {
                    const found = data.find((o) => o.value === value);
                    if (found) {
                        setSelectedOption(found);
                        setInputValue(found.label);
                    } else {
                        // Fallback if ID exists but not in list (maybe private/archived?)
                        setInputValue(value);
                    }
                }
            } catch (err) {
                console.error("Resource fetch error:", err);
                // Extract error message from axios response if available
                let msg = "Failed to load options";
                if (
                    err.response &&
                    err.response.data &&
                    err.response.data.error
                ) {
                    msg = err.response.data.error;
                } else if (err.message) {
                    msg = err.message;
                }
                setError(msg);
            } finally {
                setLoading(false);
            }
        };

        fetchResources();
    }, [service, resource]);

    // Update local state if external value changes (and we have options)
    useEffect(() => {
        if (value && options.length > 0 && !selectedOption) {
            const found = options.find((o) => o.value === value);
            if (found) {
                setSelectedOption(found);
                setInputValue(found.label);
            }
        }
    }, [value, options]);

    // Filter options based on input
    const filteredOptions = useMemo(() => {
        if (!inputValue) return options;
        return options.filter((option) =>
            option.label.toLowerCase().includes(inputValue.toLowerCase())
        );
    }, [inputValue, options]);

    const handleSelect = (val) => {
        const selected = options.find((o) => o.value === val);
        setSelectedOption(selected);
        setInputValue(selected ? selected.label : val);
        onChange(val);
    };

    const handleChange = (val) => {
        setInputValue(val);
        // If user clears input, maybe clear selection?
        // Or keep it as free text if we allow custom IDs?
        // For now, assume selection is required from list but let's allow custom input if it doesn't match?
        // Actually, for IDs, we usually want exact match.
        // But if they type an ID directly that isn't in list (rare), we might want to support it.
        // Let's stick to selection for now.
    };

    if (error) {
        return (
            <Select
                label={label}
                options={[{ label: "Error loading options", value: "" }]}
                disabled
                error={error}
            />
        );
    }

    return (
        <Combobox
            allowFreeForm={true} // Allow typing to filter
            activator={
                <Combobox.TextField
                    prefix={
                        loading ? (
                            <Spinner size="small" />
                        ) : (
                            <Icon source={SearchIcon} />
                        )
                    }
                    onChange={handleChange}
                    label={label}
                    value={inputValue}
                    placeholder={placeholder}
                    autoComplete="off"
                    disabled={disabled || loading}
                    required={required}
                />
            }
        >
            {filteredOptions.length > 0 ? (
                <Listbox onSelect={handleSelect}>
                    {filteredOptions.map((option) => (
                        <Listbox.Option
                            key={option.value}
                            value={option.value}
                            selected={value === option.value}
                            accessibilityLabel={option.label}
                        >
                            {option.label}
                        </Listbox.Option>
                    ))}
                </Listbox>
            ) : null}
        </Combobox>
    );
}
