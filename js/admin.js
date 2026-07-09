(function () {
  const loginView = document.querySelector("[data-login-view]");
  const editorView = document.querySelector("[data-editor-view]");
  const loginForm = document.querySelector("[data-login-form]");
  const editorForm = document.querySelector("[data-editor-form]");
  const pricesFields = document.querySelector("[data-prices-fields]");
  const statusMessage = document.querySelector("[data-status]");
  const addButton = document.querySelector("[data-add-price]");
  const logoutButton = document.querySelector("[data-logout]");
  const resetButton = document.querySelector("[data-reset-prices]");
  const saveButton = document.querySelector("[data-save-prices]");

  let csrfToken = "";
  let statusAnimationTimer = null;

  function setStatus(message, type) {
    statusMessage.textContent = message;
    statusMessage.dataset.type = type || "";
    statusMessage.classList.remove("admin-status-flash");

    if (statusAnimationTimer) {
      window.clearTimeout(statusAnimationTimer);
    }

    if (type === "success") {
      window.requestAnimationFrame(() => {
        statusMessage.classList.add("admin-status-flash");
      });
      statusAnimationTimer = window.setTimeout(() => {
        statusMessage.classList.remove("admin-status-flash");
      }, 1800);
    }
  }

  async function requestJson(url, options) {
    const requestOptions = options || {};
    const { headers: requestHeaders = {}, ...fetchOptions } = requestOptions;

    const response = await fetch(url, {
      credentials: "same-origin",
      headers: {
        "Content-Type": "application/json",
        ...requestHeaders,
      },
      ...fetchOptions,
    });

    const data = await response.json().catch(() => ({}));

    if (!response.ok) {
      throw new Error(data.error || "Помилка запиту");
    }

    return data;
  }

  function showEditor() {
    loginView.hidden = true;
    editorView.hidden = false;
  }

  function showLogin() {
    loginView.hidden = false;
    editorView.hidden = true;
  }

  function makeField(value) {
    const fieldset = document.createElement("fieldset");
    fieldset.className = "admin-price";
    fieldset.innerHTML = `
      <label>
        Назва послуги
        <input type="text" name="name" value="" required>
      </label>
      <label>
        Тривалість / опис
        <textarea name="time" rows="2" required></textarea>
      </label>
      <label>
        Ціна
        <input type="text" name="price" value="" required>
      </label>
      <button type="button" class="admin-button admin-button-secondary" data-remove-price>Видалити</button>
    `;

    fieldset.querySelector('[name="name"]').value = value.name || "";
    fieldset.querySelector('[name="time"]').value = value.time || "";
    fieldset.querySelector('[name="price"]').value = value.price || "";
    fieldset.querySelector("[data-remove-price]").addEventListener("click", () => {
      fieldset.remove();
      setStatus("Позицію видалено. Натисніть Зберегти, щоб застосувати зміни.", "info");
    });

    return fieldset;
  }

  function renderFields(prices) {
    pricesFields.replaceChildren(...prices.map(makeField));
  }

  function collectPrices() {
    return Array.from(pricesFields.querySelectorAll(".admin-price"))
      .map((fieldset) => ({
        name: fieldset.querySelector('[name="name"]').value.trim(),
        time: fieldset.querySelector('[name="time"]').value.trim(),
        price: fieldset.querySelector('[name="price"]').value.trim(),
      }))
      .filter((item) => item.name || item.time || item.price);
  }

  async function loadPrices() {
    const data = await requestJson("/api/prices.php");
    renderFields(data.prices || []);
  }

  async function boot() {
    try {
      const session = await requestJson("/api/session.php");
      csrfToken = session.csrfToken || "";

      if (session.authenticated) {
        showEditor();
        await loadPrices();
      } else {
        showLogin();
      }
    } catch (error) {
      showLogin();
      setStatus("Не вдалося перевірити сесію.", "error");
    }
  }

  loginForm.addEventListener("submit", async (event) => {
    event.preventDefault();
    setStatus("Перевіряємо доступ...", "info");

    const formData = new FormData(loginForm);

    try {
      const data = await requestJson("/api/login.php", {
        method: "POST",
        body: JSON.stringify({
          login: formData.get("login"),
          password: formData.get("password"),
        }),
      });

      csrfToken = data.csrfToken || "";
      loginForm.reset();
      showEditor();
      await loadPrices();
      setStatus("Ви увійшли в панель керування.", "success");
    } catch (error) {
      setStatus(error.message, "error");
    }
  });

  editorForm.addEventListener("submit", async (event) => {
    event.preventDefault();

    const prices = collectPrices();

    if (!prices.length) {
      setStatus("Додайте хоча б одну позицію прайсу.", "error");
      return;
    }

    try {
      if (saveButton) {
        saveButton.disabled = true;
        saveButton.textContent = "Зберігаємо...";
      }

      await requestJson("/api/prices.php", {
        method: "POST",
        headers: {
          "X-CSRF-Token": csrfToken,
        },
        body: JSON.stringify({ prices }),
      });

      if (saveButton) {
        saveButton.textContent = "Збережено";
        window.setTimeout(() => {
          saveButton.textContent = "Зберегти";
          saveButton.disabled = false;
        }, 1200);
      }

      setStatus("Успішно збережено - тепер оновіть сторінку сайта.", "success");
    } catch (error) {
      if (saveButton) {
        saveButton.disabled = false;
        saveButton.textContent = "Зберегти";
      }

      setStatus(error.message, "error");
    }
  });

  addButton.addEventListener("click", () => {
    pricesFields.append(makeField({ name: "", time: "", price: "" }));
    setStatus("Нову позицію додано. Заповніть поля та збережіть.", "info");
  });

  resetButton.addEventListener("click", () => {
    renderFields(window.AnnaHaiPrices.defaultPrices);
    setStatus("Початковий прайс підставлено у форму. Натисніть Зберегти, щоб застосувати.", "info");
  });

  logoutButton.addEventListener("click", async () => {
    try {
      await requestJson("/api/logout.php", {
        method: "POST",
        headers: {
          "X-CSRF-Token": csrfToken,
        },
        body: JSON.stringify({}),
      });
    } catch (error) {
      setStatus(error.message, "error");
      return;
    }

    csrfToken = "";
    showLogin();
    setStatus("Ви вийшли з панелі керування.", "info");
  });

  boot();
})();
