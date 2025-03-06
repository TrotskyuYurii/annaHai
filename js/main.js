// Ініціалізація EmailJS
emailjs.init("S888tYgOF-13G4p-w");

// Обробка сабміту форми
document
  .querySelector(".contact_form")
  .addEventListener("submit", function (event) {
    event.preventDefault();
    
    // Перевіряємо, чи всі поля заповнені
    const inputs = this.querySelectorAll('input, textarea');
    let allFilled = true;
    
    inputs.forEach(input => {
      if (!input.value.trim()) {
        allFilled = false;
        input.classList.add('error'); // Додаємо клас для стилізації помилки
      } else {
        input.classList.remove('error');
      }
    });
    
    if (!allFilled) {
      return; // Зупиняємо відправку форми
    }

    // Показуємо індикатор завантаження
    const button = this.querySelector("button");
    const originalText = button.textContent;
    button.textContent = "Відправляємо...";

    // Відправка форми
    emailjs
      .sendForm("service_ezs60hm", "template_r6kwp0y", this)
      .then(() => {
        // Успішна відправка
        button.textContent = "Відправлено!";
        this.reset(); // Очищаємо форму

        setTimeout(() => {
          button.textContent = originalText;
        }, 3000);
      })
      .catch((error) => {
        // Обробка помилок
        // console.error("Failed to send email:", error);
        button.textContent = "Помилка відправки";

        setTimeout(() => {
          button.textContent = originalText;
        }, 3000);
      });
  });


//Обробка відкриття питання-відповідь
document.addEventListener('DOMContentLoaded', function() {
    const faqItems = document.querySelectorAll('.faq_list_item');
    
    faqItems.forEach(item => {
        const question = item.querySelector('h3');
        
        question.addEventListener('click', () => {
            // Закриваємо всі інші відповіді
            faqItems.forEach(otherItem => {
                if (otherItem !== item) {
                    otherItem.classList.remove('active');
                }
            });
            
            // Перемикаємо стан поточного елемента
            item.classList.toggle('active');
        });
    });
});