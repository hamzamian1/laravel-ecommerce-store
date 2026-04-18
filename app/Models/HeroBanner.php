<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HeroBanner extends Model
{
    use HasFactory;
    protected $fillable = ['image_path', 'mobile_image_path', 'caption', 'is_active', 'sort_order', 'desktop_height', 'desktop_width', 'desktop_focus', 'mobile_focus'];
    protected $casts = [
        'is_active' => 'boolean',
    ];
}
