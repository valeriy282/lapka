<?php
session_start();

// Полностью очищаем и уничтожаем сессию
$_SESSION = array();
session_destroy();

// Уводим на главную страницу
header('Location: index.php');
exit;