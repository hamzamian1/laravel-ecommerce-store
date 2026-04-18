<?php

namespace App\Http\Controllers;

use App\Models\SubcategoryImage;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SubcategoryImageController extends Controller
{
    private $validSubcategories = ['Formals', 'Ready to Wear', 'Luxury Lawn', 'Unstitched'];

    public function store(Request $request, $subcategory)
    {
        if (!in_array($subcategory, $this->validSubcategories)) {
            return back()->with('subcat_error', 'Invalid subcategory.');
        }

        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,webp|max:5120',
        ]);

        // Delete old image if exists
        $existing = SubcategoryImage::where('subcategory', $subcategory)->first();
        if ($existing && $existing->image_path && file_exists(public_path($existing->image_path))) {
            @unlink(public_path($existing->image_path));
        }

        $image = $request->file('image');
        $filename = Str::uuid() . '.' . $image->getClientOriginalExtension();
        $image->move(public_path('images/subcategories'), $filename);
        $path = 'images/subcategories/' . $filename;

        SubcategoryImage::updateOrCreate(
            ['subcategory' => $subcategory],
            ['image_path' => $path]
        );

        return back()->with('subcat_success', "Image updated for {$subcategory}!");
    }

    public function destroy($subcategory)
    {
        if (!in_array($subcategory, $this->validSubcategories)) {
            return back()->with('subcat_error', 'Invalid subcategory.');
        }

        $image = SubcategoryImage::where('subcategory', $subcategory)->first();
        if ($image) {
            if ($image->image_path && file_exists(public_path($image->image_path))) {
                @unlink(public_path($image->image_path));
            }
            $image->delete();
        }

        return back()->with('subcat_success', "Image removed for {$subcategory}.");
    }
}
