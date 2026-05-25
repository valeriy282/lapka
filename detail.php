<?php
session_start();
require_once 'config/db.php';

// 1. Получаем ID из URL и защищаем его (превращаем в число)
$id = intval($_GET['id'] ?? 0);

if ($id <= 0) {
    header('Location: catalog.php');
    exit;
}

// 2. Запрос к БД: достаем услугу и имя врача (убрали несуществующий specialization)
$stmt = $pdo->prepare("
    SELECT services.*, doctors.name AS doctor_name 
    FROM services 
    LEFT JOIN doctors ON services.doctor_id = doctors.id 
    WHERE services.id = ?
");
$stmt->execute([$id]);
$service = $stmt->fetch();

// 3. Если услуга с таким ID не найдена в базе — уводим на каталог
if (!$service) {
    header('Location: catalog.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($service['name']) ?> — Lapka</title>
  <link rel="stylesheet" href="css/style.css">
</head>
<body>

<?php include 'includes/header.php'; ?>

<main class="container" style="padding: 60px 0;">
  <a href="catalog.php" style="color: #328312; text-decoration: none; font-weight: bold; display: inline-block; margin-bottom: 20px;">&larr; Вернуться в каталог</a>

  <div class="detail-wrapper" style="display: flex; gap: 40px; background: white; padding: 40px; border-radius: 20px; box-shadow: 0 4px 18px rgba(0,0,0,0.06);">
    
    <div class="detail-image" style="flex: 1; max-width: 500px;">
      <img src="<?= htmlspecialchars($service['image']) ?>" alt="<?= htmlspecialchars($service['name']) ?>" onerror="this.onerror=null; this.src='https://via.placeholder.com/500x350?text=Услуга';" style="width: 100%; height: auto; border-radius: 15px; object-fit: cover;">
    </div>

    <div class="detail-info" style="flex: 1; display: flex; flex-direction: column;">
      <h1 style="font-size: 36px; margin-bottom: 20px; color: #1b1b1b;"><?= htmlspecialchars($service['name']) ?></h1>
      
      <p style="font-size: 16px; color: #555; line-height: 1.7; margin-bottom: 25px;">
        <?= htmlspecialchars($service['description']) ?>
      </p>

      <div style="background: #f5f8fc; padding: 15px 20px; border-radius: 12px; margin-bottom: 25px; border-left: 4px solid #73B713;">
        <h4 style="margin-bottom: 5px; color: #1b1b1b;">Прием ведет ветеринар:</h4>
        <p style="font-weight: bold; color: #328312; font-size: 16px;">
          <?= htmlspecialchars($service['doctor_name'] ?? 'Дежурный врач клиники') ?>
        </p>
      </div>

      <div style="margin-top: auto; display: flex; align-items: center; justify-content: space-between; border-top: 1px solid #dce3ea; padding-top: 20px;">
        <div>
          <span style="font-size: 14px; color: #777; display: block;">Стоимость услуги:</span>
          <span style="color: #73B713; font-size: 32px; font-weight: bold;"><?= number_format($service['price'], 0, '.', ' ') ?> ₸</span>
        </div>
        <a href="book.php?id=<?= $service['id'] ?>" class="btn btn-primary" style="padding: 15px 35px; font-size: 18px;">Записаться на прием</a>
      </div>
    </div>

  </div>
</main>

<?php include 'includes/footer.php'; ?>

</body>
</html>