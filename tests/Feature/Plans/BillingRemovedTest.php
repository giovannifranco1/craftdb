<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;

uses(RefreshDatabase::class);

test('FREE10 billing and cashier routes are gone', function (): void {
    foreach (['billing.index', 'billing.checkout', 'billing.portal', 'billing.success', 'cashier.webhook', 'cashier.payment'] as $name) {
        expect(Route::has($name))->toBeFalse();
    }

    expect($this->post('/stripe/webhook')->getStatusCode())->toBeIn([404, 405]);
});

test('FREE11 cashier is not installed', function (): void {
    expect(class_exists(\Laravel\Cashier\Cashier::class))->toBeFalse();
});

test('FREE12 FREE03 pages have no billing link nor plan badge', function (): void {
    foreach (['/dashboard', '/settings/profile'] as $uri) {
        $content = $this->get($uri)->assertOk()->getContent();

        expect($content)->not->toContain('settings/billing')
            ->and(preg_match('/>\s*(Free|Pro|Team)\s*</', $content))->toBe(0);
    }
});

test('FREE13 regional pricing classes are gone', function (): void {
    expect(class_exists(\App\Enums\BillingCountry::class))->toBeFalse()
        ->and(class_exists(\App\Http\Middleware\SetHeaders::class))->toBeFalse()
        ->and(class_exists(\App\Http\Controllers\BillingController::class))->toBeFalse();
});
