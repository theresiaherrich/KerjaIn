<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Profile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id','username', 'name', 'email', 'image', 'phone',
        'date_of_birth', 'address', 'gender',
        'education_id', 'disability_id', 
    ];

    protected $appends = ['image_url'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function disability()
    {
        return $this->belongsTo(Disability::class);
    }

    public function education()
    {
        return $this->belongsTo(Education::class);
    }

    public function getImageUrlAttribute()
    {
        $supabaseUrl = env('SUPABASE_URL');
        $bucketName = env('SUPABASE_BUCKET', 'files');

        if (!$this->image) {
            return asset('storage/default.png');
        }

        if (filter_var($this->image, FILTER_VALIDATE_URL)) {
            return $this->image;
        }

        return "$supabaseUrl/storage/v1/object/public/$bucketName/" . $this->image;
    }
}
