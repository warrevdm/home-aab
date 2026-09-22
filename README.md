# Aerts Action Bike — centrale home-laag

Deze repository levert de centrale `/home`-laag en de gedeelde authenticatie voor de interne AAB-systemen.

## Productiestructuur

```text
/www/home/
/www/mailing-system/
/www/huur-module/
```

### Bron van waarheid

- `/home` + `/mailing-system`: deze repository.
- `/huur-module`: aparte repository `warrevdm/huur-module`.
- De map `huur-module/` in deze repository is alleen een integratiesnapshot en mag niet over een nieuwere productieversie worden gezet.

## Centrale sessie

De stabiele koppeling voor andere apps is:

```text
/www/home/auth.php
```

Die bridge laadt de centrale auth uit:

```text
/www/mailing-system/src/auth.php
```

De centrale accountdatabase blijft:

```text
/www/mailing-system/data/auth.sqlite
```

## Vereist maar niet in Git

```text
/www/mailing-system/src/config.php
/www/mailing-system/data/auth.sqlite
```

Maak `config.php` op basis van `mailing-system/src/config.example.php`. Voor gedeelde SSO moet minstens dit correct staan:

```php
const AUTH_COOKIE_PATH = '/';
const AUTH_FORCE_SECURE_COOKIE = true;
```

## Deploy

Upload vanuit deze repository alleen:

```text
home/
mailing-system/
```

Behoud altijd:

```text
mailing-system/src/config.php
mailing-system/data/
```

Deploy `huur-module/` uitsluitend vanuit `warrevdm/huur-module` en behoud daar:

```text
.env
storage/
```

## Testvolgorde

1. Open `/home/`.
2. Meld aan.
3. Open de tegel Verhuur.
4. Controleer dat `/huur-module/` opent zonder tweede login.
5. Klik in Verhuur op Dashboard en controleer de terugkeer naar `/home/`.
6. Open Klantcommunicatie zonder tweede login.
7. Log uit en controleer dat alle systemen opnieuw login vereisen.
