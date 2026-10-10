# simple_login_system

A simple PHP login system with a session-based authentication flow and a
MySQL database backend, styled with a modern dark "Vibrant Slate" UI.

## Features

- Login and registration with server-side validation
- Passwords stored with `password_hash()` and verified with `password_verify()`
- Role-based access control (`admin` and `user`)
- 3D flip login/register card, toast notifications, dark dashboard
- Prepared statements and session fixation protection

## Requirements

- XAMPP (PHP 8+ and MySQL/MariaDB)

## Setup

1. Copy this folder into `htdocs`.
2. Import `setup.sql` (phpMyAdmin, or `mysql -u root < setup.sql`).
3. Open `http://localhost/session-nodb/`.

## Demo accounts

| Username | Password   | Role  |
| -------- | ---------- | ----- |
| admin    | admin123   | admin |
| juan     | user123    | user  |
