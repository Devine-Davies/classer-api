import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";
import tailwindcss from '@tailwindcss/vite'


const pages = {
    app: ["resources/css/app.css", "resources/js/app.js"],
    checkout: [
        "resources/js/checkout.js",
        "resources/views/checkout/index/index.css",
        "resources/views/checkout/details/details.css",
        "resources/views/checkout/payment/payment.css",
        "resources/views/checkout/product/product.css",
        "resources/views/checkout/success/success.css",
    ],
    "action-camera-matcher": [
        "resources/views/action-camera-matcher/index/index.css",
        "resources/views/action-camera-matcher/index/index.js",
        "resources/views/action-camera-matcher/questions/questions.css",
        "resources/views/action-camera-matcher/questions/questions.js",
        "resources/views/action-camera-matcher/results/results.css",
        "resources/views/action-camera-matcher/results/results.js",
    ],
    "admin-login": [
        "resources/views/admin/login/index.css",
    ],
};

const components = {
    markdown: ["resources/css/markdown/main.css"],
    "card-carousel": [
        "resources/views/components/card-carousel/card-carousel.css",
        "resources/views/components/card-carousel/card-carousel.js",
    ],
    "gallery-lightbox": [
        "resources/views/components/gallery-lightbox/gallery-lightbox.css",
    ],
    hotspots: ["resources/views/components/hotspots/hotspots.css"],
    "product-gallery": [
        "resources/views/components/product-gallery/product-gallery.css",
        "resources/views/components/product-gallery/product-gallery.js",
    ],
    "steps-showcase": ["resources/views/components/steps-showcase/steps-showcase.js"],
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
        tailwindcss(),
    ],
});