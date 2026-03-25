<?php

namespace App\Services;

use App\Models\Address;
use App\Models\Category;
use Illuminate\Support\Arr;

class DealMetadataSuggestionService
{
    /**
     * Very light-weight keyword heuristic (no external AI calls).
     * This is intended to provide deterministic suggestions based on title/description.
     */
    public function suggest(string $title, ?string $description = null): array
    {
        $plain = $this->toPlainText($title.' '.($description ?? ''));
        $text = mb_strtolower($plain);

        $category = $this->inferCategory($text);
        $categoryId = $category?->id;
        $primaryCategoryId = $category?->parent_id ?? $categoryId;
        $categorySlug = $category?->slug;

        $businessType = $this->inferBusinessType($text, $categorySlug);

        [$province, $district, $tole, $municipality] = $this->inferAddress($text);

        $tags = $this->inferTags($text, $categorySlug, $province, $district, $tole, $municipality, $businessType);

        return [
            'categoryId' => $categoryId,
            'primaryCategoryId' => $primaryCategoryId,
            'subcategoryId' => ($category?->parent_id ? $categoryId : null),
            'businessType' => $businessType,
            'province' => $province,
            'district' => $district,
            'tole' => $tole,
            'municipality' => $municipality,
            'tags' => $tags,
        ];
    }

