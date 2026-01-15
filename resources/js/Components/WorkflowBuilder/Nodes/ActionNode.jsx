import React, { memo } from "react";
import { Handle, Position } from "@xyflow/react";
import NodeCard from "../NodeCard";

export default memo(({ data, selected }) => {
    return (
        <>
            <Handle
                type="target"
                position={Position.Top}
                className="!w-4 !h-4 !bg-blue-400 !border-4 !border-white !rounded-full !-top-2 hover:!w-5 hover:!h-5 hover:!bg-blue-500 transition-all"
            />

            <NodeCard
                label={data.label || "Action"}
                selected={selected}
                type="action"
                appName={data.appName}
                isValid={data.isValid}
                validationMessage={data.validationMessage}
                onDelete={data.onDelete}
                subtext={
                    data.description ||
                    (data.settings?.action
                        ? `Action: ${data.settings.action}`
                        : "Select action...")
                }
            />

            <Handle
                type="source"
                id="then"
                position={Position.Bottom}
                className="!w-4 !h-4 !bg-blue-400 !border-4 !border-white !rounded-full !-bottom-2 hover:!w-5 hover:!h-5 hover:!bg-blue-500 transition-all"
            />
        </>
    );
});
