<?php

use App\Models\FaqQuestion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('shows published homepage questions under academy with faq schema', function () {
    FaqQuestion::query()->create([
        'question' => 'Jak zapisać dziecko do akademii ETB?',
        'answer' => 'Wypełnij formularz kontaktowy albo skontaktuj się bezpośrednio z trenerem wybranej grupy.',
        'sort_order' => 2,
        'is_published' => true,
        'published_at' => now()->subDay(),
    ]);

    FaqQuestion::query()->create([
        'question' => 'Czy ETB prowadzi treningi koszykówki dla dzieci?',
        'answer' => 'Tak, akademia ETB prowadzi grupy młodzieżowe dopasowane do wieku i poziomu zawodników.',
        'sort_order' => 1,
        'is_published' => true,
        'published_at' => now()->subDay(),
    ]);

    FaqQuestion::query()->create([
        'question' => 'Ukryte pytanie',
        'answer' => 'Nie powinno być widoczne.',
        'is_published' => false,
    ]);

    FaqQuestion::query()->create([
        'question' => 'Zaplanowane pytanie',
        'answer' => 'Nie powinno być jeszcze widoczne.',
        'is_published' => true,
        'published_at' => now()->addDay(),
    ]);

    $response = $this->get(route('home'));

    $response->assertOk();
    $response->assertSee('Akademia ETB');
    $response->assertSee('Najczęstsze pytania');
    $response->assertSee('Czy ETB prowadzi treningi koszykówki dla dzieci?');
    $response->assertSee('Jak zapisać dziecko do akademii ETB?');
    $response->assertDontSee('Ukryte pytanie');
    $response->assertDontSee('Zaplanowane pytanie');
    $response->assertSee('FAQPage');
    expect(strpos($response->getContent(), 'Akademia ETB'))->toBeLessThan(strpos($response->getContent(), 'Najczęstsze pytania'));
    expect(strpos($response->getContent(), 'Czy ETB prowadzi treningi koszykówki dla dzieci?'))->toBeLessThan(strpos($response->getContent(), 'Jak zapisać dziecko do akademii ETB?'));
});

it('lets admins manage homepage faq questions from the panel', function () {
    $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

    $panel = $this->actingAs($admin)->get(route('profile.edit', ['section' => 'faq']));
    $panel->assertOk();
    $panel->assertSee('Pytania i odpowiedzi');
    $panel->assertSee('Dodaj pytanie');

    $this->actingAs($admin)->post(route('admin.faq.store'), [
        'question' => 'Gdzie odbywają się treningi ETB?',
        'answer' => 'Treningi odbywają się w halach podanych przy grupach akademii.',
        'sort_order' => 3,
        'is_published' => '1',
        'published_at' => now()->subDay()->format('Y-m-d H:i:s'),
    ])->assertRedirect(route('profile.edit', ['section' => 'faq']));

    $question = FaqQuestion::query()->firstOrFail();

    $this->assertDatabaseHas('faq_questions', [
        'question' => 'Gdzie odbywają się treningi ETB?',
        'sort_order' => 3,
        'is_published' => true,
    ]);

    $this->actingAs($admin)->put(route('admin.faq.update', $question), [
        'question' => 'Ile kosztują treningi ETB?',
        'answer' => 'Aktualne informacje o składkach przekazuje koordynator akademii.',
        'sort_order' => 1,
    ])->assertRedirect(route('profile.edit', ['section' => 'faq']));

    $this->assertDatabaseHas('faq_questions', [
        'id' => $question->id,
        'question' => 'Ile kosztują treningi ETB?',
        'is_published' => false,
    ]);

    $this->actingAs($admin)->delete(route('admin.faq.destroy', $question))
        ->assertRedirect(route('profile.edit', ['section' => 'faq']));

    $this->assertDatabaseMissing('faq_questions', [
        'id' => $question->id,
    ]);
});
