# Gym Management System

A mini DBMS project developed to manage gym operations such as member management, trainer assignments, membership plans, product purchases, and inventory tracking.

---

## 📌 Project Overview

The Gym Management System is designed to simplify gym administration by storing and managing data efficiently using a relational database.

This project demonstrates:
- Database design
- Entity Relationship modeling
- SQL queries
- Primary and Foreign Keys
- CRUD operations
- Normalization concepts

---

## 🚀 Features

- Manage gym members
- Manage membership plans
- Assign trainers to members
- Store gym products/supplements
- Track member purchases
- Maintain product inventory

---

## 🗂 Database Tables

### 1. Membership_Plan
Stores membership plan details.

| Column | Description |
|---|---|
| membership_id | Primary Key |
| plan_name | Name of plan |
| duration_months | Plan duration |
| price | Plan cost |

---

### 2. Trainer
Stores trainer information.

| Column | Description |
|---|---|
| trainer_id | Primary Key |
| trainer_name | Trainer name |
| specialization | Expertise |
| phone | Contact number |
| salary | Trainer salary |

---

### 3. Member
Stores gym member details.

| Column | Description |
|---|---|
| member_id | Primary Key |
| name | Member name |
| age | Age |
| gender | Gender |
| phone | Contact number |
| join_date | Joining date |
| membership_id | Foreign Key |
| trainer_id | Foreign Key |

---

### 4. Product
Stores gym supplements/products.

| Column | Description |
|---|---|
| product_id | Primary Key |
| product_name | Product name |
| category | Product category |
| price | Product price |
| stock_quantity | Available stock |

---

### 5. Purchase
Stores member purchase records.

| Column | Description |
|---|---|
| purchase_id | Primary Key |
| member_id | Foreign Key |
| product_id | Foreign Key |
| quantity | Quantity purchased |
| total_price | Total bill |
| purchase_date | Purchase date |

---

## 🔗 ER Diagram

```text
MEMBERSHIP_PLAN
        |
        |
      MEMBER -------- PURCHASE -------- PRODUCT
        |
        |
     TRAINER
