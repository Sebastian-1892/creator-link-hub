<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

class SettingsService
{
    /** Keys stored encrypted at rest (values passed through Crypt::encryptString). */
    public const ENCRYPTED_KEYS = [
        'mail.mailers.smtp.password',
        'cashier.secret',
        'cashier.webhook.secret',
    ];

    /** @var array<string, mixed>|null */
    protected ?array $valueCache = null;

    public function flushCache(): void
    {
        $this->valueCache = null;
    }

    /**
     * Plain value for a setting key (decrypted). Null if unset in DB.
     */
    public function getStored(string $key): ?string
    {
        $this->loadCache();

        return $this->valueCache[$key] ?? null;
    }

    /**
     * Whether a row exists for this key (even empty string).
     */
    public function hasStored(string $key): bool
    {
        return Setting::query()->where('key', $key)->exists();
    }

    /**
     * Persist value; empty string or null removes override (fall back to .env).
     *
     * @param  non-empty-string|null  $value
     */
    public function set(string $key, ?string $value, ?bool $encrypted = null): void
    {
        $encrypted ??= in_array($key, self::ENCRYPTED_KEYS, true);

        if ($value === null || $value === '') {
            $this->forget($key);

            return;
        }

        $storedValue = $encrypted ? Crypt::encryptString($value) : $value;

        Setting::query()->updateOrInsert(
            ['key' => $key],
            [
                'value' => $storedValue,
                'is_encrypted' => $encrypted,
                'updated_at' => now(),
            ]
        );

        $this->flushCache();
    }

    public function forget(string $key): void
    {
        Setting::query()->where('key', $key)->delete();
        $this->flushCache();
    }

    /**
     * Apply DB overrides to runtime config (call after config is loaded, typically from a provider).
     */
    public function applyRuntimeConfigOverrides(): void
    {
        $smtpHost = $this->getStored('mail.mailers.smtp.host');
        $smtpPassword = $this->getStored('mail.mailers.smtp.password');
        $smtpUsername = $this->getStored('mail.mailers.smtp.username');
        $smtpPortRaw = $this->getStored('mail.mailers.smtp.port');
        $smtpSchemeRaw = $this->getStored('mail.mailers.smtp.scheme');

        if ($smtpHost !== null && $smtpHost !== '') {
            config(['mail.mailers.smtp.host' => $smtpHost]);
        }

        $port = $this->normalizePort($smtpPortRaw);
        if ($port !== null) {
            config(['mail.mailers.smtp.port' => $port]);
        }

        $scheme = $this->normalizeScheme($smtpSchemeRaw);
        if ($scheme !== null && $scheme !== '') {
            config(['mail.mailers.smtp.scheme' => $scheme]);
        }

        if ($smtpUsername !== null && $smtpUsername !== '') {
            config(['mail.mailers.smtp.username' => $smtpUsername]);
        }

        if ($smtpPassword !== null && $smtpPassword !== '') {
            config(['mail.mailers.smtp.password' => $smtpPassword]);
        }

        if ($smtpHost !== null && $smtpHost !== '') {
            config(['mail.default' => 'smtp']);
        } else {
            $mailDefault = $this->getStored('mail.default');
            if ($mailDefault !== null && $mailDefault !== '') {
                config(['mail.default' => $mailDefault]);
            }
        }

        $fromAddress = $this->getStored('mail.from.address');
        if ($fromAddress !== null && $fromAddress !== '') {
            config(['mail.from.address' => $fromAddress]);
        }

        $fromName = $this->getStored('mail.from.name');
        if ($fromName !== null && $fromName !== '') {
            config(['mail.from.name' => $fromName]);
        }

        $pk = $this->getStored('cashier.key');
        if ($pk !== null && $pk !== '') {
            config(['cashier.key' => $pk]);
        }

        $secret = $this->getStored('cashier.secret');
        if ($secret !== null && $secret !== '') {
            config(['cashier.secret' => $secret]);
        }

        $whSecret = $this->getStored('cashier.webhook.secret');
        if ($whSecret !== null && $whSecret !== '') {
            config(['cashier.webhook.secret' => $whSecret]);
        }

        $stripePrices = config('creator.stripe_prices', []);
        foreach (['free', 'starter', 'pro'] as $planKey) {
            $pid = $this->getStored('creator.stripe_prices.'.$planKey);
            if ($pid !== null && $pid !== '') {
                $stripePrices[$planKey] = $pid;
            }
        }
        config(['creator.stripe_prices' => $stripePrices]);
    }

    protected function normalizePort(?string $port): ?int
    {
        if ($port === null || $port === '') {
            return null;
        }

        return ctype_digit($port) ? (int) $port : null;
    }

    protected function normalizeScheme(?string $scheme): ?string
    {
        if ($scheme === null || $scheme === '') {
            return null;
        }

        return $scheme;
    }

    protected function loadCache(): void
    {
        if ($this->valueCache !== null) {
            return;
        }

        $this->valueCache = [];

        if (! DB::getSchemaBuilder()->hasTable('settings')) {
            return;
        }

        foreach (Setting::query()->cursor() as $row) {
            $k = $row->key;
            $raw = $row->value;

            if ($raw === null || $raw === '') {
                $this->valueCache[$k] = '';

                continue;
            }

            try {
                $this->valueCache[$k] = $row->is_encrypted
                    ? Crypt::decryptString($raw)
                    : $raw;
            } catch (\Throwable) {
                $this->valueCache[$k] = '';
            }
        }
    }
}
