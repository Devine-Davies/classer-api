
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

const syncGlobalNavState = (nav, navToggle) => {
    if (!nav) return;

    if (desktopNavMediaQuery.matches) {
        nav.classList.remove("hidden");
        navToggle?.setAttribute("aria-expanded", "false");
        return;
    }

    nav.classList.add("hidden");
    navToggle?.setAttribute("aria-expanded", "false");
};

const toggleGlobalNavState = (nav, navToggle) => {
    if (!nav || desktopNavMediaQuery.matches) {
        return;
    }

    const isOpening = nav.classList.contains("hidden");
    nav.classList.toggle("hidden");
    navToggle?.setAttribute("aria-expanded", String(isOpening));
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
            }
        });
    }
});
