# Aerts Action Bike — centraal teamdashboard

`/home/` is de centrale ingang voor de interne systemen.

## Productie-architectuur

De drie applicaties staan als siblings onder dezelfde webroot:

```text
/www/home/
/www/mailing-system/
/www/huur-module/
```

- `home-aab` beheert `/home` en de centrale loginlaag rond `/mailing-system`.
- De actuele verhuurmodule blijft beheerd in de aparte repo `warrevdm/huur-module`.
- De map `huur-module/` in deze repo is een integratiesnapshot en mag niet over een nieuwere productieversie van de aparte verhuurrepo worden gekopieerd.
- Interne modules koppelen via `/home/auth.php`; die bridge laadt de centrale auth uit `mailing-system/src/auth.php`.

## Vereiste centrale auth-bestanden

Deze bestanden moeten op productie bestaan:

```text
/www/home/auth.php
/www/mailing-system/src/auth.php
/www/mailing-system/src/config.php
/www/mailing-system/data/auth.sqlite
```

`src/config.php` en `data/auth.sqlite` staan bewust niet in Git. Maak `src/config.php` vanuit `src/config.example.php` en behoud de productiedatabase bij updates.

Belangrijk in `mailing-system/src/config.php`:

```php
const AUTH_COOKIE_PATH = '/';
const AUTH_FORCE_SECURE_COOKIE = true;
```

## Werking

- Eén login en één beveiligde PHP-sessie voor `/home`, `/mailing-system` en `/huur-module`.
- De centrale accounts blijven opgeslagen in `mailing-system/data/auth.sqlite`.
- Toegang tot Verhuur wordt bepaald door een actief verhuurprofiel met hetzelfde e-mailadres.
- De verhuurrol (`admin`, `staff` of `finance`) blijft leidend binnen de verhuurmodule.
- Een bestaand verhuuraccount zonder centraal profiel wordt bij de eerste geldige login veilig gekoppeld; de bestaande wachtwoordhash wordt overgenomen.
- Een sessie verloopt na 8 uur inactiviteit of uiterlijk na 7 dagen.
- Uitloggen via eender welk systeem beëindigt de centrale sessie overal.

## Veilige deployment

Voor een update van de home-laag upload je uit deze repo alleen:

```text
home/
mailing-system/   (zonder productie config/data te overschrijven)
```

De verhuurmodule deploy je vanuit `warrevdm/huur-module`.

Nooit overschrijven:

```text
mailing-system/src/config.php
mailing-system/data/
huur-module/.env
huur-module/storage/
```

## Productiecontrole

1. Controleer dat alle vier vereiste auth-bestanden hierboven bestaan.
2. Open `/home/` en meld aan.
3. Open vanuit het dashboard `/huur-module/`; er mag geen tweede login verschijnen.
4. Open `/mailing-system/`; dezelfde sessie moet actief blijven.
5. Test uitloggen en controleer dat alle drie de systemen daarna opnieuw login vereisen.
6. Test één admin, één gewone medewerker en één finance-profiel.

Een medewerker krijgt toegang tot Verhuur wanneer het e-mailadres in beide accountdatabanken exact gelijk is.
