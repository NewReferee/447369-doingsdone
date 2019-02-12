INSERT INTO users(user_name, user_password, user_email)
VALUES
('stalker345', 'hsda3a1ijDs', 'stalker345@yandex.ru'),
('olga98', 'catmouse444', 'olga98@gmail.com');

INSERT INTO category_list(user_id, category_name)
VALUES 
((SELECT user_id
FROM users
WHERE users.user_name = 'olga98'), 'Входящие'),
((SELECT user_id
FROM users
WHERE users.user_name = 'olga98'), 'Учеба'),
((SELECT user_id
FROM users
WHERE users.user_name = 'stalker345'), 'Работа'),
((SELECT user_id
FROM users
WHERE users.user_name = 'stalker345'), 'Домашние дела'),
(null, 'Авто');

INSERT INTO tasks(task_desc, date_require, category_id, task_state, user_id)
VALUES (
'Собеседование в IT компании', '2019-02-09', 
(SELECT category_id
FROM category_list
WHERE category_list.category_name = 'Работа'), 0, (
SELECT user_id
FROM users
WHERE users.user_name = 'stalker345')),
('Выполнить тестовое задание', '2019-12-25', 
(SELECT category_id
FROM category_list
WHERE category_list.category_name = 'Работа'), 0, 
(SELECT user_id
FROM users
WHERE users.user_name = 'stalker345')),
('Сделать задание первого раздела', '2019-02-09', 
(SELECT category_id
FROM category_list
WHERE category_list.category_name = 'Учеба'), 1, 
(SELECT user_id
FROM users
WHERE users.user_name = 'olga98')),
('Встреча с другом', '2019-12-22', 
(SELECT category_id
FROM category_list
WHERE category_list.category_name = 'Входящие'), 0, 
(SELECT user_id
FROM users
WHERE users.user_name = 'olga98')),
('Купить корм для кота', null, 
(SELECT category_id
FROM category_list
WHERE category_list.category_name = 'Домашние дела'), 0, 
(SELECT user_id
FROM users
WHERE users.user_name = 'stalker345')),
('Заказать пиццу', null, 
(SELECT category_id
FROM category_list
WHERE category_list.category_name = 'Домашние дела'), 0, 
(SELECT user_id
FROM users
WHERE users.user_name = 'stalker345'));

-- Получить список всех проектов пользователя с id = 2
SELECT category_name
FROM category_list
WHERE user_id = 2;

-- Получить список всех задач для проекта с id = 4
SELECT task_desc
FROM tasks
WHERE category_id = 4;

-- Пометить задачу номер 4 как выполненную
UPDATE tasks
SET task_state = 1
WHERE task_id = 4;

-- Обновить название задачи номер 6 на: Заказать суши
UPDATE tasks
SET task_desc = 'Заказать суши'
WHERE task_id = 6;