<?php

use Illuminate\Contracts\View\View;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Support the project')] class extends Component
{
    public function render(): View
    {
        $support = (array) config('craftdb.support', []);

        // So aceita http(s): evita esquemas como javascript: em href configurado.
        $url = static fn (string $key): ?string => is_string($support[$key] ?? null) && preg_match('#^https?://#i', $support[$key])
            ? $support[$key]
            : null;

        $candidates = [
            ['url' => $url('repo_url'), 'label' => __('Star on GitHub'), 'icon' => 'star', 'variant' => 'primary'],
            ['url' => $url('github_sponsors_url'), 'label' => __('GitHub Sponsors'), 'icon' => 'heart', 'variant' => 'outline'],
            ['url' => $url('kofi_url'), 'label' => __('Ko-fi'), 'icon' => 'gift', 'variant' => 'outline'],
            ['url' => $url('buymeacoffee_url'), 'label' => __('Buy Me a Coffee'), 'icon' => null, 'variant' => 'outline'],
        ];

        return $this->view([
            'links' => array_values(array_filter($candidates, fn (array $link): bool => $link['url'] !== null)),
            'pixKey' => $support['pix_key'] ?? null,
        ]);
    }
};
