<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Routing\Controller as BaseController;
use Inertia\Inertia;
use Inertia\Response;

abstract class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    protected function successFlash(string $message, mixed $data = null): void
    {
        if ($data !== null) {
            session()->flash('flash.success', ['message' => $message, 'data' => $data]);

            return;
        }

        session()->flash('flash.success', ['message' => $message]);
    }

    protected function errorFlash(string $message): void
    {
        session()->flash('flash.error', ['message' => $message]);
    }

    protected function warningFlash(string $message): void
    {
        session()->flash('flash.warning', ['message' => $message]);
    }

    /**
     * Serialize a paginator into an Inertia-friendly payload.
     *
     * @return array<string, mixed>
     */
    protected function paginated(LengthAwarePaginator $paginator): array
    {
        return [
            'data' => $paginator->items(),
            'links' => $paginator->linkCollection()->toArray(),
            'total' => $paginator->total(),
            'per_page' => $paginator->perPage(),
            'current_page' => $paginator->currentPage(),
            'last_page' => $paginator->lastPage(),
        ];
    }

    protected function render(string $component, array $props = []): Response
    {
        return Inertia::render($component, $props);
    }
}
