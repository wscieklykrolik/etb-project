<?php

namespace App\Support;

use Illuminate\Contracts\Config\Repository;

class DeploymentConfiguration
{
    public static function production(Repository $configuration, bool $firstDeployment = false, bool $requireKey = true): bool
    {
        $valid = $configuration->get('app.env') === 'production'
            && $configuration->get('deployment.environment') === 'production'
            && $configuration->get('app.debug') === false
            && $configuration->get('app.url') === 'https://eattheball.pl'
            && $configuration->get('app.locale') === 'pl'
            && $configuration->get('app.timezone') === 'Europe/Warsaw'
            && $configuration->get('app.maintenance.driver') === 'file'
            && $configuration->get('database.default') === 'mysql'
            && $configuration->get('database.connections.mysql.url') === null
            && $configuration->get('filesystems.media_disk') === 'public'
            && $configuration->get('filesystems.default') === 'local'
            && $configuration->get('mail.default') === ($configuration->get('accounts.admin_only', false) ? 'array' : 'smtp')
            && $configuration->get('queue.default') === 'database'
            && $configuration->get('queue.connections.database.retry_after') >= 90
            && $configuration->get('cache.default') === 'database'
            && $configuration->get('cache.prefix') === 'etb_production_cache_'
            && $configuration->get('session.driver') === 'database'
            && $configuration->get('session.cookie') === 'etb_production_session'
            && $configuration->get('session.encrypt') === true
            && $configuration->get('session.secure') === true
            && $configuration->get('session.http_only') === true
            && $configuration->get('session.same_site') === 'lax'
            && $configuration->get('session.path') === '/'
            && $configuration->get('session.domain') === null
            && $configuration->get('shop.legacy_enabled') === false;

        $required = ['database.connections.mysql.host', 'database.connections.mysql.database',
            'database.connections.mysql.username', 'database.connections.mysql.password'];

        if (! $configuration->get('accounts.admin_only', false)) {
            $required = [...$required, 'mail.mailers.smtp.host', 'mail.mailers.smtp.username',
                'mail.mailers.smtp.password', 'mail.from.address'];
        } else {
            $valid = $valid && $configuration->get('accounts.admin_login') === 'admin@etb.pl';
        }

        foreach ($required as $name) {
            $value = $configuration->get($name);
            $valid = $valid && is_string($value) && $value !== ''
                && ! str_contains($value, 'PLACEHOLDER') && ! str_contains($value, 'example.com');
        }

        if (! $configuration->get('accounts.admin_only', false)) {
            $valid = $valid && filter_var($configuration->get('mail.from.address'), FILTER_VALIDATE_EMAIL) !== false
                && $configuration->get('mail.mailers.smtp.url') === null
                && (($configuration->get('mail.mailers.smtp.port') == 587 && $configuration->get('mail.mailers.smtp.require_tls') === true
                        && in_array($configuration->get('mail.mailers.smtp.scheme'), [null, 'smtp'], true))
                    || ($configuration->get('mail.mailers.smtp.port') == 465 && $configuration->get('mail.mailers.smtp.scheme') === 'smtps'))
                && $configuration->get('mail.mailers.smtp.timeout') > 0
                && $configuration->get('mail.mailers.smtp.timeout') <= 30;
        }

        $key = $configuration->get('app.key');
        $allowEmptyKey = $firstDeployment && ! $requireKey;
        $decodedKey = is_string($key) && str_starts_with($key, 'base64:')
            ? base64_decode(substr($key, 7), true) : false;
        $valid = $valid && (($allowEmptyKey && ($key === '' || $key === null))
            || (is_string($decodedKey) && strlen($decodedKey) === 32));

        return $valid;
    }
}
