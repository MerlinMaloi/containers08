# Лабораторная работа №7

## Цель работы

В рамках данной работы студенты научатся настраивать непрерывную интеграцию с помощью Github Actions.

## Задание

Создать Web приложение, написать тесты для него и настроить непрерывную интеграцию с помощью Github Actions на базе контейнеров.


## Выполнение 

Создал репозиторий `containers08` и перенес его себе на компьютер.

В директории `containers08` создал директорию `./site`. В директории `./site` будет располагаться Web приложение на базе PHP.


## Создание Web приложения

Создал в директории `./site` Web приложение на базе PHP со следующей структурой:

```
site
├── modules/
│   ├── database.php
│   └── page.php
├── templates/
│   └── index.tpl
├── styles/
│   └── style.css
├── config.php
└── index.php
```

Файл `modules/database.php` содержит класс `Database` для работы с базой данных. Для работы с базой данных используйте SQLite. Класс должен содержать методы:

- `__construct($path)` - конструктор класса, принимает путь к файлу базы данных SQLite;
- `Execute($sql)` - выполняет SQL запрос;
- `Fetch($sql)` - выполняет SQL запрос и возвращает результат в виде ассоциативного массива.
- `Create($table, $data)` - создает запись в таблице `$table` с данными из ассоциативного массива `$data` и возвращает идентификатор созданной записи;
- `Read($table, $id)` - возвращает запись из таблицы `$table` по идентификатору `$id`;
- `Update($table, $id, $data)` - обновляет запись в таблице `$table` по идентификатору `$id`данными из ассоциативного массива `$data`;
- `Delete($table, $id)` - удаляет запись из таблицы `$table` по идентификатору `$id`.
- `Count($table)` - возвращает количество записей в таблице `$table`.

Файл `modules/page.php` содержит класс Page для работы с страницами. Класс должен содержать методы:
- `__construct($template)` - конструктор класса, принимает путь к шаблону страницы;
- `Render($data)` - отображает страницу, подставляя в шаблон данные из ассоциативного массива `$data`.

Файл `templates/index.tpl` содержит шаблон страницы.

Файл `styles/style.css` может содержать стили для страницы.

Файл `index.php` содержит код для отображения страницы.

Файл `config.php` содержит настройки для подключения к базе данных.

## Подготовка SQL файла для базы данных

Создал в корневом каталоге директорию `./sql`. В созданной директории файл `schema.sql` со следующим содержимым:

```
CREATE TABLE page (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    title TEXT,
    content TEXT
);

INSERT INTO page (title, content) VALUES ('Page 1', 'Content 1');
INSERT INTO page (title, content) VALUES ('Page 2', 'Content 2');
INSERT INTO page (title, content) VALUES ('Page 3', 'Content 3');
```


## Создание тестов

Создал в корневом каталоге директорию `./tests`. В созданном каталоге файл `testframework.php` со следующим содержимым:

```
<?php

function message($type, $message) {
    $time = date('Y-m-d H:i:s');
    echo "{$time} [{$type}] {$message}" . PHP_EOL;
}

function info($message) {
    message('INFO', $message);
}

function error($message) {
    message('ERROR', $message);
}

function assertExpression($expression, $pass = 'Pass', $fail = 'Fail'): bool {
    if ($expression) {
        info($pass);
        return true;
    }
    error($fail);
    return false;
}

class TestFramework {
    private $tests = [];
    private $success = 0;

    public function add($name, $test) {
        $this->tests[$name] = $test;
    }

    public function run() {
        foreach ($this->tests as $name => $test) {
            info("Running test {$name}");
            if ($test()) {
                $this->success++;
            }
            info("End test {$name}");
        }
    }

    public function getResult() {
        return "{$this->success} / " . count($this->tests);
    }
}
```

Создал в директории `./tests файл tests.php` с тестами для всех методов класса `Database`, а также для методов класса `Page`.

## Создание Dockerfile

Создал в корневом каталоге файл `Dockerfile` со следующим содержимым:

```
FROM php:7.4-fpm as base

RUN apt-get update && \
    apt-get install -y sqlite3 libsqlite3-dev && \
    docker-php-ext-install pdo_sqlite

VOLUME ["/var/www/db"]

COPY sql/schema.sql /var/www/db/schema.sql

RUN echo "prepare database" && \
    cat /var/www/db/schema.sql | sqlite3 /var/www/db/db.sqlite && \
    chmod 777 /var/www/db/db.sqlite && \
    rm -rf /var/www/db/schema.sql && \
    echo "database is ready"

COPY site /var/www/html
```

## Настройка Github Actions

*P.S. один из самых озадачивших моментов на моменте запуска и тестирования*

Создал в корневом каталоге репозитория файл `.github/workflows/main.yml` со следующим содержимым:

```
name: CI

on:
  push:
    branches:
      - main

jobs:
  build:
    runs-on: ubuntu-latest
    steps:
      - name: Checkout
        uses: actions/checkout@v4
      - name: Build the Docker image
        run: docker build -t containers08 .
      - name: Create `container`
        run: docker create --name container --volume database:/var/www/db containers08
      - name: Copy tests to the container
        run: docker cp ./tests container:/var/www/html
      - name: Up the container
        run: docker start container
      - name: Run tests
        run: docker exec container php /var/www/html/tests/tests.php
      - name: Stop the container
        run: docker stop container
      - name: Remove the container
        run: docker rm container
```

## Запуск и тестирование

Отправлял изменения в репозиторий до успешного прохождения тестов. Для этого зашел во вкладку `Actions` в репозитории и пробовал тесты и вообще запуск `Действии`


## Ответы на вопросы:

1. Что такое непрерывная интеграция?

Непрерывная интеграция это практика в разработке, при которой изменения в коде регулярно интегрируются в общий репозитории разработчиков с автоматическим процессом сборки и тестированием для легкого обнаружения источника неполадок

2. Для чего нужны юнит-тесты? Как часто их нужно запускать?

Юнит тесты нужны для отслеживания исправности системы путем тестирования конкретных методов или функции и запускать их нужно после каждого обновления/commit'а если точнее: при каждом коммите или пуше (желательно через CI, например, GitHub Actions);/перед слиянием кода в основную ветку; /после рефакторинга или изменения логики.

3. Что нужно изменить в файле .github/workflows/main.yml для того, чтобы тесты запускались при каждом создании запроса на слияние (Pull Request)?
- ``` 
        on:
        push:
        branches:
        - main
```

4. Что нужно добавить в файл .github/workflows/main.yml для того, чтобы удалять созданные образы после выполнения тестов?
- ```
      - name: Stop the container
        run: docker stop container
      - name: Remove the container
        run: docker rm container
```
