CREATE DATABASE IF NOT EXISTS userdb;

USE userdb;

CREATE TABLE IF NOT EXISTS url_info (
id INT AUTO_INCREMENT PRIMARY KEY,
url text NOT NULL,
time DateTime,
length smallint NOT NULL
);