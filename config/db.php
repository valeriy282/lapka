<?php

$host = 'localhost';
$db   = 'lapkaaa';
$user = 'root';
$pass = '';

try {
    // Здесь мы убрали лишние слэши \ перед кавычками
    $pdo = new PDO(
        "mysql:host=$host;dbname=$db;charset=utf8",
        $user,
        $pass
    );

    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

} catch (PDOException $e) {
    die('Ошибка подключения: ' . $e->getMessage());
}