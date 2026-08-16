<?php

namespace App\Http\Controllers;

use App\Support\ApplicationCatalog;
use App\Models\Setting;

class RulesController extends Controller
{
    public function __invoke()
    {
        $categories = collect();

        try {
            $categories = ApplicationCatalog::categories(true);
        } catch (\Throwable $exception) {
            report($exception);
        }

        return view('rules', [
            'categories' => $categories,
            'minimumAge' => Setting::integer('minimum_age', 15),
            'reapplyCooldownDays' => Setting::integer('reapply_cooldown_days', 14),
            'requireDiscordGuild' => Setting::bool('require_discord_guild', false),
        ]);
    }
}
