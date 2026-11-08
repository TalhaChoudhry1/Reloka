# Reloka

A personal home inventory web app. Users register, add their homes, record the items inside them, and track each item's condition, insured value and warranty.

Built with PHP, MySQL (PDO) and plain HTML/CSS.

## Features

- Register, log in and log out (passwords hashed, session-based access)
- Add, edit and delete homes
- Add, edit and delete items (category, brand, purchase details)
- Condition history for each item (New, Good, Damaged, Needs Repair)
- Insurance page with insured values and a total
- Warranty page showing active and expired warranties
- Dashboard with household statistics
- Responsive layout for mobile, tablet and desktop

## Setup

1. Put the project in your web root, e.g. `/var/www/html/reloka`.
2. Import the database:
   ```bash
   sudo mysql < database/reloka.sql
   ```
3. Create the database user:
   ```sql
   CREATE USER 'reloka_user'@'localhost' IDENTIFIED BY 'your_password';
   GRANT ALL ON reloka.* TO 'reloka_user'@'localhost';
   ```
4. Copy `config/database.example.php` to `config/database.php` and enter your password.
5. Open `http://localhost/reloka/`.

## Database

Five tables: `users`, `homes`, `categories`, `items`, `condition_logs`.
A user has many homes, a home has many items, and an item has many condition logs.

## Security

- Prepared statements (PDO) for all queries
- `password_hash` / `password_verify` for passwords
- Every page checks the session, and every query is limited to the logged-in user's own data
- Output is escaped with `htmlspecialchars`

## Project structure

```
auth/            login, register, logout
homes/           manage homes
items/           manage items
condition_logs/  item condition history
insurance/       insured values
warranty/        warranty tracking
config/          database connection
database/        SQL schema
assets/          CSS and images
```

