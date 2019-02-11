INSERT INTO category_list(user_name, category_name)
VALUES 
('olga98', 'Входящие'),
('olga98', 'Учеба'),
('stalker345', 'Работа'),
('stalker345', 'Домашние дела'),
(null, 'Авто');

INSERT INTO tasks(task_desc, date_require, category_name, task_state, user_name)
VALUES 
('Собеседование в IT компании', '2019-02-09', 'Работа', 0, 'stalker345'),
('Выполнить тестовое задание', '2019-12-25', 'Работа', 0, 'stalker345'),
('Сделать задание первого раздела', '2019-02-09', 'Учеба', 1, 'olga98'),
('Встреча с другом', '2019-12-22', 'Входящие', 0, 'olga98'),
('Купить корм для кота', null, 'Домашние дела', 0, 'stalker345'),
('Заказать пиццу', null, 'Домашние дела', 0, 'stalker345');

INSERT INTO users(user_name, user_password, user_email)
VALUES
('stalker345', 'hsda3a1ijDs', 'stalker345@yandex.ru'),
('olga98', 'catmouse444', 'olga98@gmail.com');

-- Получить список всех проектов пользователя: stalker345
SELECT category_name
FROM category_list
WHERE user_name = 'stalker345';

-- Получить список всех задач для проекта: Домашние дела
SELECT task_desc
FROM tasks
WHERE category_name = 'Домашние дела';

-- Пометить задачу номер 4 как выполненную
UPDATE tasks
SET task_state = 1
WHERE task_id = 4;

-- Обновить название задачи номер 6 на: Заказать суши
UPDATE tasks
SET task_desc = 'Заказать суши'
WHERE task_id = 6;