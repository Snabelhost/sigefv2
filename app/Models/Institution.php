<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Institution extends Model
{
    use HasFactory;

    protected $fillable = [
        'institution_type_id',
        'name',
        'acronym',
        'phone',
        'email',
        'country',
        'province',
        'municipality',
        'address',
        'logo',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function type()
    {
        return $this->belongsTo(InstitutionType::class, 'institution_type_id');
    }

    public function institutionType()
    {
        return $this->type();
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }
}
