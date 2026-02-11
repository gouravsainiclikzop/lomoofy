@extends('layouts.admin')

@section('title', 'Integrations')

@push('styles')
<style>
    .integration-card {
        border: 1px solid #e0e0e0;
        border-radius: 8px;
        transition: all 0.3s ease;
        height: 100%;
    }
    
    .integration-card:hover {
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        transform: translateY(-2px);
    }
    
    .integration-header {
      background: #3d464d;
        /* background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); */
        color: white;
        padding: 1.5rem;
        border-radius: 8px 8px 0 0;
    }
    
    .integration-header h5 {
        margin: 0;
        font-size: 1.25rem;
        font-weight: 600;
    }
    
    .integration-header p {
        margin: 0.5rem 0 0 0;
        font-size: 0.875rem;
        opacity: 0.9;
    }
    
    .integration-body {
        padding: 1.5rem;
    }
    
    .form-group {
        margin-bottom: 1rem;
    }
    
    .form-label {
        font-weight: 500;
        color: #495057;
        margin-bottom: 0.5rem;
        font-size: 0.875rem;
    }
    
    .form-control, .form-select {
        border: 1px solid #ced4da;
        border-radius: 4px;
        padding: 0.5rem 0.75rem;
        font-size: 0.875rem;
    }
    
    .form-control:focus, .form-select:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
    }
    
    .status-toggle {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0.75rem;
        background: #f8f9fa;
        border-radius: 4px;
        margin-bottom: 1rem;
    }
    
    .status-toggle label {
        margin: 0;
        font-weight: 500;
    }
    
    .form-check-input:checked {
        background-color: #28a745;
        border-color: #28a745;
    }
    
    .btn-save-integration {
        width: 100%;
        background: #3d464d;
        /* background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); */
        border: none;
        color: white;
        padding: 0.75rem;
        border-radius: 4px;
        font-weight: 600;
        transition: all 0.3s ease;
    }
    
    .btn-save-integration:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 8px rgba(102, 126, 234, 0.3);
        color: white;
    }
    
    .integration-icon {
        width: 40px;
        height: 40px;
        background: rgba(255,255,255,0.2);
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        margin-right: 1rem;
    }
    
    .integration-status-badge {
        position: absolute;
        top: 1.5rem;
        right: 1.5rem;
        padding: 0.25rem 0.75rem;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
    }
    
    .status-enabled {
        background: #d4edda;
        color: #155724;
    }
    
    .status-disabled {
        background: #f8d7da;
        color: #721c24;
    }
    
    .password-field-wrapper {
        position: relative;
    }
    
    .password-toggle {
        position: absolute;
        right: 10px;
        top: 50%;
        transform: translateY(-50%);
        cursor: pointer;
        color: #6c757d;
    }
    
    .alert-integration {
        padding: 0.75rem;
        border-radius: 4px;
        margin-bottom: 1rem;
        font-size: 0.875rem;
    }
</style>
@endpush

@section('content')
<div class="page-header d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1">Integrations</h4>
        <p class="text-muted mb-0">Manage third-party service configurations</p>
    </div>
</div>

