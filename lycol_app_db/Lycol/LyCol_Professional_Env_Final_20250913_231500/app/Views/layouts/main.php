<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= esc($title ?? 'KISSAI SCHOOL') ?></title>
    <link rel="stylesheet" href="/assets/bulma/css/bulma.min.css">
    <link rel="stylesheet" href="<?= base_url("assets/fontawesome/css/all.min.css") ?>">
</head>
<body>

<nav class="navbar is-dark" role="navigation" aria-label="main navigation">
  <div class="navbar-brand">
    <a class="navbar-item" href="<?= base_url('/') ?>">
      <strong>KISSAI SCHOOL</strong>
    </a>

    <a role="button" class="navbar-burger" aria-label="menu" aria-expanded="false" data-target="navbarBasic">
      <span aria-hidden="true"></span>
      <span aria-hidden="true"></span>
      <span aria-hidden="true"></span>
    </a>
  </div>

  <div id="navbarBasic" class="navbar-menu">
    <div class="navbar-end">
      <a class="navbar-item" href="<?= base_url('/') ?>">
        <span class="icon"><i class="fas fa-home"></i></span>
        <span>Accueil</span>
      </a>
      <a class="navbar-item" target="_blank" href="https://codeigniter.com/user_guide/">
        <span class="icon"><i class="fas fa-book"></i></span>
        <span>Documentation</span>
      </a>
    </div>
  </div>
</nav>

<main class="section">
    <?= $this->renderSection('content') ?>
</main>

<footer class="footer">
  <div class="content has-text-centered">
    <p>
      <strong>KISSAI SCHOOL</strong> - Solution de Gestion Scolaire avec <strong>Bulma CSS</strong>. 
      Système complet et moderne.
    </p>
  </div>
</footer>

<script src="/assets/bulma/js/bulma.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
  // Get all "navbar-burger" elements
  const $navbarBurgers = Array.prototype.slice.call(document.querySelectorAll('.navbar-burger'), 0);

  // Add a click event on each of them
  $navbarBurgers.forEach( el => {
    el.addEventListener('click', () => {
      // Get the target from the "data-target" attribute
      const target = el.dataset.target;
      const $target = document.getElementById(target);

      // Toggle the "is-active" class on both the "navbar-burger" and the "navbar-menu"
      el.classList.toggle('is-active');
      $target.classList.toggle('is-active');
    });
  });
});
</script>
</body>
</html>


