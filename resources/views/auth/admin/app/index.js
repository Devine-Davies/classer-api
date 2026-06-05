import * as echarts from "echarts";
import {
    renderTemplate,
    dateTime,
} from "../../../../js/helpers";

const ADMIN_TOKEN_KEY = "classer_admin_token";
const ADMIN_TOKEN_COOKIE = "classer_admin_token";
let trendsChart = null;
let trendsDomain = "users";

const TREND_ENDPOINTS = {
    users: "users",
    subscriptions: "subscriptions",
    cloudShares: "cloudShares",
    logins: "logins",
};

const readCookie = (name) => {
    const target = `${name}=`;
    const values = document.cookie.split(";");

    for (const value of values) {
        const cookie = value.trim();
        if (cookie.startsWith(target)) {
            return decodeURIComponent(cookie.slice(target.length));
        }
    }

    return null;
};

const writeTokenCookie = (token) => {
    const maxAgeSeconds = 60 * 60 * 24 * 14;
    document.cookie = `${ADMIN_TOKEN_COOKIE}=${encodeURIComponent(token)}; path=/; max-age=${maxAgeSeconds}; samesite=lax`;
};

const clearTokenCookie = () => {
    document.cookie = `${ADMIN_TOKEN_COOKIE}=; path=/; max-age=0; samesite=lax`;
};

const getAdminToken = () => {
    const localToken = localStorage.getItem(ADMIN_TOKEN_KEY);
    if (localToken) {
        return localToken;
    }

    const cookieToken = readCookie(ADMIN_TOKEN_COOKIE);
    if (cookieToken) {
        localStorage.setItem(ADMIN_TOKEN_KEY, cookieToken);
        return cookieToken;
    }

    return null;
};

const clearAdminToken = () => {
    localStorage.removeItem(ADMIN_TOKEN_KEY);
    clearTokenCookie();
};

const handleUnauthorized = () => {
    clearAdminToken();
    window.location.assign(window.adminLoginUrl || `${window.pageUrl}/auth/admin/login`);
};

const fetchWithAdminAuth = async (path, init = {}) => {
    const token = getAdminToken();
    if (!token) {
        handleUnauthorized();
        return null;
    }

    const headers = {
        Accept: "application/json",
        "Content-Type": "application/json",
        Authorization: `Bearer ${token}`,
        ...(init.headers || {}),
    };

    const response = await fetch(`${window.pageUrl}${path}`, {
        ...init,
        headers,
    });

    if (response.status === 401) {
        handleUnauthorized();
        return null;
    }

    return response;
};

const getTrendControls = () => ({
    startDateInput: document.getElementById("trends-start-date"),
    endDateInput: document.getElementById("trends-end-date"),
    intervalSelect: document.getElementById("trends-interval"),
    applyButton: document.getElementById("trends-apply"),
    statusElement: document.getElementById("trends-status"),
    chartElement: document.getElementById("trends-chart"),
});

const setTrendStatus = (message, isError = false) => {
    const controls = getTrendControls();
    if (!controls.statusElement) {
        return;
    }

    controls.statusElement.textContent = message;
    controls.statusElement.classList.toggle("is-error", isError);
};

const setDefaultTrendDates = () => {
    const controls = getTrendControls();
    if (!controls.startDateInput || !controls.endDateInput) {
        return;
    }

    if (controls.startDateInput.value && controls.endDateInput.value) {
        return;
    }

    const now = new Date();
    const start = new Date(now);
    start.setDate(now.getDate() - 30);

    controls.startDateInput.value = toIsoDate(start);
    controls.endDateInput.value = toIsoDate(now);
};

const readTrendQuery = () => {
    const controls = getTrendControls();
    const query = new URLSearchParams();

    if (controls.startDateInput?.value) {
        query.set("startDate", controls.startDateInput.value);
    }

    if (controls.endDateInput?.value) {
        query.set("endDate", controls.endDateInput.value);
    }

    if (controls.intervalSelect?.value) {
        query.set("interval", controls.intervalSelect.value);
    }

    return query.toString();
};

