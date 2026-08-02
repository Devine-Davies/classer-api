import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";
import tailwindcss from "@tailwindcss/vite";

const pageBundles = {
    app: ["resources/css/app.css", "resources/js/app.js"],
    checkout: [
        "resources/js/checkout.js",
        "resources/views/checkout/index/index.css",
        "resources/views/checkout/details/details.css",
        "resources/views/checkout/payment/payment.css",
        "resources/views/checkout/product/product.css",
        "resources/views/checkout/success/success.css",
    ],
    actionCameraMatcher: [
        "resources/views/action-camera-matcher/index/index.css",
        "resources/views/action-camera-matcher/index/index.js",
        "resources/views/action-camera-matcher/questions/questions.css",
        "resources/views/action-camera-matcher/questions/questions.js",
        "resources/views/action-camera-matcher/results/results.css",
        "resources/views/action-camera-matcher/results/results.js",
    ],
    adminLogin: ["resources/views/admin/login/index.css"],
};

const sharedBundles = {
    docs: ["resources/css/markdown/main.css"],
    cardCarousel: [
        "resources/views/components/card-carousel/card-carousel.css",
        "resources/views/components/card-carousel/card-carousel.js",
    ],
    galleryLightbox: ["resources/views/components/gallery-lightbox/gallery-lightbox.css"],
    hotspots: ["resources/views/components/hotspots/hotspots.css"],
    productGallery: [
        "resources/views/components/product-gallery/product-gallery.css",
        "resources/views/components/product-gallery/product-gallery.js",
    ],
    stepsShowcase: ["resources/views/components/steps-showcase/steps-showcase.js"],
};

const collectInputs = (bundleMap) => Object.values(bundleMap).flat();

export default defineConfig({
    plugins: [
        laravel({
            refresh: true,
            input: [...collectInputs(pageBundles), ...collectInputs(sharedBundles)],
        }),
        tailwindcss(),
    ],
});