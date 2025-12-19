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

<section class="er-fyw bg-chinese">
    <div class="container py-5">
        <div class="er-fyw-wrapper">
            <div class="row">
                <div class="col-12">
                    <h3 class="fs-24 text-center mb-4 outfit">Trouver Un Covoiturage ?</h3>
                </div>
                <div class="col-12">
                    <div class="er-fyw-form-container d-flex align-items-center px-2">
                        <form action="<?= url('/carpool/search') ?>" method="get" class="er-fyw-form w-100 row outfit"
                              id="search-form">
                            <div class="col-md-10 col-sm-12 bg-light input-container py-1 rounded-start-pill rounded-end-0">
                                <div class="row">
                                    <div class="col-md-6 col-sm-12">
                                        <div class="row">
                                            <div class="col-md-6 col-sm-12 er-v-end-divider address-autocomplete">
                                                <input type="text" name="lieu_depart" id="lieu_depart"
                                                       class="form-control border-0 bg-transparent w-100
                                                         rounded-pill py-2 px-3"
                                                       value="<?= $_GET['lieu_depart'] ?? null ?>"
                                                       placeholder="Depart" autocomplete="off">
                                                <div class="autocomplete-results" id="depart-results"></div>
                                            </div>

                                            <div class="col-md-6 col-sm-12 er-v-end-divider address-autocomplete">
                                                <input type="text" name="lieu_arrivee" id="lieu_arrivee"
                                                       class="form-control border-0 bg-transparent w-100
                                                         rounded-pill py-2 px-3"
                                                       value="<?= $_GET['lieu_arrivee'] ?? null ?>"
                                                       placeholder="Destination" autocomplete="off">
                                                <div class="autocomplete-results" id="arrivee-results"></div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6 col-sm-12">
                                        <div class="row">
                                            <div class="col-md-7 col-sm-12 er-v-end-divider">
                                                <input type="date" name="date_depart" id="date_depart"
                                                       class="form-control border-0 bg-transparent w-100 rounded-pill py-2 px-3"
                                                       value="<?= $_GET['date_depart'] ?? null ?>"
                                                       placeholder="Date de depart">
                                            </div>
                                            <div class="col-md-5 col-sm-12">
                                                <input type="number" name="nb_passagers" id="nb_passagers"
                                                       class="form-control border-0 bg-transparent w-100 rounded-pill py-2 px-3"
                                                       value="<?= $_GET['nb_passagers'] ?? null ?>"
                                                       placeholder="Passager">
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

