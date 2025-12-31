import React from "react";
import { Head } from "@inertiajs/react";

export default function Dashboard({ shop, stats, executions, n8nUrl }) {
    return (
        <div className="min-h-screen bg-gray-50 font-sans">
            <Head title="Automation Dashboard" />

            {/* Top Navigation */}
            <nav className="bg-white border-b border-gray-200 px-6 py-4 flex justify-between items-center">
                <div className="flex items-center gap-3">
                    <h1 className="text-xl font-bold text-gray-800">
                        Shopify Automation
                    </h1>
                    <span className="text-sm bg-blue-100 text-blue-800 py-1 px-3 rounded-full">
                        {shop.name}
                    </span>
                </div>
                <div>{/* User Menu Placeholder */}</div>
            </nav>

            <main className="max-w-7xl mx-auto px-6 py-8">
                {/* Stats Grid */}
                <div className="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                    <div className="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
                        <p className="text-gray-500 text-sm font-medium">
                            Total Executions
                        </p>
                        <p className="text-3xl font-bold text-gray-900 mt-2">
                            {stats.total_executions}
                        </p>
                    </div>
                    <div className="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
                        <p className="text-gray-500 text-sm font-medium">
                            Successful
                        </p>
                        <p className="text-3xl font-bold text-green-600 mt-2">
                            {stats.success_executions}
                        </p>
                    </div>
                    <div className="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
                        <p className="text-gray-500 text-sm font-medium">
                            Failed
                        </p>
                        <p className="text-3xl font-bold text-red-600 mt-2">
                            {stats.failed_executions}
                        </p>
                    </div>
                </div>

                {/* N8N Builder / Iframe Section */}
                <div className="bg-white rounded-xl shadow-sm border border-gray-100 mb-8 overflow-hidden h-96 flex flex-col items-center justify-center text-center p-8">
                    <h2 className="text-lg font-semibold text-gray-800 mb-2">
                        Automation Builder
                    </h2>
                    <p className="text-gray-500 mb-6">
                        Create and manage your workflows visually.
                    </p>
                    <a
                        href={n8nUrl}
                        target="_blank"
                        rel="noopener noreferrer"
                        className="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700"
                    >
                        Open Workflow Editor
                    </a>
                    {/* Note: Standard N8N doesn't easily support embedding without 'embed' mode config. 
               For now, we link out or use iframe if configured. */}
                </div>

                {/* Executions Table */}
                <div className="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                    <div className="px-6 py-4 border-b border-gray-200">
                        <h3 className="text-lg font-semibold text-gray-800">
                            Recent Activity
                        </h3>
                    </div>
                    <div className="overflow-x-auto">
                        <table className="min-w-full divide-y divide-gray-200">
                            <thead className="bg-gray-50">
                                <tr>
                                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Status
                                    </th>
                                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Topic
                                    </th>
                                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Date
                                    </th>
                                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Payload Snippet
                                    </th>
                                </tr>
                            </thead>
                            <tbody className="bg-white divide-y divide-gray-200">
                                {executions.length === 0 ? (
                                    <tr>
                                        <td
                                            colSpan="4"
                                            className="px-6 py-4 text-center text-gray-500"
                                        >
                                            No executions yet.
                                        </td>
                                    </tr>
                                ) : (
                                    executions.map((exec) => (
                                        <tr key={exec.id}>
                                            <td className="px-6 py-4 whitespace-nowrap">
                                                <span
                                                    className={`px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${
                                                        exec.status ===
                                                        "processed"
                                                            ? "bg-green-100 text-green-800"
                                                            : exec.status ===
                                                              "failed"
                                                            ? "bg-red-100 text-red-800"
                                                            : "bg-yellow-100 text-yellow-800"
                                                    }`}
                                                >
                                                    {exec.status}
                                                </span>
                                            </td>
                                            <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {exec.topic}
                                            </td>
                                            <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {new Date(
                                                    exec.created_at
                                                ).toLocaleString()}
                                            </td>
                                            <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500 font-mono">
                                                {JSON.stringify(
                                                    JSON.parse(exec.payload)
                                                ).substring(0, 30)}
                                                ...
                                            </td>
                                        </tr>
                                    ))
                                )}
                            </tbody>
                        </table>
                    </div>
                </div>
            </main>
        </div>
    );
}
