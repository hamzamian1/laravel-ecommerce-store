<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'name',
        'sku',
        'price',
        'category', // Boys/Girls
        'subcategory', // Pants/Shirts
        'stock_quantity',
        'image_path',
        'secondary_image_path',
        'sizes',
        'types',
        'description',
        'discount_price',
        'stitched_price',
        'color',
        'variant_group_id',
        'is_featured',
        'is_top5',
    ];

    protected $casts = [
        'is_featured' => 'boolean',
        'is_top5'     => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($product) {
            if (empty($product->sku)) {
                $product->sku = self::generateSku();
            }
        });
    }

    public static function generateSku()
    {
        $latestId = \DB::table('products')->max('id');
        $nextId = $latestId ? $latestId + 1 : 1;
        return 'OXY-' . (1000 + $nextId);
    }

    /**
     * Get the effective price based on selected type.
     * Stitched → stitched_price, Unstitched → discount_price ?? price
     */
    public function getEffectivePrice(?string $type = null): float
    {
        if ($type && strtolower(trim($type)) === 'stitched' && $this->stitched_price) {
            return (float) $this->stitched_price;
        }
        return (float) ($this->discount_price ?? $this->price);
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }

    public function reviews()
    {
        return $this->hasMany(Review::class)->where('is_approved', true)->latest();
    }

    public function getAverageRatingAttribute()
    {
        return $this->reviews()->avg('rating') ?: 0;
    }

    public function colorVariants()
    {
        if (!$this->variant_group_id) return collect();
        return Product::where('variant_group_id', $this->variant_group_id)
                      ->where('id', '!=', $this->id)
                      ->get();
    }

    /**
     * Ensure 'sizes' fallback to empty string if null,
     * to prevent Integrity constraint violation as DB column is NOT NULL.
     */
    public function setSizesAttribute($value)
    {
        $this->attributes['sizes'] = $value ?? '';
    }
}
