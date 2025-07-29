<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Filament\Support\Facades\FilamentView;
use Filament\View\PanelsRenderHook;

class PWAServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $this->registerPWAViews();
        $this->registerFilamentHooks();
    }

    private function registerPWAViews(): void
    {
        $this->loadViewsFrom(__DIR__ . '/../../resources/views/pwa', 'pwa');
    }

    private function registerFilamentHooks(): void
    {
        // Inject PWA meta tags to head
        FilamentView::registerRenderHook(
            PanelsRenderHook::HEAD_END,
            fn (): string => view('pwa::meta-tags')->render()
        );

        // Inject PWA install script to body
        FilamentView::registerRenderHook(
            PanelsRenderHook::BODY_END,
            fn (): string => view('pwa::install-script')->render()
        );
    }
}