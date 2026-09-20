<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\TransportRouteRequest;
use App\Models\TransportRoute;
use App\Models\Vehicle;
use Illuminate\Http\Request;

class TransportRouteController extends Controller
{
    public function index(Request $request)
    {
        $query = TransportRoute::query()
            ->with(['stops:id,name,route_id,order_index,fare', 'vehicles:id,registration_no,model'])
            ->when($request->search, fn ($q, $s) => $q->where('name', 'LIKE', "%{$s}%")->orWhere('base_fare', 'LIKE', "%{$s}%"))
            ->orderByDesc('id');

        return $this->render('admin/Transport/Index', [
            'routes' => $this->paginated($query->paginate(15)->withQueryString()),
            'vehicles' => Vehicle::query()->where('status', 'active')->select('id', 'registration_no', 'model')->get(),
            'filters' => $request->only(['search']),
        ]);
    }

    public function store(TransportRouteRequest $request)
    {
        $data = $request->validated();
        $route = TransportRoute::query()->create([
            ...$data,
            'status' => $data['status'] ?? 'active',
        ]);

        $route->vehicles()->sync($request->input('vehicle_ids', []));

        if ($request->has('stops')) {
            foreach ($request->input('stops', []) as $index => $stop) {
                $route->stops()->create([
                    ...$stop,
                    'order_index' => $index + 1,
                ]);
            }
        }

        $this->successFlash('Transport route created.');

        return redirect()->route('admin.routes.index');
    }

    public function update(TransportRouteRequest $request, TransportRoute $route)
    {
        $data = $request->validated();
        $route->update([
            ...$data,
            'status' => $data['status'] ?? 'active',
        ]);

        $route->vehicles()->sync($request->input('vehicle_ids', []));

        $this->successFlash('Transport route updated.');

        return redirect()->route('admin.routes.index');
    }

    public function destroy(TransportRoute $route)
    {
        $route->stops()->delete();
        $route->vehicles()->detach();
        $route->delete();
        $this->successFlash('Transport route deleted.');

        return back();
    }
}
