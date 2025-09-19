# 3IT Test - CRM Records Management System

Professional PHP application for customer records management with modern development practices.

## 🚀 Quick Start

```bash
# Make scripts executable
chmod +x start.sh stop.sh dev.sh

# Start the application
./start.sh

# Open in browser
# http://localhost:8050
```

## 📋 Available Scripts

### Basic Operations
- `./start.sh` - Start the application with Docker
- `./stop.sh` - Stop the application
- `./dev.sh` - Development utility script

### Development Commands
```bash
./dev.sh start      # Start application
./dev.sh stop       # Stop application
./dev.sh restart    # Restart containers
./dev.sh logs       # Show live logs
./dev.sh status     # Show container status
./dev.sh db         # Connect to database
./dev.sh import     # Import sample data
./dev.sh test       # Run functionality tests
./dev.sh clean      # Clean cache and logs
./dev.sh help       # Show all commands
```

## 🌐 Application URLs

After starting with `./start.sh`:

- **Home Dashboard**: http://localhost:8050/
- **Records Table**: http://localhost:8050/tabulka
- **Data Import**: http://localhost:8050/download
- **Data Export**: http://localhost:8050/export
- **API Stats**: http://localhost:8050/api/stats

## 🛠️ Development

### Environment Configuration
Copy `.env.example` to `.env` and customize if needed:

```bash
cp .env.example .env
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