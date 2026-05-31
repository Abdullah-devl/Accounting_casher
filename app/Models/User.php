<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'US000';
    protected $primaryKey = 'GUID';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'GUID',
        'NUMBER',
        'NAME',
        'USER_NAME',
        'PASSWORD',
        'MOB1',
        'MAIL',
        'USER_LEVEL',
        'FREEZ',
        'IMG',
    ];

    protected $hidden = [
        'PASSWORD',
        'remember_token',
    ];

    // Tell Laravel to use USER_NAME as the username for authentication
    public function getAuthIdentifierName()
    {
        return 'USER_NAME';
    }

    // Tell Laravel that USER_NAME is the identifier value
    public function getAuthIdentifier()
    {
        return $this->getAttribute('USER_NAME');
    }

    // Tell Laravel to use PASSWORD column for password check
    public function getAuthPasswordName()
    {
        return 'PASSWORD';
    }

    public function getAuthPassword()
    {
        return $this->getAttribute('PASSWORD');
    }

    // Map email routing to USER_NAME
    public function routeNotificationForMail($notification = null)
    {
        return $this->USER_NAME;
    }

    // Return USER_NAME for password reset notifications
    public function getEmailForPasswordReset()
    {
        return $this->attributes['USER_NAME'] ?? null;
    }

    // Cache-based simulation of email verification to support Breeze / test assertions
    public function hasVerifiedEmail()
    {
        $guid = $this->attributes['GUID'] ?? null;
        $level = $this->attributes['USER_LEVEL'] ?? null;
        return !is_null(cache('email_verified_at_' . $guid)) || $level === 0;
    }

    public function markEmailAsVerified()
    {
        $guid = $this->attributes['GUID'] ?? null;
        cache(['email_verified_at_' . $guid => now()], now()->addDays(365));
        return true;
    }

    public function getEmailForVerification()
    {
        return $this->attributes['USER_NAME'] ?? null;
    }

    // Accessors to bridge legacy uppercase columns with Laravel lowercase expectations
    public function getRoleAttribute()
    {
        return ($this->attributes['USER_LEVEL'] ?? null) === 0 ? 'admin' : 'cashier';
    }

    public function getNameAttribute()
    {
        return $this->attributes['NAME'] ?? null;
    }

    public function getEmailAttribute()
    {
        return $this->attributes['USER_NAME'] ?? null;
    }
}
