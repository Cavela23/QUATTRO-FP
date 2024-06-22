<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Admin extends Authenticatable
{
    use HasFactory;

    use Notifiable;

    protected $primaryKey = 'admin_id';

    protected $table = 'admin';

    protected $fillable = [
        'username',
        'email',
        'password'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function setPasswordAttribute($password) {
        $this->attributes['password'] = bcrypt($password);
    }


}
