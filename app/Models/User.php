<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\HasApiTokens;
use Tymon\JWTAuth\Contracts\JWTSubject; // Import the JWTSubject interface

class User extends Authenticatable implements JWTSubject // Implement JWTSubject here
{
    use HasApiTokens, Notifiable;

    protected $fillable = [
        'name', 'phone', 'password','refer_by',
    ];

    protected $hidden = [
        'password',
    ];



        // Relationship for referring user
        public function referrer()
        {
            return $this->belongsTo(User::class, 'refer_by');
        }

        // Relationship for users referred by this user
        public function referredUsers()
        {
            return $this->hasMany(User::class, 'refer_by');
        }

    public function setPasswordAttribute($password)
    {
        $this->attributes['password'] = Hash::make($password);
    }

    public function products()
    {
        return $this->hasMany(UserProduct::class);
    }

    /**
     * Get the identifier that will be stored in the JWT token.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey(); // Usually the primary key (id)
    }

    /**
     * Return a key-value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return []; // You can add custom claims here
    }
}
