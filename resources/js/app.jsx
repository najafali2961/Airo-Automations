import "../css/app.css";
import "./bootstrap";
import { createInertiaApp } from "@inertiajs/react";
import { createRoot } from "react-dom/client";
import { router } from "@inertiajs/react";
import { AppProvider } from "@shopify/polaris";
import { NavMenu } from "@shopify/app-bridge-react";
import { createApp } from "@shopify/app-bridge";
import { getSessionToken } from "@shopify/app-bridge/utilities";
import axios from "axios";
import enTranslations from "@shopify/polaris/locales/en.json";
import "@shopify/polaris/build/esm/styles.css";
import ErrorBoundary from "./Components/ErrorBoundary";

createInertiaApp({
    resolve: (name) => {
        const pages = import.meta.glob("./Pages/**/*.jsx", { eager: true });
        return pages[`./Pages/${name}.jsx`];
    },
    setup({ el, App, props }) {
        const urlParams = new URLSearchParams(window.location.search);
        const host = urlParams.get("host");
        const apiKey = document.querySelector(
            'meta[name="shopify-api-key"]',
        )?.content;

        if (host && apiKey) {
            const app = createApp({
                apiKey: apiKey,
                host: host,
            });

            // Intercept axios requests to add session token
            window.axios = axios; // Make global for other components using window.axios
            axios.interceptors.request.use(
                async function (config) {
                    const token = await getSessionToken(app);
                    config.headers.Authorization = `Bearer ${token}`;
                    return config;
                },
                function (error) {
                    return Promise.reject(error);
                },
            );
        }

        const config = {
            apiKey: apiKey,
            host: host,
            forceRedirect: !!host,
        };

        createRoot(el).render(
            <AppProvider i18n={enTranslations} config={config}>
                {!window.location.pathname.startsWith("/admin-tools") && (
                    <NavMenu>
                        <a href="/" rel="home">
                            Home
                        </a>
                        <a href="/workflows">Workflows</a>
                        <a href="/templates">Templates</a>
                        <a href="/executions">Executions</a>
                        <a href="/connectors">Connectors</a>
                    </NavMenu>
                )}
                <ErrorBoundary>
                    <App {...props} />
                </ErrorBoundary>
            </AppProvider>,
        );
    },
});
