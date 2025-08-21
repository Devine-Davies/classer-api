class TabUI {
    constructor() {
        this.token = null;
        this.tab = "stats";
        this.handlers = {
            logs: () => requestLogs("app.log", this.token),
            stats: () => requestStats(this.token),
        };
    }

    switchTab(name) {
        this.tab = name;
        this.handlers[name]?.();
    }
}

const tabUI = new TabUI();
window.tabUI = () => tabUI;

let statsInterval = null;

document.addEventListener("DOMContentLoaded", () => {
    setupGrecaptcha();
    setupPasswordToggles();
});

function setupGrecaptcha() {
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
}

function setupPasswordToggles() {
    document.querySelectorAll(".eye-show-password").forEach((eyeButton) => {
        eyeButton.addEventListener("click", () => {
            const input = eyeButton.previousElementSibling;
            input.type = input.type === "password" ? "text" : "password";
        });
    });
}

document.addEventListener("htmx:beforeRequest", () => {
    document.querySelector(".error-message").classList.add("hidden");
    document
        .querySelector("input[type=submit]")
        .classList.add("pointer-events-none");
});

document.addEventListener("htmx:afterRequest", (evt) => {
    const res = JSON.parse(evt.detail.xhr.response);
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

        document.getElementById("form").classList.add("hidden");
        document.querySelector("[x-data]").classList.remove("hidden");

        const token = evt.detail.xhr.getResponseHeader("x-token");
        tabUI.token = token;
        tabUI.switchTab("stats");
        startAutoRefresh(token);
    }, 500);
});

function startAutoRefresh(token) {
    if (statsInterval) clearInterval(statsInterval);
    statsInterval = setInterval(() => {
        requestStats(token);
        requestLogs("app.log", token);
    }, 900000);
}

function requestStats(token) {
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

            const htmlItems = object.entries(converted).flatMap(
                ([key, items]) => {
                    return items.map((item) => {
                        return render(statsTemplate, {
                            ...item,
                            key: key,
                        });
                    });
                }
            );

            console.log("Stats data:", converted);
            console.log("Rendered HTML items:", htmlItems);

            // const htmlItems = converted.map((item) =>
            //     render(statsTemplate, item)
            // document.getElementById("form").classList.add("hidden");
            // statsContainer.innerHTML = htmlItems.join("");
        
            // const htmlItems = converted.map((item) =>
            //     render(statsTemplate, item)
            // );
            // document.getElementById("form").classList.add("hidden");
            // statsContainer.innerHTML = htmlItems.join("");
        });
    });
}

function requestLogs(filename, token) {
    fetch(pageUrl + "/api/admin/logs/" + filename, {
        method: "GET",
        headers: {
            Accept: "application/json",
            "Content-Type": "application/json",
            Authorization: "Bearer " + token,
        },
    }).then((response) => {
        response.json().then((data) => {
            const logsTemplate =
                document.getElementById("logs-template").innerHTML;
            const logsContainer = document.getElementById("logs-container");
            const htmlItems = (data ?? [])
                .reverse()
                .filter((line) => line.trim() !== "")
                .map((log) => {
                    const match = log.match(
                        /^\[(.*?)\]\s+(\w+)\.(\w+):\s+\[(.*?)\]\s+(.*)$/
                    );
                    if (!match) return "";

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
}

/**
 * Map stats response to structured format.
 * 
 * auth: Array<{ icon: string; title: string; color: string; stat: string }>;
 * login: Array<{ icon: string; title: string; color: string; stat: string }>;
 * cloudShares: Array<{ icon: string; title: string; color: string; stat: string }>;
 * @param {*} items 
 * @returns 
 */
function mapStatsResponse(items) {
    const converter = (value) => value.toLocaleString();
    const converterToMb = (value) => (value / 1024 / 1024).toFixed(2) + " MB";

    const registrationColor = "bg-blue-500";
    const loginColor = "bg-green-500";
    const csColor = "bg-yellow-500";

    const maps = {
        total_users: {
            icon: "people",
            title: "Total Users",
            color: registrationColor,
            converter,
        },
        total_monthly_registers: {
            icon: "star",
            title: "Monthly Registers",
            color: registrationColor,
            converter,
        },
        total_monthly_logins: {
            icon: "login",
            title: "Monthly Logins",
            color: loginColor,
            converter,
        },
        total_weekly_registers: {
            icon: "star",
            title: "Weekly Registers",
            color: registrationColor,
            converter,
        },
        total_weekly_logins: {
            icon: "login",
            title: "Weekly Logins",
            color: loginColor,
            converter,
        },
        cs_active_weekly_total: {
            icon: "storage",
            title: "CS - Weekly Active",
            color: csColor,
            converter,
        },
        cs_active_weekly_size: {
            icon: "storage",
            title: "CS - Weekly Size Active",
            color: csColor,
            converter: converterToMb,
        },
        cs_deleted_weekly_total: {
            icon: "storage",
            title: "CS - Weekly Deleted",
            color: csColor,
            converter,
        },
        cs_deleted_weekly_size: {
            icon: "storage",
            title: "CS - Weekly Size Deleted",
            color: csColor,
            converter: converterToMb,
        },
        cs_total: {
            icon: "cloud",
            title: "CS - Total Active/Deleted",
            color: csColor,
            converter,
        },
        cs_size: {
            icon: "cloud",
            title: "CS - Total Size Active/Deleted",
            color: csColor,
            converter: converterToMb,
        },
    };

    const groups = {
        registrationKeys: [
            "total_users",
            "total_monthly_registers",
            "total_weekly_registers",
        ],
        loginKeys: ["total_monthly_logins", "total_weekly_logins"],
        csKeys: [
            "cs_total",
            "cs_size",
            "cs_active_weekly_total",
            "cs_active_weekly_size",
            "cs_deleted_weekly_total",
            "cs_deleted_weekly_size",
        ],
    };

    const mappedStats = Object.entries(items).map(([key, value]) => ({
        ...maps[key],
        stat: maps[key].converter(value),
    }));

    return {
        auth: mappedStats.filter((item) =>
            groups.registrationKeys.includes(item.icon)
        ),
        login: mappedStats.filter((item) =>
            groups.loginKeys.includes(item.icon)
        ),
        cloudShares: mappedStats.filter((item) =>
            groups.csKeys.includes(item.icon)
        ),
    };
}

/**
 * Render a template string with data.
 * 
 * @param {*} template 
 * @param {*} data 
 * @returns 
 */
function render(template, data) {
    return template.replace(/\${(.*?)}/g, (_, p1) => data[p1.trim()]);
}
