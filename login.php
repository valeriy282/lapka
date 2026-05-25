<?php
session_start();
require_once 'config/db.php';

// Если пользователь уже авторизован, отправляем в кабинет
if (isset($_SESSION['user_id'])) {
    header('Location: cabinet.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (!empty($email) && !empty($password)) {
        // Ищем пользователя по email
        $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ?');
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        // Проверяем существование пользователя и валидность пароля
        if ($user && password_verify($password, $user['password'])) {
            // Записываем данные в сессию
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_email'] = $user['email'];

            // Перенаправляем в личный кабинет
            header('Location: cabinet.php');
            exit;
        } else {
            $error = 'Неверный email или пароль!';
        }
    } else {
        $error = 'Пожалуйста, заполните все поля!';
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Вход — КудаНибудь</title>
  <link rel="stylesheet" href="css/style.css">
</head>
<body>

<?php include 'includes/header.php'; ?>

<section class="container" style="max-width: 500px; padding: 60px 0;">
  <div style="background: white; padding: 40px; border-radius: 18px; box-shadow: 0 4px 18px rgba(0,0,0,0.08);">
    <h2 style="margin-bottom: 25px; text-align: center;">Вход в аккаунт</h2>

    <?php if ($error): ?>
        <div style="color: #dc3545; background: #f8d7da; padding: 12px; border-radius: 8px; margin-bottom: 20px; text-align: center;"><?= $error ?></div>
    <?php endif; ?>

    <form action="login.php" method="POST">
      <div style="margin-bottom: 20px;">
        <label style="display: block; margin-bottom: 8px; font-weight: 600;">Email</label>
        <input type="email" name="email" required style="width: 100%; padding: 12px; border: 1px solid #dce3ea; border-radius: 10px; font-size: 16px;">
      </div>

      <div style="margin-bottom: 25px;">
        <label style="display: block; margin-bottom: 8px; font-weight: 600;">Пароль</label>
        <input type="password" name="password" required style="width: 100%; padding: 12px; border: 1px solid #dce3ea; border-radius: 10px; font-size: 16px;">
      </div>

      <button type="submit" class="btn btn-primary" style="width: 100%; border: none; cursor: pointer; font-size: 16px;">Войти</button>
    </form>

    <p style="margin-top: 20px; text-align: center; opacity: 0.8;">Ещё нет аккаунта? <a href="register.php" style="color: #328312; text-decoration: none; font-weight: 600;">Зарегистрироваться</a></p>
  </div>
</section>

<?php include 'includes/footer.php'; ?>

</body>
</html>