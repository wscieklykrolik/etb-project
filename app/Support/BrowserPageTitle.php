<?php

namespace App\Support;

use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BrowserPageTitle
{
    public static function fromRequest(Request $request): string
    {
        $route = $request->route();
        $routeName = $route?->getName();
        $parameters = $route?->parameters() ?? [];

        return match ($routeName) {
            'home' => 'Strona główna',
            'dashboard' => 'Panel użytkownika',
            'profile.edit' => 'Panel konta',
            'login' => 'Logowanie',
            'register' => 'Rejestracja',
            'register.verify.notice', 'register.verify' => 'Kod rejestracyjny',
            'password.request' => 'Przypomnienie hasła',
            'password.reset', 'password.store' => 'Reset hasła',
            'password.confirm' => 'Potwierdzenie hasła',
            'verification.notice', 'verification.verify' => 'Weryfikacja adresu e-mail',
            'news.index' => 'Aktualności',
            'news.show', 'admin.news.preview' => self::parameterTitle($parameters, 'news', 'title', 'Aktualność'),
            'news.create' => 'Dodaj aktualność',
            'news.edit' => 'Edytuj aktualność',
            'club' => 'Klub',
            'club.history' => 'Historia',
            'club.board' => 'Władze klubu',
            'club.venue' => 'Obiekt',
            'club.business' => 'Oferta biznesowa',
            'club.success' => 'Sukcesy',
            'club.sponsors' => 'Sponsorzy',
            'club.contact', 'contact' => 'Kontakt',
            'schedule' => 'Rozgrywki',
            'schedule.matches.show' => self::matchTitle($parameters),
            'schedule.lzkosz' => 'Terminarz ŁZKosz',
            'schedule.table' => 'Tabela',
            'schedule.3x3', 'three-x-three.tournaments.index', 'three-x-three.tournaments.show' => self::parameterTitle($parameters, 'tournament', 'name', 'Turnieje 3x3'),
            'three-x-three.teams.show' => self::parameterTitle($parameters, 'team', 'name', 'Drużyna 3x3'),
            'team' => 'Drużyna',
            'team.players' => 'Zawodnicy',
            'team.players.show' => self::parameterTitle($parameters, 'player', 'full_name', 'Zawodnik'),
            'team.staff' => 'Sztab szkoleniowy',
            'team.3x3', 'team3x3.players' => 'Drużyna 3x3',
            'tickets' => 'Bilety',
            'academy' => 'Akademia',
            'academy.groups.show' => self::parameterTitle($parameters, 'group', 'name', 'Grupa akademii'),
            'shop.index' => 'Sklep',
            'shop.show' => self::parameterTitle($parameters, 'product', 'name', 'Produkt'),
            'cart.index' => 'Koszyk',
            'checkout.shipping' => 'Dostawa',
            'checkout.payment' => 'Płatność',
            'checkout.confirmation' => 'Potwierdzenie zamówienia',
            'players.index' => 'Zawodnicy',
            'players.show' => self::parameterTitle($parameters, 'player', 'full_name', 'Zawodnik'),
            'players.create' => 'Dodaj zawodnika',
            'players.edit' => 'Edytuj zawodnika',
            'matches.index' => 'Mecze',
            'matches.show' => self::matchTitle($parameters),
            'matches.create' => 'Dodaj mecz',
            'matches.edit' => 'Edytuj mecz',
            'admin.dashboard' => 'Panel administracyjny',
            'admin.matches.create' => 'Dodaj mecz',
            'admin.products.index' => 'Produkty',
            'admin.products.create' => 'Dodaj produkt',
            'admin.products.edit' => 'Edycja produktu',
            'admin.product-filters.index' => 'Filtry sklepu',
            'admin.categories.index' => 'Kategorie',
            'admin.categories.create' => 'Dodaj kategorię',
            'admin.categories.edit' => 'Edycja kategorii',
            'admin.orders.index' => 'Zamówienia',
            'admin.orders.show' => 'Szczegóły zamówienia',
            'admin.users.search' => 'Użytkownicy',
            default => self::fallbackTitle($routeName),
        };
    }

    private static function parameterTitle(array $parameters, string $key, string $attribute, string $fallback): string
    {
        $value = $parameters[$key] ?? null;
        $title = is_object($value) ? data_get($value, $attribute) : null;

        return is_string($title) && trim($title) !== '' ? trim($title) : $fallback;
    }

    private static function matchTitle(array $parameters): string
    {
        $match = $parameters['match'] ?? null;
        $opponentName = is_object($match) ? data_get($match, 'opponent_name') : null;

        return is_string($opponentName) && trim($opponentName) !== '' ? 'ETB - '.trim($opponentName) : 'Mecz';
    }

    private static function fallbackTitle(?string $routeName): string
    {
        if (! is_string($routeName) || $routeName === '') {
            return 'Strona główna';
        }

        if (Str::startsWith($routeName, 'admin.')) {
            return 'Panel administracyjny';
        }

        return 'ETB Łódź';
    }
}