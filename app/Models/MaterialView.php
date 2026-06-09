<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MaterialView extends Model
{
    protected $fillable = ['learning_material_id', 'user_id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function material()
    {
        return $this->belongsTo(LearningMaterial::class, 'learning_material_id');
    }
}