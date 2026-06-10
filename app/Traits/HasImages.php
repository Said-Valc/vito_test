<?php

namespace App\Traits;

use App\Models\ListingImage;
use Illuminate\Support\Facades\Storage;

trait HasImages
{
    /**
     * Получить все изображения объявления (полиморфная связь)
     */
    public function images()
    {
        return $this->morphMany(ListingImage::class, 'listable');
    }

    /**
     * Получить главное изображение
     */
    public function mainImage()
    {
        return $this->morphOne(ListingImage::class, 'listable')->where('is_main', true);
    }

    /**
     * Получить URL главного изображения
     */
    public function getMainImageUrlAttribute(): string
    {
        $image = $this->mainImage;
        if ($image) {
            // ВОЗВРАЩАЕМ ТОЛЬКО ПУТЬ, БЕЗ asset()
            return $image->path;
        }
        return 'images/no-image.jpg';
    }

    /**
     * Получить все URL изображений
     */
    public function getImagesUrlsAttribute(): array
    {
        return $this->images->map(function($image) {
            // ВОЗВРАЩАЕМ ТОЛЬКО ПУТЬ, БЕЗ asset()
            return $image->path;
        })->toArray();
    }

    /**
     * Получить количество изображений
     */
    public function getImagesCountAttribute(): int
    {
        return $this->images()->count();
    }

    /**
     * Добавить изображения к объявлению
     */
    public function addImages(array $images): void
    {
        foreach ($images as $index => $file) {
            $path = $file->store($this->getImagePath(), 'public');
            
            $this->images()->create([
                'path' => $path,
                'position' => $index,
                'is_main' => $index === 0,
            ]);
        }
    }

    /**
     * Удалить все изображения объявления
     */
    public function deleteImages(): void
    {
        foreach ($this->images as $image) {
            Storage::disk('public')->delete($image->path);
            $image->delete();
        }
    }

    /**
     * Получить путь для сохранения изображений
     */
    protected function getImagePath(): string
    {
        return $this->getTable() . '/' . $this->id;
    }
}