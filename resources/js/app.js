
//  const token = localStorage.getItem('classer_admin_token');


import "./bootstrap";
import Typed from 'typed.js';
import htmx from "htmx.org";
import {
    escapeHtml,
    TemplateEngine,
    money,
    dateTime,
    statusBadgeClass,
} from "./helpers";

window.Typed = Typed;
window.htmx = htmx;
window.ClasserHelpers = {
    ...(window.ClasserHelpers || {}),
    escapeHtml,
    TemplateEngine,
    money,
    dateTime,
    statusBadgeClass,
};

// Backward compatibility for pages already using window.TemplateEngine directly.
window.TemplateEngine = window.ClasserHelpers.TemplateEngine;

const checkScroll = () => {
    const nav = document.getElementById("nav");
    if (!nav) return;

    if (!nav.classList.contains("site-header--transparent")) {
        nav.classList.remove("scrolled");
        return;
    }

    window.pageYOffset
        ? nav.classList.add("scrolled")
        : nav.classList.remove("scrolled");
};

const detectHashBangNavigation = () => {
    const elm = document.getElementById(
        window.location.hash.replace("#!/", "")
    );

    elm && scrollToSection(elm);
    window.onpopstate = () => {
        const section = window.location.hash.replace("#!/", "");
        try {
            const element = document.getElementById(section);
            scrollToSection(element);
        } catch (error) { }
    };
};

const scrollToSection = (element) => {
    element.classList.add("active");
    element.scrollIntoView({
        behavior: "smooth",
    });
};

const desktopNavMediaQuery = window.matchMedia("(min-width: 768px)");

const setMobileNavOpenClass = (nav, isOpen) => {
    const siteHeader = document.getElementById("nav");
    if (!siteHeader) {
        return;
    }

    if (desktopNavMediaQuery.matches || !nav) {
        siteHeader.classList.remove("mobile-nav-open");
        return;
    }

    siteHeader.classList.toggle("mobile-nav-open", isOpen);
};

const syncGlobalNavState = (nav, navToggle) => {
    if (!nav) return;

    if (desktopNavMediaQuery.matches) {
        nav.classList.remove("hidden");
        navToggle?.setAttribute("aria-expanded", "false");
        setMobileNavOpenClass(nav, false);
        return;
    }

    nav.classList.add("hidden");
    navToggle?.setAttribute("aria-expanded", "false");
    setMobileNavOpenClass(nav, false);
};

const toggleGlobalNavState = (nav, navToggle) => {
    if (!nav || desktopNavMediaQuery.matches) {
        return;
    }

    const isOpening = nav.classList.contains("hidden");
    nav.classList.toggle("hidden");
    navToggle?.setAttribute("aria-expanded", String(isOpening));
    setMobileNavOpenClass(nav, isOpening);
};

window.addEventListener("load", () => {
    detectHashBangNavigation();
    checkScroll();
    window.addEventListener("scroll", checkScroll);

    const navToggle = document.querySelector("[data-global-nav-toggle]");
    const globalNav = document.getElementById("global-nav");

    syncGlobalNavState(globalNav, navToggle);

    if (navToggle) {
        navToggle.addEventListener("click", () => {
            toggleGlobalNavState(globalNav, navToggle);
        });
    }

    desktopNavMediaQuery.addEventListener("change", () => {
        syncGlobalNavState(globalNav, navToggle);
    });

    if (globalNav) {
        globalNav.addEventListener("click", (event) => {
            const link = event.target.closest("a[href]");
            if (!link) return;

            // Close mobile menu after selecting a destination.
            if (window.matchMedia("(max-width: 767px)").matches) {
                globalNav.classList.add("hidden");
                navToggle?.setAttribute("aria-expanded", "false");
                setMobileNavOpenClass(globalNav, false);
            }
        });
    }
});

const RECAPTCHA_SITE_KEY = "6LdNKLMpAAAAAFPilXVAY_0W7QTOEYkV6rgYZ6Yq";
const RECAPTCHA_API_SRC = `https://www.google.com/recaptcha/api.js?render=${RECAPTCHA_SITE_KEY}`;

const getRecaptchaInputs = () => document.querySelectorAll('input[name="grc"]');

const ensureRecaptchaScript = () => {
    if (typeof window.grecaptcha !== "undefined") {
        return Promise.resolve();
    }

    const existingScript = document.querySelector(`script[src="${RECAPTCHA_API_SRC}"]`);
    if (existingScript) {
        return new Promise((resolve, reject) => {
            if (typeof window.grecaptcha !== "undefined") {
                resolve();
                return;
            }

            existingScript.addEventListener("load", () => resolve(), { once: true });
            existingScript.addEventListener("error", () => reject(new Error("reCAPTCHA failed to load")), { once: true });
        });
    }

    return new Promise((resolve, reject) => {
        const script = document.createElement("script");
        script.src = RECAPTCHA_API_SRC;
        script.async = true;
        script.defer = true;
        script.onload = () => resolve();
        script.onerror = () => reject(new Error("reCAPTCHA failed to load"));
        document.head.appendChild(script);
    });
};

/**
 * Populate every <input type="hidden" name="grc"> on demand.
 * The reCAPTCHA SDK is only loaded on pages that actually render these inputs.
 */
const setupRecaptchaForms = async () => {
    const captchaInputs = getRecaptchaInputs();

    if (captchaInputs.length === 0) {
        return;
    }

    try {
        await ensureRecaptchaScript();
    } catch (error) {
        return;
    }

    if (typeof window.grecaptcha === "undefined") {
        return;
    }

    window.grecaptcha.ready(() => {
        window.grecaptcha
            .execute(RECAPTCHA_SITE_KEY, {
                action: "submit",
            })
            .then((token) => {
                captchaInputs.forEach((input) => {
                    input.value = token;
                });
            })
            .catch(() => {
                // Keep the page functional; server-side validation will reject invalid submissions.
            });
    });
};

document.addEventListener("DOMContentLoaded", setupRecaptchaForms);

if ("serviceWorker" in navigator) {
    window.addEventListener("load", () => {
        navigator.serviceWorker.register("/sw.js").catch(() => {
            // Non-fatal: keep app working even if SW registration fails.
        });
    });
}
