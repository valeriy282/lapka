<?php
session_start();
require_once 'config/db.php';

// 1. Проверяем, авторизован ли пользователь. Если нет — отправляем логиниться
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$service_id = intval($_GET['id'] ?? 0);

if ($service_id > 0) {
    // 2. Узнаем, какой врач привязан к выбранной услуге
    $stmt = $pdo->prepare("SELECT doctor_id FROM services WHERE id = ?");
    $stmt->execute([$service_id]);
    $service = $stmt->fetch();

    $doctor_id = $service ? $service['doctor_id'] : null;
    $user_id = $_SESSION['user_id'];
    
    // 3. Для простоты назначаем дату приема на завтрашний день (плюс 1 день от текущего)
    $appointment_date = date('Y-m-d 10:00:00', strtotime('+1 day'));

    // 4. Делаем магию: добавляем запись в таблицу appointments
    $insert = $pdo->prepare("INSERT INTO appointments (user_id, service_id, doctor_id, appointment_date, status) VALUES (?, ?, ?, ?, 'pending')");
    $insert->execute([$user_id, $service_id, $doctor_id, $appointment_date]);

    // 5. Перенаправляем обратно в личный кабинет, чтобы человек увидел результат
    header('Location: cabinet.php');
    exit;
}

// Если что-то пошло не так, возвращаем в каталог
header('Location: catalog.php');
exit;