    private function toPlainText(string $value): string
    {
        $value = strip_tags($value);
        // contentEditable often sends `&nbsp;` entities; normalize to a real space
        // so it doesn't become the token "nbsp" later.
        $value = html_entity_decode((string) $value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $value = str_replace(["\xc2\xa0", '&nbsp;'], ' ', (string) $value);
        $value = trim(preg_replace('/\s+/u', ' ', (string) $value));
        return $value ?? '';
    }

    private function tokenize(string $text): array
    {
        $tokens = preg_split('/[^a-zA-Z0-9]+/u', $text, -1, PREG_SPLIT_NO_EMPTY);
        $tokens = array_map(fn ($t) => mb_strtolower($t), $tokens ?? []);

        $stop = [
            'this','that','with','from','into','upon','your','their','there','about','offer','deal','discount','off',
            'special','new','best','sale','price','rs','npr','amount','save','today','now','limited','todayonly',
            // common stopwords
            'and','or','the','a','an','at','in','of','for','with','to','from','by','on','off',
            // HTML entity artifact
            'nbsp',
        ];

        return array_values(array_filter($tokens, function ($t) use ($stop) {
            if (mb_strlen($t) <= 2) return false;
            if (in_array($t, $stop, true)) return false;
            return true;
        }));
    }

    private function inferCategory(string $text): ?Category
    {
        $textTokens = $this->tokenize($text);
        if (empty($textTokens)) return null;

        $tokenSet = array_fill_keys($textTokens, true);

        // Soft keyword-to-category group hints (boost only; actual selection is token-overlap).
        $categoryKeywordMap = [
            'food' => ['restaurant', 'dining', 'food', 'cafe', 'hotel', 'lunch', 'dinner', 'brunch', 'takeout', 'delivery'],
            'beauty' => ['spa', 'salon', 'skincare', 'massage', 'facial', 'haircut', 'manicure', 'grooming', 'beauty'],
            'travel' => ['hotel', 'tour', 'trip', 'vacation', 'resort', 'booking', 'adventure', 'travel'],
            'electronics' => ['electronics', 'laptop', 'computer', 'phone', 'smartphone', 'gadget', 'audio', 'gaming', 'tablet'],
            'entertainment' => ['movies', 'concert', 'tickets', 'event', 'fun', 'nightlife', 'experience', 'activity', 'entertainment'],
        ];

        $keywordHits = [];
        foreach ($categoryKeywordMap as $groupSlug => $keywords) {
            $hits = 0;
            foreach ($keywords as $kw) {
                if (mb_stripos($text, $kw) !== false) $hits++;
            }
            if ($hits > 0) $keywordHits[$groupSlug] = $hits;
        }

        $categories = Category::query()
            ->active()
            ->with('parent:id,name,slug,parent_id')
            ->get(['id','name','slug','parent_id']);

        if ($categories->isEmpty()) return null;

        $best = null;
        $bestScore = 0;

        foreach ($categories as $cat) {
            $score = 0;

            // Token overlap between (category name/slug/parent) and input.
            $catKeywords = [];
            $catKeywords = array_merge($catKeywords, $this->tokenize((string) ($cat->name ?? '')));
            $catKeywords = array_merge($catKeywords, $this->tokenize((string) ($cat->slug ?? '')));
            if ($cat->parent) {
                $catKeywords = array_merge($catKeywords, $this->tokenize((string) ($cat->parent->name ?? '')));
                $catKeywords = array_merge($catKeywords, $this->tokenize((string) ($cat->parent->slug ?? '')));
            }
            $catKeywords = array_values(array_unique($catKeywords));

            $overlap = 0;
            foreach ($catKeywords as $kw) {
                if (isset($tokenSet[$kw])) {
                    $score += 6;
                    $overlap++;
                }
            }

            if ($overlap === 0) continue;

            // Extra boost if we detected one of the group-slugs in the input and this category belongs to it.
            if (!empty($keywordHits) && !empty($cat->slug)) {
                foreach ($keywordHits as $groupSlug => $hits) {
                    if ((string)$cat->slug === (string)$groupSlug) {
                        $score += (int) $hits * 4;
                    }
                }
            }

            if ($score > $bestScore) {
                $bestScore = $score;
                $best = $cat;
            }
        }

        // Select if we have meaningful overlap.
        return ($bestScore >= 6) ? $best : null;
    }

    private function inferBusinessType(string $text, ?string $categorySlug = null): ?string
    {
        $productKeywords = ['electronics','laptop','computer','phone','smartphone','gadget','audio','gaming','tablet','accessories'];
        $serviceKeywords = ['restaurant','dining','cafe','spa','salon','massage','facial','hotel','tour','booking','delivery','repair'];

        // Category bias.
        if ($categorySlug === 'electronics') {
            $productKeywords[] = 'electronics';
        }

        $prodHits = 0;
        foreach ($productKeywords as $kw) {
            if (mb_stripos($text, $kw) !== false) $prodHits++;
        }

        $serviceHits = 0;
        foreach ($serviceKeywords as $kw) {
            if (mb_stripos($text, $kw) !== false) $serviceHits++;
        }

        if ($prodHits > 0 && $serviceHits > 0) return 'hybrid';
        if ($prodHits > 0) return 'product';
        if ($serviceHits > 0) return 'service';

        // Fallback: use service (common default for deals).
        return 'service';
    }

    private function inferAddress(string $text): array
    {
        $districts = Address::query()
            ->select('district')
            ->distinct()
            ->pluck('district')
            ->filter()
            ->values()
            ->all();
        if (empty($districts)) {
            return [null, null, null, null];
        }

        $bestDistrict = null;
        $bestProvince = null;
        $bestLen = 0;
        foreach ($districts as $district) {
            $d = mb_strtolower((string) $district);
            if ($d === '') continue;
            if (mb_stripos($text, $d) !== false) {
                $len = mb_strlen($d);
                if ($len > $bestLen) {
                    $bestDistrict = $district;
                    $bestLen = $len;
                    $bestProvince = Address::query()->where('district', $bestDistrict)->value('province');
                }
            }
        }

        if (! $bestDistrict) {
            return [null, null, null, null];
        }

        // Infer tole within district.
        $toleCandidates = Address::query()
            ->where('district', $bestDistrict)
            ->select('tole')
            ->distinct()
            ->pluck('tole')
            ->filter()
            ->values()
            ->all();

        $bestTole = null;
        $bestToleLen = 0;
        foreach ($toleCandidates as $tole) {
            $t = mb_strtolower((string) $tole);
            if ($t === '') continue;
            if (mb_stripos($text, $t) !== false) {
                $len = mb_strlen($t);
                if ($len > $bestToleLen) {
                    $bestTole = $tole;
                    $bestToleLen = $len;
                }
            }
        }

        // Infer municipality (prefer municipality matched by tole; else by district).
        $municipality = null;
        if ($bestTole) {
            $municipality = Address::query()
                ->where('district', $bestDistrict)
                ->where('tole', $bestTole)
                ->value('municipality');
        }

        if (! $municipality) {
            $municipality = Address::query()
                ->where('district', $bestDistrict)
                ->value('municipality');
        }

        return [$bestProvince, $bestDistrict, $bestTole, $municipality];
    }

    private function inferTags(
        string $text,
        ?string $categorySlug = null,
        ?string $province = null,
        ?string $district = null,
        ?string $tole = null,
        ?string $municipality = null,
        ?string $businessType = null
    ): array
    {
        $suggested = [];

        // Include inferred address + business type as tags (so the vendor can see them and
        // they can be stored in `deal.highlights`).
        if ($businessType) {
            $suggested[] = $businessType;
        }
        if ($province) {
            $suggested[] = $province;
        }
        if ($district) {
            $suggested[] = $district;
        }
        if ($tole) {
            $suggested[] = $tole;
        }
        if ($municipality) {
            $suggested[] = $municipality;
        }

        // Pull tags from deal text only (title+description).
        // No hard-coded "static chip" lists; this keeps AI Suggest deterministic.
        $tokens = $this->tokenize($text);
        $tokens = array_slice($tokens, 0, 12);
        $suggested = array_merge($suggested, $tokens);

        // Normalize tags to `kebab-case`.
        $normalized = array_map(function ($t) {
            $t = trim((string) $t);
            $t = mb_strtolower($t);
            $t = preg_replace('/[^a-z0-9]+/u', '-', $t);
            $t = trim((string) $t, '-');
            return $t;
        }, $suggested);

        $normalized = array_values(array_filter(array_unique($normalized), fn($t) => mb_strlen($t) >= 3));

        return array_slice($normalized, 0, 15);
    }
}

