<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Program extends Model
{
    use HasFactory;

    protected $table = 'programs';
    protected $fillable = ['image', 'name', 'date', 'price'];

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function favorites(): MorphMany
    {
        return $this->morphMany(Favorite::class, 'favoritable');
    }
}

