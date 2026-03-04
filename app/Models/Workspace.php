<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;
use MongoDB\Laravel\Eloquent\SoftDeletes;

class Workspace extends Model
{
    use SoftDeletes;
    protected $collection = 'workspaces';

    protected $fillable = [
        'name',
        'description',
    ];


    public function creator()
    {
        return $this->belongsTo(User::class,'_id', 'creator_id');
    }

    public function members()
    {
        return $this->belongsToMany(User::class, null, 'workspace_ids', 'user_ids');
    }

    public function teams()
    {
        return $this->hasMany(Team::class);
    }

    public static function edit($request){
        $workspace = data_get($request,'workspace');
        $data = [];
        if ($request->has('name')) $data['name'] = $request->name;
        if ($request->has('description')) $data['description'] = $request->description;

        $workspace->update($data);
        return $workspace;
    }
}
