<?php

namespace App\Http\Controllers;

use App\Models\HeroBanner;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Services\AdminLogger;

class HeroBannerController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'images'         => 'required',
            'images.*'       => 'image|mimes:jpeg,png,jpg,webp|max:20480',
            'mobile_images.*'=> 'nullable|image|mimes:jpeg,png,jpg,webp|max:20480',
        ]);

        $mobileFiles = $request->file('mobile_images', []);

        // Deactivate old active banners so the new upload replaces them instantly
        HeroBanner::where('is_active', true)->update(['is_active' => false]);

        foreach ($request->file('images') as $index => $image) {
            $filename = Str::uuid() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('images/banners'), $filename);

            $mobileImagePath = null;
            if (!empty($mobileFiles[$index])) {
                $mFilename = Str::uuid() . '.' . $mobileFiles[$index]->getClientOriginalExtension();
                $mobileFiles[$index]->move(public_path('images/banners'), $mFilename);
                $mobileImagePath = 'images/banners/' . $mFilename;
            }

            HeroBanner::create([
                'image_path'        => 'images/banners/' . $filename,
                'mobile_image_path' => $mobileImagePath,
                'caption'           => $request->input('caption', ''),
                'is_active'         => true,
                'sort_order'        => HeroBanner::max('sort_order') + 1,
            ]);
        }

        // Log admin action
        AdminLogger::log('upload_hero_banners', 'HeroBanner', null, [
            'count' => count($request->file('images')),
        ]);

        return redirect()->route('dashboard')->with('banner_success', 'High Quality Hero Banner uploaded and applied successfully!');
    }

    public function destroy(HeroBanner $heroBanner)
    {
        if ($heroBanner->image_path && file_exists(public_path($heroBanner->image_path))) {
            @unlink(public_path($heroBanner->image_path));
        }
        AdminLogger::log('delete_hero_banner', 'HeroBanner', $heroBanner->id);
        $heroBanner->delete();

        return response()->json(['success' => true]);
    }

    public function toggleActive(HeroBanner $heroBanner)
    {
        $heroBanner->update(['is_active' => !$heroBanner->is_active]);
        AdminLogger::log('toggle_hero_banner', 'HeroBanner', $heroBanner->id, [
            'is_active' => $heroBanner->is_active,
        ]);
        return response()->json(['success' => true, 'is_active' => $heroBanner->is_active]);
    }
}

