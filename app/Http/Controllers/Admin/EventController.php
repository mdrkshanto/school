<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\EventRequest;
use App\Models\Event;
use Illuminate\Http\Request;

class EventController extends Controller
{
    public function index(Request $request)
    {
        $scope = $request->scope ?? 'upcoming';

        $query = Event::query()
            ->when($scope === 'upcoming', fn ($q) => $q->where('starts_at', '>=', now()))
            ->when($scope === 'past', fn ($q) => $q->where('starts_at', '<', now()))
            ->when($request->search, fn ($q, $s) => $q->where('title', 'LIKE', "%{$s}%"))
            ->orderBy('starts_at');

        return $this->render('admin/Events/Index', [
            'events' => $this->paginated($query->paginate(15)->withQueryString()),
            'filters' => $request->only(['search', 'scope']),
        ]);
    }

    public function store(EventRequest $request)
    {
        Event::query()->create([
            ...$request->validated(),
            'user_id' => auth()->id(),
        ]);
        $this->successFlash('Event created.');

        return redirect()->route('admin.events.index');
    }

    public function update(EventRequest $request, Event $event)
    {
        $event->update($request->validated());
        $this->successFlash('Event updated.');

        return redirect()->route('admin.events.index');
    }

    public function destroy(Event $event)
    {
        $event->delete();
        $this->successFlash('Event deleted.');

        return back();
    }
}
