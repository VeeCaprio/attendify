<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StudyClass extends Model
{
    protected $fillable = [
        'code',
        'name',
        'academic_year',
        'semester',
        'homeroom_teacher',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'semester' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function courseSchedules(): HasMany
    {
        return $this->hasMany(CourseSchedule::class);
    }
}
