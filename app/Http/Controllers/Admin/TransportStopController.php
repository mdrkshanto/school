<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\TransportStopRequest;
use App\Models\TransportRoute;
use App\Models\TransportStop;

class TransportStopController extends Controller
{
    public function store(TransportStopRequest $request)
    {
        $order = $request->integer('order', 0);
        if ($order === 0) {
            $order = TransportRoute::findOrFail($request->route_id)->stops()->count() + 1;
        }

        TransportStop::query()->create([
            ...$request->validated(),
            'order' => $order,
        ]);

        $this->successFlash('Stop added.');

        return back();
    }

    public function update(TransportStopRequest $request, TransportStop $stop)
    {
        $stop->update($request->validated());
        $this->successFlash('Stop updated.');

        return back();
    }

    public function destroy(TransportStop $stop)
    {
        $stop->delete();
        $this->successFlash('Stop removed.');

        return back();
    }
}
