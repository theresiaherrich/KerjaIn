<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Application extends Model
{
    use HasFactory;

    protected $fillable = ['jobs_id', 'name',
     'email', 'phone', 'date_of_birth',
      'disability_id', 'cv', 'cover_letter'];

    public function jobsIn()
    {
        return $this->belongsTo(jobsIn::class);
    }
    public function disability()
    {
        return $this->belongsTo(Disability::class);
    }
}
