<?php

use App\Models\Owner;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Number;

if (! function_exists('currency')) {
    /**
     * Format a number as currency
     */
    function currency(float|int $amount, ?Owner $owner, ?string $currency = null, ?string $locale = null): string
    {
        return Number::currency(
            $amount,
            $currency ?? $owner->getCurrency(),
            $locale ?? $owner->getLocale(),
        );
    }
}

if (! function_exists('redirect_back_params')) {
    /**
     * Generate query parameters that encode the current page as a redirect-back destination.
     * Use in links that navigate away from a page the user should return to.
     *
     * Route parameters (e.g. {room} in /rooms/{room}) are encoded with a _rp_ prefix
     * to distinguish them from regular query parameters and avoid conflicts with form fields.
     *
     * Usage: route('reservations.edit', [$reservation] + redirect_back_params())
     */
    function redirect_back_params(): array
    {
        $params = ['redirect_route' => Route::currentRouteName()];

        foreach (request()->route()?->parameters() ?? [] as $key => $value) {
            $params["_rp_{$key}"] = $value instanceof Model ? $value->getRouteKey() : $value;
        }

        return $params + request()->query();
    }
}

if (! function_exists('redirect_back_query')) {
    /**
     * Propagate redirect-back parameters received from the current URL's query string.
     * Use in form actions to preserve the redirect destination through form submissions.
     *
     * Usage: route('reservations.update', [$reservation] + redirect_back_query())
     */
    function redirect_back_query(): array
    {
        if (! request()->query('redirect_route')) {
            return [];
        }

        return request()->query();
    }
}

if (! function_exists('redirect_back_url')) {
    /**
     * Generate the URL to redirect back to, or fall back to the given default route.
     * Use in Blade templates for cancel links and other direct navigation.
     *
     * Usage: <a href="{{ redirect_back_url('contacts.index') }}">Cancel</a>
     */
    function redirect_back_url(string $defaultRoute, array $defaultParams = []): string
    {
        $route = request()->query('redirect_route');
        $query = collect(request()->query())->except('redirect_route');

        if ($route && Route::has($route)) {
            $routeParams = $query->filter(fn ($v, $k) => str_starts_with($k, '_rp_'))
                ->mapWithKeys(fn ($v, $k) => [substr($k, 4) => $v])
                ->all();
            $queryParams = $query->reject(fn ($v, $k) => str_starts_with($k, '_rp_'))->all();

            return route($route, $routeParams + $queryParams);
        }

        return route($defaultRoute, $defaultParams);
    }
}
