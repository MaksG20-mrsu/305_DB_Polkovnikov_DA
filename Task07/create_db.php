<?php

$databaseFile = __DIR__ . '/university.db';

try {
    $db = new PDO("sqlite:$databaseFile");
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

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
            FOREIGN KEY (group_id) REFERENCES groups(id)
        )
    ");

    $rawStudents = [
        ['1', 'Программная инженерия', 'Зубков Роман Сергеевич', 'М'],
        ['1', 'Программная инженерия', 'Иванов Максим Александрович', 'М'],
        ['1', 'Программная инженерия', 'Ивенин Артём Андреевич', 'М'],
        ['2', 'Программная инженерия', 'Казейкин Иван Иванович', 'М'],
        ['2', 'Программная инженерия', 'Колыганов Александр Павлович', 'М'],
        ['1', 'Программная инженерия', 'Кочнев Артем Алексеевич', 'М'],
        ['1', 'Программная инженерия', 'Логунов Илья Сергеевич', 'М'],
        ['1', 'Программная инженерия', 'Макарова Юлия Сергеевна', 'Ж'],
        ['2', 'Программная инженерия', 'Маклаков Сергей Александрович', 'М'],
        ['1', 'Программная инженерия', 'Маскинскова Наталья Сергеевна', 'Ж'],
        ['1', 'Программная инженерия', 'Мукасеев Дмитрий Александрович', 'М'],
        ['1', 'Программная инженерия', 'Наумкин Владислав Валерьевич', 'М'],
        ['2', 'Программная инженерия', 'Паркаев Василий Александрович', 'М'],
        ['2', 'Программная инженерия', 'Полковников Дмитрий Александрович', 'М'],
        ['2', 'Программная инженерия', 'Пузаков Дмитрий Александрович', 'М'],
        ['2', 'Программная инженерия', 'Пшеницына Полина Алексеевна', 'Ж'],
        ['2', 'Программная инженерия', 'Пяткин Игорь Алексеевич', 'М'],
        ['1', 'Программная инженерия', 'Рыбаков Евгений Геннадьевич', 'М'],
        ['2', 'Программная инженерия', 'Рыжкин Владислав Дмитриевич', 'М'],
        ['1', 'Программная инженерия', 'Рябченко Александра Станиславовна', 'Ж'],
        ['2', 'Программная инженерия', 'Снегирев Данил Александрович', 'М'],
        ['2', 'Программная инженерия', 'Тульсков Илья Андреевич', 'М'],
        ['2', 'Программная инженерия', 'Фирстов Артём Александрович', 'М'],
        ['2', 'Программная инженерия', 'Четайкин Владислав Александрович', 'М'],
        ['2', 'Программная инженерия', 'Шарунов Максим Игоревич', 'М'],
        ['1', 'Программная инженерия', 'Шушев Денис Сергеевич', 'М'],
    ];

    $currentYear = (int)date('Y');
    $graduationYear = $currentYear + 3; // группа "действующая"

    $groups = [];
    foreach ($rawStudents as $s) {
        $key = $s[0] . '|' . $s[1];
        if (!isset($groups[$key])) {
            $groups[$key] = ['number' => $s[0], 'program' => $s[1]];
        }
    }

    $groupIdMap = [];
    $stmtGroup = $db->prepare("INSERT INTO groups (number, program, graduation_year) VALUES (?, ?, ?)");
    $stmtCheckGroup = $db->prepare("SELECT id FROM groups WHERE number = ? AND program = ?");

    foreach ($groups as $key => $g) {
        $stmtCheckGroup->execute([$g['number'], $g['program']]);
        $exists = $stmtCheckGroup->fetch();
        if ($exists) {
            $groupIdMap[$key] = $exists['id'];
        } else {
            $stmtGroup->execute([$g['number'], $g['program'], $graduationYear]);
            $groupIdMap[$key] = $db->lastInsertId();
        }
    }

    // Вставляем студентов
    $usedStudentIds = [];
    $stmtStudent = $db->prepare("
        INSERT INTO students (group_id, full_name, gender, birth_date, student_id)
        VALUES (?, ?, ?, ?, ?)
    ");

    foreach ($rawStudents as $s) {
        [$groupNum, $program, $name, $gender] = $s;
        $key = "$groupNum|$program";
        $groupId = $groupIdMap[$key];

        $age = rand(19, 21);
        $birthYear = $currentYear - $age;
        $birthMonth = str_pad(rand(1, 12), 2, '0', STR_PAD_LEFT);
        $birthDay = str_pad(rand(1, 28), 2, '0', STR_PAD_LEFT);
        $birthDate = "$birthYear-$birthMonth-$birthDay";

        do {
            $studentId = str_pad(rand(10000000, 99999999), 8, '0', STR_PAD_LEFT);
        } while (in_array($studentId, $usedStudentIds));

        $usedStudentIds[] = $studentId;

        $stmtStudent->execute([$groupId, $name, $gender, $birthDate, $studentId]);
    }

} catch (PDOException $e) {
    die("Ошибка: " . $e->getMessage() . "\n");
}