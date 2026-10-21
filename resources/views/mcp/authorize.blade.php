<!DOCTYPE html>
@php
    $appearance = ($user->is_dark ?? false) ? 'dark' : 'light';
@endphp
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" @class(['dark' => $appearance === 'dark'])>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    {{-- Inline script to apply appearance immediately --}}
    <script>
        (function() {
            const appearance = '{{ $appearance }}';
            if (appearance === 'dark') document.documentElement.classList.add('dark');
            else document.documentElement.classList.remove('dark');
        })();
    </script>

    <style>
        html {
            background-color: oklch(1 0 0);
        }

        html.dark {
            background-color: oklch(0.145 0 0);
        }

        /* Custom checkbox so the checkmark is always white (accent-color is browser-controlled). */
        .team-checkbox {
            -webkit-appearance: none;
            appearance: none;
            display: grid;
            place-content: center;
        }

        .team-checkbox::after {
            content: "";
            width: 0.35rem;
            height: 0.7rem;
            border-right: 2px solid transparent;
            border-bottom: 2px solid transparent;
            transform: rotate(45deg) translateY(-0.05rem);
        }

        .team-checkbox:checked::after {
            border-color: #fff;
        }
    </style>

    <title>Authorize Application - {{ config('app.name', 'MCP Server') }}</title>

    <link rel="icon" type="image/png" href="/favicon-96x96.png" sizes="96x96" />
    <link rel="icon" type="image/svg+xml" href="/favicon.svg" />
    <link rel="shortcut icon" href="/favicon.ico" />
    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png" />
    <meta name="apple-mobile-web-app-title" content="Authorize MCP" />
    <link rel="manifest" href="/site.webmanifest" />

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css'])
</head>
<body class="min-h-screen font-sans antialiased bg-zinc-50 text-zinc-900 dark:bg-zinc-950 dark:text-zinc-50">

@php
    $teams = $user->allTeams()->values();
    $multipleTeams = $teams->count() > 1;
@endphp

