<!DOCTYPE html>
<html lang="fr">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
  <title>Connexion – Cabinet des Ardentes</title>
  <!-- Fichier CSS principal (avec couleurs médicales) -->
  <link href="assets/css/home.css" rel="stylesheet" />
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <!-- Css interne page -->
  <link rel="stylesheet" href="assets/css/login.css">
  <script type='module' src = 'js/login/auth.js' defer></script>
  <link rel="stylesheet" href="assets/css/popup.css">
</head>
<header>
  
  <!-- Inclusion du menu -->
  <?php include __DIR__ . '/../views/includes/menu.php'; ?>
</header>
<body>

  <!-- Formulaire de connexion -->
  <div class="login-container">
    <!-- Logo au-dessus du titre -->
    <div class="login-logo"></div>

    <h2>Connexion</h2>
    <form id="loginForm" >
      <!-- Champ e-mail -->
      <div class="mb-3">
        <label for="login" class="form-label">Adresse e-mail</label>
        <input type="login" class="form-login" id="login" name="login" placeholder="Jean-Marc52" required />
      </div>

      <!-- Champ mot de passe -->
      <div class="mb-3">
        <label for="password" class="form-label">Mot de passe</label>
        <input type="password" class="form-login" id="password" name="password" placeholder="••••••••" required />
      </div>

      <!-- Bouton de soumission -->
      <div class="d-grid mb-3">
        <button type="button" id='btnSubmit' name="action" value="login" class="btn btn-login">
          Se connecter
        </button>
      </div>

      <!-- Lien mot de passe oublié -->
      <div class="text-center text-muted">
        <a href="/forgot-password.php">Mot de passe oublié ?</a>
      </div>
    </form>
  </div>

  <!-- Footer médical -->
  <?php include __DIR__ . '/../views/includes/footer.php'; ?>

  <!-- Bootstrap JS (optionnel si besoin des composants) -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" defer></script>
</body>

</html>
