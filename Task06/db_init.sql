DROP TABLE IF EXISTS work_records;
DROP TABLE IF EXISTS appointment_services;
DROP TABLE IF EXISTS appointments;
DROP TABLE IF EXISTS schedules;
DROP TABLE IF EXISTS services;
DROP TABLE IF EXISTS employees;

CREATE TABLE employees (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    position TEXT NOT NULL,
    hire_date TEXT NOT NULL CHECK(hire_date = strftime('%Y-%m-%d', hire_date)),
    dismissal_date TEXT CHECK(dismissal_date IS NULL OR dismissal_date = strftime('%Y-%m-%d', dismissal_date)),
    salary_percentage REAL NOT NULL CHECK(salary_percentage BETWEEN 0 AND 100),
    status TEXT NOT NULL DEFAULT 'active' CHECK(status IN ('active', 'fired')),
    phone TEXT,
    email TEXT
);

CREATE TABLE services (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL UNIQUE,
    duration_minutes INTEGER NOT NULL CHECK(duration_minutes > 0),
    price REAL NOT NULL CHECK(price >= 0)
);

CREATE TABLE schedules (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    employee_id INTEGER NOT NULL,
    day_of_week INTEGER NOT NULL CHECK(day_of_week BETWEEN 1 AND 7),
    start_time TEXT NOT NULL CHECK(start_time = strftime('%H:%M', start_time)),
    end_time TEXT NOT NULL CHECK(end_time = strftime('%H:%M', end_time)),
    FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE
);

CREATE TABLE appointments (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    employee_id INTEGER NOT NULL,
    client_name TEXT NOT NULL,
    client_phone TEXT,
    appointment_date TEXT NOT NULL CHECK(appointment_date = strftime('%Y-%m-%d', appointment_date)),
    appointment_time TEXT NOT NULL CHECK(appointment_time = strftime('%H:%M', appointment_time)),
    end_time TEXT,
    status TEXT NOT NULL DEFAULT 'scheduled' CHECK(status IN ('scheduled', 'in_progress', 'completed', 'cancelled')),
    total_price REAL NOT NULL DEFAULT 0 CHECK(total_price >= 0),
    FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE NO ACTION
);

CREATE TABLE appointment_services (
    appointment_id INTEGER NOT NULL,
    service_id INTEGER NOT NULL,
    quantity INTEGER NOT NULL DEFAULT 1 CHECK(quantity > 0),
    PRIMARY KEY (appointment_id, service_id),
    FOREIGN KEY (appointment_id) REFERENCES appointments(id) ON DELETE CASCADE,
    FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE RESTRICT
);

CREATE TABLE work_records (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    appointment_id INTEGER NOT NULL,
    employee_id INTEGER NOT NULL,
    service_id INTEGER NOT NULL,
    work_date TEXT NOT NULL CHECK(work_date = strftime('%Y-%m-%d', work_date)),
    work_time TEXT NOT NULL CHECK(work_time = strftime('%H:%M', work_time)),
    revenue REAL NOT NULL CHECK(revenue >= 0),
    FOREIGN KEY (appointment_id) REFERENCES appointments(id) ON DELETE NO ACTION,
    FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE NO ACTION,
    FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE RESTRICT
);

CREATE TABLE salary_reports (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    employee_id INTEGER NOT NULL,
    period_start TEXT NOT NULL CHECK(period_start = strftime('%Y-%m-%d', period_start)),
    period_end TEXT NOT NULL CHECK(period_end = strftime('%Y-%m-%d', period_end)),
    total_revenue REAL NOT NULL CHECK(total_revenue >= 0),
    salary_percentage REAL NOT NULL CHECK(salary_percentage BETWEEN 0 AND 100),
    calculated_salary REAL NOT NULL CHECK(calculated_salary >= 0),
    generated_date TEXT DEFAULT (DATE('now')),
    FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE NO ACTION
);


