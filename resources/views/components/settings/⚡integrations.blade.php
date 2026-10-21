<?php

use App\Livewire\BaseComponent;

new class extends BaseComponent {}; ?>

<section class="w-full">
    @include('_partials.settings-heading')

    <x-settings.layout :heading="__('Integrations')" :subheading="__('Connect CraftDB with your favorite AI tools via MCP')">
        @php
            $mcpUrl    = url('/mcp/craftdb');
            $mcpConfig = base64_encode(json_encode(['url' => $mcpUrl]));
            $deeplink  = 'cursor://anysphere.cursor-deeplink/mcp/install?name=craftdb&config=' . $mcpConfig;
        @endphp

        <div class="mb-6 space-y-1">
            <flux:text class="text-sm font-medium text-zinc-600 dark:text-zinc-400">{{ __('Local MCP URL') }}</flux:text>
            <pre class="rounded-lg bg-zinc-900 dark:bg-zinc-950 text-zinc-100 text-xs font-mono p-3 overflow-x-auto">{{ $mcpUrl }}</pre>
            <flux:text class="text-xs text-zinc-400 dark:text-zinc-500">{{ __('The MCP server runs locally and uses your local workspace, ready to connect.') }}</flux:text>
        </div>

        <div x-data="{ tab: 'cursor' }">

            {{-- Tab buttons --}}
            <div class="flex items-center gap-1 rounded-lg border border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800/50 p-1 w-fit mb-6">
                <button
                    @click="tab = 'cursor'"
                    :class="tab === 'cursor'
                        ? 'bg-white dark:bg-zinc-700 shadow-sm text-zinc-900 dark:text-zinc-100'
                        : 'text-zinc-500 dark:text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-200'"
                    class="inline-flex items-center gap-2 rounded-md px-3.5 py-1.5 text-sm font-medium transition-all"
                >
                    <flux:icon name="cursor-agente" variant="micro" class="text-zinc-900 dark:text-zinc-100" />
                    Cursor
                </button>

                <button
                    @click="tab = 'claude'"
                    :class="tab === 'claude'
                        ? 'bg-white dark:bg-zinc-700 shadow-sm text-zinc-900 dark:text-zinc-100'
                        : 'text-zinc-500 dark:text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-200'"
                    class="inline-flex items-center gap-2 rounded-md px-3.5 py-1.5 text-sm font-medium transition-all"
                >
                    <flux:icon name="claude-agente" variant="micro" />
                    {{ __('Claude') }}
                </button>

                <button
                    @click="tab = 'stdio'"
                    :class="tab === 'stdio'
                        ? 'bg-white dark:bg-zinc-700 shadow-sm text-zinc-900 dark:text-zinc-100'
                        : 'text-zinc-500 dark:text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-200'"
                    class="inline-flex items-center gap-2 rounded-md px-3.5 py-1.5 text-sm font-medium transition-all"
                >
                    {{ __('Terminal (stdio)') }}
                </button>
            </div>

            {{-- ── CURSOR TAB ── --}}
            <div x-show="tab === 'cursor'" x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" class="space-y-5">

                <div class="flex items-start gap-4">
                    <div class="shrink-0 size-10 rounded-lg bg-zinc-900 dark:bg-zinc-100 flex items-center justify-center shadow-sm">
                        <flux:icon name="cursor-agente" variant="solid" class="text-white dark:text-zinc-900" />
                    </div>
                    <div class="flex-1 min-w-0">
                        <flux:heading size="sm" class="font-semibold">{{ __('Cursor') }}</flux:heading>
                        <flux:text class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                            {{ __('Use CraftDB directly from Cursor via the Model Context Protocol (MCP). Create diagrams, add tables, define relationships and export schemas — all from the AI chat.') }}
                        </flux:text>
                    </div>
                </div>

                {{-- One-click install --}}
                <div class="flex flex-wrap items-center gap-3">
                    <a
                        href="{{ $deeplink }}"
                        class="inline-flex items-center gap-2 rounded-md bg-zinc-900 dark:bg-zinc-100 px-4 py-2 text-sm font-medium text-white dark:text-zinc-900 shadow-sm hover:bg-zinc-700 dark:hover:bg-zinc-200 transition-colors"
                    >
                        <flux:icon name="cursor-agente" variant="micro" class="text-white dark:text-zinc-900" />
                        {{ __('Add to Cursor') }}
                    </a>
                    <flux:text class="text-xs text-zinc-400 dark:text-zinc-500">
                        {{ __('Opens Cursor and installs the MCP server automatically.') }}
                    </flux:text>
                </div>

                <flux:separator />

                {{-- Manual config --}}
                <div class="space-y-2">
                    <flux:text class="text-sm font-medium text-zinc-600 dark:text-zinc-400">
                        {{ __('Or add manually to your') }} <code class="font-mono text-xs bg-zinc-100 dark:bg-zinc-800 px-1.5 py-0.5 rounded">.cursor/mcp.json</code>:
                    </flux:text>

                    <div x-data="{ copied: false }" class="relative">
                        <pre class="rounded-lg bg-zinc-900 dark:bg-zinc-950 text-zinc-100 text-xs font-mono p-4 overflow-x-auto leading-relaxed"><span class="text-zinc-500">{</span>
  <span class="text-sky-400">"mcpServers"</span><span class="text-zinc-500">: {</span>
    <span class="text-sky-400">"craftdb"</span><span class="text-zinc-500">: {</span>
      <span class="text-sky-400">"url"</span><span class="text-zinc-500">:</span> <span class="text-emerald-400">"{{ $mcpUrl }}"</span>
    <span class="text-zinc-500">}</span>
  <span class="text-zinc-500">}</span>
<span class="text-zinc-500">}</span></pre>
                        <button
                            x-on:click="navigator.clipboard.writeText(JSON.stringify({ mcpServers: { craftdb: { url: '{{ $mcpUrl }}' } } }, null, 2)); copied = true; setTimeout(() => copied = false, 2000)"
                            class="absolute top-2.5 right-2.5 p-1.5 rounded-md text-zinc-400 hover:text-zinc-200 hover:bg-zinc-700 transition-colors"
                            :title="copied ? '{{ __('Copied!') }}' : '{{ __('Copy') }}'"
                        >
                            <svg x-show="!copied" class="size-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.666 3.888A2.25 2.25 0 0 0 13.5 2.25h-3c-1.03 0-1.9.693-2.166 1.638m7.332 0c.055.194.084.4.084.612v0a.75.75 0 0 1-.75.75H9a.75.75 0 0 1-.75-.75v0c0-.212.03-.418.084-.612m7.332 0c.646.049 1.288.11 1.927.184 1.1.128 1.907 1.077 1.907 2.185V19.5a2.25 2.25 0 0 1-2.25 2.25H6.75A2.25 2.25 0 0 1 4.5 19.5V6.278c0-1.108.806-2.057 1.907-2.185a48.208 48.208 0 0 1 1.927-.184" /></svg>
                            <svg x-show="copied" class="size-4 text-emerald-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                        </button>
                    </div>

                    <flux:text class="text-xs text-zinc-400 dark:text-zinc-500">
                        {{ __('After adding, restart Cursor and the CraftDB tools will be available right away.') }}
                    </flux:text>
                </div>
            </div>

            {{-- ── CLAUDE TAB (Code CLI + Desktop) ── --}}
            <div x-show="tab === 'claude'" x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" class="space-y-5">

                <div class="flex items-start gap-4">
                    <div class="shrink-0 size-10 rounded-lg flex items-center justify-center dark:bg-zinc-100">
                        <flux:icon name="claude-agente" variant="solid" class="text-white" />
                    </div>
                    <div class="flex-1 min-w-0">
                        <flux:heading size="sm" class="font-semibold">{{ __('Claude') }}</flux:heading>
                        <flux:text class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                            {{ __('Connect CraftDB via MCP from Claude Code (CLI) or Claude Desktop.') }}
                        </flux:text>
                    </div>
                </div>

                {{-- Claude Code CLI --}}
                <div class="space-y-2">
                    <flux:heading size="sm" class="font-semibold text-zinc-800 dark:text-zinc-200">{{ __('Claude Code (CLI)') }}</flux:heading>
                    <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">
                        {{ __('Run this in your terminal (replace the URL if you use a custom port or domain):') }}
                    </flux:text>
                    @php
                        $claudeMcpAddCmd = 'claude mcp add --transport http craftdb '.$mcpUrl;
                    @endphp
                    <div x-data="{ copied: false }" class="relative">
                        <pre class="rounded-lg bg-zinc-900 dark:bg-zinc-950 text-zinc-100 text-xs font-mono p-4 overflow-x-auto leading-relaxed whitespace-pre-wrap break-all">{{ $claudeMcpAddCmd }}</pre>
                        <button
                            x-on:click="navigator.clipboard.writeText(@js($claudeMcpAddCmd)); copied = true; setTimeout(() => copied = false, 2000)"
                            class="absolute top-2.5 right-2.5 p-1.5 rounded-md text-zinc-400 hover:text-zinc-200 hover:bg-zinc-700 transition-colors"
                            :title="copied ? '{{ __('Copied!') }}' : '{{ __('Copy') }}'"
                        >
                            <svg x-show="!copied" class="size-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.666 3.888A2.25 2.25 0 0 0 13.5 2.25h-3c-1.03 0-1.9.693-2.166 1.638m7.332 0c.055.194.084.4.084.612v0a.75.75 0 0 1-.75.75H9a.75.75 0 0 1-.75-.75v0c0-.212.03-.418.084-.612m7.332 0c.646.049 1.288.11 1.927.184 1.1.128 1.907 1.077 1.907 2.185V19.5a2.25 2.25 0 0 1-2.25 2.25H6.75A2.25 2.25 0 0 1 4.5 19.5V6.278c0-1.108.806-2.057 1.907-2.185a48.208 48.208 0 0 1 1.927-.184" /></svg>
                            <svg x-show="copied" class="size-4 text-emerald-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                        </button>
                    </div>
                </div>

                <flux:separator />

                <flux:heading size="sm" class="font-semibold text-zinc-800 dark:text-zinc-200">{{ __('Claude Desktop (manual)') }}</flux:heading>

                {{-- Step 1 --}}
                <div class="space-y-3">
                    <div class="flex items-center gap-2">
                        <span class="inline-flex size-5 shrink-0 items-center justify-center rounded-full bg-primary/10 text-primary text-xs font-semibold">1</span>
                        <flux:text class="text-sm font-medium text-zinc-700 dark:text-zinc-300">
                            {{ __('Open the Claude Desktop config file for your OS:') }}
                        </flux:text>
                    </div>

                    <div x-data="{ os: 'mac' }" class="space-y-2">
                        <div class="flex gap-1">
                            <button @click="os = 'mac'" :class="os === 'mac' ? 'bg-zinc-200 dark:bg-zinc-700 text-zinc-900 dark:text-zinc-100' : 'text-zinc-500 hover:text-zinc-700 dark:hover:text-zinc-300'" class="rounded px-2.5 py-1 text-xs font-medium transition-colors">macOS</button>
                            <button @click="os = 'win'" :class="os === 'win' ? 'bg-zinc-200 dark:bg-zinc-700 text-zinc-900 dark:text-zinc-100' : 'text-zinc-500 hover:text-zinc-700 dark:hover:text-zinc-300'" class="rounded px-2.5 py-1 text-xs font-medium transition-colors">Windows</button>
                        </div>

                        <div x-show="os === 'mac'">
                            <div x-data="{ copied: false }" class="relative">
                                <pre class="rounded-lg bg-zinc-900 dark:bg-zinc-950 text-zinc-100 text-xs font-mono p-3 overflow-x-auto"><span class="text-zinc-400">~/Library/Application Support/Claude/</span><span class="text-emerald-400">claude_desktop_config.json</span></pre>
                                <button x-on:click="navigator.clipboard.writeText('~/Library/Application Support/Claude/claude_desktop_config.json'); copied = true; setTimeout(() => copied = false, 2000)" class="absolute top-2 right-2 p-1.5 rounded-md text-zinc-400 hover:text-zinc-200 hover:bg-zinc-700 transition-colors" :title="copied ? '{{ __('Copied!') }}' : '{{ __('Copy path') }}'">
                                    <svg x-show="!copied" class="size-3.5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.666 3.888A2.25 2.25 0 0 0 13.5 2.25h-3c-1.03 0-1.9.693-2.166 1.638m7.332 0c.055.194.084.4.084.612v0a.75.75 0 0 1-.75.75H9a.75.75 0 0 1-.75-.75v0c0-.212.03-.418.084-.612m7.332 0c.646.049 1.288.11 1.927.184 1.1.128 1.907 1.077 1.907 2.185V19.5a2.25 2.25 0 0 1-2.25 2.25H6.75A2.25 2.25 0 0 1 4.5 19.5V6.278c0-1.108.806-2.057 1.907-2.185a48.208 48.208 0 0 1 1.927-.184" /></svg>
                                    <svg x-show="copied" class="size-3.5 text-emerald-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                                </button>
                            </div>
                        </div>

                        <div x-show="os === 'win'">
                            <div x-data="{ copied: false }" class="relative">
                                <pre class="rounded-lg bg-zinc-900 dark:bg-zinc-950 text-zinc-100 text-xs font-mono p-3 overflow-x-auto"><span class="text-zinc-400">%APPDATA%\Claude\</span><span class="text-emerald-400">claude_desktop_config.json</span></pre>
                                <button x-on:click="navigator.clipboard.writeText('%APPDATA%\\Claude\\claude_desktop_config.json'); copied = true; setTimeout(() => copied = false, 2000)" class="absolute top-2 right-2 p-1.5 rounded-md text-zinc-400 hover:text-zinc-200 hover:bg-zinc-700 transition-colors" :title="copied ? '{{ __('Copied!') }}' : '{{ __('Copy path') }}'">
                                    <svg x-show="!copied" class="size-3.5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.666 3.888A2.25 2.25 0 0 0 13.5 2.25h-3c-1.03 0-1.9.693-2.166 1.638m7.332 0c.055.194.084.4.084.612v0a.75.75 0 0 1-.75.75H9a.75.75 0 0 1-.75-.75v0c0-.212.03-.418.084-.612m7.332 0c.646.049 1.288.11 1.927.184 1.1.128 1.907 1.077 1.907 2.185V19.5a2.25 2.25 0 0 1-2.25 2.25H6.75A2.25 2.25 0 0 1 4.5 19.5V6.278c0-1.108.806-2.057 1.907-2.185a48.208 48.208 0 0 1 1.927-.184" /></svg>
                                    <svg x-show="copied" class="size-3.5 text-emerald-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Step 2 --}}
                <div class="space-y-2">
                    <div class="flex items-center gap-2">
                        <span class="inline-flex size-5 shrink-0 items-center justify-center rounded-full bg-primary/10 text-primary text-xs font-semibold">2</span>
                        <flux:text class="text-sm font-medium text-zinc-700 dark:text-zinc-300">
                            {{ __('Add the CraftDB MCP server to the config:') }}
                        </flux:text>
                    </div>

                    <div x-data="{ copied: false }" class="relative">
                        <pre class="rounded-lg bg-zinc-900 dark:bg-zinc-950 text-zinc-100 text-xs font-mono p-4 overflow-x-auto leading-relaxed"><span class="text-zinc-500">{</span>
  <span class="text-sky-400">"mcpServers"</span><span class="text-zinc-500">: {</span>
    <span class="text-sky-400">"craftdb"</span><span class="text-zinc-500">: {</span>
      <span class="text-sky-400">"url"</span><span class="text-zinc-500">:</span> <span class="text-emerald-400">"{{ $mcpUrl }}"</span>
    <span class="text-zinc-500">}</span>
  <span class="text-zinc-500">}</span>
<span class="text-zinc-500">}</span></pre>
                        <button
                            x-on:click="navigator.clipboard.writeText(JSON.stringify({ mcpServers: { craftdb: { url: '{{ $mcpUrl }}' } } }, null, 2)); copied = true; setTimeout(() => copied = false, 2000)"
                            class="absolute top-2.5 right-2.5 p-1.5 rounded-md text-zinc-400 hover:text-zinc-200 hover:bg-zinc-700 transition-colors"
                            :title="copied ? '{{ __('Copied!') }}' : '{{ __('Copy') }}'"
                        >
                            <svg x-show="!copied" class="size-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.666 3.888A2.25 2.25 0 0 0 13.5 2.25h-3c-1.03 0-1.9.693-2.166 1.638m7.332 0c.055.194.084.4.084.612v0a.75.75 0 0 1-.75.75H9a.75.75 0 0 1-.75-.75v0c0-.212.03-.418.084-.612m7.332 0c.646.049 1.288.11 1.927.184 1.1.128 1.907 1.077 1.907 2.185V19.5a2.25 2.25 0 0 1-2.25 2.25H6.75A2.25 2.25 0 0 1 4.5 19.5V6.278c0-1.108.806-2.057 1.907-2.185a48.208 48.208 0 0 1 1.927-.184" /></svg>
                            <svg x-show="copied" class="size-4 text-emerald-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                        </button>
                    </div>
                </div>

                {{-- Step 3 --}}
                <div class="flex items-start gap-2">
                    <span class="inline-flex size-5 shrink-0 items-center justify-center rounded-full bg-primary/10 text-primary text-xs font-semibold mt-0.5">3</span>
                    <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">
                        {{ __('Restart Claude Desktop. On the next conversation, Claude will connect to CraftDB and the tools will be available right away.') }}
                    </flux:text>
                </div>

                <div class="rounded-lg border border-amber-200 dark:border-amber-800/50 bg-amber-50 dark:bg-amber-900/10 px-4 py-3 flex gap-3">
                    <svg class="size-4 text-amber-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z"/>
                    </svg>
                    <flux:text class="text-xs text-amber-700 dark:text-amber-400">
                        {{ __('Remote MCP support requires Claude Desktop version 0.10 or later. Make sure your app is up to date.') }}
                    </flux:text>
                </div>
            </div>

            {{-- ── STDIO TAB ── --}}
            @php
                $stdioCmd = 'docker compose exec -T app php artisan mcp:start craftdb';
                $stdioJson = json_encode([
                    'mcpServers' => ['craftdb' => [
                        'command' => 'docker',
                        'args' => ['compose', 'exec', '-T', 'app', 'php', 'artisan', 'mcp:start', 'craftdb'],
                    ]],
                ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
            @endphp
            <div x-show="tab === 'stdio'" x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" class="space-y-5">
                <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">
                    {{ __('For clients that launch the MCP server as a local process, run it inside the CraftDB container via stdio.') }}
                </flux:text>

                <div class="space-y-2">
                    <flux:text class="text-sm font-medium text-zinc-600 dark:text-zinc-400">{{ __('Command') }}</flux:text>
                    <pre class="rounded-lg bg-zinc-900 dark:bg-zinc-950 text-zinc-100 text-xs font-mono p-4 overflow-x-auto whitespace-pre-wrap break-all">{{ $stdioCmd }}</pre>
                </div>

                <div class="space-y-2">
                    <flux:text class="text-sm font-medium text-zinc-600 dark:text-zinc-400">{{ __('Example client config:') }}</flux:text>
                    <div x-data="{ copied: false }" class="relative">
                        <pre class="rounded-lg bg-zinc-900 dark:bg-zinc-950 text-zinc-100 text-xs font-mono p-4 overflow-x-auto leading-relaxed">{{ $stdioJson }}</pre>
                        <button
                            x-on:click="navigator.clipboard.writeText(@js($stdioJson)); copied = true; setTimeout(() => copied = false, 2000)"
                            class="absolute top-2.5 right-2.5 p-1.5 rounded-md text-zinc-400 hover:text-zinc-200 hover:bg-zinc-700 transition-colors"
                            :title="copied ? '{{ __('Copied!') }}' : '{{ __('Copy') }}'"
                        >
                            <svg x-show="!copied" class="size-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.666 3.888A2.25 2.25 0 0 0 13.5 2.25h-3c-1.03 0-1.9.693-2.166 1.638m7.332 0c.055.194.084.4.084.612v0a.75.75 0 0 1-.75.75H9a.75.75 0 0 1-.75-.75v0c0-.212.03-.418.084-.612m7.332 0c.646.049 1.288.11 1.927.184 1.1.128 1.907 1.077 1.907 2.185V19.5a2.25 2.25 0 0 1-2.25 2.25H6.75A2.25 2.25 0 0 1 4.5 19.5V6.278c0-1.108.806-2.057 1.907-2.185a48.208 48.208 0 0 1 1.927-.184" /></svg>
                            <svg x-show="copied" class="size-4 text-emerald-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                        </button>
                    </div>
                    <flux:text class="text-xs text-zinc-400 dark:text-zinc-500">
                        {{ __('Run the command from the CraftDB project folder (where docker-compose.yml lives), with the stack running.') }}
                    </flux:text>
                </div>
            </div>

        </div>
    </x-settings.layout>
</section>
