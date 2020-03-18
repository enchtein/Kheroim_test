<?php ## Данные для БД
/* Подключение к базе данных MySQL с помощью вызова драйвера */
$dsn = 'mysql:dbname=nexteum;host=localhost'; // имя базы данных и адрес сервера 
$user = 'root'; // имя пользователя
$password = ''; // пароль

try {
    $link = new PDO($dsn, $user, $password);
} catch (PDOException $e) {
    echo 'Подключение не удалось: ' . $e->getMessage();
}
?>