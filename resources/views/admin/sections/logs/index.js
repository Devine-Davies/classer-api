// window.config = {
//     logsData,
//     activeLogFile,
// endpointTemplate: '{{ route('admin.logs.show', ['filename' => '__filename__']) }}',
// };

(() => {
    const activeLogName = document.getElementById('active-log-name');
    const container = document.getElementById('logs-container');
    const empty = document.getElementById('logs-empty');
    const status = document.getElementById('logs-status');
    const template = document.getElementById('logs-template').innerHTML;
    const searchInput = document.getElementById('log-search');
    const refreshButton = document.getElementById('refresh-log');
    const fileButtons = [...document.querySelectorAll('[data-log-file]')];

    let currentFile = window.config.activeLogFile;
    let parsedRows = [];

    const endpointTemplate = window.config.endpointTemplate;

    const escapeHtml = (value) => String(value ?? '')
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');

    const levelClass = (level) => {
        switch (String(level).toLowerCase()) {
            case 'emergency':
            case 'alert':
            case 'critical':
            case 'error':
                return 'border-rose-200 bg-rose-50 text-rose-700';
            case 'warning':
                return 'border-amber-200 bg-amber-50 text-amber-700';
            case 'notice':
            case 'info':
                return 'border-sky-200 bg-sky-50 text-sky-700';
            case 'debug':
                return 'border-slate-200 bg-slate-50 text-slate-700';
            case 'trace':
                return 'border-violet-200 bg-violet-50 text-violet-700';
            default:
                return 'border-slate-200 bg-slate-50 text-slate-700';
        }
    };

    const parseLine = (line, index) => {
        const value = String(line ?? '');

        const laravelMatch = value.match(/^\[([^\]]+)]\s+([^\.]+)\.([A-Z]+):\s+(.*)$/);

        if (laravelMatch) {
            return {
                type: laravelMatch[3],
                timestamp: laravelMatch[1],
                context: laravelMatch[2],
                message: laravelMatch[4],
                data: value,
            };
        }

        if (value.startsWith('#')) {
            return {
                type: 'TRACE',
                timestamp: '—',
                context: 'stack',
                message: value,
                data: value,
            };
        }

        if (value.includes('[previous exception]')) {
            return {
                type: 'ERROR',
                timestamp: '—',
                context: 'exception',
                message: value,
                data: value,
            };
        }

        return {
            type: value.trim() === '' ? 'EMPTY' : 'LOG',
            timestamp: '—',
            context: 'raw',
            message: value.trim() === '' ? 'Blank line' : value,
            data: value,
        };
    };

    const renderRows = () => {
        const term = searchInput.value.trim().toLowerCase();

        const rows = parsedRows.filter((row) => {
            if (!term) return true;

            return row.search.includes(term);
        });

        container.innerHTML = rows.map((row) => template
            .replaceAll('{levelClass}', escapeHtml(levelClass(row.type)))
            .replaceAll('{type}', escapeHtml(row.type))
            .replaceAll('{timestamp}', escapeHtml(row.timestamp))
            .replaceAll('{context}', escapeHtml(row.context))
            .replaceAll('{message}', escapeHtml(row.message))
            .replaceAll('{data}', escapeHtml(row.data))
            .replaceAll('{search}', escapeHtml(row.search))
        ).join('');

        empty.classList.toggle('hidden', rows.length > 0);
    };

    const setActiveButton = (filename) => {
        fileButtons.forEach((button) => {
            const isActive = button.dataset.logFile === filename;
            button.classList.toggle('bg-slate-100', isActive);
            button.classList.toggle('ring-1', isActive);
            button.classList.toggle('ring-admin-stroke', isActive);
        });
    };

    const loadLog = async (filename) => {
        if (!filename) return;

        currentFile = filename;
        activeLogName.textContent = filename;
        setActiveButton(filename);
        status.textContent = 'Loading...';
        container.innerHTML = '';
        empty.classList.add('hidden');

        try {
            const response = await fetch(endpointTemplate.replace('__filename__', encodeURIComponent(filename)), {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'authorization': `Bearer ${window.config.apiToken}`,
                },
            });

            const payload = await response.json();


            console.log('Log file response:', payload);

            if (!response.ok) {
                throw new Error(payload.message || 'Unable to load log file.');
            }

            const lines = Array.isArray(payload.lines) ? payload.lines : [];

            parsedRows = lines.map((line, index) => {
                const parsed = parseLine(line, index);

                return {
                    ...parsed,
                    search: [parsed.type, parsed.timestamp, parsed.context, parsed.message, parsed.data]
                        .join(' ')
                        .toLowerCase(),
                };
            });

            renderRows();
            status.textContent = `${parsedRows.length} lines loaded`;
        } catch (error) {
            parsedRows = [];
            renderRows();
            status.textContent = 'Error';

            container.innerHTML = `
                        <div class="px-4 py-6">
                            <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                                ${escapeHtml(error.message)}
                            </div>
                        </div>
                    `;
        }
    };

    fileButtons.forEach((button) => {
        button.addEventListener('click', () => loadLog(button.dataset.logFile));
    });

    refreshButton.addEventListener('click', () => loadLog(currentFile));
    searchInput.addEventListener('input', renderRows);

    loadLog(currentFile);
})();