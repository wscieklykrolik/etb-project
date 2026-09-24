<?php

it('renders public navigation routes without server errors', function () {
    foreach ([
        'home',
        'news.index',
        'club',
        'club.history',
        'club.board',
        'club.venue',
        'club.business',
        'club.success',
        'club.sponsors',
        'club.contact',
        'contact',
        'cookies.policy',
        'schedule',
        'schedule.third-league',
        'schedule.lzkosz',
        'schedule.table',
        'schedule.3x3',
        'team',
        'team.players',
        'team.staff',
        'team.3x3',
        'tickets',
        'academy',
        'shop.index',
    ] as $routeName) {
        $this->get(route($routeName))->assertOk();
    }

    $this->get(route('schedule.3x3.team'))->assertRedirect('/team/3x3');
    $this->get(route('schedule.3x3.tournaments'))->assertRedirect('/schedule/3x3');
    $this->get(route('schedule.third-league'))
        ->assertOk()
        ->assertSee('Oficjalna strona ŁZKosz otworzy się w nowej karcie.')
        ->assertSee('Strona ETB pozostanie otwarta tutaj.')
        ->assertSee('href="https://www.lzkosz.pl/liga/215.html"', false)
        ->assertSee('target="_blank"', false)
        ->assertSee('rel="noopener noreferrer"', false);
});

it('shows team as a separate navigation item and contact dropdown for contact and marketing', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertSee('Drużyna')
        ->assertSee(route('team'), false)
        ->assertSee(route('contact'), false)
        ->assertSee(route('contact').'#marketing', false)
        ->assertSee('Marketing');

    $this->get(route('contact'))
        ->assertOk()
        ->assertSee('Marketing')
        ->assertSee('media@etb-lodz.pl');
});
