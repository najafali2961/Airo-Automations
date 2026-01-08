export const getIconAndColor = (label = "") => {
    const lower = label.toLowerCase();

    if (lower.includes("shopify"))
        return {
            icon: "https://upload.wikimedia.org/wikipedia/commons/0/0e/Shopify_logo_2018.svg",
            isUrl: true,
            color: "bg-gradient-to-r from-green-500 to-emerald-600",
            tone: "text-emerald-700",
            border: "border-green-200"
        };

    if (lower.includes("slack"))
        return {
            icon: "https://cdn-icons-png.flaticon.com/512/2111/2111615.png",
            isUrl: true,
            color: "bg-gradient-to-r from-purple-500 to-indigo-600",
            tone: "text-indigo-700",
            border: "border-indigo-200"
        };

    if (lower.includes("email") || lower.includes("smtp"))
        return {
            icon: "https://cdn-icons-png.flaticon.com/512/732/732200.png",
            isUrl: true,
            color: "bg-gradient-to-r from-blue-400 to-cyan-500",
            tone: "text-blue-700",
            border: "border-blue-200"
        };

    if (lower.includes("google"))
        return {
            icon: "https://cdn-icons-png.flaticon.com/512/2991/2991148.png",
            isUrl: true,
            color: "bg-gradient-to-r from-blue-500 to-red-500",
            tone: "text-blue-700",
            border: "border-blue-200"
        };

    if (lower.includes("twilio"))
        return {
            icon: "https://cdn-icons-png.flaticon.com/512/5968/5968841.png",
            isUrl: true,
            color: "bg-gradient-to-r from-red-500 to-rose-600",
            tone: "text-red-700",
            border: "border-red-200"
        };

    if (lower.includes("klaviyo"))
        return {
            icon: "https://www.klaviyo.com/application-assets/klaviyo/production/static-assets/favicon.png",
            isUrl: true,
            color: "bg-gradient-to-r from-green-400 to-teal-500",
            tone: "text-teal-700",
            border: "border-teal-200"
        };

    if (lower.includes("condition"))
        return {
            icon: "🔀",
            isUrl: false,
            color: "bg-gradient-to-r from-orange-400 to-amber-500",
            tone: "text-amber-700",
            border: "border-amber-200"
        };

    if (lower.includes("delay") || lower.includes("wait"))
        return {
            icon: "⏳",
            isUrl: false,
            color: "bg-gradient-to-r from-gray-500 to-gray-600",
            tone: "text-gray-700",
            border: "border-gray-200"
        };

    // Default
    return {
        icon: "⚡",
        isUrl: false,
        color: "bg-gradient-to-r from-gray-700 to-gray-900",
        tone: "text-gray-800",
        border: "border-gray-200"
    };
};
