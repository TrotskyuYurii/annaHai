document.addEventListener('DOMContentLoaded', function() {
    initReviewsSlider();
    // Тут можна ініціалізувати інші слайдери, якщо вони будуть
});

/**
 * Ініціалізує слайдер відгуків
 */
function initReviewsSlider() {
    // Перевіряємо, чи існує елемент на сторінці
    const reviewsSliderEl = document.querySelector('.reviewsSwiper');
    
    if (reviewsSliderEl) {
        const reviewsSwiper = new Swiper('.reviewsSwiper', {
            // Основні налаштування
            slidesPerView: 2,
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
                el: '.swiper-pagination',
                clickable: true,
            },
            
            // Ефект переходу
            effect: 'slide',
        });
    }
}