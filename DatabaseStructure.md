# Database Structure

## Tables

### `users`
| Column | Type | Constraints |
|--------|------|-------------|
| id | INT | PRIMARY KEY, AUTO_INCREMENT |
| email | VARCHAR(180) | NOT NULL, UNIQUE |
| address | VARCHAR(255) | NOT NULL |

### `products`
| Column | Type | Constraints |
|--------|------|-------------|
| id | INT | PRIMARY KEY, AUTO_INCREMENT |
| name | VARCHAR(255) | NOT NULL |
| price | DOUBLE | NOT NULL |

### `orders`
| Column | Type | Constraints |
|--------|------|-------------|
| id | INT | PRIMARY KEY, AUTO_INCREMENT |
| created_at | DATETIME | NOT NULL |
| user_id | INT | NOT NULL, FK → users.id |

### `orders_products` *(join table)*
| Column | Type | Constraints |
|--------|------|-------------|
| order_id | INT | NOT NULL, FK → orders.id |
| product_id | INT | NOT NULL, FK → products.id |

---

## Relationships

- **User → Orders**: One-to-Many (a user can have many orders)
- **Order → User**: Many-to-One (each order belongs to one user)
- **Order ↔ Product**: Many-to-Many via `orders_products` (an order can have many products; a product can appear in many orders)
