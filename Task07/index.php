<?php
// === Настройки ===
$databaseFile = __DIR__ . '/university.db';

// Создаём БД, если отсутствует
if (!file_exists($databaseFile)) {
    require_once __DIR__ . '/create_db.php';
}

// Подключаемся к базе
try {
    $db = new PDO("sqlite:$databaseFile");
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Ошибка подключения к базе данных: " . htmlspecialchars($e->getMessage()));
}

$currentYear = (int)date('Y');

// Получаем список действующих групп
$stmt = $db->prepare("
    SELECT DISTINCT number
    FROM groups
    WHERE graduation_year >= ?
    ORDER BY number
");
$stmt->execute([$currentYear]);
$validGroups = $stmt->fetchAll(PDO::FETCH_COLUMN);

// Получаем выбранный номер группы из POST (или null, если не выбран)
$selectedGroup = $_POST['group'] ?? null;
if ($selectedGroup !== '' && $selectedGroup !== null && !in_array($selectedGroup, $validGroups)) {
    $selectedGroup = null; // Защита от подделки
}

// Формируем запрос
$sql = "
    SELECT
        g.number AS group_number,
        g.program,
        s.full_name,
        s.gender,
        s.birth_date,
        s.student_id
    FROM students s
    JOIN groups g ON s.group_id = g.id
    WHERE g.graduation_year >= :current_year
";

$params = ['current_year' => $currentYear];

if ($selectedGroup !== null && $selectedGroup !== '') {
    $sql .= " AND g.number = :group_number";
    $params['group_number'] = $selectedGroup;
}

$sql .= " ORDER BY g.number, s.full_name";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$students = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Список студентов</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background-color: #f9f9f9;
        }
        h1 {
            color: #2c3e50;
        }
        form {
            margin-bottom: 20px;
        }
        label {
            font-weight: bold;
            margin-right: 10px;
        }
        select, button {
            padding: 6px 10px;
            font-size: 16px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            background-color: white;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #3498db;
            color: white;
        }
        tr:hover {
            background-color: #f5f5f5;
        }
        .empty {
            color: #e74c3c;
            font-style: italic;
        }
    </style>
</head>
<body>

    <h1>Список студентов действующих групп</h1>

    <form method="POST">
        <label for="group">Фильтр по группе:</label>
        <select name="group" id="group">
            <option value="" <?= $selectedGroup === '' || $selectedGroup === null ? 'selected' : '' ?>>
                Все группы
            </option>
            <?php foreach ($validGroups as $groupNum): ?>
                <option value="<?= htmlspecialchars($groupNum) ?>" <?= $selectedGroup === $groupNum ? 'selected' : '' ?>>
                    <?= htmlspecialchars($groupNum) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <button type="submit">Применить</button>
    </form>

    <?php if (empty($students)): ?>
        <p class="empty">Нет студентов по указанному фильтру.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Группа</th>
                    <th>Направление подготовки</th>
                    <th>ФИО</th>
                    <th>Пол</th>
                    <th>Дата рождения</th>
                    <th>Номер студенческого билета</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($students as $s): ?>
                    <tr>
                        <td><?= htmlspecialchars($s['group_number']) ?></td>
                        <td><?= htmlspecialchars($s['program']) ?></td>
                        <td><?= htmlspecialchars($s['full_name']) ?></td>
                        <td><?= htmlspecialchars($s['gender']) ?></td>
                        <td><?= htmlspecialchars($s['birth_date']) ?></td>
                        <td><?= htmlspecialchars($s['student_id']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

</body>
</html>