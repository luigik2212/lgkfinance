<header class="topbar">
    <button id="menuToggle" class="menu-toggle">☰</button>
    <div>
        <h1><?= esc(ucwords(str_replace('_', ' ', $_GET['module'] ?? 'dashboard'))) ?></h1>
    </div>
    <div class="user-box">
        <span><?= esc($_SESSION['user']['nome']) ?></span>
        <a href="logout.php" class="btn-link danger">Sair</a>
    </div>
</header>
