<x-layouts.app :title="__('Contact Us - Feedback')">
    <div class="space-y-6">
        <div>
            <flux:heading size="xl">{{ __('Contact Us - Feedback') }}</flux:heading>
            <flux:text class="mt-1 text-zinc-600 dark:text-zinc-400">
                {{ __('Messages sent by users via the Contact Us form.') }}
            </flux:text>
        </div>

        <div class="space-y-4">
            @forelse($feedback as $item)
                <div class="rounded-lg border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-background-dark p-4">
                    <div class="flex items-center justify-between gap-2 mb-2">
                        <div class="flex items-center gap-2">
                            <span class="font-medium text-zinc-900 dark:text-zinc-100">{{ $item->user->name }}</span>
                            <span class="text-sm text-zinc-500 dark:text-zinc-400">{{ $item->user->email }}</span>
                        </div>
                        <flux:badge size="sm" variant="soft" color="zinc">
                            {{ $item->created_at->diffForHumans() }}
                        </flux:badge>
                    </div>
                    <p class="text-zinc-700 dark:text-zinc-300 whitespace-pre-wrap">{{ $item->message }}</p>
                </div>
            @empty
                <div class="rounded-lg border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-background-dark p-8 text-center">
                    <flux:text class="text-zinc-500 dark:text-zinc-400">
                        {{ __('No feedback submitted yet.') }}
                    </flux:text>
                </div>
            @endforelse
        </div>

        @if($feedback->hasPages())
            <div class="mt-6">
                {{ $feedback->links() }}
            </div>
        @endif
    </div>
</x-layouts.app>
