<?php

use App\Support\DeploymentConfiguration;
use Illuminate\Config\Repository;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

// Testy czystej konfiguracji: bez uruchamiania Laravel, .env i połączenia z bazą.
final class ProductionConfigurationTest extends TestCase
{
    private function configuration(): Repository
    {
        $configuration = new Repository;
        $configuration->set([
            'app.env' => 'production',
            'deployment.environment' => 'production',
            'app.debug' => false,
            'app.url' => 'https://eattheball.pl',
            'app.locale' => 'pl',
            'app.timezone' => 'Europe/Warsaw',
            'app.maintenance.driver' => 'file',
            'app.key' => 'base64:'.base64_encode(str_repeat('a', 32)),
            'database.default' => 'mysql',
            'database.connections.mysql.url' => null,
            'database.connections.mysql.host' => 'localhost',
            'database.connections.mysql.database' => 'etb_production_fixture',
            'database.connections.mysql.username' => 'fixture_user',
            'database.connections.mysql.password' => 'fixture-password',
            'filesystems.media_disk' => 'public',
            'filesystems.default' => 'local',
            'mail.default' => 'smtp',
            'mail.mailers.smtp.url' => null,
            'mail.mailers.smtp.host' => 'smtp.eattheball.pl',
            'mail.mailers.smtp.username' => 'fixture_sender',
            'mail.mailers.smtp.password' => 'fixture-password',
            'mail.mailers.smtp.port' => 587,
            'mail.mailers.smtp.scheme' => null,
            'mail.mailers.smtp.require_tls' => true,
            'mail.mailers.smtp.timeout' => 20,
            'mail.from.address' => 'poczta@eattheball.pl',
            'queue.default' => 'database',
            'queue.connections.database.retry_after' => 90,
            'cache.default' => 'database',
            'cache.prefix' => 'etb_production_cache_',
            'session.driver' => 'database',
            'session.cookie' => 'etb_production_session',
            'session.encrypt' => true,
            'session.secure' => true,
            'session.http_only' => true,
            'session.same_site' => 'lax',
            'session.path' => '/',
            'session.domain' => null,
            'shop.legacy_enabled' => false,
        ]);

        return $configuration;
    }

    public function test_accepts_production_with_starttls(): void
    {
        self::assertTrue(DeploymentConfiguration::production($this->configuration()));
    }

    public function test_accepts_admin_only_without_smtp(): void
    {
        $configuration = $this->configuration();
        $configuration->set(['accounts.admin_only' => true, 'accounts.admin_login' => 'admin@etb.pl',
            'mail.default' => 'array', 'mail.mailers.smtp.host' => '', 'mail.mailers.smtp.password' => '']);
        self::assertTrue(DeploymentConfiguration::production($configuration));
        $configuration->set('mail.default', 'log');
        self::assertFalse(DeploymentConfiguration::production($configuration));
    }

    public function test_accepts_production_with_smtps(): void
    {
        $configuration = $this->configuration();
        $configuration->set(['mail.mailers.smtp.port' => 465, 'mail.mailers.smtp.scheme' => 'smtps',
            'mail.mailers.smtp.require_tls' => false]);

        self::assertTrue(DeploymentConfiguration::production($configuration));
    }

    #[DataProvider('unsafeConfiguration')]
    public function test_rejects_unsafe_configuration(string $key, mixed $value): void
    {
        $configuration = $this->configuration();
        $configuration->set($key, $value);

        self::assertFalse(DeploymentConfiguration::production($configuration));
    }

    public static function unsafeConfiguration(): array
    {
        return [
            'local environment' => ['app.env', 'local'],
            'staging deployment' => ['deployment.environment', 'staging'],
            'debug enabled' => ['app.debug', true],
            'staging URL' => ['app.url', 'https://test.eattheball.pl'],
            'plain HTTP URL' => ['app.url', 'http://eattheball.pl'],
            'wrong locale' => ['app.locale', 'en'],
            'shared maintenance' => ['app.maintenance.driver', 'cache'],
            'SQLite database' => ['database.default', 'sqlite'],
            'database URL override' => ['database.connections.mysql.url', 'mysql://fixture@localhost/other'],
            'database placeholder' => ['database.connections.mysql.password', 'PLACEHOLDER'],
            'public default storage' => ['filesystems.default', 'public'],
            'log mailer' => ['mail.default', 'log'],
            'missing SMTP host' => ['mail.mailers.smtp.host', ''],
            'mail URL override' => ['mail.mailers.smtp.url', 'smtp://fixture@localhost'],
            'plaintext SMTP' => ['mail.mailers.smtp.require_tls', false],
            'wrong SMTP scheme' => ['mail.mailers.smtp.scheme', 'smtps'],
            'wrong SMTP port' => ['mail.mailers.smtp.port', 25],
            'invalid sender' => ['mail.from.address', 'invalid-address'],
            'placeholder sender' => ['mail.from.address', 'poczta@example.com'],
            'missing timeout' => ['mail.mailers.smtp.timeout', 0],
            'long timeout' => ['mail.mailers.smtp.timeout', 60],
            'sync queue' => ['queue.default', 'sync'],
            'unsafe queue retry' => ['queue.connections.database.retry_after', 30],
            'temporary cache' => ['cache.default', 'array'],
            'staging cache prefix' => ['cache.prefix', 'etb_staging_cache_'],
            'file sessions' => ['session.driver', 'file'],
            'staging session cookie' => ['session.cookie', 'etb_staging_session'],
            'unencrypted sessions' => ['session.encrypt', false],
            'insecure cookie' => ['session.secure', false],
            'readable cookie' => ['session.http_only', false],
            'cross-site cookie' => ['session.same_site', 'none'],
            'shared cookie domain' => ['session.domain', '.eattheball.pl'],
            'legacy checkout' => ['shop.legacy_enabled', true],
            'missing application key' => ['app.key', null],
            'plain application key' => ['app.key', str_repeat('a', 32)],
            'short application key' => ['app.key', 'base64:'.base64_encode('short')],
            'invalid base64 key' => ['app.key', 'base64:%%%'],
        ];
    }

    #[DataProvider('emptyKeys')]
    public function test_allows_empty_key_only_before_first_deployment_without_require_key(?string $key): void
    {
        $configuration = $this->configuration();
        $configuration->set('app.key', $key);

        self::assertTrue(DeploymentConfiguration::production($configuration, firstDeployment: true, requireKey: false));
        self::assertFalse(DeploymentConfiguration::production($configuration, firstDeployment: true, requireKey: true));
        self::assertFalse(DeploymentConfiguration::production($configuration, firstDeployment: false, requireKey: false));
    }

    public static function emptyKeys(): array
    {
        return [[''], [null]];
    }

    public function test_first_deployment_preserves_an_existing_valid_key(): void
    {
        $configuration = $this->configuration();
        $originalKey = $configuration->get('app.key');

        self::assertTrue(DeploymentConfiguration::production($configuration, firstDeployment: true, requireKey: false));
        self::assertTrue(DeploymentConfiguration::production($configuration, firstDeployment: true, requireKey: true));
        self::assertSame($originalKey, $configuration->get('app.key'));
    }
}
