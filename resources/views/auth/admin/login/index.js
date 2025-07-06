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
            document.querySelector("[x-data]").classList.remove("hidden");
            requestStats(token);
            requestLogs("app.log", token);
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
            const htmlItems = converted.map((item) =>
                render(statsTemplate, item)
            );
            document.getElementById("form").classList.add("hidden");
            statsContainer.innerHTML = htmlItems.join("");

            // wait for 15 minutes
            setTimeout(() => {
                requestStats(token);
                requestLogs("app.log", token);
            }, 900000);
        });
    });
};

/**
 * Request logs from the server
 * @param {*} filename
 */
const requestLogs = (filename, token) => {
    fetch(pageUrl + "/api/admin/logs/" + filename, {
        method: "GET",
        headers: {
            Accept: "application/json",
            "Content-Type": "application/json",
            Authorization: "Bearer " + token,
        },
    }).then((response) => {
        response.json().then((data) => {
            data = data.reverse(); // Reverse the logs to show the latest first
            const logsTemplate =
                document.getElementById("logs-template").innerHTML;
            const logsContainer = document.getElementById("logs-container");
            const htmlItems = (data ?? [])
                .filter((line) => line.trim() !== "")
                .map((log) => {
                    const match = log.match(
                        /^\[(.*?)\]\s+(\w+)\.(\w+):\s+\[(.*?)\]\s+(.*)$/
                    );
                    const [, timestamp, env, type, context, message] = match;
                    const iconMap = {
                        ERROR: "🔴",
                        WARNING: "🟡",
                        INFO: "🔵",
                        DEBUG: "🟤",
                        default: "🟤",
                    };

                    const icon = iconMap[type.toUpperCase()] || iconMap.default;

                    return render(logsTemplate, {
                        timestamp,
                        context,
                        message,
                        icon,
                    });
                });

            logsContainer.innerHTML = htmlItems.join("");
        });
    });
};

/**
 * Map the stats response
 * @param {*} items
 * @returns
 */
const mapStatsResponse = (items) => {
    const converter = (value) => value.toLocaleString();
    const converterToMb = (value) => (value / 1024 / 1024).toFixed(2) + " MB";
    const maps = {
        totalUsers: {
            icon: "people",
            title: "Total Users",
            color: "bg-blue-500",
            converter,
        },
        totalMonthlyRegisters: {
            icon: "star",
            title: "Monthly Registers",
            color: "bg-red-500",
            converter,
        },
        totalWeeklyRegisters: {
            icon: "star",
            title: "Weekly Registers",
            color: "bg-orange-500",
            converter,
        },
        monthlyLoginsCount: {
            icon: "login",
            title: "Monthly Logins",
            color: "bg-yellow-500",
            converter,
        },
        totalWeeklyLogins: {
            icon: "login",
            title: "Weekly Logins",
            color: "bg-indigo-500",
            converter,
        },
        cloudSharesCount: {
            icon: "cloud",
            title: "Total Cloud Shares",
            color: "bg-green-500",
            converter,
        },
        weeklyCloudSharesCount: {
            icon: "cloud",
            title: "Weekly Cloud Shares",
            color: "bg-purple-500",
            converter,
        },
        totalCloudUsage: {
            icon: "storage",
            title: "Total Cloud Usage",
            color: "bg-pink-500",
            converter: converterToMb,
        },
        totalCloudUsageWeekly: {
            icon: "storage",
            title: "Weekly Cloud Usage",
            color: "bg-teal-500",
            converter: converterToMb,
        },
    };

    return Object.entries(items).map(([key, value]) => ({
        ...maps[key],
        stat: maps[key].converter(value),
    }));
};

/**
 * Render stats view
 */
const renderStatsView = (template, data) =>
    render(template, mapStatsResponse(data));

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
