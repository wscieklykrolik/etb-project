# Dostęp administratora

W produkcji ustaw `ACCOUNTS_ADMIN_ONLY=true` oraz `MAIL_MAILER=array` w prywatnym pliku `.env`. Login to `admin@etb.pl`; adres nie wymaga aktywnej skrzynki. Publiczna rejestracja i reset hasła przez pocztę są zablokowane. Pozostałe konta i treści pozostają w bazie, ale nie mogą się logować w tym trybie.

Po wdrożeniu kodu, w SSH nowego hostingu:

```bash
cd "$HOME/apps/etb-production/current"
php artisan etb:admin
```

Podaj dwukrotnie własne hasło (15–128 znaków). Nie wklejaj hasła do czatu ani GitHuba. Polecenie tworzy administratora z uprawnieniami i oznacza konto jako zatwierdzone przez operatora, bez wysyłania e-maila. Nie usuwa istniejących kont ani treści.

Logowanie: `https://eattheball.pl/login`. Przycisk logowania na publicznej stronie pozostaje ukryty.

Odzyskanie dostępu przez SSH:

```bash
php artisan etb:admin --reset-password
```

Reset unieważnia sesje w bazie i zapamiętane logowanie administratora. Jeśli `admin@etb.pl` jest już kontem innej roli, polecenie odmawia zmiany: wymaga to osobnego sprawdzenia istniejącego konta.