const renderTrendChart = (payload) => {
    const controls = getTrendControls();
    if (!controls.chartElement) {
        return;
    }

    if (!trendsChart) {
        trendsChart = echarts.init(controls.chartElement);
    }

    const xAxis = payload?.series?.[0]?.points?.map((point) => point.x) || [];
    const series = (payload?.series || []).map((entry) => ({
        name: entry.label,
        type: "line",
        smooth: true,
        symbol: "none",
        areaStyle: { opacity: 0.08 },
        data: (entry.points || []).map((point) => point.y),
    }));

    trendsChart.setOption(
        {
            animationDuration: 350,
            color: ["#0d7f78", "#0ea5e9", "#f59e0b", "#ef4444"],
            tooltip: { trigger: "axis" },
            legend: {
                type: "scroll",
                top: 4,
            },
            grid: {
                left: 16,
                right: 18,
                top: 48,
                bottom: 18,
                containLabel: true,
            },
            xAxis: {
                type: "category",
                boundaryGap: false,
                data: xAxis,
            },
            yAxis: {
                type: "value",
                splitLine: { lineStyle: { color: "#e7edf2" } },
            },
            series,
        },
        true
    );
};

const loadTrendsDomain = async (domainKey) => {
    const endpoint = TREND_ENDPOINTS[domainKey];
    if (!endpoint) {
        return;
    }

    const query = readTrendQuery();
    setTrendStatus("Loading trend data...");

    const response = await fetchWithAdminAuth(`/api/admin/trends/${endpoint}?${query}`, {
        method: "GET",
    });

    if (!response) {
        return;
    }

    const payload = await response.json();
    if (!response.ok || payload.status === false) {
        const message = payload?.errors?.join(" ") || payload?.message || "Unable to load trend data.";
        setTrendStatus(message, true);
        return;
    }

    renderTrendChart(payload);
    const allValues = (payload?.series || []).flatMap((entry) =>
        (entry.points || []).map((point) => point.y || 0)
    );
    const hasData = allValues.some((value) => value > 0);
    const intervalLabel = payload?.meta?.interval || "daily";
    if (!hasData) {
        setTrendStatus(
            `No ${domainKey} events found for the selected range (${intervalLabel}).`
        );
        return;
    }

    setTrendStatus(`Showing ${domainKey} trend (${intervalLabel}).`);
};

const setActiveTrendDomain = (domainKey) => {
    trendsDomain = domainKey;
    document.querySelectorAll(".trend-domain").forEach((button) => {
        const isActive = button.dataset.trendDomain === domainKey;
        button.classList.toggle("is-active", isActive);
    });
};

const initTrendsSection = () => {
    const controls = getTrendControls();
    if (!controls.chartElement) {
        return;
    }

    setDefaultTrendDates();
    setActiveTrendDomain(trendsDomain);
    loadTrendsDomain(trendsDomain);

    controls.applyButton?.addEventListener("click", () => {
        loadTrendsDomain(trendsDomain);
    });

    controls.intervalSelect?.addEventListener("change", () => {
        loadTrendsDomain(trendsDomain);
    });

    document.querySelectorAll(".trend-domain").forEach((button) => {
        button.addEventListener("click", () => {
            const domain = button.dataset.trendDomain;
            if (!domain) {
                return;
            }

            setActiveTrendDomain(domain);
            loadTrendsDomain(domain);
        });
    });

    window.addEventListener("resize", () => {
        trendsChart?.resize();
    });
};

const mapStatsResponse = () => {
    const converter = (value) => value.toLocaleString();
    const converterToMb = (value) => (value / 1024 / 1024).toFixed(2) + " MB";

    return {
        total_users: {
            title: "Total Users",
            endpoint: "totalUsers",
            resultType: "value",
            dotClass: "dot-blue",
            converter,
        },
        total_monthly_registers: {
            title: "Monthly Registers",
            endpoint: "registers",
            rangePreset: "month",
            resultType: "value",
            dotClass: "dot-blue",
            converter,
        },
        total_weekly_registers: {
            title: "Weekly Registers",
            endpoint: "registers",
            rangePreset: "week",
            resultType: "value",
            dotClass: "dot-blue",
            converter,
        },
        total_monthly_logins: {
            title: "Monthly Logins",
            endpoint: "logins",
            rangePreset: "month",
            resultType: "value",
            dotClass: "dot-green",
            converter,
        },
        total_weekly_logins: {
            title: "Weekly Logins",
            endpoint: "logins",
            rangePreset: "week",
            resultType: "value",
            dotClass: "dot-green",
            converter,
        },
        cs_total: {
            title: "Cloud Share Total",
            endpoint: "cloudShares",
            resultType: "data",
            dataKey: "total",
            dotClass: "dot-amber",
            converter,
        },
        cs_size: {
            title: "Cloud Share Total Size",
            endpoint: "cloudShares",
            resultType: "data",
            dataKey: "size",
            dotClass: "dot-amber",
            converter: converterToMb,
        },
        cs_active_weekly_total: {
            title: "Cloud Share Weekly Active",
            endpoint: "cloudShares/active",
            rangePreset: "week",
            resultType: "data",
            dataKey: "total",
            dotClass: "dot-amber",
            converter,
        },
        cs_active_weekly_size: {
            title: "Cloud Share Weekly Active Size",
            endpoint: "cloudShares/active",
            rangePreset: "week",
            resultType: "data",
            dataKey: "size",
            dotClass: "dot-amber",
            converter: converterToMb,
        },
        cs_deleted_weekly_total: {
            title: "Cloud Share Weekly Deleted",
            endpoint: "cloudShares/deleted",
            rangePreset: "week",
            resultType: "data",
            dataKey: "total",
            dotClass: "dot-amber",
            converter,
        },
        cs_deleted_weekly_size: {
            title: "Cloud Share Weekly Deleted Size",
            endpoint: "cloudShares/deleted",
            rangePreset: "week",
            resultType: "data",
            dataKey: "size",
            dotClass: "dot-amber",
            converter: converterToMb,
        },
    };
};

