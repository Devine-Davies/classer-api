document.addEventListener("DOMContentLoaded", function () {
    grecaptcha.ready(function () {
        grecaptcha
            .execute("6LdNKLMpAAAAAFPilXVAY_0W7QTOEYkV6rgYZ6Yq", {
                action: "submit",
            })
            .then(function (token) {
                document
                    .querySelector("#form form")
                    .insertAdjacentHTML(
                        "beforeend",
                        '<div class="hidden" ><input type="hidden" name="grc" value="' +
                            token +
                            '"></div>'
                    );
            });
    });
});

document.addEventListener("htmx:beforeRequest", (evt) => {
    // document.querySelector(".loading-spinner").classList.remove("hidden");
    document.querySelector(".error-message").classList.add("hidden");
    document
        .querySelector("input[type=submit]")
        .classList.add("pointer-events-none");
});

document.addEventListener("htmx:afterRequest", (evt) => {
    const res = JSON.parse(evt.detail.xhr.response);
    setTimeout(() => {
        // document.querySelector(".loading-spinner").classList.add("hidden");
        if (evt.detail.successful != true) {
            document
                .querySelector("input[type=submit]")
                .classList.remove("pointer-events-none");

            const errorElm = document.querySelector(".error-message");
            errorElm.innerHTML = "Something went wrong, please try again.";
            errorElm.classList.remove("hidden");
            return;
        } else {
            // remove form elements
            document.getElementById("form").classList.add("hidden");
            const token = evt.detail.xhr.getResponseHeader("x-token");
            requestStats(token);
            setInterval(() => requestStats(token), 900000); // 15 minutes
        }
    }, 500);
});

document.addEventListener("DOMContentLoaded", function () {
    const eyeButtons = document.querySelectorAll(".eye-show-password");
    eyeButtons.forEach((eyeButton) => {
        eyeButton.addEventListener("click", (e) => {
            const input = eyeButton.previousElementSibling;
            const type =
                input.getAttribute("type") === "password" ? "text" : "password";
            input.setAttribute("type", type);
        });
    });
});

/**
 * Request stats
 * @param {string} token
 */
const requestStats = (token) => {
    fetch(pageUrl + "/api/admin/stats", {
        method: "GET",
        headers: {
            Accept: "application/json",
            "Content-Type": "application/json",
            Authorization: "Bearer " + token,
        },
    }).then((response) => {
        response.json().then((data) => {
            const statsTemplate =
                document.getElementById("stats-template").innerHTML;
            const statsContainer = document.getElementById("stats-container");
            const htmlItems = Object.entries(data.data).map(
                ([title, stat]) => renderStatsView(statsTemplate, { title, stat })
            );

            document.getElementById("form").classList.add("hidden");
            statsContainer.innerHTML = htmlItems.join("");
            
            // wait for 15 minutes
            setTimeout(() => requestStats(token), 900000);
        });
    });
};

/**
 * Render stats view
 */
const renderStatsView = (template, data) => {
    return render(template, data);
};

/**
 * Render the template with the data
 * @param {*} template
 * @param {*} data
 * @returns
 */
const render = (template, data) => {
    return template.replace(/\${(.*?)}/g, (match, p1) => {
        return data[p1.trim()];
    });
};
