<?php

namespace App\Services;

class ListingImageService
{
    public function saveImages($listing, array $images): void
    {
        foreach ($images as $index => $file) {

            $path = $file->store(
                $listing->getTable() . '/' . $listing->id,
                'public'
            );

            $listing->images()->create([
                'path' => $path,
                'position' => $index,
                'is_main' => $index === 0,
            ]);
        }
    }
}