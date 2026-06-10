<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AutoListing;
use App\Models\NedvizhimostListing;
use App\Models\ElektronikaListing;
use App\Models\HobbyListing;
use App\Models\RabotaVacancy;
use App\Models\RabotaResume;
use App\Models\UslugiListing;
use App\Models\LichnieVeschiListing;
use App\Models\DlyaDomaListing;
use App\Services\ListingService;
use App\Services\ListingImageService;
use Illuminate\Http\Request;

class ListingController extends Controller
{
    public function index()
    {
        $listings = collect();

        $models = [
            'auto' => AutoListing::class,
            'nedvizhimost' => NedvizhimostListing::class,
            'elektronika' => ElektronikaListing::class,
            'hobby' => HobbyListing::class,
            'vacancy' => RabotaVacancy::class,
            'resume' => RabotaResume::class,
            'uslugi' => UslugiListing::class,
            'lichnie_veschi' => LichnieVeschiListing::class,
            'dlya_doma' => DlyaDomaListing::class,
        ];

        foreach ($models as $type => $model) {
            $items = $model::with([
                'user:id,name',
                'images'
            ])
                ->where('is_active', true)
                ->latest()
                ->get()
                ->map(function ($item) use ($type) {

                    $image = null;

                    if ($item->main_image_url) {
                        $image =
                            url('/storage/' . $item->main_image_url);
                    }

                    return [
                        'id' => $item->id,
                        'type' => $type,
                        'title' => $item->title,
                        'description' => $item->description ?? '',
                        'price' => $item->price,
                        'city' => $item->city,
                        'image' => $image,
                        'created_at' => $item->created_at,
                        'user' => $item->user,
                    ];
                });

            $listings = $listings->concat($items);
        }

        return response()->json(
            $listings
                ->sortByDesc('created_at')
                ->values()
        );
    }

    public function store(
        Request $request,
        ListingService $listingService,
        ListingImageService $imageService
    )
    {
        $validated = $request->validate([
            'category_type' => 'required',
            'title' => 'required|max:255',
            'description' => 'required',
            'city' => 'required',
            'phone' => 'required',
            'images.*' => 'image|max:3072'
        ]);

        $data = $request->all();

        $data['user_id'] = auth()->id();

        $listing = $listingService->create($data);

        if ($request->hasFile('images')) {

            $imageService->saveImages(
                $listing,
                $request->file('images')
            );
        }

        return response()->json([
            'success' => true,
            'listing_id' => $listing->id
        ]);
    }
}