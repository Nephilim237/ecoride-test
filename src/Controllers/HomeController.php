<?php

namespace Ecoride\Ecoride\Controllers;

use Ecoride\Ecoride\Core\Controller;
use Ecoride\Ecoride\Models\UserModel;

class HomeController extends Controller
{

    protected UserModel $userModel;

    public function __construct()
    {
        parent::__construct();
        $this->userModel = new UserModel();
    }

    public function index(): void
    {
        $owlCarouselBaseCss = '<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.carousel.min.css"/>';
        $owlCarouselThemeCss = '<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.theme.default.min.css"/>';

        $this->renderView('home/index', [
            'title' => "Page D'accueil | " . APP_NAME,
            'notices' => $this->userModel->get_notices(),
            'css' => [
                $owlCarouselThemeCss,
                $owlCarouselBaseCss
            ]
        ]);
    }


}