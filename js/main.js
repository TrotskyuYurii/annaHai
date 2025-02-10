emailjs.init("S888tYgOF-13G4p-w");

document
  .querySelector(".contact_form")
  .addEventListener("submit", function (event) {
    event.preventDefault();

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
        console.error("Failed to send email:", error);
        button.textContent = "Помилка відправки";

        setTimeout(() => {
          button.textContent = originalText;
        }, 3000);
      });
  });
