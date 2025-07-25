<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Transaction extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'category_id',
        'name',
        'amount',
        'description',
        'image',
        'date',
        'merchant_name',
        'merchant_address',
    ];


    protected $casts = [
        'date' => 'date',
        'amount' => 'decimal:2',
    ];


    //relationship Transaction ini punya satu category
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    //Relationship: Transaction ini bisa punya banyak item
    public function items(): HasMany
    {
        return $this->hasMany(Item::class);
    }

    public function scopeIncome($query)
    {
        return $query->whereHas('category', function($q) {
            $q->where('type', 'income');
        });
    }

    public function scopeExpense($query)
    {
        return $query->whereHas('category', function($q) {
            $q->where('type', 'expense');
        });
    }

    public function getImageUrlAttribute()
    {
        if ($this->image) {
            return Storage::url($this->image);
        }

        return null;
    }
}
