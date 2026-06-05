<!DOCTYPE html>
<html lang="en">

<head>
    <title>Classer Admin</title>
    <script>
        pageUrl = "{{ url('/') }}";
        adminLoginUrl = "{{ url('/auth/admin/login') }}";
    </script>

    @include('partials.shared.meta')
    @vite('resources/views/auth/admin/app/index.css')
    @vite('resources/views/auth/admin/app/index.js')
</head>

<body>
    @include('partials.shared.navigation')

    <main class="admin-root" style="height: calc(100vh - 64px);">
        @include('partials.shared.triangles')

        <section class="admin-shell">
            <aside class="admin-sidebar">
                <div class="admin-sidebar-top">
                    <div class="admin-user-pill" aria-live="polite">
                        <span class="admin-user-dot" aria-hidden="true"></span>
                        <span id="admin-user-email">Signed in</span>
                    </div>

                    <nav class="admin-nav" aria-label="Admin navigation">
                        <a href="{{ url('/auth/admin/stats') }}"
                            class="admin-nav-link {{ $activeSection === 'stats' ? 'is-active' : '' }}">Stats</a>
                        <a href="{{ url('/auth/admin/trends') }}"
                            class="admin-nav-link {{ $activeSection === 'trends' ? 'is-active' : '' }}">Trends</a>
                        <a href="{{ url('/auth/admin/bulk-mails') }}"
                            class="admin-nav-link {{ $activeSection === 'bulk-mails' ? 'is-active' : '' }}">Bulk Emails</a>
                        <a href="{{ url('/auth/admin/products') }}"
                            class="admin-nav-link {{ $activeSection === 'products' ? 'is-active' : '' }}">Products</a>
                        <a href="{{ url('/auth/admin/discount-codes') }}"
                            class="admin-nav-link {{ $activeSection === 'discount-codes' ? 'is-active' : '' }}">Discount Codes</a>
                        <a href="{{ url('/auth/admin/orders') }}"
                            class="admin-nav-link {{ $activeSection === 'orders' ? 'is-active' : '' }}">Orders</a>
                        <a href="{{ url('/auth/admin/logs') }}"
                            class="admin-nav-link {{ $activeSection === 'logs' ? 'is-active' : '' }}">Logs</a>
                    </nav>
                </div>

                <button id="admin-logout" class="admin-logout">Log out</button>
            </aside>

            <section class="admin-content" data-admin-section="{{ $activeSection }}">
                @yield('content')
            </section>
        </section>
    </main>
</body>

</html>
