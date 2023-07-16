<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Slideshow extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'title',
        'sub_title',
        'description',
        'image',
        'active',
        'show_date',
        'end_date',
    ];
}
