@extends('auth.admin.layout')

@php
    $activeSection = 'users';
@endphp

@section('content')
    <header class="mb-4">
        <h2 class="m-0 text-admin-ink text-xl font-bold">Cloud Share Details</h2>
        <p class="mt-[0.35rem] text-admin-muted">Inspect cloud share metadata and all related cloud entities.</p>
    </header>

    @php
        $thClass = 'text-left text-[0.74rem] uppercase tracking-[0.04em] text-[#647384] font-bold py-[0.72rem] px-[0.9rem] border-b border-[#e2eaf0]';
    @endphp

    <section class="border border-admin-stroke bg-white shadow-[0_10px_25px_rgba(21,38,51,0.06)] p-4">
        <div class="flex items-center justify-between gap-3 flex-wrap border-b border-[#e6edf3] pb-[0.8rem]">
            <div>
                <p class="m-0 text-slate-500 text-[0.74rem] tracking-[0.04em] uppercase font-bold">Cloud Share UID</p>
                <h3 id="cloud-share-uid" class="mt-1 text-[#1f2d39] text-base font-mono">{{ $cloudShareId }}</h3>
            </div>
            <a href="{{ url('/auth/admin/users') }}" class="border border-[#d9e4ec] rounded-[0.6rem] py-[0.45rem] px-[0.7rem] text-slate-700 no-underline text-[0.82rem] font-semibold bg-white hover:border-slate-400">Back to users</a>
        </div>

        <p id="cloud-share-detail-status" class="orders-detail-status">Loading cloud share details...</p>

        <div class="grid grid-cols-2 gap-[0.8rem] mt-[0.8rem]">
            <section class="border border-[#e5edf3] rounded-[0.75rem] bg-white p-[0.85rem] col-span-full">
                <h4 class="m-0 text-[#1f2d39] text-[0.9rem] font-bold">Cloud Share</h4>
                <dl id="cloud-share-summary" class="mt-[0.6rem] grid grid-cols-[130px_1fr] gap-y-[0.42rem] gap-x-[0.6rem]"></dl>
            </section>

            <section class="border border-[#e5edf3] rounded-[0.75rem] bg-white p-[0.85rem] col-span-full">
                <h4 class="m-0 text-[#1f2d39] text-[0.9rem] font-bold">Cloud Entities</h4>
                <div class="mt-[0.6rem] overflow-x-auto">
                    <table class="w-full border-collapse min-w-[680px]">
                        <thead>
                            <tr class="bg-[#eef3f7]">
                                <th class="{{ $thClass }}">Entity UID</th>
                                <th class="{{ $thClass }}">Type</th>
                                <th class="{{ $thClass }}">Status</th>
                                <th class="{{ $thClass }}">Size</th>
                                <th class="{{ $thClass }}">E-Tag</th>
                                <th class="{{ $thClass }}">Key</th>
                                <th class="{{ $thClass }}">Created</th>
                            </tr>
                        </thead>
                        <tbody id="cloud-entities-body">
                            <tr>
                                <td colspan="7" class="orders-empty">Loading...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </section>

    <template id="detail-row-template">
        <dt class="text-slate-500 text-[0.78rem] font-bold">{label}</dt>
        <dd class="m-0 text-[#243443] text-[0.84rem]">{value}</dd>
    </template>

    <template id="cloud-entity-row-template">
        <tr>
            <td class="py-[0.78rem] px-[0.9rem] text-[#2d3b47] border-b border-[#edf2f6] text-[0.88rem]"><span class="orders-code">{uid}</span></td>
            <td class="py-[0.78rem] px-[0.9rem] text-[#2d3b47] border-b border-[#edf2f6] text-[0.88rem]">{type}</td>
            <td class="py-[0.78rem] px-[0.9rem] text-[#2d3b47] border-b border-[#edf2f6] text-[0.88rem]"><span class="orders-status {statusClass}">{statusText}</span></td>
            <td class="py-[0.78rem] px-[0.9rem] text-[#2d3b47] border-b border-[#edf2f6] text-[0.88rem]">{size}</td>
            <td class="py-[0.78rem] px-[0.9rem] text-[#2d3b47] border-b border-[#edf2f6] text-[0.88rem]"><span class="orders-code">{eTag}</span></td>
            <td class="py-[0.78rem] px-[0.9rem] text-[#2d3b47] border-b border-[#edf2f6] text-[0.88rem]"><span class="orders-code">{key}</span></td>
            <td class="py-[0.78rem] px-[0.9rem] text-[#2d3b47] border-b border-[#edf2f6] text-[0.88rem]">{createdAt}</td>
        </tr>
    </template>

    <template id="empty-row-template">
        <tr>
            <td colspan="{columns}" class="orders-empty {className}">{message}</td>
        </tr>
    </template>

    <script>
        (() => {
            const token = localStorage.getItem('classer_admin_token');
            const cloudShareUid = @json($cloudShareId);

            const els = {
                status: document.getElementById('cloud-share-detail-status'),
                uid: document.getElementById('cloud-share-uid'),
                summary: document.getElementById('cloud-share-summary'),
                entitiesBody: document.getElementById('cloud-entities-body'),
            };

            let TemplateEngine;
            let dateTime;

            const initializeHelpers = () => {
                const helpers = window.ClasserHelpers || {};

                TemplateEngine = helpers.TemplateEngine || window.TemplateEngine;
                dateTime = helpers.dateTime;

                return (
                    !!TemplateEngine &&
                    typeof TemplateEngine.render === 'function' &&
                    typeof dateTime === 'function'
                );
            };

            const toFriendlyBytes = (value) => {
                if (value === null || value === undefined || value === '') {
                    return '-';
                }

                const bytes = Number(value);
                if (!Number.isFinite(bytes) || bytes < 0) {
                    return String(value);
                }

                if (bytes === 0) {
                    return '0 B';
                }

                const units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB'];
                const unitIndex = Math.min(Math.floor(Math.log(bytes) / Math.log(1024)), units.length - 1);
                const sized = bytes / (1024 ** unitIndex);
                const digits = sized >= 100 ? 0 : sized >= 10 ? 1 : 2;

                return `${sized.toFixed(digits)} ${units[unitIndex]}`;
            };

            const toEntityStatusClass = (hasETag) => {
                return hasETag ? 'is-has-etag' : 'is-missing-etag';
            };

            const toEntityStatusText = (hasETag) => {
                return hasETag ? 'Uploaded' : 'Missing E-Tag';
            };

            const renderDetailRows = (root, rows) => {
                root.innerHTML = rows
                    .map((row) => TemplateEngine.render('detail-row-template', row))
                    .join('');
            };

            const setStatus = (message, className = '') => {
                els.status.textContent = message;
                els.status.className = `orders-detail-status ${className}`.trim();
            };

            const renderEntities = (entities) => {
                const rows = Array.isArray(entities) ? entities : [];

                if (!rows.length) {
                    els.entitiesBody.innerHTML = TemplateEngine.render('empty-row-template', {
                        columns: 7,
                        className: '',
                        message: 'No cloud entities found for this share.',
                    });
                    return;
                }

                els.entitiesBody.innerHTML = rows.map((row) => {
                    const hasETag = !!String(row.e_tag || '').trim();

                    return TemplateEngine.render('cloud-entity-row-template', {
                        uid: row.uid || '-',
                        type: row.type || '-',
                        statusClass: toEntityStatusClass(hasETag),
                        statusText: toEntityStatusText(hasETag),
                        size: toFriendlyBytes(row.size),
                        eTag: row.e_tag || '-',
                        key: row.key || '-',
                        createdAt: dateTime(row.created_at),
                    });
                }).join('');
            };

            const renderPayload = (payload) => {
                const data = payload?.data || {};

                els.uid.textContent = data.uid || cloudShareUid;
                setStatus('');

                renderDetailRows(els.summary, [
                    { label: 'UID', value: data.uid || '-' },
                    { label: 'User ID', value: data.user_id || '-' },
                    { label: 'Resource ID', value: data.resource_id || '-' },
                    { label: 'Size', value: toFriendlyBytes(data.size) },
                    { label: 'Created', value: dateTime(data.created_at) },
                    { label: 'Updated', value: dateTime(data.updated_at) },
                    { label: 'Deleted', value: dateTime(data.deleted_at) },
                ]);

                renderEntities(data.cloud_entities || []);
            };

            const load = async () => {
                if (!initializeHelpers()) {
                    setStatus('Global frontend helpers are not available.', 'is-error');
                    return;
                }

                if (!token) {
                    setStatus('Missing admin token. Please login again.', 'is-error');
                    return;
                }

                setStatus('Loading cloud share details...');

                try {
                    const response = await fetch(`${window.pageUrl}/api/admin/users/cloud-share/${encodeURIComponent(cloudShareUid)}`, {
                        headers: {
                            Accept: 'application/json',
                            Authorization: `Bearer ${token}`,
                        },
                    });

                    if (!response.ok) {
                        throw new Error('Unable to load cloud share details.');
                    }

                    const payload = await response.json();
                    renderPayload(payload);
                } catch (error) {
                    setStatus(error.message || 'Unable to load cloud share details.', 'is-error');

                    els.entitiesBody.innerHTML = TemplateEngine.render('empty-row-template', {
                        columns: 7,
                        className: 'is-error',
                        message: 'Unable to load cloud entities.',
                    });
                }
            };

            window.addEventListener('load', load, {
                once: true,
            });
        })();
    </script>
@endsection
