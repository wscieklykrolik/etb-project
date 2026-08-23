<?php

namespace App\Http\Controllers;

use App\Models\FaqQuestion;
use App\Models\News;
use App\Models\Player;
use App\Models\Product;
use App\Models\Sponsor;
use App\Models\TeamMatch;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        $latestNews = News::query()
            ->with(['author', 'images'])
            ->active()
            ->published()
            ->latest('publish_at')
            ->latest()
            ->take(11)
            ->get();

        $lastFinishedMatch = TeamMatch::query()
            ->with(['opponent', 'sportsHall'])
            ->where('status', TeamMatch::STATUS_FINISHED)
            ->where(function ($query): void {
                $query->whereNull('publish_at')->orWhere('publish_at', '<=', now());
            })
            ->latest('match_date')
            ->first();

        $upcomingMatches = TeamMatch::query()
            ->with(['opponent', 'sportsHall'])
            ->where('status', TeamMatch::STATUS_UPCOMING)
            ->where('match_date', '>=', now())
            ->where(function ($query): void {
                $query->whereNull('publish_at')->orWhere('publish_at', '<=', now());
            })
            ->orderBy('match_date')
            ->take(2)
            ->get();

        $startingFive = Player::query()
            ->where('is_starting_five', true)
            ->orderBy('number')
            ->get()
            ->sortBy(fn (Player $player): array => [$player->positionOrder(), $player->number])
            ->take(5)
            ->values();

        $sponsors = Sponsor::query()
            ->with('category')
            ->where('sponsors.is_active', true)
            ->where(function ($query): void {
                $query->whereNull('sponsor_category_id')
                    ->orWhereHas('category', fn ($categoryQuery) => $categoryQuery->active());
            })
            ->leftJoin('sponsor_categories', 'sponsors.sponsor_category_id', '=', 'sponsor_categories.id')
            ->select('sponsors.*')
            ->orderBy('sponsor_categories.sort_order')
            ->orderBy('sponsors.sort_order')
            ->orderBy('sponsors.name')
            ->get();

        $shopProducts = Product::query()
            ->with('category')
            ->where('is_published', true)
            ->inRandomOrder()
            ->take(4)
            ->get();

        $faqQuestions = FaqQuestion::query()
            ->published()
            ->orderBy('sort_order')
            ->orderBy('question')
            ->get();
        $faqSchemaJson = $faqQuestions->isEmpty() ? null : json_encode([
            '@context' => 'https://schema.org',
            '@type' => 'FAQPage',
            'mainEntity' => $faqQuestions->map(fn (FaqQuestion $item) => [
                '@type' => 'Question',
                'name' => $item->question,
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text' => $item->answer,
                ],
            ])->values()->all(),
        ], JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);

        return view('home', [
            'heroNews' => $latestNews->take(5),
            'featuredArticles' => $latestNews->slice(5, 2),
            'moreArticles' => $latestNews->slice(7, 4),
            'lastFinishedMatch' => $lastFinishedMatch,
            'upcomingMatches' => $upcomingMatches,
            'startingFive' => $startingFive,
            'sponsors' => $sponsors,
            'shopProducts' => $shopProducts,
            'faqQuestions' => $faqQuestions,
            'faqSchemaJson' => $faqSchemaJson,
        ]);
    }
}
