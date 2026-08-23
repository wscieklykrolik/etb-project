<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\AcademyCalendarNote;
use App\Models\AcademyGroup;
use App\Models\AcademyTraining;
use App\Models\AdminNotification;
use App\Models\AppSetting;
use App\Models\ClubSection;
use App\Models\FaqQuestion;
use App\Models\LeagueStanding;
use App\Models\News;
use App\Models\Order;
use App\Models\Player;
use App\Models\Product;
use App\Models\ProductFilterGroup;
use App\Models\Sponsor;
use App\Models\SponsorCategory;
use App\Models\TeamMatch;
use App\Models\TeamStaff;
use App\Models\ThreeXThreeMember;
use App\Models\ThreeXThreeTournament;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(Request $request): View
    {
        $user = $request->user();
        $userRoleFilter = in_array($request->query('user_role'), User::roles(), true)
            ? (string) $request->query('user_role')
            : 'all';
        $marketingConsentFilter = in_array($request->query('marketing_consent'), ['yes', 'no'], true)
            ? (string) $request->query('marketing_consent')
            : 'all';
        $allowedSections = [
            'dashboard',
            'users',
            'matches',
            'club-content',
            'academy',
            'faq',
            'news',
            'players',
            'staff',
            'three-x-three',
            'tournaments',
            'notifications-history',
            'league-table',
            'sponsors',
            'shop',
            'account',
        ];
        $activeSection = in_array($request->query('section'), $allowedSections, true)
            ? (string) $request->query('section')
            : 'dashboard';

        $upcomingMatches = TeamMatch::query()
            ->with(['opponent', 'sportsHall'])
            ->where('status', TeamMatch::STATUS_UPCOMING)
            ->orderBy('match_date')
            ->get();

        $finishedMatches = TeamMatch::query()
            ->with(['opponent', 'sportsHall'])
            ->where('status', TeamMatch::STATUS_FINISHED)
            ->orderByDesc('match_date')
            ->get();

        $publishedNews = News::query()
            ->with(['author', 'images'])
            ->active()
            ->published()
            ->latest()
            ->get();

        $scheduledNews = News::query()
            ->with(['author', 'images'])
            ->scheduled()
            ->orderBy('publish_at')
            ->get();

        $players = Player::query()
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();

        $staff = TeamStaff::query()->orderBy('sort_order')->orderBy('name')->get();
        $threeXThreeMembers = ThreeXThreeMember::query()->orderByDesc('is_coach')->orderBy('sort_order')->orderBy('name')->get();
        $threeXThreeTournaments = ThreeXThreeTournament::query()
            ->with(['categories', 'teams.players', 'groups.teams', 'groups.matches.teamOne', 'groups.matches.teamTwo', 'matches.teamOne', 'matches.teamTwo'])
            ->orderByDesc('date')
            ->get();
        SponsorCategory::syncDefaults();
        $sponsorCategories = SponsorCategory::query()
            ->withCount('sponsors')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
        $sponsors = Sponsor::query()
            ->with('category')
            ->leftJoin('sponsor_categories', 'sponsors.sponsor_category_id', '=', 'sponsor_categories.id')
            ->select('sponsors.*')
            ->orderBy('sponsor_categories.sort_order')
            ->orderBy('sponsors.sort_order')
            ->orderBy('sponsors.name')
            ->get();
        ClubSection::syncDefaults();
        $clubSections = ClubSection::query()
            ->with('images')
            ->whereIn('slug', array_keys(ClubSection::SECTIONS))
            ->orderBy('sort_order')
            ->get();
        $leagueStandings = LeagueStanding::query()
            ->with('opponent')
            ->orderBy('position')
            ->get();
        $academyGroups = AcademyGroup::query()
            ->with(['trainers', 'messages', 'trainings'])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
        $academyTrainingDate = $request->date('academy_training_date')?->format('Y-m-d');
        $academyTrainings = AcademyTraining::query()
            ->with('group')
            ->when($academyTrainingDate, fn ($query) => $query->whereDate('starts_at', $academyTrainingDate))
            ->orderByDesc('starts_at')
            ->take(80)
            ->get();
        $academyCalendarNotes = AcademyCalendarNote::query()
            ->orderByDesc('starts_on')
            ->take(50)
            ->get();
        $faqQuestions = FaqQuestion::query()
            ->orderBy('sort_order')
            ->orderBy('question')
            ->get();
        $adminNotifications = AdminNotification::query()
            ->with(['actor', 'acceptedBy'])
            ->latest()
            ->get();
        $notificationHistory = AdminNotification::query()
            ->withTrashed()
            ->with(['actor', 'acceptedBy'])
            ->latest()
            ->paginate(20, ['*'], 'notifications_page')
            ->withQueryString();
        $unreadNotificationsCount = $adminNotifications->whereNull('read_at')->count();
        $shopStats = [
            'orders' => Order::count(),
            'products' => Product::count(),
            'publishedProducts' => Product::where('is_published', true)->count(),
            'categories' => \App\Models\Category::count(),
            'filterGroups' => ProductFilterGroup::count(),
        ];
        $users = $user->role === User::ROLE_ADMIN
            ? User::query()
                ->with('fanProfile')
                ->when($userRoleFilter !== 'all', fn ($query) => $query->where('role', $userRoleFilter))
                ->when($marketingConsentFilter !== 'all', function ($query) use ($marketingConsentFilter): void {
                    $query->whereHas('fanProfile', fn ($profileQuery) => $profileQuery->where('marketing_email_consent', $marketingConsentFilter === 'yes'));
                })
                ->orderBy('name')
                ->paginate(10, ['*'], 'users_page')
                ->withQueryString()
            : collect();

        return view('profile.edit', [
            'user' => $user,
            'activeSection' => $activeSection,
            'isAdmin' => $user->role === User::ROLE_ADMIN,
            'isEmployee' => $user->role === User::ROLE_EMPLOYEE,
            'isAthlete' => $user->role === User::ROLE_ATHLETE,
            'users' => $users,
            'userRoleFilter' => $userRoleFilter,
            'marketingConsentFilter' => $marketingConsentFilter,
            'athleteProfile' => $user->role === User::ROLE_ATHLETE
                ? $user->athleteProfile()->first()
                : null,
            'availableRoles' => User::roles(),
            'upcomingMatches' => $upcomingMatches,
            'finishedMatches' => $finishedMatches,
            'publishedNews' => $publishedNews,
            'scheduledNews' => $scheduledNews,
            'players' => $players,
            'staff' => $staff,
            'threeXThreeMembers' => $threeXThreeMembers,
            'threeXThreeTournaments' => $threeXThreeTournaments,
            'sponsors' => $sponsors,
            'sponsorCategories' => $sponsorCategories,
            'clubSections' => $clubSections,
            'leagueStandings' => $leagueStandings,
            'defaultHomeLogo' => AppSetting::getValue('default_home_logo'),
            'academyGroups' => $academyGroups,
            'academyTrainings' => $academyTrainings,
            'academyTrainingDate' => $academyTrainingDate,
            'academyCalendarNotes' => $academyCalendarNotes,
            'faqQuestions' => $faqQuestions,
            'adminNotifications' => $adminNotifications,
            'notificationHistory' => $notificationHistory,
            'unreadNotificationsCount' => $unreadNotificationsCount,
            'shopStats' => $shopStats,
        ]);
    }

    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit');
    }

    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();
        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
