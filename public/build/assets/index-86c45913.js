const s = "classer_admin_token", r = "classer_admin_token", d = e => { document.cookie = `${r}=${encodeURIComponent(e)}; path=/; max-age=1209600; samesite=lax` }, a = e => { e && (localStorage.setItem(s, e), d(e)) }; document.addEventListener("DOMContentLoaded", () => { c(), i() }); document.addEventListener("htmx:beforeRequest", () => { document.querySelector(".error-message").classList.add("hidden"), document.querySelector("input[type=submit]").classList.add("pointer-events-none") }); document.addEventListener("htmx:afterRequest", e => { setTimeout(() => { if (!e.detail.successful) { document.querySelector("input[type=submit]").classList.remove("pointer-events-none"); const n = document.querySelector(".error-message"); n.innerHTML = "Something went wrong, please try again.", n.classList.remove("hidden"); return } const t = e.detail.xhr.getResponseHeader("x-token"); a(t); const o = window.adminLoginRedirectUrl || `${window.pageUrl}/admin/stats`; window.location.assign(o) }, 500) }); const c = () => {
    grecaptcha.ready(() => {
        grecaptcha.execute("6LdNKLMpAAAAAFPilXVAY_0W7QTOEYkV6rgYZ6Yq", { action: "submit" }).then(e => {
            document.querySelector("#form form").insertAdjacentHTML("beforeend", `<div class="hidden">
                    <input type="hidden" name="grc" value="${e}">
                </div>`)
        })
    })
}, i = () => { document.querySelectorAll(".eye-show-password").forEach(e => { e.addEventListener("click", () => { const t = e.previousElementSibling; t.type = t.type === "password" ? "text" : "password" }) }) };
