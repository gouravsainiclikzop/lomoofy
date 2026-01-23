<?php

namespace App\Services;

use App\Models\Integration;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;

class EmailService
{
    protected $config;
    protected $isConfigured = false;

    public function __construct()
    {
        $this->loadConfiguration();
    }

    /**
     * Load email configuration from database
     */
    protected function loadConfiguration()
    {
        $integration = Integration::byType('email')->active()->first();

        if ($integration) {
            $this->config = $integration->configuration;
            $this->isConfigured = true;
            
            // Apply configuration to Laravel's mail config
            $this->applyMailConfiguration();
        }
    }

    /**
     * Check if email is configured and active
     */
    public function isConfigured(): bool
    {
        return $this->isConfigured && 
               !empty($this->config['smtp_host']) && 
               !empty($this->config['username']) && 
               !empty($this->config['password']);
    }

    /**
     * Apply mail configuration dynamically
     */
    public function applyMailConfiguration()
    {
        if (!$this->isConfigured) {
            return;
        }

        try {
            // Set default mailer to smtp
            Config::set('mail.default', 'smtp');

            // Configure SMTP mailer
            Config::set('mail.mailers.smtp', [
                'transport' => 'smtp',
                'host' => $this->config['smtp_host'],
                'port' => $this->config['smtp_port'] ?? 587,
                'encryption' => $this->config['encryption'] ?? 'tls',
                'username' => $this->config['username'],
                'password' => $this->config['password'],
                'timeout' => null,
                'local_domain' => env('MAIL_EHLO_DOMAIN'),
            ]);

            // Set from address
            Config::set('mail.from', [
                'address' => $this->config['from_email'] ?? 'noreply@example.com',
                'name' => $this->config['from_name'] ?? config('app.name'),
            ]);

            // Log::info('Email configuration loaded from database', [
            //     'smtp_host' => $this->config['smtp_host'],
            //     'smtp_port' => $this->config['smtp_port'] ?? 587,
            //     'from_email' => $this->config['from_email'],
            // ]);

        } catch (\Exception $e) {
            Log::error('Failed to apply email configuration', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Get configuration value
     */
    public function getConfig($key, $default = null)
    {
        return $this->config[$key] ?? $default;
    }

    /**
     * Get SMTP host
     */
    public function getSmtpHost(): ?string
    {
        return $this->config['smtp_host'] ?? null;
    }

    /**
     * Get SMTP port
     */
    public function getSmtpPort(): int
    {
        return $this->config['smtp_port'] ?? 587;
    }

    /**
     * Get encryption type
     */
    public function getEncryption(): ?string
    {
        return $this->config['encryption'] ?? 'tls';
    }

    /**
     * Get from email address
     */
    public function getFromEmail(): ?string
    {
        return $this->config['from_email'] ?? null;
    }

    /**
     * Get from name
     */
    public function getFromName(): ?string
    {
        return $this->config['from_name'] ?? config('app.name');
    }

    /**
     * Send a test email
     */
    public function sendTestEmail($toEmail, $subject = 'Test Email', $message = 'This is a test email.')
    {
        if (!$this->isConfigured()) {
            throw new \Exception('Email service is not configured. Please configure it in Settings → Integrations.');
        }

        try {
            \Mail::raw($message, function ($mail) use ($toEmail, $subject) {
                $mail->to($toEmail)
                     ->subject($subject);
            });

            Log::info('Test email sent successfully', [
                'to' => $toEmail,
                'subject' => $subject,
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send test email', [
                'to' => $toEmail,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Refresh configuration from database
     */
    public function refresh()
    {
        $this->loadConfiguration();
    }
}
