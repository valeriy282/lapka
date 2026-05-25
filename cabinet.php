<?php
session_start();

// 1. Подключаем базу данных В САМОМ НАЧАЛЕ
require_once 'config/db.php';

// 2. Проверяем, авторизован ли пользователь
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// 3. Получаем актуальные данные пользователя из БД
$stmt = $pdo->prepare('SELECT * FROM users WHERE id = ?');
$stmt->execute([$user_id]);
$user = $stmt->fetch();

// 4. Получаем историю приемов питомцев пользователя с помощью JOIN
$history_stmt = $pdo->prepare("
    SELECT a.id, a.appointment_date, a.status, s.name AS service_name, s.price, d.name AS doctor_name
    FROM appointments a
    JOIN services s ON a.service_id = s.id
    LEFT JOIN doctors d ON a.doctor_id = d.id
    WHERE a.user_id = ?
    ORDER BY a.appointment_date DESC
");
$history_stmt->execute([$user_id]);
$appointments = $history_stmt->fetchAll();

// Переменные для вывода сообщений (успех/ошибка)
$success = '';
$errors  = [];

// 5. Обработка формы редактирования (UPDATE)
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

        <div class="cabinet-history" style="margin-top: 5px; background: white; padding: 30px; border-radius: 18px; box-shadow: 0 4px 18px rgba(0,0,0,0.06); margin-bottom: 25px;">
        <h2 style="font-size: 24px; color: #328312; margin-bottom: 20px; border-bottom: 2px solid #f5f8fc; padding-bottom: 10px;">История посещений клиники</h2>

        <?php if (empty($appointments)): ?>
            <p style="color: #666; font-style: italic;">У вас пока нет записей на прием. Ваши будущие визиты к ветеринару появятся здесь.</p>
        <?php else: ?>
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse; text-align: left;">
                    <thead>
                        <tr style="background: #f5f8fc; color: #1b1b1b; font-weight: bold;">
                            <th style="padding: 12px; border-bottom: 2px solid #dce3ea;">Дата и время</th>
                            <th style="padding: 12px; border-bottom: 2px solid #dce3ea;">Услуга</th>
                            <th style="padding: 12px; border-bottom: 2px solid #dce3ea;">Врач-ветеринар</th>
                            <th style="padding: 12px; border-bottom: 2px solid #dce3ea;">Стоимость</th>
                            <th style="padding: 12px; border-bottom: 2px solid #dce3ea;">Статус</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($appointments as $app): ?>
                            <tr style="border-bottom: 1px solid #f5f8fc; transition: 0.2s;">
                                <td style="padding: 12px; font-weight: 500;"><?= date('d.m.Y H:i', strtotime($app['appointment_date'])) ?></td>
                                <td style="padding: 12px;"><?= htmlspecialchars($app['service_name']) ?></td>
                                <td style="padding: 12px; color: #555;"><?= htmlspecialchars($app['doctor_name'] ?? 'Не назначен') ?></td>
                                <td style="padding: 12px; font-weight: bold; color: #73B713;"><?= number_format($app['price'], 0, '.', ' ') ?> ₸</td>
                                <td style="padding: 12px;">
                                    <?php 
                                    $status_style = 'background: #ffeeba; color: #856404;';
                                    $status_text = 'Ожидается';
                                    
                                    if ($app['status'] === 'completed') {
                                        $status_style = 'background: #e6f4ea; color: #328312;';
                                        $status_text = 'Завершен';
                                    } elseif ($app['status'] === 'canceled') {
                                        $status_style = 'background: #fce8e6; color: #c53929;';
                                        $status_text = 'Отменен';
                                    }
                                    ?>
                                    <span style="padding: 4px 10px; border-radius: 12px; font-size: 12px; font-weight: bold; <?= $status_style ?>">
                                        <?= $status_text ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
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