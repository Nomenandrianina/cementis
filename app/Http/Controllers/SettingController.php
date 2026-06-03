<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index()
    {
        $settings = Setting::all()->keyBy('key');
        return view('settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'parent_zone_delay_hours' => 'required|integer|min:1|max:720',
        ]);

        Setting::set('parent_zone_delay_hours', $request->parent_zone_delay_hours);

        return back()->with('success', 'Paramètres mis à jour.');
    }
}