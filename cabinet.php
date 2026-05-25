<?php
session_start();

// Если пользователь не авторизован — выгоняем его на страницу входа
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// 1. Подключаем базу данных (ОБЯЗАТЕЛЬНО)
require_once 'config/db.php';

// 2. Получаем актуальные данные пользователя из БД
$stmt = $pdo->prepare('SELECT * FROM users WHERE id = ?');
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

// Переменные для вывода сообщений (успех/ошибка)
$success = '';
$errors  = [];

// 3. Обработка формы редактирования (UPDATE)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_profile'])) {
    $name  = trim($_POST['name']  ?? '');
    $phone = trim($_POST['phone'] ?? '');

    // Валидация
    if (empty($name)) {
        $errors[] = 'Имя не может быть пустым.';
    }

    if (!empty($phone) && !preg_match('/^\+?[0-9]{10,15}$/', $phone)) {
        $errors[] = 'Введите корректный номер телефона.';
    }

    // Сохраняем, если нет ошибок
    if (empty($errors)) {
        // SQL-запрос на обновление данных
        $stmt = $pdo->prepare('UPDATE users SET name = ?, phone = ? WHERE id = ?');
        $stmt->execute([$name, $phone ?: null, $_SESSION['user_id']]);

        // Обновляем имя в сессии (чтобы шапка сайта тоже обновилась)
        $_SESSION['user_name'] = $name;

        // Обновляем локальные данные для мгновенного отображения в форме
        $user['name']  = $name;
        $user['phone'] = $phone;

        $success = 'Данные успешно сохранены.';
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Личный кабинет — Lapka</title>
  <link rel="stylesheet" href="css/style.css">
</head>
<body>

<?php include 'includes/header.php'; ?>

<section class="container" style="padding: 60px 0;">
  <div style="background: white; padding: 40px; border-radius: 18px; box-shadow: 0 4px 18px rgba(0,0,0,0.08); max-width: 800px; margin: 0 auto;">
    
    <h1 style="color: #328312; margin-bottom: 15px;">Добро пожаловать, <?= htmlspecialchars($_SESSION['user_name']) ?>!</h1>
    <p style="font-size: 18px; margin-bottom: 30px; opacity: 0.8;">Здесь вы можете управлять своими личными данными.</p>
    
    <div style="border-top: 1px solid #dce3ea; padding-top: 25px;">
      <h3 style="margin-bottom: 20px;">Мои данные</h3>

      <?php if ($success): ?>
        <div style="background: #e6f4ea; color: #328312; border: 1px solid #328312; padding: 15px; border-radius: 10px; margin-bottom: 20px;">
          <?= htmlspecialchars($success) ?>
        </div>
      <?php endif; ?>

      <?php if (!empty($errors)): ?>
        <div style="background: #fdeaea; color: #dc3545; border: 1px solid #dc3545; padding: 15px; border-radius: 10px; margin-bottom: 20px;">
          <?php foreach ($errors as $e): ?>
            <p style="margin: 0;"><?= htmlspecialchars($e) ?></p>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

      <form method="POST" action="">
        
        <div style="margin-bottom: 15px;">
          <label style="display: block; margin-bottom: 8px; font-weight: bold;">Email (изменить нельзя)</label>
          <input type="email" value="<?= htmlspecialchars($user['email']) ?>" disabled style="width: 100%; padding: 12px; border-radius: 10px; border: 1px solid #ccc; background: #f5f8fc; color: #888; font-size: 16px;">
        </div>

        <div style="margin-bottom: 15px;">
          <label style="display: block; margin-bottom: 8px; font-weight: bold;" for="name">Имя</label>
          <input type="text" id="name" name="name" value="<?= htmlspecialchars($user['name']) ?>" style="width: 100%; padding: 12px; border-radius: 10px; border: 1px solid #ccc; font-size: 16px;">
        </div>

        <div style="margin-bottom: 15px;">
          <label style="display: block; margin-bottom: 8px; font-weight: bold;" for="phone">Телефон</label>
          <input type="tel" id="phone" name="phone" value="<?= htmlspecialchars($user['phone'] ?? '') ?>" placeholder="+7xxxxxxxxxx" style="width: 100%; padding: 12px; border-radius: 10px; border: 1px solid #ccc; font-size: 16px;">
        </div>

        <div style="margin-bottom: 25px;">
          <label style="display: block; margin-bottom: 8px; font-weight: bold;">Дата регистрации</label>
          <input type="text" value="<?= date('d.m.Y', strtotime($user['created_at'])) ?>" disabled style="width: 100%; padding: 12px; border-radius: 10px; border: 1px solid #ccc; background: #f5f8fc; color: #888; font-size: 16px;">
        </div>

        <button type="submit" name="save_profile" class="btn btn-primary" style="width: 100%; font-size: 18px; border: none; cursor: pointer;">
          Сохранить изменения
        </button>

      </form>
      
    </div>
  </div>
</section>

<?php include 'includes/footer.php'; ?>

</body>
</html>