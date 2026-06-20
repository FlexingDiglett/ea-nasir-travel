<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../classes/User.php';
require_once __DIR__ . '/../classes/Translator.php';

if (!isset($auth)) {
    $auth = new User();
}
if (!isset($translator)) {
    $translator = new Translator();
}

function getLangUrl($langCode) {
    $params = $_GET;
    $params['lang'] = $langCode;
    return '?' . http_build_query($params);    # allows the user to keep the research even after changing UI language
}
?>
<!DOCTYPE html>
<html lang="<?php echo $translator->getCurrentLang(); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ea-Nasir Travel</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-custom shadow-sm py-3">
  <div class="container">
    
    <a class="navbar-brand ea-nasir-logo" href="index.php">
      Ea-Nasir Travel
    </a>

    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarNav">
      
      <ul class="navbar-nav me-auto mb-2 mb-lg-0 ms-lg-4">
        <li class="nav-item nav-divider">
          <a class="nav-link" href="index.php"><?php echo $translator->get('nav_flights'); ?></a>
        </li>
        <li class="nav-item nav-divider">
          <a class="nav-link" href="about.php"><?php echo $translator->get('nav_about'); ?></a>
        </li>
      </ul>

      <ul class="navbar-nav align-items-center">
        
        <li class="nav-item me-3 d-flex gap-2">    <!-- the opacity with the : says that if the lang is selected it's 100, otherwise 50 -->
            <a href="<?php echo getLangUrl('en'); ?>" class="text-decoration-none fs-5 <?php echo $translator->getCurrentLang() === 'en' ? 'opacity-100' : 'opacity-50'; ?>">🇬🇧</a>
            <a href="<?php echo getLangUrl('es'); ?>" class="text-decoration-none fs-5 <?php echo $translator->getCurrentLang() === 'es' ? 'opacity-100' : 'opacity-50'; ?>">🇪🇸</a>
            <a href="<?php echo getLangUrl('it'); ?>" class="text-decoration-none fs-5 <?php echo $translator->getCurrentLang() === 'it' ? 'opacity-100' : 'opacity-50'; ?>">🇮🇹</a>
            <a href="<?php echo getLangUrl('de'); ?>" class="text-decoration-none fs-5 <?php echo $translator->getCurrentLang() === 'de' ? 'opacity-100' : 'opacity-50'; ?>">🇩🇪</a>
        </li>

        <?php if ($auth->isLoggedIn()): ?>
            <li class="nav-item dropdown">
              <a class="nav-link dropdown-toggle fw-bold" href="#" id="accountDropdown" role="button" data-bs-toggle="dropdown">
                <?php echo $translator->get('nav_account'); ?>
              </a>
              <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                <li><a class="dropdown-item" href="profile.php"><?php echo $translator->get('nav_profile'); ?></a></li>
                <li><a class="dropdown-item" href="bookings.php"><?php echo $translator->get('nav_bookings'); ?></a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item text-danger" href="logout.php"><?php echo $translator->get('nav_logout'); ?></a></li>
              </ul>
            </li>
        <?php else: ?>
            <li class="nav-item">
              <a class="btn btn-copper btn-login-fixed" href="login.php"><?php echo $translator->get('nav_login'); ?></a>
            </li>
        <?php endif; ?>
      </ul>

    </div>
  </div>
</nav>

<main class="container my-5">
