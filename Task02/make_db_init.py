import csv
import os
import re

from typing import Optional, Tuple

BASE_PATH = os.path.dirname(__file__)
DATASET_PATH = os.path.join(BASE_PATH, "dataset")

FILES = {
    "movies": os.path.join(DATASET_PATH, "movies.csv"),
    "ratings": os.path.join(DATASET_PATH, "ratings.csv"),
    "tags": os.path.join(DATASET_PATH, "tags.csv"),
    "users": os.path.join(DATASET_PATH, "users.txt"),
}

def escape(value: str) -> str:
    """Экранируем кавычки для SQL."""
    return value.replace("'", "''")


def extract_year(title: str) -> Tuple[str, Optional[int]]:
    """Вырезаем год из названия в скобках, если есть"""
    match = re.search(r"\((\d{4})\)$", title.strip())
    if match:
        year = int(match.group(1))
        clean_title = title[: match.start()].strip()
        return clean_title, year
    return title, None


def generate_movies(writer):
    writer.write("DROP TABLE IF EXISTS movies;\n")
    writer.write("""
    CREATE TABLE movies (
        id INTEGER PRIMARY KEY,
        title TEXT,
        year INTEGER,
        genres TEXT
    );
    \n""")
    with open(FILES["movies"], encoding="utf-8") as f:
        reader = csv.reader(f)
        header = next(reader)  # пропускаем заголовки
        for row in reader:
            # формат: movieId, title, genres
            movie_id, title, genres = row
            clean_title, year = extract_year(title)
            year_sql = year if year else "NULL"
            writer.write(
                f"INSERT INTO movies (id, title, year, genres) "
                f"VALUES ({movie_id}, '{escape(clean_title)}', {year_sql}, '{escape(genres)}');\n"
            )

def generate_ratings(writer):
    writer.write("DROP TABLE IF EXISTS ratings;\n")
    writer.write("""
    CREATE TABLE ratings (
        id INTEGER PRIMARY KEY,
        user_id INTEGER,
        movie_id INTEGER,
        rating REAL,
        timestamp TEXT
    );
    \n""")
    with open(FILES["ratings"], encoding="utf-8") as f:
        reader = csv.reader(f)
        header = next(reader)  # пропускаем заголовок
        for idx, row in enumerate(reader, start=1):  # генерируем id сами
            user_id, movie_id, rating, ts = row
            writer.write(
                f"INSERT INTO ratings (id, user_id, movie_id, rating, timestamp) "
                f"VALUES ({idx}, {user_id}, {movie_id}, {rating}, '{ts}');\n"
            )

def generate_tags(writer):
    writer.write("DROP TABLE IF EXISTS tags;\n")
    writer.write("""
    CREATE TABLE tags (
        id INTEGER PRIMARY KEY,
        user_id INTEGER,
        movie_id INTEGER,
        tag TEXT,
        timestamp TEXT
    );
    \n""")
    with open(FILES["tags"], encoding="utf-8") as f:
        reader = csv.reader(f)
        header = next(reader)
        for idx, row in enumerate(reader, start=1):  # генерируем id сами
            user_id, movie_id, tag, ts = row
            writer.write(
                f"INSERT INTO tags (id, user_id, movie_id, tag, timestamp) "
                f"VALUES ({idx}, {user_id}, {movie_id}, '{escape(tag)}', '{ts}');\n"
            )

def generate_users(writer):
    writer.write("DROP TABLE IF EXISTS users;\n")
    writer.write("""
    CREATE TABLE users (
        id INTEGER PRIMARY KEY,
        name TEXT,
        email TEXT,
        gender TEXT,
        register_date TEXT,
        occupation TEXT
    );
    \n""")
    with open(FILES["users"], encoding="utf-8") as f:
        for i, line in enumerate(f, 1):
            row = line.strip().split("|")
            if not row or len(row) < 6:
                print(f"⚠️ Строка {i}: пропущена (полей: {len(row)}) — {line!r}")
                continue
            u_id, name, email, gender, reg_date, occupation = row

            # Проверим, есть ли проблемные символы
            if '\n' in name or '\r' in name or '\n' in occupation:
                print(f"❗️ Строка {i}: найден \\n или \\r в данных: {name!r}, {occupation!r}")

            sql = (
                f"INSERT INTO users (id, name, email, gender, register_date, occupation) "
                f"VALUES ({u_id}, '{escape(name)}', '{escape(email)}', '{gender}', '{reg_date}', '{escape(occupation)}');\n"
            )
            print(f"Строка {i} SQL: {sql[:]}")  # первые 100 символов
            writer.write(sql)


def main():
    output_path = os.path.join(BASE_PATH, "db_init.sql")
    with open(output_path, "w", encoding="utf-8") as sql_file:
        generate_movies(sql_file)
        generate_ratings(sql_file)
        generate_tags(sql_file)
        generate_users(sql_file)
    print(f"✅ db_init.sql создан в {output_path}")

if __name__ == "__main__":
    main()
