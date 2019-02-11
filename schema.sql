CREATE DATABASE doingsdone
DEFAULT CHARACTER SET utf8
DEFAULT COLLATE utf8_general_ci;

USE doingsdone;

CREATE TABLE category_list (
category_id INT AUTO_INCREMENT PRIMARY KEY,
user_name CHAR(32),
category_name CHAR(32) UNIQUE
);

CREATE TABLE tasks (
task_id INT AUTO_INCREMENT PRIMARY KEY,
category_name CHAR(32),
user_name CHAR(32),
task_desc CHAR(64),
date_create DATE,
date_perform DATE,
date_require DATE,
task_state TINYINT(1),
file_link CHAR(255)
);

CREATE TABLE users (
user_id INT AUTO_INCREMENT PRIMARY KEY,
user_name CHAR(32) UNIQUE NOT NULL,
user_password CHAR(64) NOT NULL,
user_email CHAR(64) UNIQUE,
date_register DATE
);

CREATE INDEX date_create ON tasks(date_create);
CREATE INDEX task_state ON tasks(task_state);
CREATE INDEX date_register ON users(date_register)