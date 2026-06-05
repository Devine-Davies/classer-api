const ADMIN_TOKEN_KEY = "classer_admin_token";
const ADMIN_TOKEN_COOKIE = "classer_admin_token";

const writeTokenCookie = (token) => {
    const maxAgeSeconds = 60 * 60 * 24 * 14;
    document.cookie = `${ADMIN_TOKEN_COOKIE}=${encodeURIComponent(token)}; path=/; max-age=${maxAgeSeconds}; samesite=lax`;
};

const storeAdminToken = (token) => {
    if (!token) {
        return;
    }

    localStorage.setItem(ADMIN_TOKEN_KEY, token);
    writeTokenCookie(token);
};

document.addEventListener("DOMContentLoaded", () => {
    setupGrecaptcha();
    setupPasswordToggles();
});

document.addEventListener("htmx:beforeRequest", () => {
    document.querySelector(".error-message").classList.add("hidden");
    document
        .querySelector("input[type=submit]")
        .classList.add("pointer-events-none");
});

document.addEventListener("htmx:afterRequest", (evt) => {
    setTimeout(() => {
        if (!evt.detail.successful) {
            document
                .querySelector("input[type=submit]")
                .classList.remove("pointer-events-none");
            const errorElm = document.querySelector(".error-message");
            errorElm.innerHTML = "Something went wrong, please try again.";
            errorElm.classList.remove("hidden");
            return;
        }

        const token = evt.detail.xhr.getResponseHeader("x-token");
        storeAdminToken(token);

        const redirectTo =
            window.adminLoginRedirectUrl ||
            `${window.pageUrl}/auth/admin/stats`;
        window.location.assign(redirectTo);
    }, 500);
});

/**
 * Setup Google reCAPTCHA for the form.
 */
const setupGrecaptcha = () => {
    grecaptcha.ready(() => {
        grecaptcha
            .execute("6LdNKLMpAAAAAFPilXVAY_0W7QTOEYkV6rgYZ6Yq", {
                action: "submit",
            })
            .then((token) => {
                document.querySelector("#form form").insertAdjacentHTML(
                    "beforeend",
                    `<div class="hidden">
                    <input type="hidden" name="grc" value="${token}">
                </div>`
                );
            });
    });
};

/**
 * Setup password toggle buttons to show/hide password.
 */
const setupPasswordToggles = () => {
    document.querySelectorAll(".eye-show-password").forEach((eyeButton) => {
        eyeButton.addEventListener("click", () => {
            const input = eyeButton.previousElementSibling;
            input.type = input.type === "password" ? "text" : "password";
        });
    });
};
