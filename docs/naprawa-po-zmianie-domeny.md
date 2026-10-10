# Naprawa ETB po zamianie domeny w Hostingerze

Ta procedura dotyczy istniejącej aplikacji, która wcześniej działała pod `test.eattheball.pl`, a obecnie jest wystawiona pod `eattheball.pl`. Nie jest to instalacja pustej strony. Zachowaj aktualne treści, konta, zdjęcia, bazę i klucz szyfrowania.

## 1. Ustal, która aplikacja rzeczywiście obsługuje domenę

W menedżerze plików Hostingera sprawdź katalog przypisany do `eattheball.pl` i jego `index.php`. Odczytaj ścieżkę wymaganego pliku `public/index.php`. Nie zastępuj pliku wejściowego, zanim nie ustalisz działającego wydania.

Sprawdź również `~/apps/etb-staging/shared/public-root`, symlink `current`, symlink `.env` wydania i docelowy katalog `storage`. Zmiana domeny w hPanel może zmienić katalog WWW, ale nie aktualizuje konfiguracji Laravela ani plików zapisanych przez skrypt wdrażający.

Nie wypisuj zawartości `.env` w logach ani rozmowie. Do ustalenia jego położenia wystarczy `readlink -f .env` w katalogu aktywnej aplikacji.

## 2. Napraw adresy zdjęć i pozostałe adresy aplikacji

W aktywnym `.env` ustaw:

```dotenv
APP_URL=https://eattheball.pl
```

Jeśli nadal działa dotychczasowy prywatny katalog stagingu, odśwież konfigurację:

```bash
cd "$HOME/apps/etb-staging/current"
domain_php=$(cat "$HOME/apps/etb-staging/shared/php-bin")
test -x "$domain_php"
"$domain_php" artisan config:clear
"$domain_php" artisan config:cache
```

Przy innej strukturze użyj potwierdzonego katalogu aktywnej aplikacji i rzeczywistej ścieżki PHP. Nie uruchamiaj tych poleceń w przypadkowym checkoutcie.

Nie generuj nowego `APP_KEY`. Nie kasuj plików zdjęć ani nie wgrywaj ich ponownie. Zmiana adresu nie wymaga zmiany zapisanych w bazie względnych ścieżek mediów.

Poprawka w `config/filesystems.php` ustawia dla lokalnego dysku publicznego URL `/storage`. Dzięki temu nowe wydania wyświetlają zdjęcia na aktualnej domenie. Dyski CDN/S3 zachowują własne adresy. Prawidłowy `APP_URL` nadal jest potrzebny dla linków generowanych poza żądaniem WWW, np. w poczcie.

Sprawdź stare i nowo dodane zdjęcie, galerię oraz podgląd zdjęć w panelu. Gdy bezpośredni adres pliku działa, ale podgląd nie, porównaj adres z atrybutu `src` ze sprawnym adresem. Przy 404 sprawdź symlink publicznego `storage`; przy błędzie zapisu sprawdź prywatny log i uprawnienia PHP. Nie stosuj ogólnego `chmod 777`.

## 3. Oddziel weryfikację kodu od wdrożenia na serwer

W uruchomieniu `38076171853` dla commita `c4d7a3c` testy SQLite i MySQL, audyty, build, kontrole cache oraz przygotowanie paczki zakończyły się sukcesem. Zawiódł dopiero etap wdrożenia stagingu, z kodem 1 i bez wskazania konkretnego warunku.

Skrypt stagingowy jest przeznaczony wyłącznie dla domeny testowej. Nie usuwaj jego ograniczeń, aby dopuścić katalog publicznej domeny. W repozytorium dodano komunikaty dla brakującego katalogu, markera i konfiguracji, aby następny błąd wskazywał powód zatrzymania bez ujawniania sekretów.

Do czasu przywrócenia osobnego stagingu ustaw repozytoryjne `HOSTINGER_STAGING_ENABLED=false`. Weryfikacja nadal będzie działała przy pushu. Produkcję wdrażaj osobnym workflow, po przygotowaniu istniejącej aplikacji do przejęcia przez strukturę produkcyjną.

