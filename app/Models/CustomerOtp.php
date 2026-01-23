<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

class CustomerOtp extends Model
{
    protected $fillable = [
        'email',
        'otp',
        'purpose',
        'expires_at',
        'is_verified',
        'ip_address',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'is_verified' => 'boolean',
    ];

    /**
     * Generate and send OTP
     */
    public static function generateAndSend($email, $purpose = 'registration')
    {
        // Generate 6-digit OTP
        $otp = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
        
        // Delete old OTPs for this email and purpose
        self::where('email', $email)
            ->where('purpose', $purpose)
            ->where('is_verified', false)
            ->delete();
        
        // Create new OTP
        $otpRecord = self::create([
            'email' => $email,
            'otp' => $otp,
            'purpose' => $purpose,
            'expires_at' => Carbon::now()->addMinutes(10), // 10 minutes expiry
            'ip_address' => request()->ip(),
        ]);
        
        // Send OTP email
        Mail::send('emails.otp', ['otp' => $otp, 'purpose' => $purpose], function ($message) use ($email) {
            $message->to($email)
                    ->subject('Your OTP Code - ' . config('app.name'));
        });
        
        return $otpRecord;
    }

    /**
     * Verify OTP
     */
    public static function verify($email, $otp, $purpose = 'registration')
    {
        $otpRecord = self::where('email', $email)
            ->where('otp', $otp)
            ->where('purpose', $purpose)
            ->where('is_verified', false)
            ->where('expires_at', '>', Carbon::now())
            ->first();
        
        if ($otpRecord) {
            $otpRecord->update(['is_verified' => true]);
            return true;
        }
        
        return false;
    }

    /**
     * Check if OTP is valid
     */
    public function isValid()
    {
        return !$this->is_verified && $this->expires_at->isFuture();
    }
}
