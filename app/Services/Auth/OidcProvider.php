<?php

namespace App\Services\Auth;


use SocialiteProviders\Manager\OAuth2\AbstractProvider;
use SocialiteProviders\Manager\OAuth2\User;

class OidcProvider extends AbstractProvider
{
    protected $scopeSeparator = ' ';

    /**
     * Get the authentication URL for the provider.
     *
     * @param  string  $state
     * @return string
     */
    protected function getAuthUrl($state)
    {
        return $this->buildAuthUrlFromBase(
            $this->getInstanceUri() . '/apps/oidc/authorize',
            $state
        );
    }

    /**
     * Get the token URL for the provider.
     *
     * @return string
     */
    protected function getTokenUrl()
    {
        return $this->getInstanceUri() . '/apps/oidc/token';
    }

    /**
     * Get the raw user for the given access token.
     *
     * @param  string  $token
     * @return array
     */
    protected function getUserByToken($token)
    {
        $response = $this->getHttpClient()->get(
            $this->getInstanceUri() . '/apps/oidc/userinfo',
            [
                'headers' => [
                    'Authorization' => 'Bearer ' . $token,
                ],
            ]
        );

        return json_decode($response->getBody(), true);
    }

    /**
     * Map the raw user array to a Socialite User instance.
     *
     * @param  array  $user
     * @return \Laravel\Socialite\Two\User
     */
    protected function mapUserToObject(array $user)
    {
        return (new User())->setRaw($user)->map([
            'id'       => $user['sub'] ?? null,
            'nickname' => $user['preferred_username'] ?? null,
            'name'     => $user['name'] ?? null,
            'email'    => $user['email'] ?? null,
            'avatar'   => $user['picture'] ?? null,
        ]);
    }

    /**
     * Get the Nextcloud instance URI.
     *
     * @return string
     */
    protected function getInstanceUri()
    {
        return rtrim($this->getConfig('instance_uri'), '/');
    }

    /**
     * Get a config value.
     *
     * @param  string  $key
     * @param  mixed  $default
     * @return mixed
     */
    protected function getConfig($key = null, $default = null)
    {
        return $this->config[$key] ?? config("services.{$this->getProviderName()}.{$key}", $default);
    }

    /**
     * Get the provider name for config.
     *
     * @return string
     */
    protected function getProviderName()
    {
        // Will be overridden by the driver name when instantiated
        return 'oidc';
    }

    public static function additionalConfigKeys(): array
    {
        return ['instance_uri'];
    }
}
