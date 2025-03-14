<?php

namespace App\Models;

use Laravel\Scout\Searchable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\MorphMany;


class jobsIn extends Model
{
    use HasFactory,  Searchable;

    protected $fillable = ['name', 'description', 'salary', 'location',
    'company_name', 'disability_type', 'education_level', 'experience_duration', 'type_duration', 'policy_location'];

    protected $table = 'jobs_ins';

    public function toSearchableArray()
    {
        return [
            'id'          => $this->id,
            'name'        => $this->name,
            'description' => $this->description,
            'location'    => $this->location,
            'salary'      => $this->salary,
            'company_name' => $this->company ? $this->company->name : null,
            'disability_type' => $this->disability ? $this->disability->type : null,
            'education_level' => $this->education ? $this->education->level : null,
            'experience_duration' => $this->experience ? $this->experience->duration : null,
            'type_duration' => $this->type ? $this->type->duration : null,
            'policy_location' => $this->policy ? $this->policy->location : null,
        ];
    }

        public function company()
        {
            return $this->belongsTo(Company::class, 'company_name');
        }

        public function disability()
        {
            return $this->belongsTo(Disability::class, 'disability_type');
        }

        public function education()
        {
            return $this->belongsTo(Education::class, 'education_level');
        }

        public function experience()
        {
            return $this->belongsTo(Experience::class, 'experience_duration');
        }

        public function type()
        {
            return $this->belongsTo(Type::class, 'type_duration');
        }

        public function policy()
        {
            return $this->belongsTo(Policy::class, 'policy_location');
        }

    public function applications()
    {
        return $this->hasMany(Application::class, 'jobs_id');
    }

    public function favorites(): MorphMany
    {
        return $this->morphMany(Favorite::class, 'favoritable');
    }
}
