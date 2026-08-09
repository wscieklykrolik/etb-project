<?php

namespace Database\Seeders;

use App\Enums\BasketballPosition;
use App\Models\AppSetting;
use App\Models\Category;
use App\Models\News;
use App\Models\Opponent;
use App\Models\Player;
use App\Models\Product;
use App\Models\ProductFilterGroup;
use App\Models\ProductFilterOption;
use App\Models\ProductVariantSize;
use App\Models\Sponsor;
use App\Models\SportsHall;
use App\Models\TeamMatch;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class ContentSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'admin@example.com')->first();

        if (! $admin) {
            return;
        }

        $this->seedSettings();
        $this->seedSportsHalls();
        $this->seedOpponents();
        $this->seedPlayers();
        $this->seedMatches();
        $this->seedNews($admin);
        $this->seedSponsors();
        $this->seedProducts();
    }

    private function seedSettings(): void
    {
        AppSetting::setValue('club_name', 'ETB Łódź');
        AppSetting::setValue('club_email', 'biuro@etb-lodz.pl');
        AppSetting::setValue('club_phone', '+48 42 123 45 67');
        AppSetting::setValue('club_address', 'ul. Koszykowa 1, 90-001 Łódź');
        AppSetting::setValue('facebook_url', 'https://www.facebook.com/p/Eat-The-Ball-61572240317030/');
        AppSetting::setValue('instagram_url', 'https://www.instagram.com/eat_the_ball/');
        AppSetting::setValue('youtube_url', 'https://www.youtube.com/@EatTheBall3x3');
        AppSetting::setValue('tiktok_url', 'https://www.tiktok.com/@eattheball_lodz');
    }

    private function seedSportsHalls(): void
    {
        $halls = [
            ['name' => 'Hala Sportowa MOSiR Łódź'],
            ['name' => 'Hala Sportowa Nr 1 w Łodzi'],
            ['name' => 'Hala Widowiskowo-Sportowa Łódź'],
            ['name' => 'Hala Sportowa Zgierz'],
            ['name' => 'Hala Sportowa Pabianice'],
        ];

        foreach ($halls as $hall) {
            SportsHall::firstOrCreate($hall);
        }
    }

    private function seedOpponents(): void
    {
        $opponents = [
            ['name' => 'KS Łódź'],
            ['name' => 'MKS Piotrków Trybunalski'],
            ['name' => 'UKS Zgierz'],
            ['name' => 'TKS Łask'],
            ['name' => 'Pabianice Basketball'],
            ['name' => 'KS Widzew Łódź'],
            ['name' => 'MKS Skierniewice'],
            ['name' => 'UKS Brzeziny'],
            ['name' => 'Tomaszów Mazowiecki KKS'],
            ['name' => 'ŁKS Łódź'],
            ['name' => 'KS Pabianice'],
            ['name' => 'MKS Kutno'],
        ];

        foreach ($opponents as $opponent) {
            Opponent::firstOrCreate($opponent);
        }
    }

    private function seedPlayers(): void
    {
        $players = [
            [
                'first_name' => 'Kacper',
                'last_name' => 'Wiśniewski',
                'number' => 4,
                'position' => 'point_guard',
                'date_of_birth' => '1998-03-15',
                'height' => 185,
                'weight' => 82,
                'is_starting_five' => true,
                'description' => 'Doświadczony rozgrywający z ponad 7-letnim stażem w III lidze. Znany z doskonałego widzenia boiska i celnych podań.',
                'publish_description' => true,
            ],
            [
                'first_name' => 'Michał',
                'last_name' => 'Kowalski',
                'number' => 7,
                'position' => 'shooting_guard',
                'date_of_birth' => '1997-07-22',
                'height' => 190,
                'weight' => 85,
                'is_starting_five' => true,
                'description' => 'Skuteczny rzucający obrońca, specjalista od rzutów za 3 punkty. Średnia 12 punktów na mecz w zeszłym sezonie.',
                'publish_description' => true,
            ],
            [
                'first_name' => 'Bartosz',
                'last_name' => 'Zieliński',
                'number' => 11,
                'position' => 'small_forward',
                'date_of_birth' => '1999-01-10',
                'height' => 198,
                'weight' => 93,
                'is_starting_five' => true,
                'description' => 'Wszechstronny skrzydłowy, świetny w obronie i w ataku. Jego wsady są ozdobą każdego meczu.',
                'publish_description' => true,
            ],
            [
                'first_name' => 'Piotr',
                'last_name' => 'Nowak',
                'number' => 21,
                'position' => 'power_forward',
                'date_of_birth' => '1996-11-05',
                'height' => 202,
                'weight' => 105,
                'is_starting_five' => true,
                'description' => 'Silny skrzydłowy, walczący pod koszem o każdą piłkę. Lider w zbiórkach w zespole.',
                'publish_description' => true,
            ],
            [
                'first_name' => 'Jakub',
                'last_name' => 'Adamczyk',
                'number' => 34,
                'position' => 'center',
                'date_of_birth' => '1995-05-18',
                'height' => 208,
                'weight' => 115,
                'is_starting_five' => true,
                'description' => 'Najwyższy zawodnik w drużynie, filar defensywy. Blokuje średnio 2 rzuty na mecz.',
                'publish_description' => true,
            ],
            [
                'first_name' => 'Mateusz',
                'last_name' => 'Lewandowski',
                'number' => 8,
                'position' => 'point_guard',
                'date_of_birth' => '2000-09-12',
                'height' => 183,
                'weight' => 78,
                'is_starting_five' => false,
                'description' => 'Młody, dynamiczny rozgrywający z dużym potencjałem. Szybki drybling i dobra decyzyjność.',
                'publish_description' => true,
            ],
            [
                'first_name' => 'Filip',
                'last_name' => 'Dąbrowski',
                'number' => 12,
                'position' => 'shooting_guard',
                'date_of_birth' => '1998-12-03',
                'height' => 188,
                'weight' => 84,
                'is_starting_five' => false,
                'description' => 'Obrońca z doskonałym instynktem do przechwytów. Średnio 2 przechwyty na mecz.',
                'publish_description' => true,
            ],
            [
                'first_name' => 'Szymon',
                'last_name' => 'Wójcik',
                'number' => 15,
                'position' => 'small_forward',
                'date_of_birth' => '1997-04-28',
                'height' => 196,
                'weight' => 90,
                'is_starting_five' => false,
                'description' => 'Skrzydłowy z dobrym rzutem ze średniego dystansu. Waleczny w defensywie.',
                'publish_description' => true,
            ],
            [
                'first_name' => 'Kamil',
                'last_name' => 'Kamiński',
                'number' => 23,
                'position' => 'power_forward',
                'date_of_birth' => '1999-08-14',
                'height' => 200,
                'weight' => 100,
                'is_starting_five' => false,
                'description' => 'Młody silny skrzydłowy, dobrze czujący się pod koszem. Przyszłość drużyny.',
                'publish_description' => true,
            ],
            [
                'first_name' => 'Marcin',
                'last_name' => 'Piotrowicz',
                'number' => 44,
                'position' => 'center',
                'date_of_birth' => '1996-02-20',
                'height' => 205,
                'weight' => 112,
                'is_starting_five' => false,
                'description' => 'Doświadczony center, świetnie ustawiający zasłony i walczący o pozycję pod koszem.',
                'publish_description' => true,
            ],
        ];

        foreach ($players as $player) {
            Player::updateOrCreate(
                ['number' => $player['number']],
                $player,
            );
        }
    }

    private function seedMatches(): void
    {
        $halls = SportsHall::pluck('id', 'name');
        $opponents = Opponent::pluck('id', 'name');

        $matches = [
            [
                'opponent_id' => $opponents['KS Łódź'],
                'opponent_name' => 'KS Łódź',
                'match_date' => Carbon::now()->subDays(14)->setTime(17, 0),
                'location' => 'Hala Sportowa MOSiR Łódź',
                'sports_hall_id' => $halls['Hala Sportowa MOSiR Łódź'],
                'is_home' => true,
                'our_score' => 78,
                'opponent_score' => 72,
                'status' => TeamMatch::STATUS_FINISHED,
                'season' => '2025/2026',
            ],
            [
                'opponent_id' => $opponents['MKS Piotrków Trybunalski'],
                'opponent_name' => 'MKS Piotrków Trybunalski',
                'match_date' => Carbon::now()->subDays(7)->setTime(18, 30),
                'location' => 'Hala Sportowa MOSiR Łódź',
                'sports_hall_id' => $halls['Hala Sportowa MOSiR Łódź'],
                'is_home' => true,
                'our_score' => 65,
                'opponent_score' => 71,
                'status' => TeamMatch::STATUS_FINISHED,
                'season' => '2025/2026',
            ],
            [
                'opponent_id' => $opponents['TKS Łask'],
                'opponent_name' => 'TKS Łask',
                'match_date' => Carbon::now()->subDays(3)->setTime(17, 0),
                'location' => 'Hala Sportowa w Łasku',
                'is_home' => false,
                'our_score' => 82,
                'opponent_score' => 68,
                'status' => TeamMatch::STATUS_FINISHED,
                'season' => '2025/2026',
            ],
            [
                'opponent_id' => $opponents['Pabianice Basketball'],
                'opponent_name' => 'Pabianice Basketball',
                'match_date' => Carbon::now()->addDays(4)->setTime(17, 0),
                'location' => 'Hala Sportowa MOSiR Łódź',
                'sports_hall_id' => $halls['Hala Sportowa MOSiR Łódź'],
                'is_home' => true,
                'status' => TeamMatch::STATUS_UPCOMING,
                'season' => '2025/2026',
                'is_ticketed' => true,
                'ticket_url' => route('tickets'),
            ],
            [
                'opponent_id' => $opponents['KS Widzew Łódź'],
                'opponent_name' => 'KS Widzew Łódź',
                'match_date' => Carbon::now()->addDays(11)->setTime(18, 0),
                'location' => 'Hala Sportowa Nr 1 w Łodzi',
                'sports_hall_id' => $halls['Hala Sportowa Nr 1 w Łodzi'],
                'is_home' => false,
                'status' => TeamMatch::STATUS_UPCOMING,
                'season' => '2025/2026',
                'is_ticketed' => true,
                'ticket_url' => route('tickets'),
            ],
            [
                'opponent_id' => $opponents['UKS Zgierz'],
                'opponent_name' => 'UKS Zgierz',
                'match_date' => Carbon::now()->addDays(18)->setTime(17, 0),
                'location' => 'Hala Sportowa MOSiR Łódź',
                'sports_hall_id' => $halls['Hala Sportowa MOSiR Łódź'],
                'is_home' => true,
                'status' => TeamMatch::STATUS_UPCOMING,
                'season' => '2025/2026',
            ],
        ];

        foreach ($matches as $match) {
            TeamMatch::create($match);
        }
    }

    private function seedNews(User $admin): void
    {
        $news = [
            [
                'title' => 'Zwycięski mecz z KS Łódź w III lidze ŁZKosz!',
                'content' => '<p>ETB Łódź odniosło kolejne ważne zwycięstwo w sezonie 2025/2026 III ligi ŁZKosz. Mecz z KS Łódź zakończył się wynikiem 78:72 na korzyść naszej drużyny.</p><p>Od samego początku mecz był bardzo wyrównany. Pierwsza kwarta zakończyła się prowadzeniem gości 20:18. W drugiej kwarcie nasi zawodnicy zdołali odrobić straty i na przerwę schodzili z jednopunktowym prowadzeniem 38:37.</p><p>Trzecia kwarta należała już zdecydowanie do ETB Łódź. Dzięki świetnej obronie i skutecznym kontrom udało się wypracować kilkunastopunktową przewagę. Ostatecznie mecz zakończył się wynikiem 78:72.</p><p>Najlepszym strzelcem meczu został Michał Kowalski, który zdobył 22 punkty, w tym 4 celne rzuty za 3 punkty.</p>',
                'excerpt' => 'ETB Łódź wygrywa z KS Łódź 78:72 w emocjonującym meczu III ligi ŁZKosz. Świetna druga połowa w wykonaniu naszych zawodników.',
                'author_id' => $admin->id,
                'publish_at' => Carbon::now()->subDays(13),
                'is_visible' => true,
            ],
            [
                'title' => 'Porażka z MKS Piotrków Trybunalski po zaciętym boju',
                'content' => '<p>Niestety tym razem musieliśmy uznać wyższość rywali. Mecz z MKS Piotrków Trybunalski zakończył się wynikiem 65:71.</p><p>Mecz był niezwykle wyrównany od pierwszej do ostatniej minuty. Przez całe spotkanie żadna z drużyn nie zdołała wypracować więcej niż kilkupunktowej przewagi. Niestety w końcówce to rywale okazali się skuteczniejsi.</p><p>Mimo porażki, zespół pokazał charakter i walczył do ostatnich sekund. Do samego końca mieliśmy szansę na odwrócenie losów meczu. Brakowało trochę szczęścia w ostatnich akcjach.</p><p>Kolejny mecz już za tydzień - tym razem podejmujemy TKS Łask na wyjeździe.</p>',
                'excerpt' => 'ETB Łódź przegrywa z MKS Piotrków Trybunalski 65:71 w zaciętym spotkaniu. Kolejny mecz już za tydzień.',
                'author_id' => $admin->id,
                'publish_at' => Carbon::now()->subDays(6),
                'is_visible' => true,
            ],
            [
                'title' => 'Wielkie zwycięstwo na wyjeździe! ETB Łódź lepsze od TKS Łask',
                'content' => '<p>Fantastyczne wieści z Łasku! ETB Łódź odniosło spektakularne zwycięstwo na wyjeździe, pokonując TKS Łask 82:68.</p><p>Od samego początku meczu nasi zawodnicy narzucili swoje tempo gry. Pierwsza kwarta zakończyła się wynikiem 24:14, co dało solidną podstawę do kontrolowania meczu. W drugiej kwarcie powiększyliśmy prowadzenie do 44:30.</p><p>Po przerwie TKS Łask próbował odrobić straty, jednak nasza defensywa działała doskonale. Kluczowe okazały się zbiórki w obronie i szybkie przejścia do ataku.</p><p>Gratulacje dla całego zespołu za świetny mecz! Kolejne spotkanie rozegramy u siebie z Pabianice Basketball.</p>',
                'excerpt' => 'ETB Łódź wygrywa na wyjeździe z TKS Łask 82:68! Świetna postawa całego zespołu w meczu III ligi ŁZKosz.',
                'author_id' => $admin->id,
                'publish_at' => Carbon::now()->subDays(2),
                'is_visible' => true,
            ],
            [
                'title' => 'Nabór do Akademii ETB - sezon 2026/2027',
                'content' => '<p>Akademia ETB Łódź ogłasza nabór dzieci i młodzieży do grup treningowych na sezon 2026/2027!</p><p>Jeśli Twoje dziecko kocha koszykówkę i chce rozwijać swoje umiejętności pod okiem doświadczonych trenerów, to idealny moment, aby dołączyć do naszej akademii.</p><p><strong>Oferujemy:</strong></p><ul><li>Profesjonalne treningi koszykarskie dla dzieci od 7 roku życia</li><li>Wykwalifikowaną kadrę trenerską</li><li>Regularne udział w turniejach i ligach młodzieżowych</li><li>Możliwość rozwoju i awansu do drużyn seniorskich</li></ul><p><strong>Zapisy i informacje:</strong> biuro@etb-lodz.pl</p><p>Dołącz do nas i zostań częścią koszykarskiej rodziny ETB Łódź!</p>',
                'excerpt' => 'Rusza nabór do Akademii ETB na sezon 2026/2027! Zapraszamy dzieci i młodzież do zapisów.',
                'author_id' => $admin->id,
                'publish_at' => Carbon::now()->subDay(),
                'is_visible' => true,
            ],
            [
                'title' => 'Zapowiedź meczu: ETB Łódź vs Pabianice Basketball',
                'content' => '<p>Już w najbliższą sobotę o godzinie 17:00 nasz zespół zmierzy się u siebie z Pabianice Basketball w ramach III ligi ŁZKosz.</p><p>To będzie niezwykle ważne spotkanie dla układu tabeli. Nasi zawodnicy są w doskonałej formie po ostatnim zwycięstwie na wyjeździe, co napawa optymizmem przed tym meczem.</p><p>Pabianice Basketball to wymagający rywal, ale grając u siebie, przy wsparciu naszych wspaniałych kibiców, jesteśmy w stanie osiągnąć dobry wynik.</p><p><strong>Szczegóły meczu:</strong></p><ul><li>Data: sobota, godzina 17:00</li><li>Miejsce: Hala Sportowa MOSiR Łódź</li><li>Bilety dostępne w przedsprzedaży online</li></ul><p>Zachęcamy wszystkich kibiców do przyjścia i wsparcia naszej drużyny! Razem możemy wiele!</p>',
                'excerpt' => 'Zapowiedź sobotniego meczu ETB Łódź vs Pabianice Basketball. Bilety już w sprzedaży!',
                'author_id' => $admin->id,
                'publish_at' => Carbon::now()->addHours(2),
                'is_visible' => true,
            ],
        ];

        foreach ($news as $item) {
            News::create($item);
        }
    }

    private function seedSponsors(): void
    {
        $sponsors = [
            [
                'name' => 'Urząd Miasta Łodzi',
                'type' => Sponsor::TYPE_STRATEGIC,
                'url' => 'https://uml.lodz.pl',
                'logo_path' => '',
                'sort_order' => 1,
                'is_active' => true,
            ],
            [
                'name' => 'Łódzki Związek Koszykówki',
                'type' => Sponsor::TYPE_STRATEGIC,
                'url' => 'https://lzkosz.pl',
                'logo_path' => '',
                'sort_order' => 2,
                'is_active' => true,
            ],
            [
                'name' => 'Firma Transportowa "Szybka Piłka"',
                'type' => Sponsor::TYPE_SPONSOR,
                'url' => '#',
                'logo_path' => '',
                'sort_order' => 1,
                'is_active' => true,
            ],
            [
                'name' => 'Sklep Sportowy "Koszyk"',
                'type' => Sponsor::TYPE_SPONSOR,
                'url' => '#',
                'logo_path' => '',
                'sort_order' => 2,
                'is_active' => true,
            ],
            [
                'name' => 'Restauracja "Pod Koszem"',
                'type' => Sponsor::TYPE_SPONSOR,
                'url' => '#',
                'logo_path' => '',
                'sort_order' => 3,
                'is_active' => true,
            ],
            [
                'name' => 'Centrum Medyczne "Zdrowy Sportowiec"',
                'type' => Sponsor::TYPE_PARTNER,
                'url' => '#',
                'logo_path' => '',
                'sort_order' => 1,
                'is_active' => true,
            ],
            [
                'name' => 'Radio Łódź',
                'type' => Sponsor::TYPE_PARTNER,
                'url' => 'https://radiolodz.pl',
                'logo_path' => '',
                'sort_order' => 2,
                'is_active' => true,
            ],
            [
                'name' => 'Gazeta "Sport Łódzki"',
                'type' => Sponsor::TYPE_PARTNER,
                'url' => '#',
                'logo_path' => '',
                'sort_order' => 3,
                'is_active' => true,
            ],
            [
                'name' => 'WebDesign Łódź',
                'type' => Sponsor::TYPE_TECHNOLOGY,
                'url' => '#',
                'logo_path' => '',
                'sort_order' => 1,
                'is_active' => true,
            ],
            [
                'name' => 'Hosting Pro Sp. z o.o.',
                'type' => Sponsor::TYPE_TECHNOLOGY,
                'url' => '#',
                'logo_path' => '',
                'sort_order' => 2,
                'is_active' => true,
            ],
        ];

        foreach ($sponsors as $sponsor) {
            Sponsor::updateOrCreate(
                ['name' => $sponsor['name']],
                $sponsor,
            );
        }
    }

    private function seedProducts(): void
    {
        $categoryKoszulki = Category::firstOrCreate(
            ['slug' => 'koszulki'],
            ['name' => 'Koszulki', 'description' => 'Oficjalne koszulki meczowe i treningowe ETB Łódź'],
        );
        $categoryAkcesoria = Category::firstOrCreate(
            ['slug' => 'akcesoria'],
            ['name' => 'Akcesoria', 'description' => 'Czapki, opaski, bidony i inne akcesoria klubowe'],
        );
        $categoryGadzety = Category::firstOrCreate(
            ['slug' => 'gadzety'],
            ['name' => 'Gadżety', 'description' => 'Pamiątki i gadżety kibica ETB Łódź'],
        );

        $filterOptions = collect([
            'Typ produktu' => ['Koszulka', 'Akcesoria', 'Gadżet'],
            'Przeznaczenie' => ['Mecz', 'Trening', 'Kibic', 'Na co dzień'],
            'Kolekcja' => ['Oficjalny merch', 'Sezon 2025/2026'],
        ])->flatMap(function (array $options, string $groupName) {
            $group = ProductFilterGroup::updateOrCreate(
                ['slug' => \Illuminate\Support\Str::slug($groupName)],
                ['name' => $groupName, 'sort_order' => 0, 'is_active' => true],
            );

            return collect($options)->mapWithKeys(function (string $optionName, int $index) use ($group) {
                $option = ProductFilterOption::updateOrCreate(
                    [
                        'product_filter_group_id' => $group->id,
                        'slug' => \Illuminate\Support\Str::slug($optionName),
                    ],
                    ['name' => $optionName, 'sort_order' => $index, 'is_active' => true],
                );

                return [$optionName => $option->id];
            });
        });

        $products = [
            [
                'name' => 'Koszulka meczowa ETB 2025/2026',
                'description' => 'Oficjalna koszulka meczowa ETB Łódź na sezon 2025/2026. Wykonana z oddychającego materiału Dri-FIT, zapewniającego komfort podczas gry. Nadruk numeru i nazwiska z tyłu.',
                'price_grosze' => 14999,
                'vat_rate' => 23,
                'category_id' => $categoryKoszulki->id,
                'stock_qty' => 50,
                'is_physical' => true,
                'is_published' => true,
                'variants' => [
                    ['size_label' => 'S', 'stock_qty' => 10, 'extra_price_grosze' => 0],
                    ['size_label' => 'M', 'stock_qty' => 15, 'extra_price_grosze' => 0],
                    ['size_label' => 'L', 'stock_qty' => 15, 'extra_price_grosze' => 0],
                    ['size_label' => 'XL', 'stock_qty' => 10, 'extra_price_grosze' => 500],
                ],
            ],
            [
                'name' => 'Koszulka treningowa ETB',
                'description' => 'Lekka koszulka treningowa z logo ETB Łódź z przodu. Idealna na trening i na co dzień.',
                'price_grosze' => 8999,
                'vat_rate' => 23,
                'category_id' => $categoryKoszulki->id,
                'stock_qty' => 30,
                'is_physical' => true,
                'is_published' => true,
                'variants' => [
                    ['size_label' => 'M', 'stock_qty' => 10, 'extra_price_grosze' => 0],
                    ['size_label' => 'L', 'stock_qty' => 15, 'extra_price_grosze' => 0],
                    ['size_label' => 'XL', 'stock_qty' => 5, 'extra_price_grosze' => 500],
                ],
            ],
            [
                'name' => 'Czapka z daszkiem ETB',
                'description' => 'Klubowa czapka z daszkiem z haftowanym logo ETB Łódź z przodu. Regulowane zapięcie z tyłu.',
                'price_grosze' => 5999,
                'vat_rate' => 23,
                'category_id' => $categoryAkcesoria->id,
                'stock_qty' => 40,
                'is_physical' => true,
                'is_published' => true,
                'variants' => [],
            ],
            [
                'name' => 'Opaska sportowa ETB',
                'description' => 'Opaska na nadgarstek z nadrukiem ETB Łódź. Chłonie pot i świetnie sprawdza się podczas gry.',
                'price_grosze' => 1999,
                'vat_rate' => 23,
                'category_id' => $categoryAkcesoria->id,
                'stock_qty' => 100,
                'is_physical' => true,
                'is_published' => true,
                'variants' => [],
            ],
            [
                'name' => 'Bidon ETB Łódź',
                'description' => 'Metalowy bidon termiczny z grawerem logo ETB. Pojemność 500 ml. Idealny na trening i mecz.',
                'price_grosze' => 4499,
                'vat_rate' => 23,
                'category_id' => $categoryAkcesoria->id,
                'stock_qty' => 35,
                'is_physical' => true,
                'is_published' => true,
                'variants' => [],
            ],
            [
                'name' => 'Szalik kibica ETB',
                'description' => 'Gruby, dziany szalik w barwach ETB Łódź z nadrukowanym logo. Idealny na mecze wyjazdowe.',
                'price_grosze' => 3999,
                'vat_rate' => 23,
                'category_id' => $categoryGadzety->id,
                'stock_qty' => 60,
                'is_physical' => true,
                'is_published' => true,
                'variants' => [],
            ],
            [
                'name' => 'Naklejka ETB Łódź',
                'description' => 'Zestaw 5 naklejek z logo ETB Łódź. Naklej na laptopa, zeszyt lub telefon.',
                'price_grosze' => 999,
                'vat_rate' => 23,
                'category_id' => $categoryGadzety->id,
                'stock_qty' => 200,
                'is_physical' => true,
                'is_published' => true,
                'variants' => [],
            ],
            [
                'name' => 'Brelok ETB Łódź',
                'description' => 'Metalowy brelok do kluczy z grawerem logo ETB Łódź. Mały upominek dla każdego kibica.',
                'price_grosze' => 1499,
                'vat_rate' => 23,
                'category_id' => $categoryGadzety->id,
                'stock_qty' => 150,
                'is_physical' => true,
                'is_published' => true,
                'variants' => [],
            ],
        ];

        foreach ($products as $data) {
            $variants = $data['variants'];
            unset($data['variants']);

            $product = Product::updateOrCreate(
                ['slug' => \Illuminate\Support\Str::slug($data['name'])],
                $data,
            );

            if (! empty($variants)) {
                $product->variantSizes()->delete();

                foreach ($variants as $variant) {
                    $product->variantSizes()->create($variant);
                }
            }

            $filters = ['Oficjalny merch'];

            if ($data['category_id'] === $categoryKoszulki->id) {
                $filters[] = 'Koszulka';
                $filters[] = \Illuminate\Support\Str::contains($data['name'], 'meczowa') ? 'Mecz' : 'Trening';
            } elseif ($data['category_id'] === $categoryAkcesoria->id) {
                $filters[] = 'Akcesoria';
                $filters[] = \Illuminate\Support\Str::contains($data['name'], ['Opaska', 'Bidon']) ? 'Trening' : 'Na co dzień';
            } else {
                $filters[] = 'Gadżet';
                $filters[] = 'Kibic';
            }

            if (\Illuminate\Support\Str::contains($data['name'], '2025/2026')) {
                $filters[] = 'Sezon 2025/2026';
            }

            $product->filterOptions()->sync(
                collect($filters)
                    ->map(fn (string $filter) => $filterOptions->get($filter))
                    ->filter()
                    ->all(),
            );
        }
    }
}
