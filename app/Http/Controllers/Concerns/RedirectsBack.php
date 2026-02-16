<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Http\RedirectResponse;

trait RedirectsBack
{
    /**
     * Redirect to the route encoded in redirect_route query parameter,
     * or fall back to the given default route.
     *
     * Uses redirect_back_url() helper which handles _rp_ prefixed route
     * parameters and regular query parameters.
     */
    protected function redirectBack(string $defaultRoute, array $defaultParams = []): RedirectResponse
    {
        return redirect(redirect_back_url($defaultRoute, $defaultParams));
    }
}
