<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['brief_id', 'business_unit_name', 'confidence','status','rejection_reason',
'internal_notes'])]
class PitchAssignment extends Model
{
    //
}