## 4. Przygotuj kopię aktualnej aplikacji przed przeniesieniem

Zachowaj poza WWW i z ograniczonym dostępem:

- eksport aktualnej bazy z danymi;
- aktualny `shared/storage`, w tym pliki prywatne;
- aktywny `.env` z dotychczasowym `APP_KEY`;
- działające wydanie, plik wejściowy, `.htaccess`, `robots.txt` i cele symlinków;
- ustawienia crona oraz informacje o ostatnim poprawnym wydaniu.

Potwierdź możliwość odtworzenia. Przed końcową kopią i przełączeniem wstrzymaj nowe zapisy oraz worker kolejki. Kopia wykonana w trakcie zmian nie zapewnia spójnego stanu bazy i uploadów.

## 5. Przenieś istniejącą aplikację do struktury produkcyjnej

Docelową strukturę i kontrolę konfiguracji opisuje [instrukcja publikacji](publikacja-produkcji.md). Dla istniejącej strony obowiązują dodatkowe zasady:

- Nie używaj pustej bazy ani `migrate:fresh`. Zachowaj bazę aktywnej aplikacji lub jej końcową, spójną kopię. Migracje wdrożeniowe mają aktualizować schemat, a nie kasować treści.
- Zachowaj dotychczasowy `APP_KEY` podczas przenoszenia tej samej aplikacji. Wygenerowanie nowego klucza mogłoby uniemożliwić odczyt szyfrowanych danych. To zachowanie ciągłości istniejącej strony, a nie współdzielenie klucza z osobnym przyszłym stagingiem.
- Skopiuj aktualne uploady do zwykłego katalogu produkcyjnego `shared/storage`; nie współdziel go symlinkiem z przyszłym stagingiem.
- Przygotuj działające `current` i wydanie produkcyjne przed przełączeniem publicznego `index.php`. Pozostaw dawną aplikację jako prywatną kopię do powrotu.
- Utwórz nowy cookie i prefix cache produkcji. Użytkownicy mogą potrzebować ponownego logowania; dane kont pozostają w bazie.
- Ustaw `DEPLOYMENT_ENVIRONMENT=production`, `ACCOUNTS_ADMIN_ONLY=true`, `MAIL_MAILER=array` oraz `SHOP_LEGACY_ENABLED=false`. Dostęp administratora bez SMTP opisuje [instrukcja](dostep-administratora.md). Sklep wiadomościowy pozostaje dostępny.
- Publiczny `storage` ma wskazywać produkcyjny katalog uploadów. Zmieniaj symlink dopiero po sprawdzeniu istniejącego celu i utworzeniu kopii.
- Po utworzeniu markera produkcji i poprawnego pliku wejściowego skonfiguruj GitHub Environment `production` oraz uruchom ręczne wdrożenie.

Polecenia pierwszej instalacji z pustym `APP_KEY` i nową bazą z ogólnej instrukcji nie dotyczą przejęcia już działającej aplikacji. Dokładne polecenia przeniesienia muszą wynikać z faktycznych ścieżek odczytanych z serwera.

## 6. Odbierz publiczną stronę

Po poprawnym wdrożeniu sprawdź działający frontend i zgodność `etb-release.txt` z publikowanym commitem. Następnie potwierdź:

- istniejące i nowe zdjęcia, zachowane treści i konta;
- wysyłkę i rzeczywiste otrzymanie potwierdzenia adresu oraz resetu hasła;
- HTTPS i przekierowanie `www` bez pętli;
- prawidłowy cron produkcyjny i brak równoległego workera dawnej aplikacji;
- brak stagingowego `Disallow: /` i nagłówka `X-Robots-Tag: noindex`;
- brak publicznego dostępu do sekretów, kodu prywatnego i dokumentów użytkowników.

Reguły indeksowania publikuj dopiero po odbiorze strony. Ręczna zmiana samego `robots.txt` nie wystarczy, jeśli nadal działa stagingowy nagłówek albo następne wdrożenie ponownie go zapisze.
