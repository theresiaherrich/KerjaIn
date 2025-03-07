<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Company extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'location', 'description', 'logo'];

    public function jobOpenings()
    {
        return $this->hasMany(jobsIn::class);
    }
}
