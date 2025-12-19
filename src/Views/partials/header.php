<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title><?= $title ?? APP_NAME ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css"/>;
    <link rel="stylesheet" href="<?= assets('/css/bootstrap.min.css') ?>">
    <link rel="stylesheet" href="<?= assets('/css/styles.css') ?>">

    <?php if (!empty($css)) : ?>
        <?php foreach ($css as $link) : ?>
            <?= $link ?>
        <?php endforeach; ?>
    <?php endif; ?>
</head>
<body>
<header class="er-header" id="er-header">
    <nav class="navbar navbar-expand-lg" data-bs-theme="light">
        <div class="container">
            <a href="#" class="navbar-brand">
                <img src="<?= assets('/img/logo_ecoride.png') ?>" height="80" alt="Logo de <?= APP_NAME ?>">
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                    data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent"
                    aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <ul class="navbar-nav outfit fs-18 fw-500 me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a href="<?= url('/') ?>" class="nav-link px-4 ms-3 er-text-dark active" aria-current="page">Accueil</a>
                    </li>
                    <li class="nav-item">
                        <a href="<?= url('carpool') ?>" class="nav-link px-4 ms-3 er-text-dark ">Covoiturages</a>
                    </li>
                    <li class="nav-item">
                        <a href="#" class="nav-link  px-4 ms-3 er-text-dark">Contact</a>
                    </li>

                    <?php
                    if ($isLoggedIn && isset($currentUser)) : ?>
                        <li class="nav-item dropdown">
                            <a href="#" class="nav-link dropdown-toggle" id="navbarDropdown" role="button"
                               data-bs-toggle="dropdown">
                                <img src="<?= $currentUser['image'] ?? assets('img/avatar-default.png') ?>" width="32"
                                     alt="Image de profil de <?= sanitize($currentUser['pseudo']) ?>">
                                <?= sanitize($currentUser['pseudo']) ?>
                            </a>

                            <ul class="dropdown-menu dropdown-menu-end px-1">
                                <li>
                                    <a href="<?= url('/profile', ['p' => $currentUser['pseudo']]) ?>" class="dropdown-item">Mon Profil</a>
                                </li>
                                <!--  Liens en rapport avec les niveaux d'acces -->
                                <?php if ($currentUser['adminLevelMask'] === 15): ?>
                                    <li>
                                        <a href="#" class="dropdown-item">Ajouter Un Employe</a>
                                    </li>
                                <?php endif; ?>

                                <!--  Liens en rapport avec le role chauffeur -->
                                <?php if (in_array('chauffeur', $currentUser['roles'])): ?>
                                    <li>
                                        <a href="#" class="dropdown-item">Ajouter Un covoiturage</a>
                                    </li>
                                <?php endif; ?>
                                <li>
                                    <a class="dropdown-item text-danger" href="<?= url('/logout') ?>">Deconnexion</a>
                                </li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a href="<?= url('login') ?>" class="nav-link px-4 ms-3 er-text-dark ">Connexion</a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= url('register') ?>"
                               class="nav-link er-subscribe btn-bg-chinese px-4 ms-3">Inscription</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
</header>
