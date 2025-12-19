<section class="flash-messages">
    <div class="container">
        <?php if ($this->session->has_flash('error')): ?>
            <div class="alert alert-danger">
                <?= $this->session->get_flash('error') ?>
            </div>
        <?php endif; ?>

        <?php if ($this->session->has_flash('success')): ?>
            <div class="alert alert-success">
                <?= $this->session->get_flash('success') ?>
            </div>
        <?php endif; ?>
    </div>

</section>

<section class="er-pci d-flex align-items-center py-5 er-text-light bg-chinese">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-3 col-sm-12">
                <div class="profile-image">
                    <img src="<?= $currentUser['image'] ?? assets('img/avatar-default.png') ?>"
                        alt="Image de profil de <?= sanitize($currentUser['pseudo'] ?? null) ?>" class="img-fluid" />
                </div>
            </div>

            <div class="col-md-9 col-sm-12">
                <div class="row ms-5  mb-3 outfit">
                    <div class="col-md-8 col-sm-12">
                        <h3>A propos de <?= sanitize($currentUser['pseudo'] ?? null) ?></h3>
                        <h6 class="er-text-green fst-italic">
                            (
                            <?= sanitize($currentUser['adminLevelInfo']['name'] ?? null) ?>
                            <?= $isPassenger ? ' | Passager' : null ?>
                            <?= $isDriver ? ' | Chauffeur' : null ?>
                            )
                        </h6>
                        <div class="user-info mb-3">
                            Pseudo: <span class="mb-3 text-white-50 fw-300 er-pu">
                                <?= sanitize($currentUser['pseudo'] ?? null) ?>
                            </span>
                            <br>
                            Email: <span class="mb-3 text-white-50 fw-300 er-pe">
                                <?= sanitize($currentUser['email'] ?? null) ?>
                            </span> <br>
                            Inscrit depuis le: <span class="mb-3 text-white-50 fw-300 er-psd">
                                <?= sanitize($currentUser['date_creation'] ?? null) ?>
                            </span>
                        </div>

                        <div class="er-pse">
                            <?php if (!$isDriver): ?>
                                <a href="<?= url('/become-partner') ?>"
                                    class="btn btn-bg-green-2 rounded-pill mb-3 px-4 me-1 fw-400 align-bottom er-follow">
                                    Devenir Partenaire
                                </a>
                            <?php endif; ?>
                            <?php if ($isDriver): ?>
                                <button type="button"
                                    class="btn btn-bg-green-2 rounded-pill mb-3 px-4 me-1 fw-400 align-bottom er-follow"
                                    data-bs-toggle="modal"
                                    data-bs-target="#OptionsModal" title="Ajouter une preference">
                                    <i class="fas fa-sliders-h"></i>
                                </button>
                                <a href="<?= url('/add-car') ?>"
                                    class="btn btn-bg-green-2 rounded-pill mb-3 px-4 me-1 fw-400 align-bottom er-follow"
                                    title="Ajouter Un vehicule">
                                    <i class="fas fa-car"></i>
                                </a>
                            <?php endif; ?>
                            <?php if (!$isPassenger): ?>
                                <a href="<?= url('/become-passenger') ?>"
                                    class="btn btn-bg-green-2 rounded-pill mb-3 px-4 me-1 fw-400 align-bottom er-follow">
                                    Trouver Un covoiturage
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="col-md-4 col-sm-12">

                        <?php if ($isDriver): ?>
                            <ul class="list-group mb-3">
                                <li class="list-group-item px-0 d-flex bg-chinese er-text-light
                                border-0 justify-content-between align-items-start">
                                    <div class="ms-2 me-auto">
                                        <div class="fw-300">Nombre de vehicules</div>
                                    </div>
                                    <span class="badge fw-300 bg-green rounded-pill"><?= $currentUser['nombreVehicules'] ?></span>
                                </li>
                                <li class="list-group-item px-0 d-flex bg-chinese er-text-light
                                border-0 justify-content-between align-items-start">
                                    <div class="ms-2 me-auto">
                                        <div class="fw-300">Covoiturages effectues</div>
                                    </div>
                                    <span class="badge fw-300 bg-green rounded-pill">34</span>
                                </li>
                                <li class="list-group-item px-0 d-flex bg-chinese er-text-light
                                border-0 justify-content-between align-items-start">
                                    <div class="ms-2 me-auto">
                                        <div class="fw-300">Moyenne Generale</div>
                                        <!--                                        <small class="text-white-50">Moyenne generale sur /5</small>-->
                                    </div>
                                    <span class="badge fw-300 bg-green rounded-pill">4.2 / 5</span>
                                </li>
                            </ul>

                            <a href="#" class="btn btn-bg-green-2 rounded-pill px-4 me-1 fw-400 align-bottom er-follow">
                                Nouveau Trajet <i class="fas fa-road"></i>
                            </a>
                        <?php else: ?>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="row ms-5 align-items-center mb-3 outfit">
                    <div class="options-button pt-3">
                        <button class="profile-personal-edit btn btn-link" type="button" id="btnProfile">Modifier
                            son profil
                        </button>
                        <button class="profile-password-edit btn btn-link" type="button" id="btnPassword">Changer le
                            Mot de Passe
                        </button>
                    </div>
                </div>

            </div>
        </div>
    </div>
</section>

<section class="er-modal">
    <div class="container">
        <div class="modal fade" id="OptionsModal" tabindex="-1" aria-labelledby="forOptions" aria-hidden="true"
            data-bs-backdrop="static" data-bs-keyboard="false">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5" id="forOptions">Ajouter Une Preference</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form action="<?= url('add-preference') ?>" method="post" class="er-form">
                            <div class="row align-items-center">
                                <div class="mb-3 col-sm-9">
                                    <input type="text"
                                        class="form-control px-4 border-0 outfit
                                           <?= isset($errors['preference']) ? 'is-invalid' : '' ?>
                                           fw-300 rounded-pill fs-16"
                                        id="property" name="property"
                                        <?= sanitize($oldData['property'] ?? '') ?>
                                        placeholder="Entrez une preference">
                                    <?php if (isset($errors['preference'])): ?>
                                        <div class="invalid-feedback ps-4"><?= $errors['preference'] ?></div>
                                    <?php endif; ?>
                                </div>
                                <div class="mb-3 col-sm-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input er-check-input bg-green-80" type="checkbox"
                                            role="switch"
                                            id="value" name="value"
                                            <?= (isset($oldData['value']) && $oldData['value'] === 'oui') ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="value"></label>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="submit" class="btn btn-bg-green-2 outfit fw-500 rounded-pill fs-18 w-100">Ajouter</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>