# Romanttinen Kutsu (V1.1)

WordPress-lisäosa romanttinen.fi -kutsutuotteelle — progressiivinen **peli** (osiot + tasot).

**Versio:** 1.3.3 · **Plugin slug:** `romanttinen-kutsu` (sama kuin V1 — ei deactivate-to-install)

## Changelog

### 1.3.3 — Brand align + peel history + Tapaaminen
Etusivu ja craft jakavat saajan Wine `#4A1F2C` / Cream `#F3E6D8` / serif-maailman (ei sydämiä); fabric/bokeh vain hero/teaser. Peel: osion otsikko kortissa labelina (`Elokuvahetki` · `Yhteinen ateria` · `Kotona`) + `Vihje`; vanhat tasot 55–65 % opacity, uusin cream+wine+giftIn. **Tapaaminen** heti avauksen jälkeen (`La 14.6. · 18:00`, paikka valinnainen). Teaser countdown-only. Modal +1. Kehtaisinko (Portti 1) ennallaan.

### 1.3.2 — Portti 1 visual QA (logo + fabric)
Saajan näkymä avauksen jälkeen: yksi logo (header-chrome; teaser-wordmark piilotetaan kunnolla — `display:flex` yli kirjoitti `[hidden]`). Cream silk/fabric (`hero-fabric`) säilyy teaser → peel; ei enää flat beige `is-opened`-hyppyä. Vihje-kortit pitävät kermapinnan fabricin päällä.

### 1.3.1 — Portti 1 peel QA fix
Juurisyy: «Haluatko kuulla lisää?» / modal näkyy vain kun osiolla on syvempiä tasoja kuin nykyinen depth. Stub-/testikutsut joissa vain L1 täytetty → CTA ei koskaan tule. Korjaus: luontilomake esitäyttää 2 osiota × 3 tasoa; WP-admin **Luo peel-testikutsu**; manage-varoitus jos peel ei ole mahdollinen; JS laskee vain ei-tyhjät tasot.

### 1.3.0 — Portti 1: Saajan hetki (peel rhythm stub)
Teaser: silk-tausta + kelluva kermakortti, **Avaa kutsu**. Avaus: `giftIn` (.38s) ja *Nyt saat tietää…* + yksi **Vihje**-kortti. **Haluatko kuulla lisää?** → modal (**Kerro lisää** / **Pidän jännityksen**). Seuraava vihje korvaa edellisen (ei pinoa). Portti, sessionStorage ja +1 / vahvistus ennallaan.

## Asennus / päivitys ilman deaktivointia

1. Lataa `romanttinen-kutsu.zip` WordPressiin: **Lisäosat → Lisää uusi → Lataa lisäosa**.
2. Kun slug on sama (`romanttinen-kutsu`), WP voi tarjota **Korvaa nykyinen** — hyväksy se. Deaktivointia ei tarvita.
3. Vaihtoehto SSH/SFTP: pura/korvaa tiedostot suoraan `wp-content/plugins/romanttinen-kutsu/` **lisäosa aktiivisena**.
4. Version bump flushaa rewrite-säännöt automaattisesti (`romant_kutsu_version`). Jos reitit eivät toimi: **Asetukset → Permainkkit → Tallenna**.
5. Asetukset: **Asetukset → Romanttinen**
   - **Hinta** (oletus 4,90 €)
   - **Stub payments** (oletus päällä)
   - **Käytä Romanttinen-etusivua**
   - **Visma Pay API Key** + **Private Key**
   - **Yritystiedot** (valinnainen; kuittiin — tyhjänä `romanttinen.fi · kutsu@romanttinen.fi`)

```php
define('ROMANTTINEN_STUB_PAYMENTS', true);
```

## Tuotevirta (V1.1)

1. Organisoija: **kutsujan nimi** (pakollinen), valinnainen **Saate**, treffiaika, valinnainen pukeutumisvihje, **1–3 osiota** (kussakin N tekstitasoa).
2. Luonnos + hallintalinkki (`romant_manage_key`).
3. Esikatselu: teaser + kunkin osion **ensimmäinen taso**.
4. **Hanki jaettava linkki (4,90 €)** → kutsujan nimi + Nimi kuittiin + pakollinen sähköposti → stub / Visma Pay → Nea-kuitti + hallintalinkki sähköpostiin.
5. Vastaanottaja (`/kutsu/{token}/`):
   - Teaser: Logo A (`logo-a.png`), `{nimi} kutsui sinut`, **Sinut on kutsuttu treffeille**, countdown, valinnainen Saate, CTA **Avaa kutsu**. Paikka ei näy teaserissa.
   - **Jaa tarina** / **Kopioi linkki** teaserissa (ei spoilereita PNG:ssä).
   - Avauksen jälkeen: **Tapaaminen**-kortti (päivä + valinnainen paikka), sitten osiot. Jokainen vihje-kortti näyttää osion otsikon; avatut tasot pinoutuvat. CTA **Haluatko kuulla lisää?** → modal (*Haluatko kuulla lisää?* / *Voit pitää jännityksen — tai avata seuraavan vihjeen.* / **Kerro lisää** / **Pidän jännityksen**) → unlock seuraava taso (+1, historia jää).
