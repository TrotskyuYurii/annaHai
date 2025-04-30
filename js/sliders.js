document.addEventListener("DOMContentLoaded", function () {
  initReviewsSlider();
});

function initReviewsSlider() {
  const reviewsSliderEl = document.querySelector(".reviewsSwiper");

  if (reviewsSliderEl) {
    const reviewsSwiper = new Swiper(".reviewsSwiper", {
      // Основні налаштування
      slidesPerView: 1,
      spaceBetween: 30,
      loop: true,
      grabCursor: true,

      // Автопрокрутка
      autoplay: {
        delay: 3000,
        disableOnInteraction: false,
      },

      // Пагінація (крапки внизу)
      pagination: {
        el: ".swiper-pagination",
        clickable: true,
      },

      // Ефект переходу
      effect: "slide",

      breakpoints: {
        1040: {
          slidesPerView: 2,
          spaceBetween: 30,
        },
      },
    });
  }
}
