<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Blog extends Model
{
    use HasFactory;
    protected $fillable =[
        'name', 'body', 'category_id', 'status', 'deleted_at'
    ];

    public function photos()
    {
        return $this->hasMany(BlogPhoto::class,'blog_id','id');
    }
}