<div class="row g-4">
    <!-- Email (SMTP) Integration -->
    <div class="col-md-6 col-lg-4">
        <div class="integration-card">
            <div class="integration-header">
                <div class="d-flex align-items-center">
                    <div class="integration-icon">
                        <i class="fas fa-envelope fa-lg"></i>
                    </div>
                    <div>
                        <h5>Email (SMTP)</h5>
                        <p>SMTP configuration for email delivery</p>
                    </div>
                </div>  
                @if(isset($integrations['email']))
                <span class="integration-status-badge {{ $integrations['email']->status ? 'status-enabled' : 'status-disabled' }}">
                    {{ $integrations['email']->status ? 'Enabled' : 'Disabled' }}
                </span>
                @endif
            </div>
            <div class="integration-body">
                <form class="integration-form" data-integration-type="email" data-integration-id="{{ $integrations['email']->id ?? '' }}">
                    <input type="hidden" name="integration_type" value="email">
                    <input type="hidden" name="integration_id" value="{{ $integrations['email']->id ?? '' }}">
                    
                    <div class="form-group">
                        <label class="form-label">Mail Driver</label>
                        <select class="form-select" name="mail_driver" {{ !auth()->user()->hasPermission('integrations.update') ? 'disabled' : '' }} required>
                            <option value="smtp" {{ (isset($integrations['email']) && $integrations['email']->configuration['mail_driver'] ?? '' == 'smtp') ? 'selected' : '' }}>SMTP</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">SMTP Host <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="smtp_host" 
                               value="{{ $integrations['email']->configuration['smtp_host'] ?? '' }}" 
                               placeholder="smtp.example.com" {{ !auth()->user()->hasPermission('integrations.update') ? 'disabled' : '' }} required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">SMTP Port <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" name="smtp_port" 
                               value="{{ $integrations['email']->configuration['smtp_port'] ?? '' }}" 
                               placeholder="587" {{ !auth()->user()->hasPermission('integrations.update') ? 'disabled' : '' }} required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Encryption</label>
                        <select class="form-select" name="encryption" {{ !auth()->user()->hasPermission('integrations.update') ? 'disabled' : '' }}>
                            <option value="tls" {{ (isset($integrations['email']) && ($integrations['email']->configuration['encryption'] ?? 'tls') == 'tls') ? 'selected' : '' }}>TLS</option>
                            <option value="ssl" {{ (isset($integrations['email']) && ($integrations['email']->configuration['encryption'] ?? '') == 'ssl') ? 'selected' : '' }}>SSL</option>
                            <option value="none" {{ (isset($integrations['email']) && ($integrations['email']->configuration['encryption'] ?? '') == 'none') ? 'selected' : '' }}>None</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Username / Email <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="username" 
                               value="{{ $integrations['email']->configuration['username'] ?? '' }}" 
                               placeholder="user@example.com" {{ !auth()->user()->hasPermission('integrations.update') ? 'disabled' : '' }} required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Password <span class="text-danger">*</span></label>
                        <div class="password-field-wrapper">
                            <input type="password" class="form-control password-field" name="password" 
                                   value="{{ isset($integrations['email']) ? ($integrations['email']->getMaskedConfiguration()['password'] ?? '') : '' }}" 
                                   placeholder="Enter password" {{ !auth()->user()->hasPermission('integrations.update') ? 'disabled' : '' }} required>
                            <i class="fas fa-eye password-toggle" data-toggle="password"></i>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">From Email Address <span class="text-danger">*</span></label>
                        <input type="email" class="form-control" name="from_email" 
                               value="{{ $integrations['email']->configuration['from_email'] ?? '' }}" 
                               placeholder="noreply@example.com" {{ !auth()->user()->hasPermission('integrations.update') ? 'disabled' : '' }} required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">From Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="from_name" 
                               value="{{ $integrations['email']->configuration['from_name'] ?? '' }}" 
                               placeholder="Your Company Name" {{ !auth()->user()->hasPermission('integrations.update') ? 'disabled' : '' }} required>
                    </div>
                    
                    <div class="status-toggle">
                        <label>Status</label>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="status" 
                                   {{ (isset($integrations['email']) && $integrations['email']->status) ? 'checked' : '' }}
                                   {{ !auth()->user()->hasPermission('integrations.update') ? 'disabled' : '' }}>
                            <label class="form-check-label ms-2">
                                <span class="status-text">{{ (isset($integrations['email']) && $integrations['email']->status) ? 'Enabled' : 'Disabled' }}</span>
                            </label>
                        </div>
                    </div>
                    
                    @if(auth()->user()->hasPermission('integrations.update'))
                        <button type="submit" class="btn btn-save-integration">
                            <i class="fas fa-save me-2"></i>Save Configuration
                        </button>
                    @endif
                </form>
                
                @if(isset($integrations['email']) && $integrations['email']->status && auth()->user()->hasPermission('integrations.update'))
                    <div class="mt-3 pt-3 border-top">
                        <button type="button" class="btn btn-sm btn-outline-primary w-100" id="testEmailBtn" data-integration-id="{{ $integrations['email']->id }}">
                            <i class="fas fa-paper-plane me-2"></i>Send Test Email
                        </button>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Razorpay Payment Gateway -->
    <div class="col-md-6 col-lg-4">
        <div class="integration-card">
            <div class="integration-header">
                <div class="d-flex align-items-center">
                    <div class="integration-icon">
                        <i class="fas fa-credit-card fa-lg"></i>
                    </div>
                    <div>
                        <h5>Razorpay Payment</h5>
                        <p>Payment gateway credentials</p>
                    </div>
                </div>
                @if(isset($integrations['razorpay']))
                <span class="integration-status-badge {{ $integrations['razorpay']->status ? 'status-enabled' : 'status-disabled' }}">
                    {{ $integrations['razorpay']->status ? 'Enabled' : 'Disabled' }}
                </span>
                @endif
            </div>
            <div class="integration-body">
                <form class="integration-form" data-integration-type="payment" data-integration-id="{{ $integrations['razorpay']->id ?? '' }}">
                    <input type="hidden" name="integration_type" value="payment">
                    <input type="hidden" name="provider" value="razorpay">
                    <input type="hidden" name="integration_id" value="{{ $integrations['razorpay']->id ?? '' }}">
                    
                    <div class="form-group">
                        <label class="form-label">Key ID <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="key_id" 
                               value="{{ $integrations['razorpay']->configuration['key_id'] ?? '' }}" 
                               placeholder="rzp_test_xxxxx" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Key Secret <span class="text-danger">*</span></label>
                        <div class="password-field-wrapper">
                            <input type="password" class="form-control password-field" name="key_secret" 
                                   value="{{ isset($integrations['razorpay']) ? ($integrations['razorpay']->getMaskedConfiguration()['key_secret'] ?? '') : '' }}" 
                                   placeholder="Enter key secret" required>
                            <i class="fas fa-eye password-toggle" data-toggle="password"></i>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Webhook Secret <span class="text-danger">*</span></label>
                        <div class="password-field-wrapper">
                            <input type="password" class="form-control password-field" name="webhook_secret" 
                                   value="{{ isset($integrations['razorpay']) ? ($integrations['razorpay']->getMaskedConfiguration()['webhook_secret'] ?? '') : '' }}" 
                                   placeholder="Enter webhook secret" required>
                            <i class="fas fa-eye password-toggle" data-toggle="password"></i>
                        </div>
                        <small class="text-muted d-block mt-1">
                            <i class="fas fa-info-circle"></i> Webhook URL: <code>{{ url('/webhook/razorpay') }}</code>
                            <button type="button" class="btn btn-sm btn-link p-0 ms-1" onclick="navigator.clipboard.writeText('{{ url('/webhook/razorpay') }}')">
                                <i class="fas fa-copy"></i> Copy
                            </button>
                        </small>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Currency</label>
                        <select class="form-select" name="currency">
                            <option value="INR" {{ (isset($integrations['razorpay']) && ($integrations['razorpay']->configuration['currency'] ?? 'INR') == 'INR') ? 'selected' : '' }}>INR</option>
                            <!-- <option value="USD" {{ (isset($integrations['razorpay']) && ($integrations['razorpay']->configuration['currency'] ?? '') == 'USD') ? 'selected' : '' }}>USD</option>
                            <option value="EUR" {{ (isset($integrations['razorpay']) && ($integrations['razorpay']->configuration['currency'] ?? '') == 'EUR') ? 'selected' : '' }}>EUR</option> -->
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Mode</label>
                        <div class="d-flex gap-3">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="mode" value="test" 
                                       {{ (!isset($integrations['razorpay']) || ($integrations['razorpay']->configuration['mode'] ?? 'test') == 'test') ? 'checked' : '' }}>
                                <label class="form-check-label">Test</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="mode" value="live" 
                                       {{ (isset($integrations['razorpay']) && ($integrations['razorpay']->configuration['mode'] ?? '') == 'live') ? 'checked' : '' }}>
                                <label class="form-check-label">Live</label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="status-toggle">
                        <label>Status</label>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="status" 
                                   {{ (isset($integrations['razorpay']) && $integrations['razorpay']->status) ? 'checked' : '' }}>
                            <label class="form-check-label ms-2">
                                <span class="status-text">{{ (isset($integrations['razorpay']) && $integrations['razorpay']->status) ? 'Enabled' : 'Disabled' }}</span>
                            </label>
                        </div>
                    </div>
                    
                    @if(auth()->user()->hasPermission('integrations.update'))
                        <button type="submit" class="btn btn-save-integration">
                            <i class="fas fa-save me-2"></i>Save Configuration
                        </button>
                    @endif
                </form>
            </div>
        </div>
    </div>

    <!-- OTP Service -->
    <div class="col-md-6 col-lg-4">
        <div class="integration-card">
            <div class="integration-header">
                <div class="d-flex align-items-center">
                    <div class="integration-icon">
                        <i class="fas fa-sms fa-lg"></i>
                    </div>
                    <div>
                        <h5>OTP Service</h5>
                        <p>SMS OTP provider configuration</p>
                    </div>
                </div>
                @if(isset($integrations['otp']))
                <span class="integration-status-badge {{ $integrations['otp']->status ? 'status-enabled' : 'status-disabled' }}">
                    {{ $integrations['otp']->status ? 'Enabled' : 'Disabled' }}
                </span>
                @endif
            </div>
            <div class="integration-body">
                <form class="integration-form" data-integration-type="otp" data-integration-id="{{ $integrations['otp']->id ?? '' }}">
                    <input type="hidden" name="integration_type" value="otp">
                    <input type="hidden" name="integration_id" value="{{ $integrations['otp']->id ?? '' }}">
                    
                    <div class="form-group">
                        <label class="form-label">Provider Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="provider_name" 
                               value="{{ $integrations['otp']->configuration['provider_name'] ?? '' }}" 
                               placeholder="e.g., MSG91, Twilio" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">API Key <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="api_key" 
                               value="{{ $integrations['otp']->configuration['api_key'] ?? '' }}" 
                               placeholder="Enter API key" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">API Secret <span class="text-danger">*</span></label>
                        <div class="password-field-wrapper">
                            <input type="password" class="form-control password-field" name="api_secret" 
                                   value="{{ isset($integrations['otp']) ? ($integrations['otp']->getMaskedConfiguration()['api_secret'] ?? '') : '' }}" 
                                   placeholder="Enter API secret" required>
                            <i class="fas fa-eye password-toggle" data-toggle="password"></i>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Sender ID <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="sender_id" 
                               value="{{ $integrations['otp']->configuration['sender_id'] ?? '' }}" 
                               placeholder="6 character sender ID" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Template ID <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="template_id" 
                               value="{{ $integrations['otp']->configuration['template_id'] ?? '' }}" 
                               placeholder="DLT template ID" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">OTP Length</label>
                        <input type="number" class="form-control" name="otp_length" 
                               value="{{ $integrations['otp']->configuration['otp_length'] ?? '6' }}" 
                               min="4" max="8" placeholder="6">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">OTP Expiry Time (seconds)</label>
                        <input type="number" class="form-control" name="otp_expiry" 
                               value="{{ $integrations['otp']->configuration['otp_expiry'] ?? '300' }}" 
                               placeholder="300">
                    </div>
                    
                    <div class="status-toggle">
                        <label>Status</label>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="status" 
                                   {{ (isset($integrations['otp']) && $integrations['otp']->status) ? 'checked' : '' }}
                                   {{ !auth()->user()->hasPermission('integrations.update') ? 'disabled' : '' }}>
                            <label class="form-check-label ms-2">
                                <span class="status-text">{{ (isset($integrations['otp']) && $integrations['otp']->status) ? 'Enabled' : 'Disabled' }}</span>
                            </label>
                        </div>
                    </div>
                    
                    @if(auth()->user()->hasPermission('integrations.update'))
                        <button type="submit" class="btn btn-save-integration">
                            <i class="fas fa-save me-2"></i>Save Configuration
                        </button>
                    @endif
                </form>
            </div>
        </div>
    </div>

    <!-- WhatsApp Messaging -->
    <div class="col-md-6 col-lg-4">
        <div class="integration-card">
            <div class="integration-header">
                <div class="d-flex align-items-center">
                    <div class="integration-icon">
                        <i class="fab fa-whatsapp fa-lg"></i>
                    </div>
                    <div>
                        <h5>WhatsApp Messaging</h5>
                        <p>WhatsApp Business API credentials</p>
                    </div>
                </div>
                @if(isset($integrations['whatsapp']))
                <span class="integration-status-badge {{ $integrations['whatsapp']->status ? 'status-enabled' : 'status-disabled' }}">
                    {{ $integrations['whatsapp']->status ? 'Enabled' : 'Disabled' }}
                </span>
                @endif
            </div>
            <div class="integration-body">
                <form class="integration-form" data-integration-type="whatsapp" data-integration-id="{{ $integrations['whatsapp']->id ?? '' }}">
                    <input type="hidden" name="integration_type" value="whatsapp">
                    <input type="hidden" name="integration_id" value="{{ $integrations['whatsapp']->id ?? '' }}">
                    
                    <div class="form-group">
                        <label class="form-label">Provider Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="provider_name" 
                               value="{{ $integrations['whatsapp']->configuration['provider_name'] ?? '' }}" 
                               placeholder="e.g., Meta, Twilio" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Business Phone Number <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="business_phone" 
                               value="{{ $integrations['whatsapp']->configuration['business_phone'] ?? '' }}" 
                               placeholder="+1234567890" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Phone Number ID <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="phone_number_id" 
                               value="{{ $integrations['whatsapp']->configuration['phone_number_id'] ?? '' }}" 
                               placeholder="Enter phone number ID" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Access Token <span class="text-danger">*</span></label>
                        <div class="password-field-wrapper">
                            <input type="password" class="form-control password-field" name="access_token" 
                                   value="{{ isset($integrations['whatsapp']) ? ($integrations['whatsapp']->getMaskedConfiguration()['access_token'] ?? '') : '' }}" 
                                   placeholder="Enter access token" required>
                            <i class="fas fa-eye password-toggle" data-toggle="password"></i>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Webhook Verify Token <span class="text-danger">*</span></label>
                        <div class="password-field-wrapper">
                            <input type="password" class="form-control password-field" name="webhook_verify_token" 
                                   value="{{ isset($integrations['whatsapp']) ? ($integrations['whatsapp']->getMaskedConfiguration()['webhook_verify_token'] ?? '') : '' }}" 
                                   placeholder="Enter webhook verify token" required>
                            <i class="fas fa-eye password-toggle" data-toggle="password"></i>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Default Country Code</label>
                        <input type="text" class="form-control" name="country_code" 
                               value="{{ $integrations['whatsapp']->configuration['country_code'] ?? '+91' }}" 
                               placeholder="+91">
                    </div>
                    
                    <div class="status-toggle">
                        <label>Status</label>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="status" 
                                   {{ (isset($integrations['whatsapp']) && $integrations['whatsapp']->status) ? 'checked' : '' }}
                                   {{ !auth()->user()->hasPermission('integrations.update') ? 'disabled' : '' }}>
                            <label class="form-check-label ms-2">
                                <span class="status-text">{{ (isset($integrations['whatsapp']) && $integrations['whatsapp']->status) ? 'Enabled' : 'Disabled' }}</span>
                            </label>
                        </div>
                    </div>
                    
                    @if(auth()->user()->hasPermission('integrations.update'))
                        <button type="submit" class="btn btn-save-integration">
                            <i class="fas fa-save me-2"></i>Save Configuration
                        </button>
                    @endif
                </form>
            </div>
        </div>
    </div>

    <!-- Shipping Integration -->
    <div class="col-md-6 col-lg-4">
        <div class="integration-card">
            <div class="integration-header">
                <div class="d-flex align-items-center">
                    <div class="integration-icon">
                        <i class="fas fa-truck fa-lg"></i>
                    </div>
                    <div>
                        <h5>Shipping Integration</h5>
                        <p>Shipping provider API configuration</p>
                    </div>
                </div>
                @if(isset($integrations['shipping']))
                <span class="integration-status-badge {{ $integrations['shipping']->status ? 'status-enabled' : 'status-disabled' }}">
                    {{ $integrations['shipping']->status ? 'Enabled' : 'Disabled' }}
                </span>
                @endif
            </div>
            <div class="integration-body">
                <form class="integration-form" data-integration-type="shipping" data-integration-id="{{ $integrations['shipping']->id ?? '' }}">
                    <input type="hidden" name="integration_type" value="shipping">
                    <input type="hidden" name="integration_id" value="{{ $integrations['shipping']->id ?? '' }}">
                    
                    <div class="form-group">
                        <label class="form-label">Provider Name <span class="text-danger">*</span></label>
                        <select class="form-select" name="provider_name" required>
                            <option value="">Select Provider</option>
                            <option value="fedex" {{ (isset($integrations['shipping']) && ($integrations['shipping']->configuration['provider_name'] ?? '') == 'fedex') ? 'selected' : '' }}>FedEx</option>
                            <option value="ups" {{ (isset($integrations['shipping']) && ($integrations['shipping']->configuration['provider_name'] ?? '') == 'ups') ? 'selected' : '' }}>UPS</option>
                            <option value="dhl" {{ (isset($integrations['shipping']) && ($integrations['shipping']->configuration['provider_name'] ?? '') == 'dhl') ? 'selected' : '' }}>DHL</option>
                            <option value="usps" {{ (isset($integrations['shipping']) && ($integrations['shipping']->configuration['provider_name'] ?? '') == 'usps') ? 'selected' : '' }}>USPS</option>
                            <option value="shiprocket" {{ (isset($integrations['shipping']) && ($integrations['shipping']->configuration['provider_name'] ?? '') == 'shiprocket') ? 'selected' : '' }}>Shiprocket</option>
                            <option value="other" {{ (isset($integrations['shipping']) && ($integrations['shipping']->configuration['provider_name'] ?? '') == 'other') ? 'selected' : '' }}>Other</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">API Key <span class="text-danger">*</span></label>
                        <div class="password-field-wrapper">
                            <input type="password" class="form-control password-field" name="api_key" 
                                   value="{{ isset($integrations['shipping']) ? ($integrations['shipping']->getMaskedConfiguration()['api_key'] ?? '') : '' }}" 
                                   placeholder="Enter API key" required>
                            <i class="fas fa-eye password-toggle" data-toggle="password"></i>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">API Secret <span class="text-danger">*</span></label>
                        <div class="password-field-wrapper">
                            <input type="password" class="form-control password-field" name="api_secret" 
                                   value="{{ isset($integrations['shipping']) ? ($integrations['shipping']->getMaskedConfiguration()['api_secret'] ?? '') : '' }}" 
                                   placeholder="Enter API secret" required>
                            <i class="fas fa-eye password-toggle" data-toggle="password"></i>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Account Number</label>
                        <input type="text" class="form-control" name="account_number" 
                               value="{{ $integrations['shipping']->configuration['account_number'] ?? '' }}" 
                               placeholder="Enter account number">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">API Endpoint</label>
                        <input type="url" class="form-control" name="api_endpoint" 
                               value="{{ $integrations['shipping']->configuration['api_endpoint'] ?? '' }}" 
                               placeholder="https://api.provider.com">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Mode</label>
                        <div class="d-flex gap-3">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="mode" value="test" 
                                       {{ (!isset($integrations['shipping']) || ($integrations['shipping']->configuration['mode'] ?? 'test') == 'test') ? 'checked' : '' }}>
                                <label class="form-check-label">Test</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="mode" value="live" 
                                       {{ (isset($integrations['shipping']) && ($integrations['shipping']->configuration['mode'] ?? '') == 'live') ? 'checked' : '' }}>
                                <label class="form-check-label">Live</label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="status-toggle">
                        <label>Status</label>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="status" 
                                   {{ (isset($integrations['shipping']) && $integrations['shipping']->status) ? 'checked' : '' }}
                                   {{ !auth()->user()->hasPermission('integrations.update') ? 'disabled' : '' }}>
                            <label class="form-check-label ms-2">
                                <span class="status-text">{{ (isset($integrations['shipping']) && $integrations['shipping']->status) ? 'Enabled' : 'Disabled' }}</span>
                            </label>
                        </div>
                    </div>
                    
                    @if(auth()->user()->hasPermission('integrations.update'))
                        <button type="submit" class="btn btn-save-integration">
                            <i class="fas fa-save me-2"></i>Save Configuration
                        </button>
                    @endif
                </form>
            </div>
        </div>
    </div>

    <!-- Google Review Integration -->
    <div class="col-md-6 col-lg-4">
        <div class="integration-card">
            <div class="integration-header">
                <div class="d-flex align-items-center">
                    <div class="integration-icon">
                        <i class="fab fa-google fa-lg"></i>
                    </div>
                    <div>
                        <h5>Google Review</h5>
                        <p>Google My Business reviews integration</p>
                    </div>
                </div>
                @if(isset($integrations['google_review']))
                <span class="integration-status-badge {{ $integrations['google_review']->status ? 'status-enabled' : 'status-disabled' }}">
                    {{ $integrations['google_review']->status ? 'Enabled' : 'Disabled' }}
                </span>
                @endif
            </div>
            <div class="integration-body">
                <form class="integration-form" data-integration-type="google_review" data-integration-id="{{ $integrations['google_review']->id ?? '' }}">
                    <input type="hidden" name="integration_type" value="google_review">
                    <input type="hidden" name="integration_id" value="{{ $integrations['google_review']->id ?? '' }}">
                    
                    <div class="form-group">
                        <label class="form-label">Google Business Profile ID <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="business_profile_id" 
                               value="{{ $integrations['google_review']->configuration['business_profile_id'] ?? '' }}" 
                               placeholder="Enter business profile ID" required>
                        <small class="text-muted d-block mt-1">
                            <i class="fas fa-info-circle"></i> Found in Google Business Profile settings
                        </small>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">API Key <span class="text-danger">*</span></label>
                        <div class="password-field-wrapper">
                            <input type="password" class="form-control password-field" name="api_key" 
                                   value="{{ isset($integrations['google_review']) ? ($integrations['google_review']->getMaskedConfiguration()['api_key'] ?? '') : '' }}" 
                                   placeholder="Enter Google API key" required>
                            <i class="fas fa-eye password-toggle" data-toggle="password"></i>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Client ID</label>
                        <input type="text" class="form-control" name="client_id" 
                               value="{{ $integrations['google_review']->configuration['client_id'] ?? '' }}" 
                               placeholder="Enter OAuth client ID">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Client Secret</label>
                        <div class="password-field-wrapper">
                            <input type="password" class="form-control password-field" name="client_secret" 
                                   value="{{ isset($integrations['google_review']) ? ($integrations['google_review']->getMaskedConfiguration()['client_secret'] ?? '') : '' }}" 
                                   placeholder="Enter OAuth client secret">
                            <i class="fas fa-eye password-toggle" data-toggle="password"></i>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Refresh Token</label>
                        <div class="password-field-wrapper">
                            <input type="password" class="form-control password-field" name="refresh_token" 
                                   value="{{ isset($integrations['google_review']) ? ($integrations['google_review']->getMaskedConfiguration()['refresh_token'] ?? '') : '' }}" 
                                   placeholder="Enter OAuth refresh token">
                            <i class="fas fa-eye password-toggle" data-toggle="password"></i>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="auto_sync" value="1"
                                   {{ (isset($integrations['google_review']) && ($integrations['google_review']->configuration['auto_sync'] ?? false)) ? 'checked' : '' }}>
                            <label class="form-check-label">Auto Sync Reviews</label>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Sync Interval (hours)</label>
                        <input type="number" class="form-control" name="sync_interval" 
                               value="{{ $integrations['google_review']->configuration['sync_interval'] ?? '24' }}" 
                               min="1" placeholder="24">
                    </div>
                    
                    <div class="status-toggle">
                        <label>Status</label>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="status" 
                                   {{ (isset($integrations['google_review']) && $integrations['google_review']->status) ? 'checked' : '' }}
                                   {{ !auth()->user()->hasPermission('integrations.update') ? 'disabled' : '' }}>
                            <label class="form-check-label ms-2">
                                <span class="status-text">{{ (isset($integrations['google_review']) && $integrations['google_review']->status) ? 'Enabled' : 'Disabled' }}</span>
                            </label>
                        </div>
                    </div>
                    
                    @if(auth()->user()->hasPermission('integrations.update'))
                        <button type="submit" class="btn btn-save-integration">
                            <i class="fas fa-save me-2"></i>Save Configuration
                        </button>
                    @endif
                </form>
            </div>
        </div>
    </div>

    <!-- Google Analytics -->
    <div class="col-md-6 col-lg-4">
        <div class="integration-card">
            <div class="integration-header">
                <div class="d-flex align-items-center">
                    <div class="integration-icon">
                        <i class="fas fa-chart-line fa-lg"></i>
                    </div>
                    <div>
                        <h5>Google Analytics</h5>
                        <p>Analytics tracking configuration</p>
                    </div>
                </div>
                @if(isset($integrations['analytics']))
                <span class="integration-status-badge {{ $integrations['analytics']->status ? 'status-enabled' : 'status-disabled' }}">
                    {{ $integrations['analytics']->status ? 'Enabled' : 'Disabled' }}
                </span>
                @endif
            </div>
            <div class="integration-body">
                <form class="integration-form" data-integration-type="analytics" data-integration-id="{{ $integrations['analytics']->id ?? '' }}">
                    <input type="hidden" name="integration_type" value="analytics">
                    <input type="hidden" name="integration_id" value="{{ $integrations['analytics']->id ?? '' }}">
                    
                    <div class="form-group">
                        <label class="form-label">Tracking Type</label>
                        <select class="form-select" name="tracking_type">
                            <option value="ga4" {{ (!isset($integrations['analytics']) || ($integrations['analytics']->configuration['tracking_type'] ?? 'ga4') == 'ga4') ? 'selected' : '' }}>GA4</option>
                            <option value="universal" {{ (isset($integrations['analytics']) && ($integrations['analytics']->configuration['tracking_type'] ?? '') == 'universal') ? 'selected' : '' }}>Universal Analytics</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Measurement ID <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="measurement_id" 
                               value="{{ $integrations['analytics']->configuration['measurement_id'] ?? '' }}" 
                               placeholder="G-XXXXXXXXXX" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">API Secret</label>
                        <div class="password-field-wrapper">
                            <input type="password" class="form-control password-field" name="api_secret" 
                                   value="{{ isset($integrations['analytics']) ? ($integrations['analytics']->getMaskedConfiguration()['api_secret'] ?? '') : '' }}" 
                                   placeholder="Enter API secret">
                            <i class="fas fa-eye password-toggle" data-toggle="password"></i>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Property ID</label>
                        <input type="text" class="form-control" name="property_id" 
                               value="{{ $integrations['analytics']->configuration['property_id'] ?? '' }}" 
                               placeholder="123456789">
                    </div>
                    
                    <div class="form-group">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="enable_ecommerce" value="1"
                                   {{ (isset($integrations['analytics']) && ($integrations['analytics']->configuration['enable_ecommerce'] ?? false)) ? 'checked' : '' }}>
                            <label class="form-check-label">Enable Ecommerce Tracking</label>
                        </div>
                    </div>
                    
                    <div class="status-toggle">
                        <label>Status</label>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="status" 
                                   {{ (isset($integrations['analytics']) && $integrations['analytics']->status) ? 'checked' : '' }}
                                   {{ !auth()->user()->hasPermission('integrations.update') ? 'disabled' : '' }}>
                            <label class="form-check-label ms-2">
                                <span class="status-text">{{ (isset($integrations['analytics']) && $integrations['analytics']->status) ? 'Enabled' : 'Disabled' }}</span>
                            </label>
                        </div>
                    </div>
                    
                    @if(auth()->user()->hasPermission('integrations.update'))
                        <button type="submit" class="btn btn-save-integration">
                            <i class="fas fa-save me-2"></i>Save Configuration
                        </button>
                    @endif
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const integrationPermissions = {
    view: @json(auth()->user()->hasPermission('integrations.view')),
    update: @json(auth()->user()->hasPermission('integrations.update'))
};

