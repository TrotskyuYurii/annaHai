<!doctype html>
<html lang="uk">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="/css/admin.css">
    <script defer src="/js/prices-admin.js"></script>
    <script defer src="/js/admin.js"></script>
    <title>Панель керування | ANNA HAI</title>
</head>

<body>
    <main class="admin-page">
        <header class="admin-header">
            <h1 class="admin-title">Панель керування</h1>
            <a class="admin-link" href="/#prices">На сайт</a>
        </header>

        <p class="admin-status" data-status></p>

        <section class="admin-panel" data-login-view>
            <h2>Вхід</h2>
            <form class="admin-form" data-login-form>
                <label>
                    Логін
                    <input type="text" name="login" autocomplete="username" required>
                </label>
                <label>
                    Пароль
                    <input type="password" name="password" autocomplete="current-password" required>
                </label>
                <div class="admin-actions">
                    <button class="admin-button" type="submit">Увійти</button>
                </div>
            </form>
        </section>

        <section class="admin-panel" data-editor-view hidden>
            <h2>Редагування прайсу</h2>
            <form class="admin-form" data-editor-form>
                <div class="admin-form" data-prices-fields></div>
                <div class="admin-actions">
                    <button class="admin-button" type="submit" data-save-prices>Зберегти</button>
                    <button class="admin-button admin-button-secondary" type="button" data-add-price>Додати позицію</button>
                    <button class="admin-button admin-button-warning" type="button" data-reset-prices>Скинути</button>
                    <button class="admin-button admin-button-secondary" type="button" data-logout>Вийти</button>
                </div>
            </form>
        </section>
    </main>
</body>

</html>
