import "./bootstrap";
import htmx from "htmx.org";

window.htmx = htmx;

const checkScroll = () => {
    const nav = document.getElementById("nav");
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
        } catch (error) {}
    };
};

const scrollToSection = (element) => {
    element.classList.add("active");
    element.scrollIntoView({
        behavior: "smooth",
    });
};

const updateGloalNavState = () => {
    const nav = document.getElementById("global-nav");
    nav.classList.toggle("hidden");
};

window.addEventListener("load", () => {
    detectHashBangNavigation();
    window.addEventListener("scroll", checkScroll);

    document
        .querySelector("[data-global-nav-toggle]")
        .addEventListener("click", updateGloalNavState);

    document
        .querySelectorAll(".link")
        .forEach((link) =>
            link.addEventListener("click", () => updateGloalNavState())
        );
});
