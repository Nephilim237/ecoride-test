$(document).ready(function () {
    $('.carousel-testimonial').owlCarousel({
        loop: true,
        margin: 0,
        autoplay:true,
        autoplayTimeout:3000,
        autoplayHoverPause:true,
        responsiveClass: true,
        responsive: {
            0: {
                items: 1,
                nav: true
            },
            480: {
                items: 1,
                nav: false
            },
            768: {
                items: 2,
                nav: true,
                loop: false
            },
            1000: {
                items: 3,
                nav: true,
                loop: false
            }
        }
    })
});