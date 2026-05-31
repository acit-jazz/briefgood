<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Http\Requests\BussinessUnit\BussinessUnitRequest;
use App\Http\Resources\Backend\BussinessUnitResource;
use App\Models\BussinessUnit;
use Facades\App\Http\Repositories\BussinessUnitRepository;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;

class BussinessUnitController extends Controller
{
    public function index()
    {
        $items = BussinessUnitRepository::paginate(20);

        return Inertia::render(strtolower('BussinessUnit').'/index', [
            'bussinessunits' => BussinessUnitResource::collection($items),
            'title' => request('trash') ? 'Trash' : 'BussinessUnit',
            'trash' => request('trash') ? true : false,
            'request' => request()->all(),
            'breadcumb' => [
                ['text' => 'Dashboard', 'url' => route('dashboard.index')],
                ['text' => 'BussinessUnit', 'url' => route(strtolower('BussinessUnit').'.index')],
            ],
        ]);
    }

    public function create()
    {
        $item = new BussinessUnit();
        $item = BussinessUnitResource::make($item)->resolve();

        return Inertia::render(strtolower('BussinessUnit').'/form', [
            'method' => 'post',
            'bussinessunit' => $item,
            'title' => 'Create BussinessUnit',
            'breadcumb' => [
                ['text' => 'Dashboard', 'url' => route('dashboard.index')],
                ['text' => 'BussinessUnit', 'url' => route(strtolower('BussinessUnit').'.index')],
            ],
        ]);
    }

    public function store(BussinessUnitRequest $request)
    {
        $item = BussinessUnit::create($request->all());
        Cache::tags(['bussinessunits'])->flush();

        return redirect()->back()->with('message', toTitle($item->title).' has been created');
    }

    public function edit(BussinessUnit $bussinessunit)
    {
        return Inertia::render(strtolower('BussinessUnit').'/form', [
            'bussinessunit' => BussinessUnitResource::make($bussinessunit)->resolve(),
            'method' => 'patch',
            'title' => 'Edit BussinessUnit',
            'breadcumb' => [
                ['text' => 'Dashboard', 'url' => route('dashboard.index')],
                ['text' => 'BussinessUnit', 'url' => route(strtolower('BussinessUnit').'.index')],
            ],
        ]);
    }

    public function update(BussinessUnitRequest $request, BussinessUnit $bussinessunit)
    {
        $bussinessunit->update($request->all());
        Cache::tags(['bussinessunits'])->flush();

        return redirect()->back()->with('message', toTitle($bussinessunit->title).' has been updated');
    }

    public function delete($id)
    {
        $item = BussinessUnit::find($id);
        if (! $item) abort(404);
        $item->delete();

        Cache::tags(['bussinessunits'])->flush();

        return redirect()->route(strtolower('BussinessUnit').'.index')->with('message', toTitle($item->title).' has been deleted');
    }

    public function destroy($id)
    {
        $item = BussinessUnit::withTrashed()->find($id);
        if (! $item) abort(404);
        $item->forceDelete();

        Cache::tags(['bussinessunits'])->flush();

        return redirect()->route(strtolower('BussinessUnit').'.index')->with('message', toTitle($item->title).' has been destroyed');
    }

    public function destroyAll()
    {
        $ids = explode(',', request('selected'));
        $items = BussinessUnit::whereIn('_id', $ids)->withTrashed()->get();

        foreach ($items as $item) {
            $item->forceDelete();
        }

        Cache::tags(['bussinessunits'])->flush();

        return redirect()->route(strtolower('BussinessUnit').'.index')->with('message', 'Selected bussinessunits have been destroyed');
    }

    public function restore($id)
    {
        $item = BussinessUnit::withTrashed()->find($id);
        if (! $item) abort(404);
        $item->restore();

        Cache::tags(['bussinessunits'])->flush();

        return redirect()->route(strtolower('BussinessUnit').'.index')->with('message', toTitle($item->title).' has been restored');
    }
}