<section class="carpools-results py-5 my-5" id="carpools-results">
    <div class="container">
        <?php if ($searchParams): ?>
            <?php if (empty($carpools)): ?>
                <div class="empty-message">
                    <i class="fas fa-car-side fa-3x text-muted mb-3"></i>
                    <h3>Aucun covoiturage disponible.</h3>
                    <!-- Affichage de la prochaine date disponible -->
                    <?php if ($nextAvailableDate): ?>
                        <div class="alert alert-info mt-3">
                            <p>
                                Prochaine date prevu pour ce trajet:
                                <strong><?= date('d/m/Y', strtotime($nextAvailableDate)) ?></strong>
                                <input type="hidden" id="next-date" value="<?= $nextAvailableDate ?>">
                            </p>
                            <div class="mt-2">
                                <button class="btn btn-bg-green-2" id="next-carpool">Reserver sur ce trajet</button>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

            <?php else: ?>
                <!-- LISTE DES COVOITURAGES -->
                <h3 class="text-center fw-500 outfit mb-3"><?= count($carpools) ?> covoiturage(s) trouve(s).</h3>
                <div class="row">
                    <div class="col-md-3 col-sm-12 outfit">
                        <h5 class="mb-4">Filtres</h5>
                        <div class="mb-4" id="filters-section">
                            <form action="#" method="get" id="filtersForm" class="g-3 align-items-end">
                                <!-- Champs caches pour mainteneir la recherche -->
                                <input type="hidden" name="lieu_depart"
                                       value="<?= sanitize($searchParams['lieu_depart']) ?>">
                                <input type="hidden" name="lieu_arrivee"
                                       value="<?= sanitize($searchParams['lieu_arrivee']) ?>">
                                <input type="hidden" name="date_depart"
                                       value="<?= sanitize($searchParams['date_depart']) ?>">
                                <input type="hidden" name="nb_passagers"
                                       value="<?= sanitize($searchParams['nb_passagers']) ?>">

                                <div class="form-check form-switch mb-3">
                                    <input type="checkbox" name="is_ecologic" id="is_ecologic"
                                           class="form-check-input" <?= $searchParams['is_ecologic'] === 'on' ? 'checked' : '' ?>>
                                    <label for="is_ecologic" class="form-check-label">
                                        <i class="fas fa-leaf er-text-main me-1"></i>Trajets Eco
                                    </label>
                                </div>

                                <div class="mb-3 border-bottom">
                                    <label for="prix_max" class="form-label">Prix Max</label>
                                    <input type="range" name="prix_max" class="form-range" min="0" max="1000" id="prix_max"
                                           step="1" value="<?= $searchParams['prix_max'] ?? '' ?>">
                                    <output for="prix_max" class="rangeOutput" aria-hidden="true"></output>
                                </div>

                                <div class="mb-3 border-bottom">
                                    <label for="duree_max" class="form-label">Duree Max</label>
                                    <input type="range" name="duree_max" class="form-range" min="0" max="24" id="duree_max"
                                           step="1" value="<?= $searchParams['duree_max'] ?? '' ?>">
                                    <output for="duree_max" class="rangeOutput" aria-hidden="true"></output>
                                </div>

                                <div class="mb-3 border-bottom">
                                    <label for="note_min" class="form-label">Note</label>
                                    <input type="range" name="note_min" class="form-range" min="0" max="5" id="note_min"
                                           step="1" value="<?= $searchParams['note_min'] ?? '' ?>">
                                    <output for="note_min" class="rangeOutput" aria-hidden="true"></output>
                                </div>
                                <button type="submit" class="btn btn-bg-main rounded-pill btn-sm w-100 mb-3">
                                    <i class="fas fa-check me-1"></i> Appliquer les filtres
                                </button>
                                <a href="<?= $this->build_clear_filters_url() ?>" class="btn btn-sm btn-outline-info w-100 rounded-pill">
                                    Effacer les filtres
                                </a>
                            </form>
                        </div>

                        <?php if (!empty($activeFilters)): ?>
                            <div class="mb-3">
                                <h5 class="text-muted me-2">Filtres actifs:  </h5>
                                <div class="d-flex flex-wrap gap-2 align-items-center">
                                    <?php foreach ($activeFilters as $filter): ?>
                                        <?= $filter['label'] ?>
                                        <a href="<?= $this->build_remove_filter_url($filter['name']) ?>" class="btn btn-dark btn-sm p-1 er-text-light ms-2">
                                            <i class="fas fa-times"></i>
                                        </a>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-9 col-sm-12">
                        <?php foreach ($carpools as $carpool): ?>
                            <div class="card card-carpools outfit mb-3">
                                <div class="card-body">
                                    <div class="row align-items-center">
                                        <div class="col-md-8 col-sm-12">
                                            <div class="d-flex align-items-center mb-2">
                                                <div class="driver-avatar me-3">
                                                    <img src="<?= $carpool['conducteur']['photo'] ?? assets('img/avatar-default.png') ?>"
                                                         width="64px" alt="" class="rounded-circle">
                                                </div>

                                                <div>
                                                    <h5 class="mb-0"><?= $carpool['conducteur']['prenom'] . ' ' . $carpool['conducteur']['nom'] ?></h5>
                                                    <div class="text-warning">
                                                        <small class="text-warning">
                                                            <?= $carpool['conducteur']['note'] ?
                                                                $carpool['conducteur']['note'] . ' <i class="fas fa-star"></i>' :
                                                                'Pas encore note.' ?>
                                                        </small>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="carpool-info">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <div class="fs-14">
                                                        <strong><?= date('H:i', strtotime($carpool['heure_depart'])) ?></strong>
                                                        <span class="text-muted"> - <?= date('d/m/Y', strtotime($carpool['date_depart'])) ?></span>
                                                    </div>
                                                    <div class="text-end fs-14">
                                                        <strong><?= date('H:i', strtotime($carpool['heure_arrivee'])) ?></strong>
                                                        <span class="text-muted"> - <?= date('d/m/Y', strtotime($carpool['date_arrivee'])) ?></span>
                                                    </div>
                                                </div>
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <div class="text-truncate">
                                                        <i class="fas fa-map-marker-alt er-text-main"></i>
                                                        <?= sanitize($carpool['lieu_depart']) ?>
                                                    </div>
                                                    <div class="mx-3">
                                                        <i class="fas fa-arrow-right text-muted"></i>
                                                    </div>
                                                    <div class="text-truncate text-end">
                                                        <i class="fas fa-flag-checkered text-info"></i>
                                                        <?= sanitize($carpool['lieu_arrivee']) ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-md-4 col-sm-12 text-end">
                                            <div class="mb-2">
                                                <?php if ($carpool['is_ecologic']): ?>
                                                    <span class="badge bg-main-color px-3 rounded-pill mb-2">
                                                <i class="fas fa-leaf"></i> Ecologique
                                            </span>
                                                <?php endif; ?>
                                                <span class="badge bg-info px-3 rounded-pill">
                                            <?= $carpool['places_restantes'] ?> Places restantes
                                        </span>
                                            </div>

                                            <div class="price h4 er-text-main mb-2">
                                                <?= $carpool['prix_personne'] ?> Credits
                                                <!-- <small class="text-muted">/personne</small>-->
                                            </div>
                                            <div class="vehicle-info text-muted small mb-2">
                                                <?= $carpool['vehicule']['marque'] ?> <?= $carpool['vehicule']['modele'] ?>
                                            </div>

                                            <a href="#" class="btn btn-sm btn-bg-main rounded-pill px-4">Voir les
                                                details</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
    <input type="hidden" id="autocomplete-url" value="<?= url('carpool/autocomplete') ?>">
