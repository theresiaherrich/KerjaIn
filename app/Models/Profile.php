<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Profile extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'username', 'name', 'email', 'image', 'phone', 'date_of_birth', 'address', 'gender', 'education_id', 'disability_id', 'cv'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
