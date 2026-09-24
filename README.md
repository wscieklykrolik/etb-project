# ETB

Projekt jest skonfigurowany do pracy lokalnej na Laravelu, SQLite i Vite.

## Uruchomienie lokalne

1. Zainstaluj zależności PHP i JS:

```bash
composer install
npm install
```

2. Przygotuj plik środowiskowy, jeżeli jeszcze go nie ma:

```bash
copy .env.example .env
php artisan key:generate
```

3. Przygotuj bazę SQLite:

```bash
php artisan migrate
```

4. Uruchom aplikację:

```bash
composer run dev
```

Na Windows możesz też użyć:

```bat
scripts\start.bat
```

Domyślny adres aplikacji to `http://127.0.0.1:8000`, a Vite działa obok w trybie developerskim.

## Testy

```bash
composer test
```

## Dokumentacja

- Studium wykonalności: `docs/studium-wykonalnosci.md`
