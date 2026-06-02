<?php

namespace App\Http\Controllers\Guest;

use App\Http\Controllers\Controller;
use App\Models\CafeProfile;
use App\Services\CafePackageCatalog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PackageController extends Controller
{
    public function __construct(
        private readonly CafePackageCatalog $packageCatalog,
    ) {
    }

    public function index(Request $request): View
    {
        $filters = [
            'q' => $request->string('q')->trim()->toString(),
            'category' => $request->string('category')->trim()->toString(),
            'duration' => $request->string('duration')->trim()->toString(),
            'price' => $request->string('price')->trim()->toString(),
            'sort' => $request->string('sort')->trim()->toString() ?: 'latest',
        ];

        return view('guest.paket.paket', [
            'profile' => CafeProfile::query()->first(),
            'packages' => $this->packageCatalog->filter($filters)->all(),
            'categories' => $this->packageCatalog->categories()->prepend('Semua Kategori')->all(),
            'durations' => $this->packageCatalog->durations()->prepend('Semua Durasi')->all(),
            'filters' => $filters,
        ]);
    }
}
