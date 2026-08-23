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
        'schedule',
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
    $this->get(route('schedule.third-league'))->assertRedirect('https://www.lzkosz.pl/liga/215.html');
});