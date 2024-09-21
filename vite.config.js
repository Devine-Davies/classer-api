import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";

const pages = {
    app: ["resources/css/app.css", "resources/js/app.js"],
    "action-camera-matcher": [
        "resources/views/action-camera-matcher/index.css",
        "resources/views/action-camera-matcher/index.js",
    ],
};

const components = {
    markdown: ["resources/css/markdown/main.css"],
};

const pagesList = Object.entries(pages)
    .map(([key, value]) => value)
    .flat();

const componentsList = Object.entries(components)
    .map(([key, value]) => value)
    .flat();

export default defineConfig({
    plugins: [
        laravel({
            refresh: true,
            input: [...pagesList, ...componentsList],
        }),
    ],
});
