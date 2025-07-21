<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    //
    public function users()
    {
        return $this->belongsToMany(User::class);
    }
    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    protected $fillable = ['name', 'total_members','organization_id'];
}
