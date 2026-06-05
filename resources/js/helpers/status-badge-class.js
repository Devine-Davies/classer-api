export const statusBadgeClass = (status) => {
    const key = String(status || "").toLowerCase();

    if (key === "paid") return "is-paid";
    if (key === "pending") return "is-pending";
    if (key === "processing") return "is-processing";
    if (key === "refunded") return "is-refunded";
    if (["cancelled", "canceled", "failed"].includes(key)) return "is-failed";

    return "is-default";
};
