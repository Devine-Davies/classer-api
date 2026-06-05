<div class="p-6 bg-white border border-gray-200 rounded-lg shadow-sm">
    <h2 class="text-2xl font-bold mb-4">Send Bulk Emails</h2>

    <!-- where server responses will render -->
    <div id="bulk-mail-feedback" class="mb-4"></div>

    <form id="bulk-mail-form" hx-post="{{ url('/') }}/api/admin/bulk-mails/queue" hx-target="#bulk-mail-feedback"
        hx-swap="innerHTML" hx-indicator="#bulk-mail-spinner" class="space-y-4">
        @csrf

        <div>
            <label for="template" class="block mb-2 text-sm font-medium">
                Email Template
            </label>
            <select id="template" name="template" required
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                <option value="" disabled selected>Select a template</option>
                @foreach ($mailTemplates as $key => $template)
                    <option value="{{ $key }}">{{ $template['label'] }}</option>
                @endforeach
            </select>
            <p class="mt-2 text-xs text-gray-500">
                The selected template controls which users are eligible for sending.
            </p>
        </div>

        <div>
            <label for="emails" class="block mb-2 text-sm font-medium">
                Email Addresses (separated by commas)
            </label>
            <textarea id="emails" name="emails" rows="4" required
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 dark:bg-off-white-600 dark:border-gray-500 dark:placeholder-gray-400"
                placeholder="user1@example.com, user2@example.com"></textarea>
            <p class="mt-2 text-xs text-gray-500">
                Tip: you can paste a list; commas and line breaks are both OK.
            </p>
        </div>

        <button type="submit"
            class="inline-flex justify-center items-center py-2 px-4 text-base font-medium text-center text-white bg-blue-600 border border-transparent rounded-md shadow-sm hover:bg-blue-700 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
            hx-disable="true">
            <span>Queue Emails</span>
            <svg id="bulk-mail-spinner" class="ml-2 h-5 w-5 animate-spin hidden hx-indicator" viewBox="0 0 24 24"
                fill="none">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"></circle>
                <path class="opacity-75" d="M4 12a8 8 0 018-8" stroke="currentColor"></path>
            </svg>
        </button>
    </form>

    <template id="bulk-mail-feedback-error-template">
        <p class="bulk-mail-error">{message}</p>
    </template>

    <template id="bulk-mail-feedback-success-template">
        <p class="bulk-mail-success">Queued {sentCount} emails using "{templateLabel}".</p>
    </template>

    <template id="bulk-mail-feedback-meta-template">
        <p class="bulk-mail-meta">Not found: {notFound}</p>
    </template>

    <template id="bulk-mail-feedback-ineligible-template">
        <p class="bulk-mail-meta">Not eligible for this template: {ineligible}</p>
    </template>
</div>