6. Edistyminen tallennetaan selainiin (ei vastaanottajatiliä).

## Data model

| Meta key | Tyyppi | Kuvaus |
|----------|--------|--------|
| `romant_inviter_name` | string | Kutsujan nimi (teaser / vastaanottaja) |
| `romant_receipt_name` | string | Nimi kuittiin (Kutsuja-rivi; oletus = kutsujan nimi) |
| `romant_saate` | textarea | Valinnainen teaser-tervehdys (ei spoilereita) |
| `romant_datetime` | ISO string | Treffiaika (Europe/Helsinki) |
| `romant_dress` | textarea | Pukeutumisvihje |
| `romant_location` | string | Valinnainen paikka (Tapaaminen-kortti avauksen jälkeen; ei teaserissa) |
| `romant_sections` | JSON | Osio-lista (max 3) |
| `romant_token` | secret | Julkinen vastaanottajalinkki |
| `romant_manage_key` | secret | Hallintalinkki |
| `romant_paid` / `romant_paid_at` | bool / ISO | Maksu |
| `romant_email` | email | Organisoijan sähköposti |
| `romant_opened_at` | ISO | Ensimmäinen avaus |
| `romant_events` | JSON | Avaus-/reveal-loki |

### `romant_sections` JSON

```json
[
  {
    "id": "a1b2c3d4…",
    "title": "Elokuvahetki",
    "levels": ["Taso 1 teksti", "Taso 2…", "Taso 3…"]
  }
]
```

- Max **3** osiota.
- Tasot: oletus **3**, soft max **10** (varoitus), hard max **20**.
- Vanha V1 `spoiler_pieni` / `spoiler_melkein` / `spoiler_koko` + `spoileritaso` migrataan automaattisesti yhdeksi osioksi **Vihjeet** kun `romant_sections` on tyhjä.

## Vastaanottajan edistyminen (client)

Avain: `romant_peli_{token}`

```json
{ "opened": true, "depth": { "0": 1, "1": 2 } }
```

- `depth[i]` = osiossa `i` avattu tasosyvyys (1-pohjainen).
- Tallennus: **sessionStorage** avaimella `romant_peli_{token}` (ei localStorage / pitkäikäistä cookieta — QA-istunnot eivät jaa tilaa; edistyminen säilyy vain saman selainistunnon ajan avauksen jälkeen).
- Ei WP-käyttäjätiliä vastaanottajalle.

## Sähköposti-hookit (jos organisoijan email tiedossa)

AJAX `romant_track_event` (nonce):

| Tapahtuma | Subject |
|-----------|---------|
| Ensimmäinen avaus | `Kutsu avattiin — {treffiaika}` |
| Ensimmäinen deepen / osio | `Pyysi kuulla lisää: {osio}` |

SMTP-konfiguraatio ei ole pakollinen — sama kuin kuitti-/hallintalinkki-mail.

Maksun jälkeen lähetetään kuitti (subject: `Treffikutsu valmis — 4,90 €`) hallintalinkillä; **Kutsuja:** = Nimi kuittiin (`romant_receipt_name`, oletus kutsujan nimi). Teaser käyttää edelleen kutsujan nimeä.

## Reitit

| URL | Käyttö |
|-----|--------|
| `/kutsu/uusi/` | Luo kutsu |
| `/kutsu/hallitse/{manage_key}/` | Muokkaa, esikatsele, maksa |
| `/kutsu/{token}/` | Vastaanottaja (peli) |
| `/kutsu/{token}/story/` | Tarina PNG (vain teaser) |
| `/kutsu/maksu/paluu/` | Visma return |
| `/kutsu/maksu/ilmoitus/` | Visma notify |

## Shortcodet

- `[romant_luo_kutsu]` — luontilomake
- `[romant_etusivu]` — markkinointifragmentti

## Maksut

Stub ON (oletus) / Visma Pay kun stub OFF + avaimet. Hinta 4,90 €. Gateway: `includes/payment/`.

## Vaatimukset

- PHP 8.0+
- WordPress 6.x
- Ei WooCommerce- / Composer-riippuvuutta
