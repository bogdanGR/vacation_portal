# Vacation Portal

#### PHP Developer Assignment at Epignosis.

A lightweight, PHP 8.2 app where employees request vacations and managers approve/reject them.  
Stack: **PHP 8.2, Apache, MySQL 8, Docker, PHPUnit, Bootstrap**.

---

## ⚙️ Requirements

- **Git**
- **Docker** & **Docker Compose**
---

## 1. Clone

```bash
git clone git@github.com:bogdanGR/vacation_portal.git
cd vacation_portal
```
---
## 2. Build
```bash
docker compose up --build

# or detach so you can run other commands:
docker compose up -d --build
```
---
##  3. Seed data
```bash
# open new terminal window in root project if you ran docker compose without flag -d
docker compose exec app composer seed:all
```

---
## 4. Open in browser

### App 
http://localhost:8080 
### phpMyAdmin
http://localhost:3400 

---
## Credentials

### manager account
- username: `manager`
- password: `password`
---
### Employee account
- username: `bogdan`
- password: `password`
--- 

## To Run Tests

```bash
docker compose exec app composer test
```


