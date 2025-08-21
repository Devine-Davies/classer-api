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

/**
 * Start auto-refreshing stats and logs every 15 minutes.
 * @param {*} token
 */
let statsInterval = null;
const startAutoRefresh = (token) => {
    if (statsInterval) clearInterval(statsInterval);
    statsInterval = setInterval(() => {
        requestStats(token);
        requestLogs("app.log", token);
    }, 900000);
};

/**
 * Request stats from the server.
 * @param {*} token
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
            const sections = Object.entries(converted).map(
                ([sectionKey, items]) => {
                    return items
                        .map((item) =>
                            render(statsTemplate, {
                                ...item,
                                stat: item.converter(data.data[item.key] || 0),
                            })
                        )
                        .join("");
                }
            );

            statsContainer.innerHTML = sections.join("");
        });
    });
};

/**
 * Request logs from the server.
 * @param {*} filename
 * @param {*} token
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

                    const [, timestamp, env, type, context, rawMessage] = match;
                    const iconMap = {
                        INFO: "⚪️",
                        WARNING: "🟡",
                        ERROR: "🔴",
                        DEBUG: "🟤",
                        default: "🟤",
                    };
                    const icon = iconMap[type.toUpperCase()] || iconMap.default;

                    // split raw message by first instance of | if exists and use the second part as data
                    const messageParts = rawMessage.split("|");
                    const message = messageParts[0].trim();
                    const data =
                        messageParts.length > 1
                            ? messageParts.slice(1).join("|").trim()
                            : null;

                    const formattedDate = (date) =>
                        new Date(date).toLocaleString();

                    return render(logsTemplate, {
                        timestamp: formattedDate(timestamp),
                        context,
                        message,
                        data,
                        icon,
                    });
                });

            logsContainer.innerHTML = htmlItems.join("");
            Alpine.initTree(logsContainer);
        });
    });
};

/**
 * Map stats response to structured format.
 * auth: Array<{ icon: string; title: string; color: string; stat: string }>;
 * login: Array<{ icon: string; title: string; color: string; stat: string }>;
 * cloudShares: Array<{ icon: string; title: string; color: string; stat: string }>;
 *
 * @param {*} items
 * @returns
 */
const mapStatsResponse = (items) => {
    console.log("Mapping stats response:", items);
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

    return groupMapData(maps, groups);
};

/**
 * Group map data based on provided groups.
 *
 * @param {*} maps
 * @param {*} groups
 * @returns
 */
const groupMapData = (maps, groups) => {
    const result = {};
    for (const [groupName, keys] of Object.entries(groups)) {
        result[groupName] = keys.reduce((acc, key) => {
            maps[key] && acc.push({ key, ...maps[key] });
            return acc;
        }, []);
    }

    return result;
};

/**
 * Render a template string with data.
 *
 * @param {*} template
 * @param {*} data
 * @returns
 */
const render = (template, data) =>
    template.replace(/\${(.*?)}/g, (_, p1) => data[p1.trim()]);
