<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo isset($pageTitle) ? $pageTitle : 'ShopNets Admin'; ?></title>
  <link rel="apple-touch-icon" sizes="57x57" href="<?php echo isset($baseUrl) ? $baseUrl : ''; ?>assets/images/icons/icons_logo/apple-icon-57x57.png">
  <link rel="apple-touch-icon" sizes="60x60" href="<?php echo isset($baseUrl) ? $baseUrl : ''; ?>assets/images/icons/icons_logo/apple-icon-60x60.png">
  <link rel="apple-touch-icon" sizes="72x72" href="<?php echo isset($baseUrl) ? $baseUrl : ''; ?>assets/images/icons/icons_logo/apple-icon-72x72.png">
  <link rel="apple-touch-icon" sizes="76x76" href="<?php echo isset($baseUrl) ? $baseUrl : ''; ?>assets/images/icons/icons_logo/apple-icon-76x76.png">
  <link rel="apple-touch-icon" sizes="114x114" href="<?php echo isset($baseUrl) ? $baseUrl : ''; ?>assets/images/icons/icons_logo/apple-icon-114x114.png">
  <link rel="apple-touch-icon" sizes="120x120" href="<?php echo isset($baseUrl) ? $baseUrl : ''; ?>assets/images/icons/icons_logo/apple-icon-120x120.png">
  <link rel="apple-touch-icon" sizes="144x144" href="<?php echo isset($baseUrl) ? $baseUrl : ''; ?>assets/images/icons/icons_logo/apple-icon-144x144.png">
  <link rel="apple-touch-icon" sizes="152x152" href="<?php echo isset($baseUrl) ? $baseUrl : ''; ?>assets/images/icons/icons_logo/apple-icon-152x152.png">
  <link rel="apple-touch-icon" sizes="180x180" href="<?php echo isset($baseUrl) ? $baseUrl : ''; ?>assets/images/icons/icons_logo/apple-icon-180x180.png">
  <link rel="icon" type="image/png" sizes="192x192"  href="<?php echo isset($baseUrl) ? $baseUrl : ''; ?>assets/images/icons/icons_logo/android-icon-192x192.png">
  <link rel="icon" type="image/png" sizes="32x32" href="<?php echo isset($baseUrl) ? $baseUrl : ''; ?>assets/images/icons/icons_logo/favicon-32x32.png">
  <link rel="icon" type="image/png" sizes="96x96" href="<?php echo isset($baseUrl) ? $baseUrl : ''; ?>assets/images/icons/icons_logo/favicon-96x96.png">
  <link rel="icon" type="image/png" sizes="16x16" href="<?php echo isset($baseUrl) ? $baseUrl : ''; ?>assets/images/icons/icons_logo/favicon-16x16.png">
  <link rel="manifest" href="<?php echo isset($baseUrl) ? $baseUrl : ''; ?>assets/images/icons/icons_logo/manifest.json">
  <meta name="msapplication-TileColor" content="#ffffff">
  <meta name="msapplication-TileImage" content="<?php echo isset($baseUrl) ? $baseUrl : ''; ?>assets/images/icons/icons_logo/ms-icon-144x144.png">
  <meta name="theme-color" content="#ffffff">
  <link rel="stylesheet" href="<?php echo isset($baseUrl) ? $baseUrl : ''; ?>assets/css/admin.css">
  <link rel="stylesheet" href="<?php echo isset($baseUrl) ? $baseUrl : ''; ?>assets/css/components/modals.css">
  <link rel="stylesheet" href="<?php echo isset($baseUrl) ? $baseUrl : ''; ?>assets/css/components/profile-popup.css">
  <link rel="stylesheet" href="<?php echo isset($baseUrl) ? $baseUrl : ''; ?>assets/css/pages/logout.css">
  <link rel="stylesheet" href="<?php echo isset($baseUrl) ? $baseUrl : ''; ?>assets/css/base/dark-theme.css"><?php if (isset($currentPage) && $currentPage === 'settings'): ?>
  <link rel="stylesheet" href="<?php echo isset($baseUrl) ? $baseUrl : ''; ?>assets/css/pages/settings.css">
  <?php endif; ?>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
