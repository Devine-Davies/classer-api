export const dateTime = (value) => {
    return value ? new Date(value).toLocaleString() : "-";
};
