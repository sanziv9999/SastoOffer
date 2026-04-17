<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $deals = DB::table('images')
            ->where('imageable_type', 'App\\Models\\Deal')
            ->select('imageable_id')
            ->distinct()
            ->pluck('imageable_id');

        foreach ($deals as $dealId) {
            $images = DB::table('images')
                ->where('imageable_type', 'App\\Models\\Deal')
                ->where('imageable_id', $dealId)
                ->orderBy('sort_order')
                ->orderBy('id')
                ->get();

            $hasFeature = $images->contains(fn ($img) => $img->attribute_name === 'feature_photo');

            foreach ($images->values() as $position => $img) {
                $attributeName = $img->attribute_name;

                if ($position === 0 && !$hasFeature) {
                    $attributeName = 'feature_photo';
                }

                DB::table('images')
                    ->where('id', $img->id)
                    ->update([
                        'sort_order' => $position,
                        'attribute_name' => $attributeName,
                    ]);
            }
        }
    }

    public function down(): void
    {
        // no rollback needed
    }
};
