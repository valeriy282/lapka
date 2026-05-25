<?php
// ЭТАП 7: Инициализация сессии и получение данных из БД
session_start();

// Подключаем настройки базы данных
require_once 'config/db.php';

?>
<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Lapka - Ветеринарная клиника</title>
  <link rel="stylesheet" href="css/style.css">
</head>
<body>

<?php include 'includes/header.php'; ?>

<section class="hero">
  <div class="container">
    <div class="hero-content">
      <h1>Добро пожаловать в нашу ветеринарную клинику "Lapka"!</h1>
      <p>Профессиональная забота о здоровье ваших питомцев 24/7</p>
      <a href="catalog.php" class="btn btn-primary">Записаться на приём</a>
    </div>
  </div>
</section>

<section>
  <div class="container">
    <h2 class="section-title">Ваш питомец — наша страсть!</h2>

    <div class="pets-gallery">
      <img src="images/pit1.jpg" alt="Наш пациент 1" class="pet-photo">
      <img src="images/pit2.jpg" alt="Наш пациент 2" class="pet-photo">
      <img src="images/pit3.jpg" alt="Наш пациент 3" class="pet-photo">
      <img src="images/pit4.jpg" alt="Наш пациент 4" class="pet-photo">
    </div>
  </div>
</section>

<section>
  <div class="container">
    <h2 class="section-title">Наши преимущества</h2>

    <div class="advantages">
      <div class="advantage">
        <h3>Квалифицированная помощь 24/7</h3>
      </div>

      <div class="advantage">
        <h3>Современное оборудование</h3>
      </div>

      <div class="advantage">
        <h3>Комплексный подход к лечению питомцев</h3>
      </div>

      <div class="advantage">
        <h3>Уютная атмосфера без стресса</h3>
      </div>

      <div class="advantage">
        <h3>Лучшие специалисты города</h3>
      </div>

      <div class="advantage">
        <h3>Выгодные цены</h3>
      </div>
    </div>
  </div>
</section>

<section>
  <div class="container">
    <div class="cta">
      <h2>Хотите вылечить своего питомца?</h2>
      <p>Записывайтесь на прием и мы подберём лучший курс лечения именно для вашего любимца.</p>
      <br>
      <a href="catalog.php" class="btn btn-primary">Записаться на приём</a>
    </div>
  </div>
</section>

<?php include 'includes/footer.php'; ?>

</body>
</html>