<?php
function mb_str_pad_($str, $length, $pad_string = ' ', $pad_type = STR_PAD_RIGHT) {
    $diff = strlen($str) - mb_strlen($str, 'UTF-8');
    return str_pad($str, $length + $diff, $pad_string, $pad_type);
}

// Путь к базе данных
$databaseFile = __DIR__ . '/university.db';

if (!file_exists($databaseFile)) {
    echo "База данных не найдена. Запускаем create_db.php...\n";
    require_once __DIR__ . '/create_db.php';
    echo "База данных создана!\n\n";
}

try {
    $db = new PDO("sqlite:$databaseFile");
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Ошибка подключения к базе данных: " . $e->getMessage() . "\n");
}

$currentYear = (int)date('Y');

$stmt = $db->prepare("
    SELECT DISTINCT number
    FROM groups
    WHERE graduation_year >= ?
    ORDER BY number
");
$stmt->execute([$currentYear]);
$validGroups = $stmt->fetchAll(PDO::FETCH_COLUMN);

if (empty($validGroups)) {
    echo "Нет действующих групп на " . $currentYear . " год.\n";
    exit(1);
}

echo "Доступные номера групп:\n";
echo implode(', ', $validGroups) . "\n";
echo "Введите номер группы (или нажмите Enter для вывода всех): ";

$input = trim(fgets(STDIN));

if ($input !== '' && !in_array($input, $validGroups)) {
    echo "Ошибка: группы '$input' нет в списке действующих.\n";
    exit(1);
}

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

if ($input !== '') {
    $sql .= " AND g.number = :group_number";
    $params['group_number'] = $input;
}

$sql .= " ORDER BY g.number, s.full_name";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$students = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($students)) {
    echo "Нет студентов по указанному фильтру.\n";
    exit(0);
}

// === Вывод в псевдографической таблице ===

$headers = ['Группа', 'Направление', 'ФИО', 'Пол', 'Дата рожд.', 'Студ. билет'];
$widths = array_map('strlen', $headers);

foreach ($students as $row) {
    $widths[0] = max($widths[0], strlen($row['group_number']));
    $widths[1] = max($widths[1], mb_strlen($row['program'], 'UTF-8'));
    $widths[2] = max($widths[2], mb_strlen($row['full_name'], 'UTF-8'));
    $widths[3] = max($widths[3], strlen($row['gender']));
    $widths[4] = max($widths[4], strlen($row['birth_date']));
    $widths[5] = max($widths[5], strlen($row['student_id']));
}

$totalWidth = array_sum($widths) + (count($widths) * 3) + 1;
$border = str_repeat('─', $totalWidth);

echo $border . "\n";

$row = '│';
for ($i = 0; $i < count($headers); $i++) {
    $row .= ' ' . mb_str_pad_($headers[$i], $widths[$i]) . ' │';
}
echo $row . "\n";
echo $border . "\n";

foreach ($students as $s) {
    $row = '│';
    $row .= ' ' . str_pad($s['group_number'], $widths[0]) . ' │';
    $row .= ' ' . mb_str_pad_($s['program'], $widths[1]) . ' │';
    $row .= ' ' . mb_str_pad_($s['full_name'], $widths[2]) . ' │';
    $row .= ' ' . str_pad($s['gender'], $widths[3]) . ' │';
    $row .= ' ' . str_pad($s['birth_date'], $widths[4]) . ' │';
    $row .= ' ' . str_pad($s['student_id'], $widths[5]) . ' │';
    echo $row . "\n";
}

echo $border . "\n";