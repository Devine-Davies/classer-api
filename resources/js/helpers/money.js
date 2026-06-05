export const money = (currency, amount) => {
    const code = String(currency || "").toUpperCase();
    const value = (Number(amount || 0) / 100).toFixed(2);

    return `${code} ${value}`;
};
