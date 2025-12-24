<?php
require_once __DIR__ . '/src/db.php';

$db = getDatabaseConnection();

$db->exec("
    CREATE TABLE IF NOT EXISTS groups (
        id INTEGER PRIMARY KEY,
        number TEXT NOT NULL,
        program TEXT NOT NULL,
        graduation_year INTEGER NOT NULL
    )
");

$db->exec("
    CREATE TABLE IF NOT EXISTS students (
        id INTEGER PRIMARY KEY,
        group_id INTEGER NOT NULL,
        full_name TEXT NOT NULL,
        gender TEXT NOT NULL CHECK(gender IN ('М', 'Ж')),
        birth_date TEXT NOT NULL,
        student_id TEXT NOT NULL UNIQUE,
        FOREIGN KEY (group_id) REFERENCES groups(id) ON DELETE CASCADE
    )
");

$db->exec("
    CREATE TABLE IF NOT EXISTS disciplines (
        id INTEGER PRIMARY KEY,
        program TEXT NOT NULL,
        course INTEGER NOT NULL,
        name TEXT NOT NULL,
        UNIQUE(program, course, name)
    )
");

$db->exec("
    CREATE TABLE IF NOT EXISTS exams (
        id INTEGER PRIMARY KEY,
        student_id INTEGER NOT NULL,
        discipline_id INTEGER NOT NULL,
        exam_date TEXT NOT NULL,
        score INTEGER NOT NULL CHECK(score BETWEEN 2 AND 5),
        FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
        FOREIGN KEY (discipline_id) REFERENCES disciplines(id) ON DELETE CASCADE
    )
");

$groups = [
    ['1', 'Программная инженерия', 2028],
    ['2', 'Программная инженерия', 2028],
];
$stmt = $db->prepare("INSERT OR IGNORE INTO groups (number, program, graduation_year) VALUES (?, ?, ?)");
foreach ($groups as $g) {
    $stmt->execute($g);
}

$disciplines = [
    ['Программная инженерия', 1, 'Математический анализ'],
    ['Программная инженерия', 1, 'Основы программирования'],
    ['Программная инженерия', 2, 'Алгоритмы и структуры данных'],
    ['Программная инженерия', 2, 'Математическая логика'],
    ['Программная инженерия', 3, 'Базы данных'],
    ['Программная инженерия', 3, 'Машинное обучений'],
];

$stmt = $db->prepare("INSERT OR IGNORE INTO disciplines (program, course, name) VALUES (?, ?, ?)");
foreach ($disciplines as $d) {
    $stmt->execute($d);
}

$students = [
    [1, 'Зубков Роман Сергеевич', 'М', '2004-05-12', '12345678'],
    [1, 'Иванов Максим Александрович', 'М', '2003-11-30', '12345679'],
    [1, 'Ивенин Артём Андреевич', 'М', '2005-02-14', '12345680'],
    [2, 'Казейкин Иван Иванович', 'М', '2004-08-22', '12345681'],
    [2, 'Колыганов Александр Павлович', 'М', '2003-07-19', '12345682'],
    [1, 'Кочнев Артем Алексеевич', 'М', '2004-12-03', '12345683'],
    [1, 'Логунов Илья Сергеевич', 'М', '2005-01-17', '12345684'],
    [1, 'Макарова Юлия Сергеевна', 'Ж', '2004-09-28', '12345685'],
    [2, 'Маклаков Сергей Александрович', 'М', '2003-04-11', '12345686'],
    [1, 'Маскинскова Наталья Сергеевна', 'Ж', '2005-06-05', '12345687'],
    [1, 'Мукасеев Дмитрий Александрович', 'М', '2004-03-21', '12345688'],
    [1, 'Наумкин Владислав Валерьевич', 'М', '2003-10-14', '12345689'],
    [2, 'Паркаев Василий Александрович', 'М', '2004-07-30', '12345690'],
    [2, 'Полковников Дмитрий Александрович', 'М', '2005-11-09', '12345691'],
    [2, 'Пузаков Дмитрий Александрович', 'М', '2004-01-25', '12345692'],
    [2, 'Пшеницына Полина Алексеевна', 'Ж', '2003-12-18', '12345693'],
    [2, 'Пяткин Игорь Алексеевич', 'М', '2004-04-02', '12345694'],
    [1, 'Рыбаков Евгений Геннадьевич', 'М', '2005-08-13', '12345695'],
    [2, 'Рыжкин Владислав Дмитриевич', 'М', '2004-10-07', '12345696'],
    [1, 'Рябченко Александра Станиславовна', 'Ж', '2003-05-29', '12345697'],
    [2, 'Снегирев Данил Александрович', 'М', '2004-06-16', '12345698'],
    [2, 'Тульсков Илья Андреевич', 'М', '2005-09-04', '12345699'],
    [2, 'Фирстов Артём Александрович', 'М', '2003-02-28', '12345700'],
    [2, 'Четайкин Владислав Александрович', 'М', '2004-11-22', '12345701'],
    [2, 'Шарунов Максим Игоревич', 'М', '2005-03-11', '12345702'],
    [1, 'Шушев Денис Сергеевич', 'М', '2004-07-09', '12345703'],
];

$stmt = $db->prepare("INSERT OR IGNORE INTO students (group_id, full_name, gender, birth_date, student_id) VALUES (?, ?, ?, ?, ?)");
foreach ($students as $s) {
    $stmt->execute($s);
}