$(document).ready(function() {
    const csrfToken = $('meta[name="csrf-token"]').attr('content');
     
    if (!integrationPermissions.update) {
        $('.integration-form input:not([type="hidden"]), .integration-form select, .integration-form textarea').prop('disabled', true);
        $('.integration-form input[type="checkbox"], .integration-form input[type="radio"]').prop('disabled', true);
    }
     
    function showToast(type, title, message) {
        const toastContainer = $('.sa-app__toasts');
        const toastId = 'toast-' + Date.now();
        
        const iconClass = {
            'success': 'fa-check-circle text-success',
            'error': 'fa-exclamation-circle text-danger',
            'warning': 'fa-exclamation-triangle text-warning',
            'info': 'fa-info-circle text-info'
        }[type] || 'fa-info-circle';
        
        const toastHtml = `
            <div id="${toastId}" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="toast-header">
                    <i class="fas ${iconClass} me-2"></i>
                    <strong class="me-auto">${title}</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
                <div class="toast-body">
                    ${message}
                </div>
            </div>
        `;
        
        toastContainer.append(toastHtml);
        
        const toastElement = document.getElementById(toastId);
        const toast = new bootstrap.Toast(toastElement, {
            autohide: true,
            delay: 3000
        });
        
        toast.show();
         
        toastElement.addEventListener('hidden.bs.toast', function() {
            toastElement.remove();
        });
    }
     
    $(document).on('click', '.password-toggle', function() {
        const $input = $(this).siblings('.password-field');
        const type = $input.attr('type') === 'password' ? 'text' : 'password';
        $input.attr('type', type);
        $(this).toggleClass('fa-eye fa-eye-slash');
    });
     
    $(document).on('change', '.form-check-input[name="status"]', function() {
        const $statusText = $(this).closest('.form-check').find('.status-text');
        $statusText.text(this.checked ? 'Enabled' : 'Disabled');
    });
     
    $('.integration-form').on('submit', function(e) {
        e.preventDefault();
        
        if (!integrationPermissions.update) {
            showToast('error', 'Error!', 'You do not have permission to update integrations.');
            return;
        }
        
        const $form = $(this);
        const $btn = $form.find('.btn-save-integration');
        const originalText = $btn.html();
        const formData = new FormData(this);
          
        const data = {};
        formData.forEach((value, key) => {
            data[key] = value;
        });
         
        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>Saving...');
        
        $.ajax({
            url: '{{ route("integrations.store") }}',
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            data: data,
            success: function(response) {
                if (response.success) { 
                    if (response.integration && response.integration.id) {
                        $form.find('input[name="integration_id"]').val(response.integration.id);
                        $form.attr('data-integration-id', response.integration.id);
                    }
                     
                    showToast('success', 'Success!', response.message);
                     
                    const $card = $form.closest('.integration-card');
                    const statusBadge = $card.find('.integration-status-badge');
                    const isEnabled = $form.find('input[name="status"]').is(':checked');
                    
                    if (statusBadge.length) {
                        statusBadge.removeClass('status-enabled status-disabled')
                                   .addClass(isEnabled ? 'status-enabled' : 'status-disabled')
                                   .text(isEnabled ? 'Enabled' : 'Disabled');
                    } else { 
                        $card.find('.integration-header').append(
                            `<span class="integration-status-badge ${isEnabled ? 'status-enabled' : 'status-disabled'}">
                                ${isEnabled ? 'Enabled' : 'Disabled'}
                            </span>`
                        );
                    }
                }
            },
            error: function(xhr) {
                console.error('Error:', xhr);
                let errorMessage = 'Failed to save integration';
                
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }
                 
                showToast('error', 'Error!', errorMessage);
            },
            complete: function() { 
                $btn.prop('disabled', false).html(originalText);
            }
        });
    });
     
    $('[data-bs-toggle="tooltip"]').tooltip();
     
    $(document).on('click', '#testEmailBtn', function() {
        if (!integrationPermissions.update) {
            showToast('error', 'Error!', 'You do not have permission to update integrations.');
            return;
        }
        
        const $btn = $(this);
        const originalText = $btn.html();
         
        const testEmail = prompt('Enter email address to send test email:');
        
        if (!testEmail) {
            return;
        }
         
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(testEmail)) {
            showToast('error', 'Invalid Email', 'Please enter a valid email address');
            return;
        }
        
        // Show loading state
        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>Sending...');
        
        $.ajax({
            url: '{{ route("integrations.test-email") }}',
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            data: {
                email: testEmail
            },
            success: function(response) {
                if (response.success) {
                    showToast('success', 'Email Sent!', 'Test email sent successfully to ' + testEmail);
                } else {
                    showToast('error', 'Failed', response.message || 'Failed to send test email');
                }
            },
            error: function(xhr) {
                console.error('Test email error:', xhr);
                let errorMessage = 'Failed to send test email';
                
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }
                
                showToast('error', 'Error!', errorMessage);
            },
            complete: function() {
                $btn.prop('disabled', false).html(originalText);
            }
        });
    });
});
</script>
@endpush
