CREATE DATABASE gym_management;
USE gym_management;

-- membership table
CREATE TABLE Membership_Plan (
    membership_id INT PRIMARY KEY AUTO_INCREMENT,
    plan_name VARCHAR(50),
    duration_months INT,
    price DECIMAL(10,2)
);

-- trainer table
CREATE TABLE Trainer (
    trainer_id INT PRIMARY KEY AUTO_INCREMENT,
    trainer_name VARCHAR(100),
    specialization VARCHAR(100),
    phone VARCHAR(15),
    salary DECIMAL(10,2)
);

-- product table
CREATE TABLE Product (
    product_id INT PRIMARY KEY AUTO_INCREMENT,
    product_name VARCHAR(100),
    category VARCHAR(50),
    price DECIMAL(10,2),
    stock_quantity INT
);

-- member table
CREATE TABLE Member (
    member_id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100),
    age INT,
    gender VARCHAR(10),
    phone VARCHAR(15),
    join_date DATE,
    membership_id INT,
    trainer_id INT,

    FOREIGN KEY (membership_id)
    REFERENCES Membership_Plan(membership_id),

    FOREIGN KEY (trainer_id)
    REFERENCES Trainer(trainer_id)
);

-- purchase table 
CREATE TABLE Purchase (
    purchase_id INT PRIMARY KEY AUTO_INCREMENT,
    member_id INT,
    product_id INT,
    quantity INT,
    total_price DECIMAL(10,2),
    purchase_date DATE,

    FOREIGN KEY (member_id)
    REFERENCES Member(member_id),

    FOREIGN KEY (product_id)
    REFERENCES Product(product_id)
);

INSERT INTO Membership_Plan (plan_name, duration_months, price)
VALUES
('Monthly', 1, 1000.00),
('Quarterly', 3, 2500.00),
('Yearly', 12, 9000.00);

INSERT INTO Trainer (trainer_name, specialization, phone, salary)
VALUES
('Rahul Sharma', 'Weight Training', '9876543210', 30000.00),
('Amit Verma', 'Cardio Training', '9876543211', 28000.00),
('Priya Singh', 'Yoga', '9876543212', 25000.00);

INSERT INTO Product (product_name, category, price, stock_quantity)
VALUES
('Whey Protein', 'Supplement', 2500.00, 10),
('Creatine', 'Supplement', 1500.00, 15),
('Peanut Butter', 'Nutrition', 350.00, 20),
('Mass Gainer', 'Supplement', 3000.00, 8),
('Shaker Bottle', 'Accessory', 250.00, 25);

INSERT INTO Member
(name, age, gender, phone, join_date, membership_id, trainer_id)
VALUES
('Arjun', 22, 'Male', '9991110001', '2025-09-01', 1, 1),
('Sneha', 21, 'Female', '9991110002', '2025-09-02', 2, 2),
('Rohan', 24, 'Male', '9991110003', '2025-09-03', 3, 1),
('Anjali', 23, 'Female', '9991110004', '2025-09-04', 1, 3);

INSERT INTO Purchase
(member_id, product_id, quantity, total_price, purchase_date)
VALUES
(1, 1, 1, 2500.00, '2025-09-05'),
(2, 3, 2, 700.00, '2025-09-05'),
(3, 2, 1, 1500.00, '2025-09-06'),
(4, 5, 1, 250.00, '2025-09-06');


-- VIEW ALL MEMBERS
SELECT * FROM Member;

-- VIEW ALL TRAINERS
SELECT * FROM Trainer;

-- VIEW ALL PRODUCTS
SELECT * FROM Product;

-- VIEW ALL MEMBERSHIP PLANS
SELECT * FROM Membership_Plan;

-- VIEW ALL PURCHASES
SELECT * FROM Purchase;

-- SHOW MEMBERS WITH THEIR MEMBERSHIP PLANS
SELECT 
    m.name,
    mp.plan_name,
    mp.price
FROM Member m
JOIN Membership_Plan mp
ON m.membership_id = mp.membership_id;

-- SHOW MEMBERS WITH THEIR TRAINERS
SELECT
    m.name AS member_name,
    t.trainer_name,
    t.specialization
FROM Member m
JOIN Trainer t
ON m.trainer_id = t.trainer_id;

-- SHOW PRODUCT PURCHASES BY MEMBERS
SELECT
    m.name,
    p.product_name,
    pu.quantity,
    pu.total_price
FROM Purchase pu
JOIN Member m
ON pu.member_id = m.member_id
JOIN Product p
ON pu.product_id = p.product_id;

-- TOTAL REVENUE FROM PURCHASES
SELECT SUM(total_price) AS total_revenue
FROM Purchase;

-- COUNT TOTAL MEMBERS
SELECT COUNT(*) AS total_members
FROM Member;

-- PRODUCTS WITH LOW STOCK
SELECT *
FROM Product
WHERE stock_quantity < 10;

-- HIGHEST PRODUCT PRICE
SELECT MAX(price) AS highest_price
FROM Product;

-- MEMBERS WHO PURCHASED PRODUCTS ABOVE 1000
SELECT
    m.name,
    p.product_name,
    pu.total_price
FROM Purchase pu
JOIN Member m
ON pu.member_id = m.member_id
JOIN Product p
ON pu.product_id = p.product_id
WHERE pu.total_price > 1000;

-- SORT PRODUCTS BY PRICE (HIGH TO LOW)
SELECT *
FROM Product
ORDER BY price DESC;

-- COUNT PRODUCTS BY CATEGORY
SELECT
    category,
    COUNT(*) AS total_products
FROM Product
GROUP BY category;

-- SHOW MEMBERS JOINED AFTER A SPECIFIC DATE
SELECT *
FROM Member
WHERE join_date > '2025-09-02';

-- AVERAGE PRODUCT PRICE
SELECT AVG(price) AS average_price
FROM Product;

-- SHOW TRAINERS WITH SALARY ABOVE 25000
SELECT *
FROM Trainer
WHERE salary > 25000;