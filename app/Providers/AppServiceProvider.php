<?php

namespace App\Providers;

use App\Models\Diagram;
use App\View\Components\Layouts\CanvasPublic;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Sleep;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\Component;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

     /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::addNamespace('layouts', resource_path('views/components/layouts'));
        View::addNamespace('pages', resource_path('views/pages'));
        View::addNamespace('placeholders', resource_path('views/components/ui/placeholders'));

        /** @var \Illuminate\Foundation\Application $app */
        $app = $this->app;

        if ($app->isProduction()) {
            $this->configureCommands();
        }

        // Force HTTPS in production and staging (homolog) environments
        if (! $app->environment(['local', 'testing'])) {
            $this->configureUrls();
        }

        $this->configureTrustedProxies();
        $this->configureDates();
        $this->configureJsonResource();
        $this->configureModels();
        $this->configureVitePrefetching();
        $this->configureDefaultRulesForPassword();
        $this->configureUnitTests();
        $this->configureDiagramCaching();
        $this->AliasComponents();
    }

    /**
     * Configure the application's commands.
     */
    private function configureCommands(): void
    {
        DB::prohibitDestructiveCommands();
    }

    /**
     * Configure trusted proxies for Cloudflare/Traefik.
     */
    private function configureTrustedProxies(): void
    {
        Request::setTrustedProxies(
            ['*'],
            SymfonyRequest::HEADER_X_FORWARDED_FOR
                | SymfonyRequest::HEADER_X_FORWARDED_HOST
                | SymfonyRequest::HEADER_X_FORWARDED_PORT
                | SymfonyRequest::HEADER_X_FORWARDED_PROTO
        );
    }

    /**
     * Configure the application's dates.
     */
    private function configureDates(): void
    {
        Date::use(CarbonImmutable::class);
    }

    /**
     * Configure the application's JSON resource.
     */
    private function configureJsonResource(): void
    {
        JsonResource::withoutWrapping();
    }

    /**
     * Configure the application's models.
     */
    private function configureModels(): void
    {
        Model::unguard();
        Model::shouldBeStrict();
    }

    /**
     * Configure the application's URLs.
     */
    private function configureUrls(): void
    {
        URL::forceHttps();
    }

    /**
     * Configure the application's Vite prefetching.
     */
    private function configureVitePrefetching(): void
    {
        Vite::useAggressivePrefetching();
    }

    /**
     * Configure the application's default rules for password.
     */
    private function configureDefaultRulesForPassword(): void
    {
        Password::defaults(fn (): Password => Password::min(8)
            ->max(32)
            ->letters()
        );
    }

    /**
     * Configure the application's unit tests.
     */
    private function configureUnitTests(): void
    {
        if ($this->app->runningUnitTests()) {
            Http::preventStrayRequests();
            Sleep::fake();
        }
    }

    /**
     * Configure caching for Diagram route model binding.
     *
     * This caches the diagram lookups by slug to avoid repeated database queries
     * when Livewire components make multiple requests to the same diagram.
     */
    private function configureDiagramCaching(): void
    {
        Route::bind('diagram', function (string $value) {
            $cacheKey = "diagram:slug:{$value}";

            $cacheStore = $this->app->environment('local') ? 'array' : config('cache.default');
            $cacheDuration = $this->app->environment('local') ? 60 : 300;

            return Cache::store($cacheStore)->remember($cacheKey, $cacheDuration, function () use ($value) {
                return Diagram::where('slug', $value)->firstOrFail();
            });
        });
    }

    private function AliasComponents(): void
    {
        Blade::component(CanvasPublic::class, 'layouts.canvas.public');
    }
}