CREATE INDEX idx_employees_status ON employees(status);
CREATE INDEX idx_employees_hire_dismiss ON employees(hire_date, dismissal_date);

CREATE INDEX idx_schedules_employee_day ON schedules(employee_id, day_of_week);

CREATE INDEX idx_appointments_employee_date ON appointments(employee_id, appointment_date);
CREATE INDEX idx_appointments_date_status ON appointments(appointment_date, status);
CREATE INDEX idx_appointments_date_time ON appointments(appointment_date, appointment_time);

CREATE INDEX idx_work_records_employee_date ON work_records(employee_id, work_date);
CREATE INDEX idx_work_records_date ON work_records(work_date);

CREATE INDEX idx_salary_reports_employee_period ON salary_reports(employee_id, period_start, period_end);


INSERT INTO employees (name, position, hire_date, dismissal_date, salary_percentage, status, phone, email) VALUES
('Сергеев Иван Петрович', 'Старший мастер', '2023-03-15', NULL, 35.0, 'active', '+7-901-111-22-33', 'sergeev@avtoservice.ru'),
('Козлова Мария Александровна', 'Мастер-диагност', '2023-06-20', NULL, 32.5, 'active', '+7-902-222-33-44', 'kozlovam@avtoservice.ru'),
('Николаев Андрей Владимирович', 'Мастер', '2022-11-10', '2024-08-31', 28.0, 'fired', '+7-903-333-44-55', 'nikolaev@mail.ru'),
('Васнецова Ольга Сергеевна', 'Мастер', '2024-01-12', NULL, 30.0, 'active', '+7-904-444-55-66', 'vasnecova@avtoservice.ru'),
('Петров Алексей Дмитриевич', 'Мастер-шиномонтаж', '2024-02-28', NULL, 25.0, 'active', '+7-905-555-66-77', 'petrova@avtoservice.ru'),
('Смирнов Денис Игоревич', 'Мастер', '2023-09-05', '2024-10-15', 27.5, 'fired', '+7-906-666-77-88', 'smirnovd@yandex.ru');

INSERT INTO services (name, duration_minutes, price) VALUES
('Диагностика ходовой части', 60, 2000.00),
('Замена масла двигателя', 40, 3000.00),
('Замена масла в АКПП', 90, 7000.00),
('Компьютерная диагностика', 30, 1500.00),
('Замена тормозных дисков', 120, 10000.00),
('Замена передних колодок', 60, 4500.00),
('Замена задних колодок', 45, 4000.00),
('Замена шаровой опоры', 90, 5500.00),
('Замена сайлентблоков', 120, 8000.00),
('Балансировка колес (комплект)', 40, 2000.00),
('Шиномонтаж (1 колесо)', 20, 800.00),
('Развал-схождение 3D', 60, 3500.00),
('Замена амортизатора', 90, 6000.00),
('Замена ремня ГРМ', 180, 12000.00),
('Промывка инжектора', 60, 4000.00);

INSERT INTO schedules (employee_id, day_of_week, start_time, end_time) VALUES
(1, 1, '08:00', '18:00'),
(1, 2, '08:00', '18:00'),
(1, 3, '08:00', '18:00'),
(1, 4, '08:00', '18:00'),
(1, 5, '08:00', '18:00'),
(2, 2, '09:00', '19:00'),
(2, 3, '09:00', '19:00'),
(2, 4, '09:00', '19:00'),
(2, 5, '09:00', '19:00'),
(2, 6, '09:00', '19:00'),
(4, 1, '10:00', '20:00'),
(4, 2, '10:00', '20:00'),
(4, 3, '10:00', '20:00'),
(4, 4, '10:00', '20:00'),
(4, 5, '10:00', '20:00'),
(5, 1, '08:00', '17:00'),
(5, 2, '08:00', '17:00'),
(5, 3, '08:00', '17:00'),
(5, 4, '08:00', '17:00'),
(5, 5, '08:00', '17:00'),
(5, 6, '08:00', '17:00');

