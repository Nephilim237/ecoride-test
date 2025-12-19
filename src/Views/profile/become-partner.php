<section class="flash-messages">
    <div class="container">
        <?php if ($this->session->has_flash('info')): ?>
            <div class="alert alert-info">
                <?= $this->session->get_flash('info') ?>
            </div>
        <?php endif; ?>
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

<section class="register-form py-5 my-5" id="register-form">
    <div class="container">
        <div class="row">
            <div class="col-md-8 col-sm-12 mx-auto">

                <?php dump($errors, $oldData) ?>
                <div class="form-container">
                    <div class="row justify-content-center">
                        <div class="col-md-10 col-sm-12">
                            <h3 class="text-center outfit fw-600 fs-main-title er-text-dark mb-5">Devenir
                                Partenaire</h3>
                        </div>
                    </div>
                    <form action="<?= url('/become-partner/handle') ?>" method="post" class="er-form">
                        <fieldset class="mb-3">
                            <legend class="px-3 fs-18 outfit fw-500 ">Informations Personnelles</legend>
                            <div class="row">
                                <div class="col-md-6 mb-3 col-sm-12">
                                    <label for="name"
                                           class="form-label fs-16 ps-4  outfit fw-300 er-text-dark">Nom:*</label>
                                    <input type="text" id="name"
                                           class="form-control px-4 outfit fw-300 rounded-pill fs-16
                                           <?= isset($errors['nom']) ? 'is-invalid' : '' ?>"
                                           name="name" value="<?= htmlspecialchars($oldData['nom'] ?? '') ?>"
                                            >
                                    <?php if (isset($errors['nom'])): ?>
                                        <div class="invalid-feedback ps-4"><?= $errors['nom'] ?></div>
                                    <?php endif; ?>
                                </div>
                                <div class="col-md-6 mb-3 col-sm-12">
                                    <label for="firstname"
                                           class="form-label fs-16 ps-4  outfit fw-300 er-text-dark">Prenom:*</label>
                                    <input type="text" id="firstname"
                                           class="form-control px-4 outfit fw-300 rounded-pill fs-16
                                        <?= isset($errors['prenom']) ? 'is-invalid' : '' ?>"
                                           name="firstname" value="<?= htmlspecialchars($oldData['prenom'] ?? '') ?>"
                                            >
                                    <?php if (isset($errors['prenom'])): ?>
                                        <div class="invalid-feedback ps-4"><?= $errors['prenom'] ?></div>
                                    <?php endif; ?>
                                </div>
                                <div class="col-md-6 mb-3 col-sm-12">
                                    <label for="phone"
                                           class="form-label fs-16 ps-4  outfit fw-300 er-text-dark">Telephone:*</label>
                                    <input type="text" id="phone"
                                           class="form-control px-4 outfit fw-300 rounded-pill fs-16
                                        <?= isset($errors['telephone']) ? 'is-invalid' : '' ?>"
                                           name="phone" placeholder="Ex.: +33 1 23 45 67 89"
                                           value="<?= htmlspecialchars($oldData['telephone'] ?? '') ?>"  >
                                    <?php if (isset($errors['telephone'])): ?>
                                        <div class="invalid-feedback ps-4"><?= $errors['telephone'] ?></div>
                                    <?php endif; ?>

                                </div>
                                <div class="col-md-6 mb-3 col-sm-12">
                                    <label for="birthdate"
                                           class="form-label fs-16 ps-4  outfit fw-300 er-text-dark">Date de
                                        naissance:*</label>
                                    <input type="date" id="birthdate"
                                           class="form-control px-4 outfit fw-300 rounded-pill fs-16
                                        <?= isset($errors['date_naissance']) ? 'is-invalid' : '' ?>"
                                           name="birthdate"
                                           value="<?= htmlspecialchars($oldData['date_naissance'] ?? '') ?>"
                                            >
                                    <?php if (isset($errors['date_naissance'])): ?>
                                        <div class="invalid-feedback ps-4"><?= $errors['date_naissance'] ?></div>
                                    <?php endif; ?>

                                </div>
                                <div class="mb-3 col-12">
                                    <label for="address"
                                           class="form-label fs-16 ps-4  outfit fw-300 er-text-dark">Adresse:*</label>
                                    <input type="text" id="address"
                                           class="form-control px-4 outfit fw-300 rounded-pill fs-16
                                        <?= isset($errors['adresse']) ? 'is-invalid' : '' ?>"
                                           name="address" placeholder="Ex.: 75001 PARIS"
                                           value="<?= htmlspecialchars($oldData['adresse'] ?? '') ?>"  >
                                    <?php if (isset($errors['adresse'])): ?>
                                        <div class="invalid-feedback ps-4"><?= $errors['adresse'] ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>

                        </fieldset>

                        <fieldset>
                            <legend class="px-3 fs-18 outfit fw-500 ">Informations Du Vehicule</legend>

                            <div class="row">
                                <div class="col-md-6 mb-3 col-sm-12">
                                    <label for="license_plate"
                                           class="form-label fs-16 ps-4  outfit fw-300 er-text-dark">Immatriculation:*</label>
                                    <input type="text" id="license_plate"
                                           class="form-control px-4 outfit fw-300 rounded-pill fs-16
                                           <?= isset($errors['immatriculation']) ? 'is-invalid' : '' ?>"
                                           name="license_plate"
                                           value="<?= htmlspecialchars($oldData['immatriculation'] ?? '') ?>"
                                            >
                                    <?php if (isset($errors['immatriculation'])): ?>
                                        <div class="invalid-feedback ps-4"><?= $errors['immatriculation'] ?></div>
                                    <?php endif; ?>

                                </div>
                                <div class="col-md-6 mb-3 col-sm-12">
                                    <label for="license_plate_date"
                                           class="form-label fs-16 ps-4  outfit fw-300 er-text-dark">Premiere
                                        immatriculation:*</label>
                                    <input type="date" id="license_plate_date"
                                           class="form-control px-4 outfit fw-300 rounded-pill fs-16
                                           <?= isset($errors['date_premiere_immatriculation']) ? 'is-invalid' : '' ?>"
                                           name="license_plate_date"
                                           value="<?= htmlspecialchars($oldData['date_premiere_immatriculation'] ?? '') ?>"
                                            >
                                    <?php if (isset($errors['date_premiere_immatriculation'])): ?>
                                        <div class="invalid-feedback ps-4"><?= $errors['date_premiere_immatriculation'] ?></div>
                                    <?php endif; ?>
                                </div>
                                <div class="col-md-4 mb-3 col-sm-12">
                                    <label for="brand"
                                           class="form-label fs-16 ps-4  outfit fw-300 er-text-dark">Marque:*</label>
                                    <input type="text" id="brand"
                                           class="form-control px-4 outfit fw-300 rounded-pill fs-16
                                        <?= isset($errors['marque']) ? 'is-invalid' : '' ?>"
                                           name="brand" value="<?= htmlspecialchars($oldData['marque'] ?? '') ?>"
                                            >
                                    <?php if (isset($errors['marque'])): ?>
                                        <div class="invalid-feedback ps-4"><?= $errors['marque'] ?></div>
                                    <?php endif; ?>

                                </div>
                                <div class="col-md-4 mb-3 col-sm-12">
                                    <label for="model"
                                           class="form-label fs-16 ps-4  outfit fw-300 er-text-dark">Modele:*</label>
                                    <input type="text" id="model"
                                           class="form-control px-4 outfit fw-300 rounded-pill fs-16
                                        <?= isset($errors['modele']) ? 'is-invalid' : '' ?>"
                                           name="model" value="<?= htmlspecialchars($oldData['modele'] ?? '') ?>"
                                            >
                                    <?php if (isset($errors['modele'])): ?>
                                        <div class="invalid-feedback ps-4"><?= $errors['modele'] ?></div>
                                    <?php endif; ?>
                                </div>
                                <div class="col-md-4 mb-3 col-sm-12">
                                    <label for="color"
                                           class="form-label fs-16 ps-4  outfit fw-300 er-text-dark">Couleur:*</label>
                                    <input type="text" id="color"
                                           class="form-control px-4 outfit fw-300 rounded-pill fs-16
                                        <?= isset($errors['couleur']) ? 'is-invalid' : '' ?>"
                                           name="color" value="<?= htmlspecialchars($oldData['couleur'] ?? '') ?>"
                                            >
                                    <?php if (isset($errors['couleur'])): ?>
                                        <div class="invalid-feedback ps-4"><?= $errors['couleur'] ?></div>
                                    <?php endif; ?>
                                </div>
                                <div class="row align-items-center">
                                    <div class="col-md-3 mb-3 col-sm-6">
                                        <input type="number" id="seats"
                                               class="form-control px-4 outfit fw-300 rounded-pill fs-16
                                            <?= isset($errors['nb_places']) ? 'is-invalid' : '' ?>"
                                               name="seats" placeholder="Nb. Places"
                                               value="<?= htmlspecialchars($oldData['nb_places'] ?? '') ?>"
                                                >
                                        <?php if (isset($errors['nb_places'])): ?>
                                            <div class="invalid-feedback ps-4"><?= $errors['nb_places'] ?></div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="col-md-9 col-sm-6 mb-3 rounded-pill bg-green-80">
                                        <div class="row align-items-center py-1 justify-content-center">
                                            <div class="col-md-4 col-sm-12">
                                                <div class="form-check form-switch">
                                                    <label class="form-check-label" for="energie">Electrique*</label>
                                                    <input class="form-check-input" name="energie" type="checkbox"
                                                           role="switch"
                                                           id="energie"
                                                        <?= (isset($oldData['energie']) && $oldData['energie'] === 1) ? 'checked' : '' ?>>
                                                </div>
                                            </div>
                                            <div class="col-md-4 col-sm-12">
                                                <div class="form-check form-switch">
                                                    <label class="form-check-label" for="animal">Animaux*</label>
                                                    <input class="form-check-input" name="animal" type="checkbox"
                                                           role="switch"
                                                           id="animal"
                                                        <?= (isset($oldData['animaux']) && $oldData['animaux'] === 'oui') ? 'checked' : '' ?>>
                                                </div>
                                            </div>
                                            <div class="col-md-4 col-sm-12">
                                                <div class="form-check form-switch">
                                                    <label class="form-check-label" for="smoker">Fumeur*</label>
                                                    <input class="form-check-input" name="smoker" type="checkbox"
                                                           role="switch"
                                                           id="smoker"
                                                        <?= (isset($oldData['fumeurs']) && $oldData['fumeurs'] === 'oui') ? 'checked' : '' ?>>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </fieldset>


                        <div class="mb-3 mt-5">
                            <div class="col-md-6 col-sm-8 mx-auto">
                                <input type="submit" value="Postuler"
                                       class="btn btn-bg-chinese outfit fw-500 rounded-pill fs-18 w-100">
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>