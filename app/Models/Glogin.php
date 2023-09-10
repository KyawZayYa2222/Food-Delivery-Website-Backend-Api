<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Glogin extends Model
{
    use HasFactory;

    protected $fillable = ['uid', 'user_id'];
}
