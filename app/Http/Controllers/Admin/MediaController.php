<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\MediaService;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\Storage;

class MediaController extends Controller
{
    public function __construct(private readonly Filesystem $disk) {}

    public function show(string $path)
    {
        $disk = Storage::disk('local');

        if (! $disk->exists($path)) {
            abort(404);
        }

        return $disk->response($path);
    }
}