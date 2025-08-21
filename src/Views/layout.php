<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Vacation Portal</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Bootstrap CSS (CDN) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/style.css">
</head>
<body>
<nav class="navbar navbar-expand-lg bg-body-tertiary mb-4">
    <div class="container">
        <a class="navbar-brand fw-semibold" href="/">Vacation Portal</a>
        <div class="ms-auto">
            <?php if (!empty($_SESSION['user'])): ?>
                <span class="me-3"><?= htmlspecialchars($_SESSION['user']['name']) ?> (<?= htmlspecialchars($_SESSION['user']['role']) ?>)</span>
                <a href="/logout" class="btn btn-outline-secondary btn-sm">Logout</a>
            <?php else: ?>
                <a href="/login" class="btn btn-primary btn-sm">Login</a>
            <?php endif; ?>
        </div>
    </div>
</nav>

<main class="container container-narrow">
    <?= $content ?>
</main>

<!-- Bootstrap JS (optional) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
