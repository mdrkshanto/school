<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\VehicleRequest;
use App\Models\Vehicle;
use Illuminate\Http\Request;

class VehicleController extends Controller
{
    public function index(Request $request)
    {
        $query = Vehicle::query()
            ->withCount('routes')
            ->when($request->search, fn ($q, $s) => $q->where('registration_no', 'LIKE', "%{$s}%")->orWhere('model', 'LIKE', "%{$s}%")->orWhere('driver_name', 'LIKE', "%{$s}%"))
            ->when($request->status, fn ($q, $v) => $q->where('status', $v))
            ->orderByDesc('id');

        return $this->render('admin/Transport/Vehicles', [
            'vehicles' => $this->paginated($query->paginate(15)->withQueryString()),
            'filters' => $request->only(['search', 'status']),
        ]);
    }

    public function store(VehicleRequest $request)
    {
        Vehicle::query()->create($request->validated());
        $this->successFlash('Vehicle added.');

        return redirect()->route('admin.vehicles.index');
    }

    public function update(VehicleRequest $request, Vehicle $vehicle)
    {
        $vehicle->update($request->validated());
        $this->successFlash('Vehicle updated.');

        return redirect()->route('admin.vehicles.index');
    }

    public function destroy(Vehicle $vehicle)
    {
        $vehicle->routes()->detach();
        $vehicle->delete();
        $this->successFlash('Vehicle removed.');

        return back();
    }
}
