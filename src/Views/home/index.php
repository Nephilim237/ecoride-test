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

<section class="er-hb py-md-5 bg-green-95">
    <div class="container py-5 my-md-5">
        <div class="row er-hb-wrapper">
            <div class="col-md-6">
                <div class="hbt-container d-flex justify-content-center align-items-center h-100">
                    <h1 class="playwrite-au-tas fw-400 text-center lh-lg">
                        <span class="er-text-main">Covoiturage</span> Partout & Pour Tous.
                    </h1>
                </div>
            </div>
            <div class="col-md-6">
                <div class="hbi-container rounded-5 overflow-hidden">
                    <img src="<?= assets('img/home-preview.jpg') ?>" alt="Banniere d'accueil"
                         class="rounded-5 img-fluid">
                </div>
            </div>
        </div>
    </div>
</section>

<section class="er-fyw bg-chinese">
    <div class="container py-5">
        <div class="er-fyw-wrapper">
            <div class="row">
                <div class="col-12">
                    <h3 class="fs-24 text-center mb-4 outfit">Quelle Est Votre Prochaine Destination ?</h3>
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                    <div class="er-fyw-form-container d-flex align-items-center px-2">
                        <form action="" class="er-fyw-form w-100 row outfit">
                            <div class="col-md-10 col-sm-12 bg-light  py-1 rounded-start-pill rounded-end-0">
                                <div class="row">
                                    <div class="col-md-6 col-sm-12">
                                        <div class="row">
                                            <div class="col-md-6 col-sm-12 er-v-end-divider">
                                                <input type="text"
                                                       class="form-control border-0 bg-transparent w-100 rounded-pill py-2 px-3"
                                                       placeholder="Depart">
                                            </div>

                                            <div class="col-md-6 col-sm-12 er-v-end-divider">
                                                <input type="text"
                                                       class="form-control border-0 bg-transparent w-100 rounded-pill py-2 px-3"
                                                       placeholder="Destination">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6 col-sm-12">
                                        <div class="row">
                                            <div class="col-md-7 col-sm-12 er-v-end-divider">
                                                <input type="date"
                                                       class="form-control border-0 bg-transparent w-100 rounded-pill py-2 px-3"
                                                       placeholder="Date de depart">
                                            </div>
                                            <div class="col-md-5 col-sm-12">
                                                <input type="number"
                                                       class="form-control border-0 bg-transparent w-100 rounded-pill py-2 px-3"
                                                       placeholder="Passager" value="1">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <input type="submit" value="Rechercher"
                                   class="btn btn-bg-green-2 col-md-2 col-sm-12 rounded-start-0 rounded-end-pill">
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="er-partners py-md-5 bg-green-95">
    <div class="container py-5 my-md-5">
        <div class="row er-partners-wrapper">
            <div class="col-md-6 col-sm-12">
                <div class="grid-container rounded-3 p-3">
                    <img src="<?= assets('img/partners/partner-1.jpg') ?>" alt=""
                         class="img-fluid grid-item grid-item-1">
                    <img src="<?= assets('img/partners/partner-2.jpg') ?>" alt=""
                         class="img-fluid grid-item grid-item-2">
                    <img src="<?= assets('img/partners/partner-4.jpg') ?>" alt=""
                         class="img-fluid grid-item grid-item-3">
                    <img src="<?= assets('img/partners/partner-3.jpg') ?>" alt=""
                         class="img-fluid grid-item grid-item-4">
                </div>
            </div>

            <div class="col-md-6 col-sm-12">
                <div class="er-ad d-flex justify-content-center align-items-center h-100">
                    <div class="er-ad-content">
                        <p class="h3 outfit fw-400 text-center mb-4"><?= APP_NAME ?>, c'est plus de <span
                                    class="er-text-main fw-600">400 partenaires</span> a travers la France</p>
                        <p class="h5 outfit fw-400 text-center">Covoiturez en en toute circonstance, partout & pour
                            tout 😇</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="er-testimonials py-md-5">
    <div class="container py-5 my-5">
        <div class="testimonials-wrap outfit">
            <div class="heading-section text-center">
                <h3 class="sub-heading fw-600 text-uppercase d-block">Ils en parlent</h3>
                <h5 class="fw-600">Avis & Suggestions</h5>
            </div>
            <div class="carousel-testimonial owl-carousel">
                <?php if (isset($notices)): ?>
                    <?php foreach ($notices as $notice) : ?>
                        <div class="item">
                            <div class="testimonial-box d-block text-center">
                                <div class="user-img">
                                    <img src="<?= $notice->photo ?? assets('img/avatar-default.png') ?>"
                                         alt="Image de profile de <?= sanitize($notice->nom) ?>">
                                </div>
                                <div class="">
                                    <span class="quote"><i class="fa fa-quote-left"></i></span>
                                    <p><?= sanitize($notice->commentaire) ?></p>
                                    <p class="user-name"><?= sanitize($notice->nom . ' ' . $notice->prenom) ?></p>
                                     <span class="user-title">Note: <?= sanitize($notice->note) ?>/5</span>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <h1>Aucun avis pour le moment</h1>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"
        integrity="sha512-v2CJ7UaYy4JwqLDIrZUI/4hqeoQieOmAZNXBeQyjo21dadnwR+8ZaIJVT8EE2iyI61OV8e6M8PP2/4hpQINQ/g=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/owl.carousel.min.js"
        integrity="sha512-bPs7Ae6pVvhOSiIcyUClR7/q2OAsRiovw4vAkX+zJbw3ShAeeqezq50RIIcIURq7Oa20rW2n2q+fyXBNcU9lrw=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script src="<?= assets('/js/main.js') ?>"></script>