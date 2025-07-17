<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Mentions Légales - Cabinet Médical des Ardentes</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <!-- Bootstrap intégrale CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/legal-notice.css">
  <link rel="stylesheet" href="assets/css/popup.css">
</head>
<header>
  <!-- Inclusion du menu -->
  <?php include __DIR__ . '/../views/includes/menu.php'; ?>
</header>
<body>
  <div class="container">
    <h1 class="mb-4">Mentions légales</h1>

    <section class="mb-5">
      <h2>1. Éditeur du site</h2>
      <p><strong>Nom du cabinet :</strong> Cabinet Médical des Ardentes</p>
      <p><strong>Adresse :</strong> Rue des Pâquerettes 2, 4000 Liège, Belgique</p>
      <p><strong>Téléphone :</strong> +32 4 123 45 67</p>
      <p><strong>E-mail :</strong> <a href="mailto:contact@cabinet-ardentes.be">contact@cabinet-ardentes.be</a></p>
      <p><strong>Responsable de la publication :</strong> Dr Jean Dupont (Numéro INAMI : 12345678901)</p>
      <p><strong>Numéro d'entreprise (BCE) :</strong> 0123.456.789</p>
      <p><strong>Forme juridique :</strong> SPRL médicale</p>
    </section>

    <section class="mb-5">
      <h2>2. Hébergeur</h2>
      <p><strong>Nom :</strong> OVH SAS</p>
      <p><strong>Adresse :</strong> 2 rue Kellermann, 59100 Roubaix, France</p>
      <p><strong>Téléphone :</strong> +33 9 72 10 10 07</p>
      <p><strong>Site :</strong> <a href="https://www.ovh.com">www.ovh.com</a></p>
    </section>

    <section class="mb-5">
      <h2>3. Objet du site</h2>
      <p>Ce site a pour but de fournir des informations sur le Cabinet Médical des Ardentes : présentation de l’équipe, horaires, spécialités, modalités de prise de rendez-vous, etc.</p>
      <ul>
        <li>Le site est à but informatif et ne remplace pas une consultation médicale.</li>
        <li>En cas de question médicale, consultez directement un professionnel de santé.</li>
      </ul>
    </section>

    <section class="mb-5">
      <h2>4. Propriété intellectuelle</h2>
      <p>Tout le contenu du site (textes, images, logo, etc.) est protégé par le droit d’auteur. Toute reproduction, même partielle, sans autorisation écrite est interdite.</p>
    </section>

    <section class="mb-5">
      <h2>5. Données personnelles</h2>
      <p>Conformément au RGPD et à la loi belge relative à la protection de la vie privée, les données collectées (prise de rendez-vous, formulaire de contact, etc.) sont :</p>
      <ul>
        <li>Strictement nécessaires à la gestion médicale et administrative des patients.</li>
        <li>Sécurisées via un hébergement protégé et une base PostgreSQL chiffrée (pgcrypto).</li>
        <li>Non partagées avec des tiers sans consentement préalable.</li>
      </ul>
      <p>Vous pouvez exercer vos droits d’accès, de rectification ou de suppression de vos données en envoyant un mail à : <a href="mailto:privacy@cabinet-ardentes.be">privacy@cabinet-ardentes.be</a> ou par courrier recommandé à l’adresse du cabinet.</p>
      <p>En cas de litige, vous pouvez contacter l’<a href="https://www.autoriteprotectiondonnees.be/">Autorité de protection des données (APD)</a> en Belgique.</p>
    </section>

    <section class="mb-5">
      <h2>6. Cookies</h2>
      <p>Le site utilise des cookies pour :</p>
      <ul>
        <li>Assurer le bon fonctionnement (ex. : sessions, sécurité CSRF)</li>
        <li>Analyser la fréquentation (statistiques anonymes)</li>
      </ul>
      <p>Vous pouvez gérer vos préférences via les réglages de votre navigateur.</p>
    </section>

    <section class="mb-5">
      <h2>7. Limitation de responsabilité</h2>
      <p>Le contenu est mis à jour régulièrement, mais le Cabinet Médical des Ardentes ne peut garantir l’exhaustivité ou l’absence d’erreurs. En cas de bug, d’inexactitude ou d’interruption du site, sa responsabilité ne saurait être engagée.</p>
    </section>

    <section class="mb-5">
      <h2>8. Droit applicable et juridiction</h2>
      <p>Le présent site est régi par le droit belge. En cas de litige, seuls les tribunaux de l’arrondissement judiciaire de Liège sont compétents.</p>
    </section>

    <div class="text-center py-4 border-top mt-5">
      <p><a href="index.php">Retour</a></p>
</div>
  </div>

  <!-- Bootstrap JS (optionnel si déjà dans ton bundle) -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>

  <!-- Footer médical -->
  <?php include __DIR__ . '/../views/includes/footer.php'; ?>
</html>
