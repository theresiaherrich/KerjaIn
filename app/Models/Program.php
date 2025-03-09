<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Program extends Model
{
    use HasFactory;

    protected $table = 'programs';
    protected $fillable = ['image', 'name', 'date', 'price'];

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }
}