</section>


<script>

    document.addEventListener('DOMContentLoaded', () => {

        const departInput = document.getElementById('lieu_depart');
        const arriveeInput = document.getElementById('lieu_arrivee');
        const departResults = document.getElementById('depart-results');
        const arriveeResults = document.getElementById('arrivee-results');
        const autocompleteUrl = document.getElementById('autocomplete-url');

        function set_up_autocomplete(input, resultsContainer) {
            let timeoutId;

            input.addEventListener('input', function () {
                clearTimeout(timeoutId);
                const query = this.value.trim();

                if (query.length < 3) {
                    resultsContainer.style.display = 'none';
                    return;
                }
                console.log(`${autocompleteUrl.value}?query=${encodeURIComponent(query)}`)
                timeoutId = setTimeout(function () {
                    fetch(`${autocompleteUrl.value}?query=${encodeURIComponent(query)}`)
                        .then(
                            response => response.json()
                        )
                        .then(addresses => {
                            console.log(addresses);
                            displayResults(addresses, resultsContainer, input);
                        })
                        .catch((error) => {
                            console.log('Erreur autocompletion: ', error);
                        });
                }, 300);
            });

            // Cacher les resultats quand on clique ailleurs
            document.addEventListener('click', function (e) {
                if (!input.contains(e.target) && !resultsContainer.contains(e.target)) {
                    resultsContainer.style.display = 'none';
                }
            });
        }

        function displayResults(addresses, container, input) {
            container.innerHTML = "";

            if (addresses.length === 0) {
                container.style.display = 'none';
                return;
            }

            addresses.forEach(address => {
                const item = document.createElement('div');
                item.className = 'autocomplete-item';
                item.textContent = address.label;
                item.addEventListener('click', function () {
                    input.value = address.label;
                    container.style.display = 'none';
                });
                container.appendChild(item);
            });

            container.style.display = 'block';
        }

        set_up_autocomplete(departInput, departResults);
        set_up_autocomplete(arriveeInput, arriveeResults);

        // Premplir le champ date avec le jour courant, l'utilisateur pourra le changer
        const dateField = document.getElementById('date_depart');
        if (!dateField.value || dateField.value.trim() === '') {
            const now = new Date();
            dateField.value = now.toISOString().split('T')[0];
        }

        // Reserver covoiturage prochaine date suggeree
        const nextCarpool = document.getElementById('next-carpool');
        const nextDate = document.getElementById('next-date');

        console.log(nextDate);
        if (nextCarpool) {
            nextCarpool.addEventListener('click', () => {
                dateField.value = nextDate.value;
                document.getElementById('search-form').submit();
            });
        }

        // Soumission automatique des formulaires apres un certain moment
        const filterInputs = document.querySelectorAll('#filtersForm input');
        filterInputs.forEach(input => {
            input.addEventListener('change', () => {
                setTimeout(() => {
                    document.getElementById('filtersForm').submit();
                }, 500);
            });
        });

        // Gestion des input de type range
        const allRangeInput = document.querySelectorAll('input[type=range]');
        console.log(allRangeInput);
        allRangeInput.forEach(element => {
            element.value = 0;
            const rangeOutput = element.nextElementSibling;
            rangeOutput.textContent = element.value;

            element.addEventListener('input', function () {
                rangeOutput.textContent = this.value;
            })
        })
    })

</script>