<?php

namespace App;

use Ramsey\Uuid\Uuid;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Notifications\Notifiable;
use Modules\MPS\Models\Traits\ActivityTrait;
use Illuminate\Contracts\Auth\Access\Authorizable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable implements Authorizable
{
    use ActivityTrait;
    use HasRoles;
    use Notifiable;

    public $incrementing = false;

    protected $hidden = ['password', 'remember_token'];

    protected $keyType = 'string';

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            $model->id = Uuid::uuid4()->toString();
        });
    }

    public function actingUser()
    {
        if (!session()->has('impersonate')) {
            return null;
        }

        $user = user(session('impersonate'));
        return [
            'name'        => $user->name,
            'avatar'      => $user->avatar,
            'username'    => $user->username,
            'location_id' => $user->location_id,
        ];
    }

    public function isImpersonating()
    {
        return session()->has('impersonate');
    }

    public function logActivity($msg)
    {
        log_activity(
            $msg,
            [
                'user' => [
                    'name'     => $this->name,
                    'email'    => $this->email,
                    'username' => $this->username,
                    'phone'    => $this->phone,
                    'active'   => $this->active,
                ],
            ],
            $this
        );
    }

    public function scopeEmployee($query)
    {
        return $query->where('employee', 1);
    }

    public function setImpersonating()
    {
        if ($this->active && $this->can_impersonate) {
            session()->put('impersonate', $this->id);
        }
    }

    public function stopImpersonating()
    {
        session()->forget('impersonate');
    }
}