const toIsoDate = (date) => date.toISOString().slice(0, 10);

const getDatePresetRange = (rangePreset) => {
    const now = new Date();

    if (rangePreset === "month") {
        return {
            startDate: toIsoDate(new Date(now.getFullYear(), now.getMonth(), 1)),
            endDate: toIsoDate(now),
        };
    }

    if (rangePreset === "week") {
        const dayIndex = now.getDay();
        const diff = dayIndex === 0 ? -6 : 1 - dayIndex;
        const start = new Date(now);
        start.setDate(now.getDate() + diff);

        return {
            startDate: toIsoDate(start),
            endDate: toIsoDate(now),
        };
    }

    return null;
};

const buildStatsQueryString = (rangePreset, globalStartDate, globalEndDate) => {
    const dateQuery = new URLSearchParams();

    if (globalStartDate || globalEndDate) {
        if (globalStartDate) {
            dateQuery.set("startDate", globalStartDate);
        }

        if (globalEndDate) {
            dateQuery.set("endDate", globalEndDate);
        }
    } else {
        const presetRange = getDatePresetRange(rangePreset);

        if (presetRange?.startDate) {
            dateQuery.set("startDate", presetRange.startDate);
        }

        if (presetRange?.endDate) {
            dateQuery.set("endDate", presetRange.endDate);
        }
    }

    return dateQuery.toString() ? `?${dateQuery.toString()}` : "";
};

const loadStatsSection = async () => {
    const statsContainer = document.getElementById("stats-container");
    if (!statsContainer || !document.getElementById("stats-template")) {
        return;
    }

    const map = mapStatsResponse();
    const queryParams = new URLSearchParams(window.location.search);
    const globalStartDate = queryParams.get("startDate");
    const globalEndDate = queryParams.get("endDate");

    const entries = Object.entries(map);
    const responses = await Promise.all(
        entries.map(([, config]) => {
            const querySuffix = buildStatsQueryString(
                config.rangePreset,
                globalStartDate,
                globalEndDate
            );

            return fetchWithAdminAuth(
                `/api/admin/stats/${config.endpoint}${querySuffix}`,
                {
                    method: "GET",
                }
            );
        })
    );

    if (responses.some((response) => !response)) {
        return;
    }

    const payloads = await Promise.all(responses.map((response) => response.json()));
    const data = {};

    entries.forEach(([key, config], index) => {
        if (config.resultType === "data") {
            data[key] = payloads[index]?.data?.[config.dataKey] ?? 0;
            return;
        }

        data[key] = payloads[index]?.value ?? 0;
    });

    const html = Object.entries(map)
        .map(([key, config]) => {
            return renderTemplate("stats-template", {
                title: config.title,
                dotClass: config.dotClass,
                stat: config.converter(data[key] || 0),
            });
        })
        .join("");

    statsContainer.innerHTML = html;
};

const toLevelClass = (type) => {
    switch ((type || "").toUpperCase()) {
        case "ERROR":
            return "is-error";
        case "WARNING":
            return "is-warning";
        case "INFO":
            return "is-info";
        default:
            return "is-debug";
    }
};

