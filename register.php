<?php
session_start();
require_once 'config/db.php';

// Если пользователь уже авторизован, отправляем его в кабинет
if (isset($_SESSION['user_id'])) {
    header('Location: cabinet.php');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (!empty($name) && !empty($email) && !empty($password)) {
        // Проверяем, нет ли уже такого email в базе данных
        $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
        $stmt->execute([$email]);
        
        if ($stmt->fetch()) {
            $error = 'Пользователь с таким email уже зарегистрирован!';
        } else {
            // Хэшируем пароль для безопасности
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            // Сохраняем в БД
            $insert = $pdo->prepare('INSERT INTO users (name, email, password) VALUES (?, ?, ?)');
            if ($insert->execute([$name, $email, $hashedPassword])) {
                $success = 'Регистрация успешна! Теперь вы можете войти.';
            } else {
                $error = 'Что-то пошло не так при регистрации.';
            }
        }
    } else {
        $error = 'Заполните все поля!';
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Регистрация — Lapka</title>
  <link rel="stylesheet" href="css/style.css">
</head>
<body>

<?php include 'includes/header.php'; ?>

<section class="container" style="max-width: 500px; padding: 60px 0;">
  <div style="background: white; padding: 40px; border-radius: 18px; box-shadow: 0 4px 18px rgba(0,0,0,0.08);">
    <h2 style="margin-bottom: 25px; text-align: center;">Регистрация</h2>

    <?php if ($error): ?>
        <div style="color: #dc3545; background: #f8d7da; padding: 12px; border-radius: 8px; margin-bottom: 20px; text-align: center;"><?= $error ?></div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div style="color: #28a745; background: #d4edda; padding: 12px; border-radius: 8px; margin-bottom: 20px; text-align: center;"><?= $success ?></div>
    <?php endif; ?>

    <form action="register.php" method="POST" class="auth-form">
      <div style="margin-bottom: 20px;">
        <label style="display: block; margin-bottom: 8px; font-weight: 600;">Ваше имя</label>
        <input type="text" name="name" required style="width: 100%; padding: 12px; border: 1px solid #dce3ea; border-radius: 10px; font-size: 16px;">
      </div>

      <div style="margin-bottom: 20px;">
        <label style="display: block; margin-bottom: 8px; font-weight: 600;">Email (Логин)</label>
        <input type="email" name="email" required style="width: 100%; padding: 12px; border: 1px solid #dce3ea; border-radius: 10px; font-size: 16px;">
      </div>

      <div style="margin-bottom: 25px;">
        <label style="display: block; margin-bottom: 8px; font-weight: 600;">Пароль</label>
        <input type="password" name="password" required style="width: 100%; padding: 12px; border: 1px solid #dce3ea; border-radius: 10px; font-size: 16px;">
      </div>

      <button type="submit" class="btn btn-primary" style="width: 100%; border: none; cursor: pointer; font-size: 16px;">Создать аккаунт</button>
    </form>

    <p style="margin-top: 20px; text-align: center; opacity: 0.8;">Уже есть аккаунт? <a href="login.php" style="color: #328312; text-decoration: none; font-weight: 600;">Войти</a></p>
  </div>
</section>

<?php include 'includes/footer.php'; ?>

</body>
</html>