<div class="min-h-screen flex items-center justify-center p-4 sm:p-6">
    <div class="w-full max-w-lg">
        <!-- Card Container -->
        <div class="rounded-xl border border-zinc-200 bg-white text-zinc-900 shadow-sm dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-50">
            <!-- Header -->
            <div class="flex flex-col space-y-1.5 p-6">
                <div class="flex items-center justify-center mb-4">
                    <!-- Shield Icon -->
                    <svg class="h-12 w-12  dark:text-zinc-400" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.618 5.984A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.031 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                    </svg>
                </div>

                <h3 class="text-2xl font-semibold leading-none tracking-tight text-center">
                    Authorize {{ $client->name }}
                </h3>

                <p class="text-sm text-zinc-500 dark:text-zinc-400 text-center">
                    This application will be able to use the CraftDB MCP server.
                </p>
            </div>

            <!-- Content -->
            <div class="p-6 pt-0 space-y-4">
                <!-- User Info -->
                <div class="rounded-lg border border-zinc-200 bg-zinc-50 p-4 dark:border-zinc-600 dark:bg-zinc-800/60">
                    <p class="text-sm text-zinc-500 dark:text-zinc-400 mb-2">Logged in as:</p>
                    <p class="font-medium">{{ $user->email }}</p>
                </div>

                <!-- Permissions -->
                <div class="space-y-2">
                    <p class="text-sm font-medium">Permissions:</p>
                    <ul class="space-y-1.5">
                        <li class="flex items-start gap-2">
                            <div class="rounded-full bg-teal-500/15 p-1 mt-0.5 shrink-0 dark:bg-teal-400/15">
                                <div class="h-1.5 w-1.5 rounded-full bg-teal-600 dark:bg-teal-400"></div>
                            </div>
                            <span class="text-sm text-zinc-600 dark:text-zinc-400">Create, edit and delete diagrams</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <div class="rounded-full bg-teal-500/15 p-1 mt-0.5 shrink-0 dark:bg-teal-400/15">
                                <div class="h-1.5 w-1.5 rounded-full bg-teal-600 dark:bg-teal-400"></div>
                            </div>
                            <span class="text-sm text-zinc-600 dark:text-zinc-400">Add and update tables, columns and relationships</span>
                        </li>
                    </ul>
                </div>

                <!-- Workspace (Team) Access -->
                <div class="space-y-2">
                    <div class="flex items-center justify-between">
                        <p class="text-sm font-medium">Workspace access:</p>
                        @if($multipleTeams)
                            <button type="button" id="toggleAllBtn" class="text-xs text-sky-700 cursor-pointer hover:underline dark:text-sky-400" onclick="toggleAll()">
                                Deselect all
                            </button>
                        @endif
                    </div>

                    @if($teams->isEmpty())
                        <p class="text-sm text-zinc-500 dark:text-zinc-400">No workspaces found.</p>
                    @else
                        <div
                            class="max-h-64 overflow-y-auto rounded-lg border border-zinc-200 bg-zinc-50/80 dark:border-zinc-600 dark:bg-zinc-950/40"
                            id="teamList"
                        >
                            <ul class="divide-y divide-zinc-200 dark:divide-zinc-600">
                                @foreach($teams as $team)
                                    <li>
                                        <label
                                            class="flex cursor-pointer items-start gap-3 px-4 py-3.5 transition-colors hover:bg-white dark:hover:bg-zinc-800/90 sm:items-center sm:py-3"
                                            for="team_{{ $team->id }}"
                                        >
                                            <input
                                                type="checkbox"
                                                form="authorizeForm"
                                                id="team_{{ $team->id }}"
                                                name="mcp_teams[]"
                                                value="{{ $team->id }}"
                                                checked
                                                class="team-checkbox mt-0.5 h-4 w-4 shrink-0 rounded border border-zinc-300 bg-white checked:border-sky-500 checked:bg-sky-600 dark:border-zinc-500 dark:bg-zinc-950 dark:checked:border-sky-500 dark:checked:bg-sky-600 sm:mt-0"
                                                onchange="onTeamChange()"
                                            />
                                            <div class="min-w-0 flex-1">
                                                <p class="text-sm font-medium leading-snug text-zinc-900 dark:text-zinc-100">{{ $team->name }}</p>
                                                <p class="mt-0.5 text-xs text-zinc-500 dark:text-zinc-400">
                                                    @if($user->ownsTeam($team))
                                                        Owner
                                                    @else
                                                        Member
                                                    @endif
                                                </p>
                                            </div>
                                        </label>
                                    </li>
                                @endforeach
                            </ul>
                        </div>

                        <p class="text-xs text-zinc-500 dark:text-zinc-400" id="selectionHint">
                            The application will only access the selected workspaces.
                        </p>
                    @endif
                </div>
            </div>

            <!-- Footer With Buttons -->
            <div class="flex items-center p-6 pt-0 gap-3">
                <!-- Deny Form -->
                <form method="POST" action="{{ route('passport.authorizations.deny') }}" class="flex-1">
                    @csrf
                    @method('DELETE')
                    <input type="hidden" name="state" value="">
                    <input type="hidden" name="client_id" value="{{ $client->id }}">
                    <input type="hidden" name="auth_token" value="{{ $authToken }}">
                    <button type="submit" class="inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-zinc-400 focus-visible:ring-offset-2 focus-visible:ring-offset-zinc-50 disabled:pointer-events-none disabled:opacity-50 border border-zinc-300 bg-white text-zinc-900 hover:bg-zinc-100 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100 dark:hover:bg-zinc-700 dark:focus-visible:ring-offset-zinc-950 h-10 px-4 py-2 w-full">
                        <svg class="mr-2 h-4 w-4" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                        Cancel
                    </button>
                </form>

                <!-- Approve Form (posts to our custom controller) -->
                <form method="POST" action="{{ route('mcp.authorize.approve') }}" class="flex-1" id="authorizeForm">
                    @csrf
                    <input type="hidden" name="state" value="">
                    <input type="hidden" name="client_id" value="{{ $client->id }}">
                    <input type="hidden" name="auth_token" value="{{ $authToken }}">
                    {{-- Workspace checkboxes use form="authorizeForm" so they stay in the list layout but submit with this form --}}
                    <button type="submit" id="authorizeButton" class="inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2 focus-visible:ring-offset-white disabled:pointer-events-none disabled:opacity-50 bg-zinc-900 text-white hover:bg-zinc-800 dark:bg-white dark:text-zinc-900 dark:hover:bg-zinc-100 dark:focus-visible:ring-offset-zinc-900 h-10 px-4 py-2 w-full">
                        <span id="authorizeText">Authorize</span>

                        <svg id="loadingSpinner" class="animate-spin ml-2 h-4 w-4 hidden text-white dark:text-zinc-900" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form   = document.getElementById('authorizeForm');
        const button = document.getElementById('authorizeButton');

        if (! form) {
            return;
        }

        // Submit handler — loading state + close on redirect
        form.addEventListener('submit', function() {
            button.disabled = true;
            document.getElementById('authorizeText').textContent = 'Authorizing...';
            document.getElementById('loadingSpinner').classList.remove('hidden');

            setTimeout(function() {
                const checkRedirect = setInterval(function() {
                    if (!window.location.href.includes('/oauth/authorize') ||
                        window.location.search.includes('code=') ||
                        window.location.search.includes('error=')) {
                        clearInterval(checkRedirect);
                        window.close();
                    }
                }, 100);

                setTimeout(function() {
                    clearInterval(checkRedirect);
                    window.close();
                }, 5000);
            }, 200);
        });

        // Cancel — close window after deny
        const cancelForm = document.querySelector('form[method="POST"]:has(input[name="_method"])');
        if (cancelForm) {
            cancelForm.addEventListener('submit', function() {
                setTimeout(() => window.close(), 200);
            });
        }

        onTeamChange();
    });

    let allSelected = true;

    function onTeamChange() {
        const checkboxes = document.querySelectorAll('.team-checkbox');
        const checked    = document.querySelectorAll('.team-checkbox:checked');
        const btn        = document.getElementById('authorizeButton');
        const hint       = document.getElementById('selectionHint');

        allSelected = checked.length === checkboxes.length;

        const toggleBtn = document.getElementById('toggleAllBtn');
        if (toggleBtn) {
            toggleBtn.textContent = allSelected ? 'Deselect all' : 'Select all';
        }

        if (checked.length === 0) {
            btn.disabled = true;
            if (hint) hint.textContent = 'Select at least one workspace to authorize.';
        } else {
            btn.disabled = false;
            if (hint) hint.textContent = checked.length === checkboxes.length
                ? 'The application will access all your workspaces.'
                : `The application will only access the ${checked.length} selected workspace(s).`;
        }
    }

    function toggleAll() {
        const checkboxes = document.querySelectorAll('.team-checkbox');
        const newState   = !allSelected;
        checkboxes.forEach(cb => cb.checked = newState);
        onTeamChange();
    }
</script>
</body>
</html>
