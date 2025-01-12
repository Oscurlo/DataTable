CREATE DATABASE IF NOT EXISTS test_datatable;

USE test_datatable;

CREATE TABLE IF NOT EXISTS
    users (
        id INT PRIMARY KEY,
        name VARCHAR(100),
        email VARCHAR(100)
    );

INSERT INTO
    users (id, name, email)
VALUES
    (1, 'John Doe', 'john.doe@example.com'),
    (2, 'Jane Smith', 'jane.smith@example.com'),
    (3, 'Sam Green', 'sam.green@example.com');

CREATE TABLE IF NOT EXISTS
    orders (
        order_id INT PRIMARY KEY,
        user_id INT,
        product_name VARCHAR(100),
        order_date DATE,
        FOREIGN KEY (user_id) REFERENCES users (id)
    );

INSERT INTO
    orders (order_id, user_id, product_name, order_date)
VALUES
    (1, 1, 'Laptop', '2025-01-01'),
    (2, 2, 'Smartphone', '2025-01-05'),
    (3, 1, 'Tablet', '2025-01-06');