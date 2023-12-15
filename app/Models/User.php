<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Laravel\Sanctum\NewAccessToken;

use Illuminate\Support\Str;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function createToken(string $name, $abilities = ['*'])
    {
        if ($name == "Auth token") {
            $token = $this->tokens()->create([
                'name' => $name,
                'token' => hash('sha256', $plainTextToken = Str::random(40)),
                'abilities' => $abilities,
                'expired_at' => now()->addHours(24)
            ]);
        }elseif ($name == "Action token") {
            $token = $this->tokens()->create([
                'name' => $name,
                'token' => hash('sha256', $plainTextToken = Str::random(40)),
                'abilities' => $abilities,
                'expired_at' => now()->addMinutes(3)
            ]);
        }
            

        return new NewAccessToken($token, $token->getKey().'|'.$plainTextToken);
    }

}
