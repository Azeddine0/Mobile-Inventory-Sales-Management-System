CREATE DATABASE MobileShop_byme;
USE MobileShop_byme;

CREATE TABLE categories (
    cat_id INT PRIMARY KEY,
    cat_name VARCHAR(50) NOT NULL,
    cat_desc VARCHAR(255) NOT NULL
);

CREATE TABLE items (
    it_id INT PRIMARY KEY,
    it_name VARCHAR(50) NOT NULL,
    it_cat INT NOT NULL,
    it_qty INT NOT NULL,
    it_price INT NOT NULL,
    FOREIGN KEY (it_cat) REFERENCES categories(cat_id)
);

CREATE TABLE sellers (
    sell_id INT PRIMARY KEY,
    sell_name VARCHAR(50) NOT NULL,
    sell_gender VARCHAR(50) NOT NULL,
    sell_phone VARCHAR(50) NOT NULL,
    sell_adress VARCHAR(50) NOT NULL,
    sell_email VARCHAR(50) NOT NULL,
    sell_password VARCHAR(50) NOT NULL
);

CREATE TABLE bill (
    b_number INT PRIMARY KEY,
    b_date VARCHAR(50) NOT NULL,
    seller INT NOT NULL,
    amount INT NOT NULL,
    FOREIGN KEY (seller) REFERENCES sellers(sell_id)
);
CREATE DATABASE MobileShop_byme;
USE MobileShop_byme;

CREATE TABLE categories (
    cat_id INT PRIMARY KEY,
    cat_name VARCHAR(50) NOT NULL,
    cat_desc VARCHAR(255) NOT NULL
);

CREATE TABLE items (
    it_id INT PRIMARY KEY,
    it_name VARCHAR(50) NOT NULL,
    it_cat INT NOT NULL,
    it_qty INT NOT NULL,
    it_price INT NOT NULL,
    FOREIGN KEY (it_cat) REFERENCES categories(cat_id)
);

CREATE TABLE sellers (
    sell_id INT PRIMARY KEY,
    sell_name VARCHAR(50) NOT NULL,
    sell_gender VARCHAR(50) NOT NULL,
    sell_phone VARCHAR(50) NOT NULL,
    sell_adress VARCHAR(50) NOT NULL,
    sell_email VARCHAR(50) NOT NULL,
    sell_password VARCHAR(50) NOT NULL
);

CREATE TABLE bill (
    b_number INT PRIMARY KEY,
    b_date VARCHAR(50) NOT NULL,
    seller INT NOT NULL,
    amount INT NOT NULL,
    FOREIGN KEY (seller) REFERENCES sellers(sell_id)
);