INSERT INTO appointments (employee_id, client_name, client_phone, appointment_date, appointment_time, end_time, status, total_price) VALUES
(1, 'Иванов Константин Викторович', '+7-911-123-45-67', '2024-12-02', '09:00', '10:00', 'completed', 2000.00),
(1, 'Семенова Елена Алексеевна', '+7-911-234-56-78', '2024-12-02', '11:00', '12:40', 'completed', 7000.00),
(5, 'Крылов Михаил Юрьевич', '+7-911-345-67-89', '2024-12-02', '10:00', '10:40', 'completed', 2000.00),
(2, 'Федоров Павел Сергеевич', '+7-911-456-78-90', '2024-12-03', '10:00', '10:30', 'completed', 1500.00),
(1, 'Громова Анна Дмитриевна', '+7-911-567-89-01', '2024-12-03', '14:00', '16:00', 'completed', 10000.00),
(4, 'Белов Артем Игоревич', '+7-911-678-90-12', '2024-12-03', '11:00', '13:00', 'completed', 8000.00),
(2, 'Тарасов Виктор Николаевич', '+7-911-789-01-23', '2024-12-04', '11:00', '12:00', 'completed', 4000.00),
(5, 'Орлова Дарья Владимировна', '+7-911-890-12-34', '2024-12-04', '09:00', '10:00', 'completed', 3200.00),
(1, 'Жуков Александр Петрович', '+7-911-901-23-45', '2024-12-04', '15:00', '17:00', 'scheduled', 5500.00),
(4, 'Мельников Сергей Олегович', '+7-912-012-34-56', '2024-12-05', '12:00', '15:00', 'scheduled', 12000.00),
(2, 'Павлова Ирина Валерьевна', '+7-912-123-45-67', '2024-12-05', '14:00', '15:00', 'scheduled', 3500.00),
(5, 'Данилов Роман Андреевич', '+7-912-234-56-78', '2024-12-06', '08:30', '10:30', 'scheduled', 6000.00),
(1, 'Титова Марина Сергеевна', '+7-912-345-67-89', '2024-12-06', '11:00', '12:00', 'scheduled', 4500.00);

INSERT INTO appointment_services (appointment_id, service_id, quantity) VALUES
(1, 1, 1),
(2, 3, 1),
(3, 10, 1),
(4, 4, 1),
(5, 5, 1),
(6, 9, 1),
(7, 7, 1),
(8, 11, 4),
(9, 8, 1),
(10, 14, 1),
(11, 12, 1),
(12, 13, 1),
(13, 6, 1); 

INSERT INTO work_records (appointment_id, employee_id, service_id, work_date, work_time, revenue) VALUES
(1, 1, 1, '2024-12-02', '09:00', 2000.00),
(2, 1, 3, '2024-12-02', '11:00', 7000.00),
(5, 1, 5, '2024-12-03', '14:00', 10000.00),
(4, 2, 4, '2024-12-03', '10:00', 1500.00),
(7, 2, 7, '2024-12-04', '11:00', 4000.00),
(6, 4, 9, '2024-12-03', '11:00', 8000.00),
(3, 5, 10, '2024-12-02', '10:00', 2000.00),
(8, 5, 11, '2024-12-04', '09:00', 3200.00);

INSERT INTO salary_reports (employee_id, period_start, period_end, total_revenue, salary_percentage, calculated_salary) VALUES
(1, '2024-12-01', '2024-12-04', 19000.00, 35.0, 6650.00),
(2, '2024-12-01', '2024-12-04', 5500.00, 32.5, 1787.50),
(4, '2024-12-01', '2024-12-04', 8000.00, 30.0, 2400.00),
(5, '2024-12-01', '2024-12-04', 5200.00, 25.0, 1300.00),
(3, '2024-11-01', '2024-11-30', 45000.00, 28.0, 12600.00),
(6, '2024-10-01', '2024-10-31', 32000.00, 27.5, 8800.00);