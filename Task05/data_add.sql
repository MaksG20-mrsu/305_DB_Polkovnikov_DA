INSERT INTO users (name, email, gender, register_date, occupation)
VALUES ('Дмитрий Полковников', 'dmitry.polkovnikov@student.university.edu', 'male', date('now'), 'student');

INSERT INTO users (name, email, gender, register_date, occupation)
VALUES ('Владислав Наумкин', 'vladislav.naumkin@university.edu', 'male', date('now'), 'student');

INSERT INTO users (name, email, gender, register_date, occupation)
VALUES ('Дмитрий Пузаков', 'dmitry.puzakov@university.edu', 'male', date('now'), 'student');

INSERT INTO users (name, email, gender, register_date, occupation)
VALUES ('Василий Паркаев', 'vasya.parkaev@university.edu', 'male', date('now'), 'student');

INSERT INTO users (name, email, gender, register_date, occupation)
VALUES ('Полина Пшеницына', 'polina.pshenitsina@university.edu', 'female', date('now'), 'student');

INSERT INTO movies (title, year)
VALUES ('Форрест Гамп 1994', 2026);

INSERT INTO movie_genres (movie_id, genre_id)
SELECT m.id, g.id 
FROM movies m, genres g 
WHERE m.title = 'Форрест Гамп 1994' AND g.name = 'Action';

INSERT INTO movies (title, year)
VALUES ('Операция «Ы» и другие приключения Шурика 1965', 2026);

INSERT INTO movie_genres (movie_id, genre_id)
SELECT m.id, g.id 
FROM movies m, genres g 
WHERE m.title = 'Операция «Ы» и другие приключения Шурика 1965' AND g.name = 'Comedy';

INSERT INTO movies (title, year)
VALUES ('Аватар 2009', 2026);

INSERT INTO movie_genres (movie_id, genre_id)
SELECT m.id, g.id 
FROM movies m, genres g 
WHERE m.title = 'Аватар 2009' AND g.name = 'Sci-Fi';

INSERT INTO ratings (user_id, movie_id, rating, timestamp)
SELECT 
    (SELECT id FROM users WHERE email = 'dmitry.polkovnikov@student.university.edu'),
    (SELECT id FROM movies WHERE title = 'Форрест Гамп 1994'),
    4.5,
    strftime('%s', 'now');

INSERT INTO ratings (user_id, movie_id, rating, timestamp)
SELECT 
    (SELECT id FROM users WHERE email = 'dmitry.polkovnikov@student.university.edu'),
    (SELECT id FROM movies WHERE title = 'Операция «Ы» и другие приключения Шурика 1965'),
    5.0,
    strftime('%s', 'now');

INSERT INTO ratings (user_id, movie_id, rating, timestamp)
SELECT 
    (SELECT id FROM users WHERE email = 'dmitry.polkovnikov@student.university.edu'),
    (SELECT id FROM movies WHERE title = 'Аватар 2009'),
    4.0,
    strftime('%s', 'now');
