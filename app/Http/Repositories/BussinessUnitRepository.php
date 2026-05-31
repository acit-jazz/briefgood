<?php

namespace App\Http\Repositories;

use App\Models\BussinessUnit;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class BussinessUnitRepository
{
    const CACHE_KEY = 'BUSSINESSUNIT';
    const CACHE_TAG = 'bussiness_units';
    const CACHE_DURATION = 6; // in months

    public function pluck($name, $id)
    {
        return $this->cacheRemember("pluck.{$name}.{$id}", function () use ($name, $id) {
            return BussinessUnit::pluck($name, $id);
        });
    }

    public function all()
    {
        $keys = $this->requestValue();
        return $this->cacheRemember("all.{$keys}", function () {
            return BussinessUnit::allWithFilters();
        });
    }

    public function find($id)
    {
        return $this->cacheRemember("find.{$id}", function () use ($id) {
            return BussinessUnit::find($id);
        });
    }

    public function findBySlug($slug)
    {
        return $this->cacheRemember("findBySlug.{$slug}", function () use ($slug) {
            return BussinessUnit::findBySlug($slug);
        });
    }

    public function paginate($number)
    {
        $keys = $this->requestValue();
        return $this->cacheRemember("paginate.{$number}.{$keys}", function () use ($number) {
            return BussinessUnit::paginateWithFilters($number);
        });
    }

    public function paginateTrash($number)
    {
        request()->merge(['trash' => '1']);
        $keys = $this->requestValue();
        return $this->cacheRemember("paginateTrash.{$number}.{$keys}", function () use ($number) {
            return BussinessUnit::paginateWithFilters($number);
        });
    }

    public function countTrash()
    {
        return $this->cacheRemember("countTrash", function () {
            return BussinessUnit::onlyTrashed()->count();
        });
    }

    private function getCacheKey($key)
    {
        return self::CACHE_KEY.'.'.strtoupper($key);
    }

    private function cacheRemember($key, \Closure $callback)
    {
        $cacheKey = $this->getCacheKey($key);
        return Cache::tags([self::CACHE_TAG])->remember(
            $cacheKey,
            Carbon::now()->addMonths(self::CACHE_DURATION),
            $callback
        );
    }

    private function requestValue()
    {
        return http_build_query(request()->all(), '', '.');
    }
}
