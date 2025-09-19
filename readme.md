# 3IT - testovací úloha

## Xampp
1. V případě, že nemáte dostatek zkušeností s Dockerem, můžete pro lokální vývoj použít XAMPP jako alternativu

## Docker

### Webserver 
1. Ve složce spustíme Docker příkazem:
    ```
    docker-compose up -d
    ```
    a počkáme až se připraví a spustí Docker.

2. Otevřeme Apache kontejner pomocí příkazu:
    ```
    docker exec -it 3it_test_webserver bash
    ```

4. Nainstalujeme php knihovny dle potřeby:
    ```
    composer install
    ```
4. V **hosts** si nastavíme doménu **3it-test.localhost** na IP adresu **127.0.0.1**.
5. Máme připravený projekt na url **http://3it_test.localhost:8050**.
6. Databáze je dostupná následovně
    ```
      host => 3it_test_database
      user => root
      password => toor
      database => 3it-test
   ```