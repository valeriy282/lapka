<?php
session_start();
require_once 'config/db.php';

// 1. Читаем фильтры из GET-параметров
$search    = trim($_GET['search'] ?? '');
$max_price = intval($_GET['max_price'] ?? 0);
$sort      = $_GET['sort'] ?? 'id';

// Защита сортировки
$allowed_sort = ['id', 'price_asc', 'price_desc', 'name'];
if (!in_array($sort, $allowed_sort)) $sort = 'id';

// 2. Строим динамический WHERE
$where  = [];
$params = [];

if (!empty($search)) {
    // Ищем по названию услуги
    $where[]  = 'services.name LIKE ?';
    $params[] = '%' . $search . '%';
}

if ($max_price > 0) {
    // Фильтр по максимальной цене
    $where[]  = 'services.price <= ?';
    $params[] = $max_price;
}

$where_sql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

// 3. Сортировка (Классический вариант для совместимости со всеми версиями PHP)
if ($sort === 'price_asc') {
    $order_sql = 'ORDER BY services.price ASC';
} elseif ($sort === 'price_desc') {
    $order_sql = 'ORDER BY services.price DESC';
} elseif ($sort === 'name') {
    $order_sql = 'ORDER BY services.name ASC';
} else {
    $order_sql = 'ORDER BY services.id ASC';
}

// 4. Итоговый SQL-запрос с JOIN (безопасное склеивание)
$sql = "SELECT services.*, doctors.name AS doctor_name "
     . "FROM services "
     . "LEFT JOIN doctors ON services.doctor_id = doctors.id ";

if (!empty($where_sql)) {
    $sql .= $where_sql . " ";
}
$sql .= $order_sql;

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$services = $stmt->fetchAll();
$count    = count($services);
?>
<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Услуги клиники — Lapka</title>
  <link rel="stylesheet" href="css/style.css">
</head>
<body>

<?php include 'includes/header.php'; ?>

<section class="catalog-page">
  <div class="container">
    <h1 class="section-title">Наши услуги</h1>

    <div class="filter-panel">
      <form method="GET" action="catalog.php" class="filter-form">
        
        <div class="filter-group">
          <label for="search">Поиск по услугам</label>
          <input type="text" id="search" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Например: вакцинация">
        </div>

        <div class="filter-group">
          <label for="max_price">Макс. цена (₸)</label>
          <input type="number" id="max_price" name="max_price" value="<?= $max_price > 0 ? $max_price : '' ?>" placeholder="До скольки?">
        </div>

        <div class="filter-group">
          <label for="sort">Сортировка</label>
          <select id="sort" name="sort">
            <option value="id" <?= $sort==='id' ? 'selected' : '' ?>>По умолчанию</option>
            <option value="price_asc" <?= $sort==='price_asc' ? 'selected' : '' ?>>Сначала дешевле</option>
            <option value="price_desc" <?= $sort==='price_desc' ? 'selected' : '' ?>>Сначала дороже</option>
            <option value="name" <?= $sort==='name' ? 'selected' : '' ?>>По алфавиту</option>
          </select>
        </div>

        <div class="filter-actions">
          <button type="submit" class="btn btn-primary">Показать</button>
          <?php if ($search || $max_price): ?>
            <a href="catalog.php" class="btn btn-outline reset-btn">Сбросить</a>
          <?php endif; ?>
        </div>
      </form>
    </div>

    <div class="catalog-header">
      <h2><?= ($search || $max_price) ? 'Результаты поиска' : 'Все доступные услуги' ?></h2>
      <span class="catalog-count">Найдено услуг: <b><?= $count ?></b></span>
    </div>

    <?php if (empty($services)): ?>
      <div class="empty-state">
        <p>К сожалению, по вашему запросу ничего не найдено.</p>
        <a href="catalog.php" class="btn btn-primary" style="margin-top: 15px;">Сбросить фильтры</a>
      </div>
    <?php else: ?>
      <div class="cards">
        <?php foreach ($services as $s): ?>
          <div class="card">
            <img src="<?= htmlspecialchars($s['image']) ?>" alt="<?= htmlspecialchars($s['name']) ?>" onerror="this.onerror=null; this.src='https://via.placeholder.com/400x220?text=Услуга';">
            <div class="card-body">
              <h3><?= htmlspecialchars($s['name']) ?></h3>
              <p style="margin-bottom: 15px; font-size: 14px; color: #555;">
                <?= htmlspecialchars($s['description']) ?>
              </p>
              
              <div class="doctor-badge">
                 Врач: <span><?= htmlspecialchars($s['doctor_name'] ?? 'Не назначен') ?></span>
              </div>

              <div class="price">
                <?= number_format($s['price'], 0, '.', ' ') ?> ₸
              </div>
              <a href="#" class="btn btn-outline" style="width: 100%; text-align: center; margin-top: auto;">Записаться</a>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

  </div>
</section>

<?php include 'includes/footer.php'; ?>

</body>
</html>