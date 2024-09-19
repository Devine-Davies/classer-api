import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";

export default defineConfig({
    plugins: [
        laravel({
            input: [
                "resources/css/app.css",
                "resources/css/markdown/main.css",
                "resources/js/app.js",
                "resources/js/action-camera-matcher.js",
            ],
            refresh: true,
        }),
    ],
});
