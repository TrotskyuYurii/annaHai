(function () {
  const defaultPrices = [
    {
      name: "ІНДИВІДУАЛЬНА КОНСУЛЬТАЦІЯ",
      time: "55 хвилин",
      price: "1800 UAH / 40 EUR",
    },
    {
      name: "ПЕРСОНАЛЬНА КОНСУЛЬТАЦІЯ ІЗ СУПРОВОДОМ",
      time: "55 хвилин + підтримка в месенджері 3 рази на тиждень",
      price: "2500 UAH / 50 EUR",
    },
    {
      name: "РОДИННА КОНСУЛЬТАЦІЯ",
      time: "80 хвилин",
      price: "2200 UAH / 45 EUR",
    },
    {
      name: "ПАРНА КОНСУЛЬТАЦІЯ",
      time: "90 хвилин",
      price: "2500 UAH / 50 EUR",
    },
  ];

  async function getPrices() {
    try {
      const response = await fetch("/api/prices.php", {
        credentials: "same-origin",
        cache: "no-store",
      });

      if (!response.ok) {
        throw new Error("Prices request failed");
      }

      const data = await response.json();

      if (Array.isArray(data.prices) && data.prices.length) {
        return data.prices;
      }
    } catch (error) {
      console.warn("Prices data cannot be loaded, default prices are used", error);
    }

    return defaultPrices;
  }

  function createPriceItem(item, index) {
    const li = document.createElement("li");
    li.setAttribute("data-aos", "fade-down");
    li.setAttribute("data-aos-easing", "linear");
    li.setAttribute("data-aos-duration", "1500");

    if (index > 0) {
      li.setAttribute("data-aos-delay", String(index * 200));
    }

    const wrapper = document.createElement("div");
    wrapper.className = "prices_list_item";

    const nameBlock = document.createElement("div");
    nameBlock.className = "prices_list_name";

    const name = document.createElement("p");
    name.textContent = item.name || "";

    const mobileTime = document.createElement("p");
    mobileTime.className = "prices_list_time desktop-hidden";
    mobileTime.textContent = item.time || "";

    const price = document.createElement("p");
    price.className = "prices_list_price";
    price.textContent = item.price || "";

    const line = document.createElement("div");
    line.className = "prices_list_line";

    const desktopTime = document.createElement("p");
    desktopTime.className = "prices_list_time mobile-hidden";
    desktopTime.textContent = item.time || "";

    nameBlock.append(name, mobileTime, price);
    wrapper.append(nameBlock, line, desktopTime);
    li.append(wrapper);

    return li;
  }

  async function renderPrices() {
    const pricesList = document.querySelector("#prices .prices_list");

    if (!pricesList) {
      return;
    }

    const prices = await getPrices();
    pricesList.replaceChildren(...prices.map(createPriceItem));

    if (window.AOS) {
      window.AOS.refreshHard();
    }
  }

  window.AnnaHaiPrices = {
    defaultPrices,
    getPrices,
    renderPrices,
  };

  document.addEventListener("DOMContentLoaded", renderPrices);
})();
