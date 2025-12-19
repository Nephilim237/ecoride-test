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
            <div class="col-md-6 col-sm-10 mx-auto">
                <div class="form-container">
                    <div class="row justify-content-center">
                        <div class="col-md-10 col-sm-12">
                            <h3 class="text-center outfit fw-600 fs-main-title er-text-dark mb-5">Inscription</h3>
                        </div>
                    </div>
                    <form action="<?= url('/register/handle') ?>" method="post" class="er-form">
                        <div class="mb-3">
                            <div class="col-md-10 col-sm-12 mx-auto">
                                <label for="email"
                                       class="form-label fs-18 ps-4  outfit fw-500 er-text-dark">Email</label>
                                <input type="email" id="email"
                                       class="form-control py-2 px-4 border-2 outfit fw-500 rounded-pill fs-18"
                                       name="email" required>
                                <div class="invalid-feedback ps-4">
                                    Bonjour
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <div class="col-md-10 col-sm-12 mx-auto">
                                <label for="pseudo"
                                       class="form-label fs-18 ps-4  outfit fw-500 er-text-dark">Pseudo</label>
                                <input type="text" id="pseudo"
                                       class="form-control py-2 px-4 border-2 outfit fw-500 rounded-pill fs-18"
                                       name="pseudo" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <div class="col-md-10 col-sm-12 mx-auto">
                                <label for="password"
                                       class="form-label fs-18 ps-4  outfit fw-500 er-text-dark">Mot de
                                    passe</label>
                                <input type="password" id="password"
                                       class="form-control py-2 px-4 border-2 outfit fw-500 rounded-pill fs-18"
                                       name="password" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <div class="col-md-10 col-sm-12 mx-auto">
                                <label for="password_confirm"
                                       class="form-label fs-18 ps-4  outfit fw-500 er-text-dark">Confirmer le mot de
                                    passe</label>
                                <input type="password" id="password_confirm"
                                       class="form-control py-2 px-4 border-2 outfit fw-500 rounded-pill fs-18"
                                       name="password_confirm" required>
                            </div>
                        </div>

                        <div class="mb-3 mt-5">
                            <div class="col-md-6 col-sm-8 mx-auto">
                                <input type="submit" value="S'inscrire"
                                       class="btn btn-bg-chinese outfit fw-500 py-2 rounded-pill fs-18 w-100">
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>
