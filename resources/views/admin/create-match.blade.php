<form method="POST" action="/admin/matches" class="p-10 space-y-4">
    @csrf

    <input type="text" name="opponent" placeholder="Przeciwnik" class="border p-2 w-full">

    <input type="datetime-local" name="match_date" class="border p-2 w-full">

    <input type="text" name="location" placeholder="Miejsce" class="border p-2 w-full">

    <label class="flex items-center gap-2">
        <input type="checkbox" name="is_home" checked>
        Mecz u siebie
    </label>

    <button class="bg-yellow-400 px-4 py-2 font-bold">
        Dodaj mecz
    </button>
</form>
