<?php

namespace App\Http\Controllers\Guest;

use App\Http\Controllers\Controller;
use App\Models\CafeProfile;
use App\Services\CafePackageCatalog;
use Illuminate\View\View;

class WelcomeController extends Controller
{
    public function __construct(
        private readonly CafePackageCatalog $packageCatalog,
    ) {
    }

    public function index(): View
    {
        return view('guest.home.welcome', [
            'profile' => CafeProfile::query()->first(),
            'recommendedPackages' => $this->packageCatalog->all()->take(5)->all(),
            'featuredPackages' => $this->packageCatalog->featured([
                'coffee-date-corner',
                'work-brew-table',
                'live-music-hangout',
            ])->all(),
        ]);
    }

    public function about(): View
    {
        return view('guest.home.about', [
            'profile' => CafeProfile::query()->first(),
        ]);
    }
}
