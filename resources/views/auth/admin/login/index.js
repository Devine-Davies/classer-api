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
            const converted = mapStatsResponse(data.data);

            console.log(converted);

            const htmlItems = converted.map((item) =>
                render(statsTemplate, item)
            );
            document.getElementById("form").classList.add("hidden");
            statsContainer.innerHTML = htmlItems.join("");

            // wait for 15 minutes
            setTimeout(() => requestStats(token), 900000);
        });
    });
};

/**
 * Map the stats response
 * @param {*} items
 * @returns
 */
const mapStatsResponse = (items) => {
    const maps = {
        totalUsers: {
            icon: "people",
            title: "Total Users",
            color: "bg-blue-500",
        },
        totalMonthlyRegisters: {
            icon: "star",
            title: "Monthly Registers",
            color: "bg-red-500",
        },
        totalWeeklyRegisters: {
            icon: "star",
            title: "Weekly Registers",
            color: "bg-orange-500",
        },
        monthlyLoginsCount: {
            icon: "login",
            title: "Monthly Logins",
            color: "bg-yellow-500",
        },
        totalWeeklyLogins: {
            icon: "login",
            title: "Weekly Logins",
            color: "bg-indigo-500",
        },
    };

    return Object.entries(items).map(([key, value]) => ({
        ...maps[key],
        stat: value,
    }));
};

/**
 * Render stats view
 */
const renderStatsView = (template, data) => {
    return render(template, mapStatsResponse(data));
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
