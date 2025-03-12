<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Application extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'jobs_id', 'name',
    'email', 'phone', 'date_of_birth','gender', 'disability_id', 'cv', 'cover_letter'];


    public function jobsIn()
    {
        return $this->belongsTo(jobsIn::class, 'jobs_id');
    }
    public function disability()
    {
        return $this->belongsTo(Disability::class);
    }

    public function user()
{
    return $this->belongsTo(User::class);
}
}
