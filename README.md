# 🔍 Lost & Found – Symfony Web Application

A modern **Lost & Found platform** built with **Symfony** that helps people report, search, and recover lost items in a simple and secure way.

This project was developed as an academic project using Symfony’s MVC architecture, Doctrine ORM, and Twig templates.

---

## 📌 Features

### 👤 Authentication
- User registration
- User login & logout
- Access control (only authenticated users can post or manage items)

### 📦 Item Management
- Post a lost item (with image, category, location, contact info)
- Edit or delete your own items
- Mark an item as **Returned**
- Re-open a returned item (back to Lost)

### 📊 Dashboard
- View all active lost items from the community
- Search items by keyword
- Filter items by category

### 👤 Profile Page
- View personal information
- View **your own items**
  - Lost & active items
  - Returned (found) items
- Manage your items from one place

### 🏠 Homepage
- Recent lost & found items
- Statistics (items reported / lost / found)
- Item details shown via modal

---

## 🛠 Tech Stack

| Layer | Technology |
|-----|-----------|
| Backend | **Symfony 6** |
| Language | **PHP 8** |
| Database | **SQLite** (Doctrine ORM) |
| Frontend | **Twig**, Bootstrap 5 |
| Authentication | Symfony Security |
| ORM | Doctrine |
| Version Control | Git & GitHub |

---

## 🧱 Project Architecture (MVC)

This project follows the **classic Symfony MVC pattern**:

- **Entities** → Database models (`User`, `Item`)
- **Repositories** → Database queries
- **Controllers** → Business logic & routing
- **Templates** → UI using Twig

