# Aerts Action Bike — centraal teamdashboard

`/home/` is de centrale ingang voor de interne systemen.

## Werking

- Eén login en één beveiligde PHP-sessie voor `/home`, `/mailing-system` en `/huur-module`.
- De centrale accounts blijven opgeslagen in `mailing-system/data/auth.sqlite`.
- Toegang tot Verhuur wordt bepaald door een actief verhuurprofiel met hetzelfde e-mailadres.
- De verhuurrol (`admin`, `staff` of `finance`) blijft leidend binnen de verhuurmodule.
- Een bestaand verhuuraccount zonder centraal profiel wordt bij de eerste geldige login veilig gekoppeld; de bestaande wachtwoordhash wordt overgenomen.
- Een sessie verloopt na 8 uur inactiviteit of uiterlijk na 7 dagen.
- Uitloggen via eender welk systeem beëindigt de centrale sessie overal.

## Productiecontrole

1. Behoud `mailing-system/data/auth.sqlite` en `huur-module/storage/database.sqlite` bij een update.
2. Controleer dat `AUTH_COOKIE_PATH` in `mailing-system/src/config.php` op `/` staat.
3. Gebruik uitsluitend HTTPS.
4. Meld aan via `https://www.aertsactionbike.cc/home/`.
5. Test voor één admin en één gewone medewerker beide systeemtegels en het uitloggen.

Een medewerker krijgt toegang tot Verhuur wanneer het e-mailadres in beide accountdatabanken exact gelijk is.
