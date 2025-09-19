# Aplikace pro správu záznamů

Webová aplikace pro import, zobrazení a správu databázových záznamů s možností označování.

## Funkce

- **Import dat** - Stažení dat ze vzdáleného JSON zdroje
- **Zobrazení dat** - Strukturované zobrazení s řazením
- **Označování záznamů** - Interaktivní označování s perzistentním ukládáním

## Technologie

- PHP 8.1+
- MariaDB/MySQL
- Dibi Database Layer
- Latte Template Engine
- jQuery
- Docker (pro development)

## Instalace

### 1. Naklonování repozitáře
```bash
git clone <repository-url>
cd 3it-test
```

### 2. Instalace závislostí
```bash
composer install
```

### 3. Databáze
Vytvořte databázi a spusťte SQL skripty:
```bash
mysql -u root -p < create.sql
mysql -u root -p < migrations.sql
```

### 4. Konfigurace
Upravte připojení k databázi v `DbConfig.php`

### 5. Spuštění
#### Docker
```bash
docker-compose up -d
```
Aplikace bude dostupná na http://localhost:8080

#### Lokální PHP server
```bash
php -S localhost:8000
```

## Struktura projektu

```
├── app/               # Aplikační logika
│   ├── Home.php      # Hlavní stránka
│   ├── Tabulka.php   # Zobrazení tabulky
│   ├── Download.php  # Import dat
│   ├── Mark.php      # AJAX označování
│   ├── Cache.php     # Cache systém
│   └── *.latte       # Šablony
├── config/           # Docker konfigurace
├── vendor/           # Composer závislosti
├── zeta/            # Logy a cache
├── create.sql       # Databázové schéma
├── migrations.sql   # Databázové migrace
└── index.php        # Vstupní bod aplikace
```

## Bezpečnost

- Ochrana proti SQL Injection pomocí parametrizovaných dotazů
- XSS prevence automatickým escapováním v šablonách
- Validace vstupních dat
- Bezpečné session management
- CSRF ochrana (připravena pro implementaci)

## API Endpoints

- `GET /` - Hlavní stránka
- `GET /tabulka` - Zobrazení tabulky s daty
- `GET /download` - Import dat ze vzdáleného zdroje
- `POST /mark` - AJAX endpoint pro označování záznamů

## Optimalizace

- Cachování vzdálených dat (5 minut)
- Databázové indexy pro rychlé řazení
- Transakce pro atomické operace
- AJAX pro interaktivní označování
- Lazy loading pro velké datasety (připraveno)

## Testování

Aplikace obsahuje:
- Validaci vstupních dat
- Error handling s logováním
- Debug mód pro development

## Licence

Vytvořeno jako testovací úloha pro 3IT.cz