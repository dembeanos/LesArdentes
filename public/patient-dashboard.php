<?php
require_once __DIR__ . '/../src/auth/Auth.php';

use App\auth\Auth;

Auth::requirePatientLogin();
?>
<!DOCTYPE html>
<html lang="fr">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Espace Patient</title>
  <!-- Bootstrap CSS -->
  <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.2.3/css/bootstrap.min.css" rel="stylesheet" />
  <!-- Style Page -->
  <link rel="stylesheet" href="assets/css/patient-dashboard.css">
  <link rel="stylesheet" href="assets/css/popup.css">
  <!-- FullCalendar CSS -->
  <link href='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/main.min.css' rel='stylesheet' />
  <!-- Style perso Calendar-->
  <link rel="stylesheet" href="assets/css/calendar.css">
  <!-- FullCalendar JS api -->
  <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.17/index.global.min.js'></script>
  <!-- Gestion onglets et appels classes -->
  <script src="js/boot/bootPatient.js" defer></script>
</head>

<header>
  <?php include __DIR__ . '/../views/includes/menu.php' ?>
</header>

<body>
  <!-- En-tête -->
  <div class="container-fluid">
    <div class="row gx-0">
      <!-- Sidebar -->
      <nav class="col-md-3 col-lg-2 sidebar d-flex flex-column py-4">
        <ul class="nav nav-pills flex-column mb-auto">
          <li class="nav-item">
            <a href="#" class="nav-link active" data-target="agenda-section">Agenda / Prendre RDV</a>
          </li>
          <li class="nav-item">
            <a href="#" class="nav-link" data-target="info-section">Mes informations</a>
          </li>
          <li class="nav-item">
            <a href="#" class="nav-link" data-target="password-section">Modifier mot de passe</a>
          </li>
        </ul>
      </nav>

      <!-- Contenu principal -->
      <main class="col-md-9 col-lg-10 py-4">
        <!-- Alertes -->
        <div class="alert alert-danger mb-3" role="alert">
          En cas d’urgence, appelez le <strong>15</strong>.
        </div>
        <div class="alert alert-warning mb-4" role="alert">
          Pour prendre rendez-vous, séléctionnez une date et une heure disponible et reservé<br>
          Horaires d'ouverture:
          <strong>Matin :</strong> 09:00 – 12:00 &nbsp;|&nbsp; <strong>Après-midi :</strong> 14:00 – 18:00
        </div>

        <!-- 1. Agenda -->
        <section id="agenda-section" class="content-section active">
          <div class="card mb-4">
            <div class="card-header">
              <h2 class="h5 mb-0">Agenda et Prise de RDV</h2>
            </div>
            <div class="card-body">
              <form>
                <div class="mb-3">
                  <label for="doctorSelect" class="form-label"> 1: Choisir un médecin</label>
                  <select id="doctorSelect" class="form-select" required>
                    <option value="" selected disabled>-- Sélectionnez un médecin --</option>
                  </select>
                </div>
                <!-- Calendar -->

                <div id="calendar">
                  <div class="col-md-6">
                    <label for="date-select" class="form-label">2: Sélectionner une date</label><br>
                    <input type="date" id="date-select" class="form-control" min="2025-06-01" required>
                  </div>
                </div>
              </form>


            </div>
          </div>
        </section>

        <!-- Modal prise de RDV -->
        <div class="modal fade" id="appointmentModal" tabindex="-1" aria-labelledby="appointmentModalLabel" aria-hidden="true">
          <div class="modal-dialog">
            <form id="formAddAppointmentPatient" class="modal-content">
              <input type="hidden" name='formName' id="formAddAppointmentPatient_formName" value="formAddAppointmentPatient">
              <input type="hidden" name='csrfToken' id="formAddAppointmentPatient_csrfToken" value="">
              <div class="modal-header">
                <h5 class="modal-title" id="appointmentModalLabel">Prendre un rendez-vous</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
              </div>
              <div class="modal-body">
                <div class="mb-3">
                  <label for="appointmentDateTime" class="form-label">Date & heure</label>
                  <input type="text" id="appointmentDateTime" class="form-control" readonly>
                </div>
                <div class="mb-3">
                  <label for="appointmentMotif" class="form-label">Motif</label>
                  <input type="text" id="appointmentMotif" class="form-control" placeholder="Ex : Consultation générale" required>
                </div>
                <input type="hidden" id="appointmentDoctorId">
                <input type="hidden" id="appointmentStartISO">
              </div>
              <div class="modal-footer">
                <button type="submit" class="btn btn-primary">Confirmer</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
              </div>
            </form>
          </div>
        </div>


        <!-- 2. Mes informations -->
        <section id="info-section" class="content-section">
          <div class="card mb-4">
            <div class="card-header">
              <h2 class="h5 mb-0">Mes Informations Personnelles</h2>
            </div>
            <div class="card-body">
              <form id='formPatientInfo'>
                <input type="hidden" name='formName' id="formPatientInfo_formName" value="formPatientInfo">
                <input type="hidden" name='csrfToken' id="formPatientInfo_csrfToken" value="">
                <div class="row g-3 mb-3">
                  <div class="col-md-6">
                    <label for="info-lastname" class="form-label">Nom (non modifiable)</label>
                    <input type="text" id="info-lastname" class="form-control" value="Durand" readonly>
                  </div>
                  <div class="col-md-6">
                    <label for="info-firstname" class="form-label">Prénom (non modifiable)</label>
                    <input type="text" id="info-firstname" class="form-control" value="Claire" readonly>
                  </div>
                  <div class="col-md-6">
                    <label for="info-email" class="form-label">Email (non modifiable)</label>
                    <input type="email" id="info-email" class="form-control" value="claire.durand@example.com" readonly>
                  </div>
                  <div class="col-md-6">
                    <label for="info-phone" class="form-label">Téléphone</label>
                    <input type="tel" id="info-phone" class="form-control" value="+33 6 23 45 67 89" required>
                  </div>
                </div>
                <div class="row g-3 mb-3">
                  <div class="col-md-6">
                    <label for="info-address" class="form-label">Adresse</label>
                    <textarea id="info-address" class="form-control" rows="2" required>45 rue Victor Hugo, 75010 Paris</textarea>
                  </div>
                </div>

                <button type="submit" class="btn btn-primary">Mettre à jour mes coordonnées</button>
              </form>
            </div>
          </div>
        </section>

        <!-- 3. Modifier mot de passe -->
        <section id="password-section" class="content-section">
          <div class="card mb-4">
            <div class="card-header">
              <h2 class="h5 mb-0">Modifier mon mot de passe</h2>
            </div>
            <div class="card-body">
              <form id= "formPatientPswd">
                <input type="hidden" name='formName' id="formPatientPswd_formName" value="formPatientPswd">
                <input type="hidden" name='csrfToken' id="formPatientPswd_csrfToken" value="">
                <div class="row g-3 mb-3">
                  <div class="col-md-6">
                    <label for="pwd-current" class="form-label">Mot de passe actuel</label>
                    <input type="password" id="pwd-current" class="form-control" required>
                  </div>
                  <div class="col-md-6">
                    <label for="pwd-new" class="form-label">Nouveau mot de passe</label>
                    <input type="password" id="pwd-new" class="form-control" required>
                  </div>
                </div>
                <div class="mb-3">
                  <label for="pwd-confirm" class="form-label">Confirmer le nouveau mot de passe</label>
                  <input type="password" id="pwd-confirm" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-primary">Modifier le mot de passe</button>
              </form>
            </div>
          </div>
        </section>

        
      </main>
    </div>
  </div>

  <!-- Bootstrap JS -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.2.3/js/bootstrap.bundle.min.js"></script>
</body>
<?php include __DIR__ . '/../views/includes/footer.php'; ?>

</html>