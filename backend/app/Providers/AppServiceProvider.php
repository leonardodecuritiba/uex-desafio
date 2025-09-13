<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Domain\Auth\Repositories\UserRepository;
use App\Infrastructure\Persistence\EloquentUserRepository;
use App\Domain\Contacts\Repositories\ContactRepository;
use App\Infrastructure\Persistence\EloquentContactRepository;
use App\Domain\Geo\Geocoder;
use App\Infrastructure\Geo\GoogleGeocoder;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(UserRepository::class, EloquentUserRepository::class);
        $this->app->bind(ContactRepository::class, EloquentContactRepository::class);
        $this->app->bind(Geocoder::class, function () {
            return new GoogleGeocoder(
                apiKey: (string) env('GOOGLE_MAPS_API_KEY', ''),
                timeoutMs: (int) config('geo.timeout_ms'),
                retries: (int) config('geo.retries'),
            );
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
