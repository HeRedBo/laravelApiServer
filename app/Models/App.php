<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class App extends Model
{
    /**
     * 表名
     * @var string
     */
    protected $table = 'api_apps';

    /**
     * The attributes that are mass assignable.
     * 
     * @var array 
     */
    protected $fillable = [
        'id', 'app_id', 'app_secret', 'app_name', 'app_desc', 'status', 'created_at', 'updated_at',
    ];

}
