CREATE DATABASE lab_loans;
USE lab_loans;

CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(100) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  role ENUM('admin','laboran','peminjam') DEFAULT 'peminjam',
  prodi VARCHAR(80), nim VARCHAR(30),
  is_blocked BOOLEAN DEFAULT 0
);

CREATE TABLE categories (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL UNIQUE
);

CREATE TABLE locations (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  room VARCHAR(50),
  shelf VARCHAR(50)
);

CREATE TABLE items (
  id INT AUTO_INCREMENT PRIMARY KEY,
  code_unique VARCHAR(50) NOT NULL UNIQUE,
  name VARCHAR(120) NOT NULL,
  category_id INT NOT NULL,
  location_id INT NOT NULL,
  condition_enum ENUM('new','good','fair','broken') DEFAULT 'good',
  total_qty INT DEFAULT 0,
  available_qty INT DEFAULT 0,
  min_qty_alert INT DEFAULT 1,
  notes TEXT,
  FOREIGN KEY (category_id) REFERENCES categories(id),
  FOREIGN KEY (location_id) REFERENCES locations(id)
);

-- Admin default (password: admin123)
INSERT INTO users(name,email,password,role)
VALUES(
  'Admin Lab',
  'admin@lab.test',
  '$2y$10$vdTAgC29RR7D9tEzyL1Cj.Mf/5oMhtk0D4bRQ1CmxHKT0QkkS4BGq',
  'admin'
);

INSERT INTO users(name,email,password,role)
VALUES(
  'Admin Lab 2',
  'admin2@lab.test',
  'admin123',
  'admin'
);