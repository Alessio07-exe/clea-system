# CLEA - Sistema di Gestione Manutenzione Macchinari

## Descrizione

CLEA è un sistema web per la gestione centralizzata della manutenzione di macchinari. Progettato per tecnici e responsabili di manutenzione, consente di:

- ✅ Registrare nuovi macchinari con foto e dettagli tecnici
- ✅ Tracciare la manutenzione ordinaria e straordinaria
- ✅ Consultare lo storico degli interventi
- ✅ Generare rapporti PDF
- ✅ Condividere i dati via QR code

## Caratteristiche Principali

### Per i Tecnici
- Dashboard intuitiva con statistiche
- Registrazione rapida di nuovi macchinari
- Upload foto e documentazione
- Storico manutenzione completo
- Generazione PDF dei dati

### Per i Manager
- Visualizzazione attività tecnici
- Tracciamento manutenzioni
- Reportistica su interventi
- Gestione team

### Sicurezza
- Autenticazione con email/password (BCRYPT)
- CSRF protection su tutti i form
- Token pubblici per accesso condiviso
- Session management con timeout
- Validazione input lato server

## Requisiti

- PHP 7.4+
- MySQL 5.7+
- Composer (opzionale)
- Browser moderno (Chrome, Firefox, Safari, Edge)

## Installazione

### 1. Clone Repository

```bash
git clone https://github.com/Alessio07-exe/clea-system.git
cd clea-system
```

### 2. Configurazione Database

Crea un nuovo database MySQL:

```sql
CREATE DATABASE clea_system CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Importa lo schema (quando disponibile):

```bash
mysql -u root -p clea_system < database/schema.sql
```

### 3. Configurazione Applicazione

Modifica `config/config.php` con le tue credenziali:

```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', 'tua_password');
define('DB_NAME', 'clea_system');
define('APP_URL', 'http://localhost/clea-system');
```

### 4. Permessi Cartelle

```bash
chmod 755 uploads/
chmod 755 uploads/macchinari/
```

### 5. Accesso Applicazione

Visita `http://localhost/clea-system` nel browser

## Credenziali Demo

Per testare l'applicazione:

- **Email**: tecnico@clea.it
- **Password**: password123

## Struttura Directory

```
clea-system/
├── public/              # File pubblici
│   ├── index.php       # Login page
│   └── macchinario.php # Pagina pubblica macchinario
├── private/            # Area protetta
│   ├── dashboard.php   # Dashboard
│   ├── macchinari.php  # Lista macchinari
│   ├── nuova_installazione.php
│   ├── modifica_macchinario.php
│   ├── macchinario.php # Dettagli macchinario
│   ├── nuova_manutenzione.php
│   ├── pdf.php         # Generazione PDF
│   └── auth/           # Autenticazione
├── includes/           # File include
│   ├── header.php
│   ├── footer.php
│   ├── functions.php   # Funzioni utilità
│   └── db.php          # Classe Database
├── config/             # Configurazione
│   └── config.php
├── assets/             # CSS, JS, immagini
│   ├── css/
│   └── js/
├── uploads/            # Upload foto
│   └── macchinari/
├── database/           # Script database
│   └── schema.sql
└── README.md
```

## Utilizzo

### Login
1. Accedi con email e password
2. La sessione rimane attiva per 1 ora

### Registrare un Nuovo Macchinario
1. Click su "Nuova Installazione"
2. Compila dati macchinario
3. Carica foto
4. Salva

### Aggiungere Manutenzione
1. Visualizza il macchinario
2. Click su "Aggiungi Manutenzione"
3. Compila dettagli intervento
4. Salva

### Condividere Dati
1. Visualizza il macchinario
2. Un QR code o link pubblico permette accesso senza login

## API Endpoints

### Autenticazione
- `POST /public/index.php` - Login
- `GET /private/auth/logout.php` - Logout

### Macchinari
- `GET /private/macchinari.php` - Lista macchinari
- `GET /private/macchinario.php?id=X` - Dettagli macchinario
- `POST /private/nuova_installazione.php` - Registra macchinario
- `POST /private/modifica_macchinario.php?id=X` - Modifica macchinario
- `GET /public/macchinario.php?token=X` - Accesso pubblico

### Manutenzione
- `POST /private/nuova_manutenzione.php` - Registra manutenzione

## Database Schema

### Tabelle Principali

#### users
```
id, email, password_hash, nome, cognome, azienda, ruolo, created_at
```

#### machines
```
id, serial_number, brand, model, refrigerant_quantity,
installation_date, installation_description, technician_id,
installation_company, general_description, public_token
```

#### machine_photos
```
id, machine_id, file_path, original_filename, created_at
```

#### maintenance
```
id, machine_id, technician_id, maintenance_date, technician_name,
maintenance_company, maintenance_type, problem_description,
intervention_description, created_at
```

## Sicurezza

- ✅ Prepared statements per prevenire SQL injection
- ✅ Input sanitization
- ✅ CSRF tokens su tutti i form
- ✅ Password hashing con BCRYPT
- ✅ Session timeout dopo 1 ora di inattività
- ✅ Validazione MIME type per upload file
- ✅ Dimensione massima file 5MB

## Contribuire

1. Fork il repository
2. Crea un branch per la feature (`git checkout -b feature/AmazingFeature`)
3. Commit le modifiche (`git commit -m 'Add some AmazingFeature'`)
4. Push nel branch (`git push origin feature/AmazingFeature`)
5. Apri una Pull Request

## Roadmap

- [ ] Generazione PDF automatici
- [ ] Generazione QR code per macchinari
- [ ] API REST
- [ ] Mobile app
- [ ] Sincronizzazione offline
- [ ] Notifiche email reminder
- [ ] Dashboard analytics avanzate
- [ ] Export dati (CSV, Excel)

## Supporto

Per problemi o suggerimenti, apri una issue su GitHub.

## Licenza

MIT License - vedi LICENSE per dettagli

## Autore

**Alessio Colombo**
- GitHub: [@Alessio07-exe](https://github.com/Alessio07-exe)
- Email: alessio.colombo07@gmail.com

---

**Versione**: 1.0.0  
**Ultima modifica**: Settembre 2026
