<?php

namespace Modules\StagedForm\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\StagedForm\Database\factories\StagedFormFactory;

class StagedForm extends Model
{
    use HasFactory;

    protected $fillable = [
        'stage_id', // session id
        'stage_type', // registration type
        'user_id'
    ];
}
