<?php

namespace App\Entities;

use Illuminate\Database\Eloquent\Model;

class Message extends Model {
    protected $table = "messages";
    public $timestamps = true;
}