<?php

namespace App\Http\Controllers;

use App\Http\Requests\BusinessUnit\BusinessUnitRequest;
use App\Http\Repositories\BusinessUnitRepository;
use App\Http\Resources\BusinessUnitResource;
use App\Models\BusinessUnit;
use App\Models\Category;
use App\Models\Service;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class BusinessUnitController extends Controller
{
    public function __construct(
        protected BusinessUnitRepository $businessUnits,
    ) {}

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', BusinessUnit::class);

        $items = $this->businessUnits->paginate(20, $request->only(['search', 'category_id']));

        return Inertia::render('business-units/Index', [
            'businessUnits' => BusinessUnitResource::collection($items),
            'filters' => $request->only(['search', 'category_id']),
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', BusinessUnit::class);

        return Inertia::render('business-units/Form', [
            'businessUnit' => null,
            'categories' => Category::query()->orderBy('name')->get(['id', 'name']),
            'users' => \App\Models\User::query()->orderBy('name')->get(['id', 'name', 'role']),
            'services' => Service::query()->where('is_active', true)->orderBy('name')->get(['id', 'name', 'keywords']),
        ]);
    }

    public function store(BusinessUnitRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['slug'] = $data['slug'] ?? Str::slug($data['name']);

        // Extract service attachments (service_id with specialization_score)
        $serviceAttachments = $data['services'] ?? [];
        unset($data['services']);

        $unit = BusinessUnit::query()->create($data);

        // Attach services with specialization scores via pivot
        foreach ($serviceAttachments as $serviceData) {
            if (empty($serviceData['service_id'])) {
                continue;
            }

            $unit->services()->attach($serviceData['service_id'], [
                'specialization_score' => $serviceData['specialization_score'] ?? 50,
                'notes' => $serviceData['notes'] ?? null,
            ]);
        }

        return redirect()
            ->route('business-units.index')
            ->with('success', 'Business unit created.');
    }

    public function edit(BusinessUnit $businessUnit): Response
    {
        $this->authorize('update', $businessUnit);

        $businessUnit->load(['category', 'services', 'pic']);

        return Inertia::render('business-units/Form', [
            'businessUnit' => BusinessUnitResource::make($businessUnit)->resolve(),
            'categories' => Category::query()->orderBy('name')->get(['id', 'name']),
            'users' => \App\Models\User::query()->orderBy('name')->get(['id', 'name', 'role']),
            'services' => Service::query()->where('is_active', true)->orderBy('name')->get(['id', 'name', 'keywords']),
        ]);
    }

    public function update(BusinessUnitRequest $request, BusinessUnit $businessUnit): RedirectResponse
    {
        $data = $request->validated();
        $data['slug'] = $data['slug'] ?? Str::slug($data['name']);

        // Extract service attachments
        $serviceAttachments = $data['services'] ?? [];
        unset($data['services']);

        $businessUnit->update($data);

        // Sync services - detach all and reattach with new scores
        $businessUnit->services()->detach();

        foreach ($serviceAttachments as $serviceData) {
            if (empty($serviceData['service_id'])) {
                continue;
            }

            $businessUnit->services()->attach($serviceData['service_id'], [
                'specialization_score' => $serviceData['specialization_score'] ?? 50,
                'notes' => $serviceData['notes'] ?? null,
            ]);
        }

        return redirect()
            ->route('business-units.index')
            ->with('success', 'Business unit updated.');
    }

    public function destroy(BusinessUnit $businessUnit): RedirectResponse
    {
        $this->authorize('delete', $businessUnit);

        $businessUnit->delete();

        return redirect()
            ->route('business-units.index')
            ->with('success', 'Business unit deleted.');
    }
}
