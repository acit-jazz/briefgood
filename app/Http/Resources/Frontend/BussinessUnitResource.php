<?php

namespace App\Http\Resources\Frontend;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;

class BussinessUnitResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
             'name' => $this->name,
            'description' => $this->description,
        ];
    }
}
