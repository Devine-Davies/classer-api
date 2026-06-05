import { escapeHtml } from "./escape-html";

const cache = new Map();

export const getTemplate = (templateId) => {
    if (!cache.has(templateId)) {
        const templateElement = document.getElementById(templateId);

        if (!templateElement) {
            throw new Error(`Template not found: ${templateId}`);
        }

        cache.set(templateId, templateElement.innerHTML.trim());
    }

    return cache.get(templateId);
};

export const renderTemplate = (templateId, data = {}) => {
    return getTemplate(templateId).replace(/\{\s*([\w.]+)\s*\}/g, (_, key) => {
        const value = key
            .split(".")
            .reduce((result, property) => result?.[property], data);

        return escapeHtml(value ?? "-");
    });
};

export const clearTemplateCache = () => cache.clear();

export const TemplateEngine = {
    get: getTemplate,
    render: renderTemplate,
    clear: clearTemplateCache,
};