const loadLogsSection = async () => {
    const response = await fetchWithAdminAuth("/api/admin/logs/app.log", {
        method: "GET",
    });

    if (!response) {
        return;
    }

    const payload = await response.json();
    const logs = Array.isArray(payload) ? payload : [];

    const logsContainer = document.getElementById("logs-container");
    if (!document.getElementById("logs-template") || !logsContainer) {
        return;
    }

    const html = logs
        .reverse()
        .filter((line) => line.trim() !== "")
        .map((line) => {
            const match = line.match(/^\[(.*?)\]\s+(\w+)\.(\w+):\s+\[(.*?)\]\s+(.*)$/);
            if (!match) {
                return "";
            }

            const [, timestamp, , type, context, rawMessage] = match;
            const messageParts = rawMessage.split("|");
            const message = messageParts[0].trim();
            const details = messageParts.length > 1 ? messageParts.slice(1).join("|").trim() : "No extra payload";

            return renderTemplate("logs-template", {
                type,
                levelClass: toLevelClass(type),
                timestamp: dateTime(timestamp),
                context,
                message,
                data: details,
            });
        })
        .join("");

    logsContainer.innerHTML = html;
};

const updateBulkMailFeedback = (responseText) => {
    const target = document.getElementById("bulk-mail-feedback");
    if (!target) {
        return;
    }

    let payload = null;
    try {
        payload = JSON.parse(responseText);
    } catch (error) {
        target.innerHTML = renderTemplate("bulk-mail-feedback-error-template", {
            message: "Unable to parse response.",
        });
        return;
    }

    if (payload.status) {
        const sentCount = payload?.data?.total_sent ?? 0;
        const notFound = payload?.data?.not_found ?? [];
        const ineligible = payload?.data?.ineligible ?? [];
        const templateLabel = payload?.data?.template?.label || "Selected Template";

        const details = [
            notFound.length
                ? renderTemplate("bulk-mail-feedback-meta-template", {
                      notFound: notFound.join(", "),
                  })
                : "",
            ineligible.length
                ? renderTemplate("bulk-mail-feedback-ineligible-template", {
                      ineligible: ineligible.join(", "),
                  })
                : "",
        ].join("");

        target.innerHTML = `${renderTemplate("bulk-mail-feedback-success-template", {
            sentCount,
            templateLabel,
        })}${details}`;
        return;
    }

    const errorText = Array.isArray(payload.errors)
        ? payload.errors.join(" ")
        : payload.message || "Something went wrong.";
    target.innerHTML = renderTemplate("bulk-mail-feedback-error-template", {
        message: errorText,
    });
};

const initBulkMailSection = () => {
    const form = document.getElementById("bulk-mail-form");
    if (!form) {
        return;
    }

    const token = getAdminToken();
    if (!token) {
        handleUnauthorized();
        return;
    }

    writeTokenCookie(token);
    form.setAttribute(
        "hx-headers",
        JSON.stringify({
            Authorization: `Bearer ${token}`,
        })
    );

    document.body.addEventListener("htmx:beforeSwap", (evt) => {
        if (evt.target?.id !== "bulk-mail-feedback") {
            return;
        }

        evt.detail.shouldSwap = false;
        updateBulkMailFeedback(evt.detail.xhr.responseText);
    });

    document.body.addEventListener("htmx:responseError", (evt) => {
        if (evt.target?.id !== "bulk-mail-feedback") {
            return;
        }

        updateBulkMailFeedback(evt.detail.xhr.responseText);
    });
};

const initLogout = () => {
    const logoutButton = document.getElementById("admin-logout");
    if (!logoutButton) {
        return;
    }

    logoutButton.addEventListener("click", () => {
        clearAdminToken();
        window.location.assign(window.adminLoginUrl || `${window.pageUrl}/auth/admin/login`);
    });
};

const setSignedInUserLabel = async () => {
    const emailElement = document.getElementById("admin-user-email");
    if (!emailElement) {
        return;
    }

    const response = await fetchWithAdminAuth("/api/user", {
        method: "GET",
    });

    if (!response) {
        return;
    }

    const payload = await response.json();
    const email =
        payload?.email ||
        payload?.user?.email ||
        payload?.data?.email ||
        "Signed in";

    emailElement.textContent = email;
};

const refreshSections = (section) => {
    if (section === "stats") {
        loadStatsSection();
    }

    if (section === "logs") {
        loadLogsSection();
    }

    if (section === "trends") {
        loadTrendsDomain(trendsDomain);
    }
};

document.addEventListener("DOMContentLoaded", () => {
    const token = getAdminToken();
    if (!token) {
        handleUnauthorized();
        return;
    }

    writeTokenCookie(token);
    initLogout();
    setSignedInUserLabel();

    const section = document.querySelector("[data-admin-section]")?.dataset
        ?.adminSection;

    if (section === "stats") {
        loadStatsSection();
    }

    if (section === "bulk-mails") {
        initBulkMailSection();
    }

    if (section === "logs") {
        loadLogsSection();
    }

    if (section === "trends") {
        initTrendsSection();
    }

    window.setInterval(() => refreshSections(section), 900000);
});
