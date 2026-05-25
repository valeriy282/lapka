<header>
  <div class="container navbar">
    <div class="logo">
  <a href="index.php" style="text-decoration: none; color: #71B612; display: flex; align-items: center; gap: 10px;">
    <img src="images/logo.png" alt="Логотип Lapka" style="height: 35px; width: auto; object-fit: contain;">
    Lapka
  </a>
</div>

    <ul class="menu">
      <li><a href="index.php">Главная</a></li>
      <li><a href="catalog.php">Услуги</a></li>
      <li><a href="#">Специалисты</a></li>
      <li><a href="#">Контакты</a></li>
    </ul>

    <div class="buttons">
      <?php if (isset($_SESSION['user_id'])): ?>
        <a href="cabinet.php" class="btn btn-outline">Кабинет (<?= htmlspecialchars($_SESSION['user_name']) ?>)</a>
        <a href="logout.php" class="btn btn-primary" style="background: #dc3545; border-color: #dc3545;">Выйти</a>
      <?php else: ?>
        <a href="login.php" class="btn btn-outline">Войти</a>
        <a href="register.php" class="btn btn-primary">Регистрация</a>
      <?php endif; ?>
    </div>
  </div>
</header>