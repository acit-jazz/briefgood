<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['business_unit_id', 'name','description'])]
class Service extends Model
{
    //
}
