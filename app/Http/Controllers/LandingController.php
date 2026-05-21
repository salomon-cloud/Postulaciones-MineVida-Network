<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Support\ApplicationCatalog;

class LandingController extends Controller
{
    public function __invoke()
    {
        $categories = ApplicationCatalog::categories();

        return view('welcome', [
            'minimumAge' => Setting::integer('minimum_age', 15),
            'applicationsOpen' => Setting::bool('applications_open', true),
            'applicationAreas' => $categories->isNotEmpty()
                ? $categories->take(4)
                : collect(ApplicationCatalog::types())->map(fn ($label, $slug) => (object) [
                    'slug' => $slug,
                    'name' => $label,
                    'summary' => 'Formulario de postulacion para '.$label.'.',
                    'icon' => str($label)->substr(0, 2)->upper()->toString(),
                    'is_open' => true,
                ])->take(4),
        ]);
    }
}
