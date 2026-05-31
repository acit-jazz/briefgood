<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['client_name', 'industry', 'title','brief_date','budget',
'deadline','notes','file_name','file_path','status','ai_status','ai_error'])]
class Brief extends Model
{
    //
}
