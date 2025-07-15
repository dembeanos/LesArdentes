<?php
require_once __DIR__ . '/../src/auth/Auth.php';

use App\auth\Auth;

Auth::requireDoctorLogin();
?>
<!DOCTYPE html>
<html lang="fr">

<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <title>Dashboard Médecin – Gestion Patients Centralisée</title>

  <!-- Font Awesome & Google Fonts -->
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet"
    type="text/css">
  <link
    href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i"
    rel="stylesheet">

  <!-- Bootstrap CSS -->
  <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">

  <!-- CSS personnalisé -->
  <link rel="stylesheet" href="/Ardentes/public/assets/css/doctor-dashboard.css">
  <link rel="stylesheet" href="/Ardentes/public/assets/css/popup.css">

  <!-- Script de gestion des onglets -->
  <script type='module' src="/Ardentes/public/js/boot/bootDoctor.js" defer></script>
</head>

<body>
  <header>
      <?php include __DIR__ . '/../views/includes/menu.php' ?>
</header>
  <!-- Sidebar -->
  <div class="sidebar">

    <ul class="nav flex-column">
      <li class="nav-item">
        <a class="nav-link active" href="#" data-target="accueil">
          <i class="fas fa-fw fa-tachometer-alt"></i>
          <span>Tableau de bord</span>
        </a>
      </li>
      <hr class="sidebar-divider">

      <!-- Gestion Patients -->
      <li class="nav-item">
        <a class="nav-link" href="#" data-target="patients-section">
          <i class="fas fa-fw fa-users"></i>
          <span>Gestion Patients</span>
        </a>
      </li>
      <hr class="sidebar-divider">

      <div class="sidebar-heading px-3 text-uppercase small text-secondary">Administration</div>
      <li class="nav-item">
        <a class="nav-link" href="#" data-target="rendez-vous">
          <i class="fas fa-fw fa-calendar-alt"></i>
          <span>Rendez-vous</span>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="#" data-target="statistiques">
          <i class="fas fa-fw fa-chart-bar"></i>
          <span>Statistiques</span>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="#" data-target="contact-secretariat">
          <i class="fas fa-fw fa-envelope"></i>
          <span>Contact Secrétariat</span>
        </a>
      </li>

    </ul>
  </div>

  <!-- Main Content -->
  <div class="main-content">
    <!-- Topbar -->
    

    <!-- Sections -->
    <section id="accueil" class="content-section active">
      <div class="row mb-4">
        <!-- Cards statistiques générales -->
        <div class="col-xl-3 col-md-6 mb-4">
          <div class="card stat-card h-100 py-2">
            <div class="card-body">
              <div class="row align-items-center">
                <div class="col me-2">
                  <div class="text-xs fw-bold text-primary text-uppercase mb-1">Patients aujourd'hui</div>
                  <div id='patientToday' class="h5 mb-0 fw-bold text-gray-800">-</div>
                </div>
                <div class="col-auto">
                  <i class="fas fa-users fa-2x text-gray-300"></i>
                </div>
              </div>
            </div>
          </div>
        </div>
        <!-- Revenus mensuels -->
        <div class="col-xl-3 col-md-6 mb-4">
          <div class="card stat-card h-100 py-2">
            <div class="card-body">
              <div class="row align-items-center">
                <div class="col me-2">
                  <div class="text-xs fw-bold text-success text-uppercase mb-1">Revenus (Mois)</div>
                  <div id='moneyMonth' class="h5 mb-0 fw-bold text-gray-800">-</div>
                </div>
                <div class="col-auto">
                  <i class="fas fa-euro-sign fa-2x text-gray-300"></i>
                </div>
              </div>
            </div>
          </div>
        </div>
        <!-- Consultations en attente -->
        <div class="col-xl-3 col-md-6 mb-4">
          <div class="card stat-card h-100 py-2">
            <div class="card-body">
              <div class="row align-items-center">
                <div class="col me-2">
                  <div class="text-xs fw-bold text-info text-uppercase mb-1">Consultations en attente</div>
                  <div id='attendance' class="h5 mb-0 fw-bold text-gray-800">-</div>
                </div>
                <div class="col-auto">
                  <i class="fas fa-clipboard-list fa-2x text-gray-300"></i>
                </div>
              </div>
            </div>
          </div>
        </div>
        <!-- Nouveaux patients -->
        <div class="col-xl-3 col-md-6 mb-4">
          <div class="card stat-card h-100 py-2">
            <div class="card-body">
              <div class="row align-items-center">
                <div class="col me-2">
                  <div class="text-xs fw-bold text-warning text-uppercase mb-1">Nouveaux patients (Mois)</div>
                  <div id='newPatientMonth' class="h5 mb-0 fw-bold text-gray-800">-</div>
                </div>
                <div class="col-auto">
                  <i class="fas fa-user-plus fa-2x text-gray-300"></i>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <!-- Planning du jour - amélioré avec bouton "Consultation" -->
      <div class="row mb-4">
        <div class="col-xl-12">
          <div class="card">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
              <h6 class="m-0 fw-bold text-primary">Planning du jour</h6>
            </div>
            <div id="appointment" class="card-body">
              <div class="table-responsive">
                <table class="table table-bordered mb-0">
                  <thead class="table-light">
                    <tr>
                      <th>Heure</th>
                      <th>Patient</th>
                      <th>Type</th>
                      <th>Statut</th>
                      <th class="text-center">Actions</th>
                    </tr>
                  </thead>
                  <tbody id="today-appointments-tbody">
                  </tbody>
                </table>
              </div>

              <div class="modal fade" id="modalConsultation" tabindex="-1" aria-labelledby="modalConsultationLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                  <div class="modal-content">
                    <div class="modal-header">
                      <h5 class="modal-title" id="modalConsultationLabel">Consultation du Patient</h5>
                      <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                    </div>
                    <div class="modal-body">
                      <div id="consultation-history" class="mb-4">
                        <h6 class="text-muted">Historique des consultations</h6>
                        <div class="border p-2 rounded bg-light">
                          
                          <p class="fst-italic small">Chargement de l'historique...</p>
                        </div>
                      </div>

                      <form id="consultation-form">
                        <input type="hidden" name="formName" id="formDoctorAddConsultation_formName" value="formDoctorAddConsultation" />
                        <input type="hidden" name="csrfToken" id="formDoctorAddConsultation_csrfToken" value="" />

                        <h6 class="text-muted">Nouvelle consultation</h6>
                        <div class="mb-3">
                          <label for="diagnosis" class="form-label">Titre</label>
                          <input type="text" name="title" class="form-control" placeholder="Titre (ex : Sevrage tabagique)">
                        </div>
                        <div class="mb-3">
                          <label for="symptoms" class="form-label">Symptômes</label>
                          <textarea class="form-control" id="symptoms" name="symptoms" rows="3" required></textarea>
                        </div>

                        <div class="mb-3">
                          <label for="diagnosis" class="form-label">Diagnostic</label>
                          <textarea class="form-control" id="diagnosis" name="diagnosis" rows="3" required></textarea>
                        </div>
                        <div class="mb-3">
                          <label for="treatment" class="form-label">Traitement</label>
                          <textarea class="form-control" id="treatment" name="prescription" rows="2"></textarea>
                        </div>

                        <div class="mb-3">
                          <label for="treatment" class="form-label">Tarification</label>
                          <input type="number" name="price" class="form-control" placeholder="Prix (€)" step="0.01" min="0">
                        </div>
                        <input type="hidden" name="appointmentId" id="consultation-appointment-id" />
                      </form>
                    </div>
                    <div class="modal-footer">
                      <button type="button" class="btn btn-danger me-auto" id="btn-mark-absent">
                        <i class="fas fa-user-slash me-1"></i> Marquer comme absent
                      </button>
                      <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                      <button type="submit" form="consultation-form" class="btn btn-success">
                        <i class="fas fa-stethoscope me-1"></i> Enregistrer la consultation
                      </button>
                    </div>
                  </div>
                </div>
              </div>

            </div>
          </div>
        </div>
      </div>
    </section>

    <section id="patients-section" class="content-section">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="h5">Fiches Patients</h2>
        <button class="btn btn-primary btn-sm" id="btn-add-patient" data-bs-toggle="modal" data-bs-target="#modalAddPatientGlobal">
          <i class="fas fa-user-plus me-1"></i> Nouveau patient
        </button>
      </div>

      <!-- Barre de recherche / filtre -->
      <div class="row mb-3">
        <div class="col-md-6 mb-2">
          <input
            type="search"
            id="search-patient-global"
            class="form-control"
            placeholder="Rechercher par nom, ID…" />
        </div>
      </div>

      <!-- Tableau des patients -->
      <div class="table-responsive">
        <table class="table table-hover mb-3">
          <thead>
            <tr>
              <th>Nom</th>
              <th>Prénom</th>
              <th>Naissance</th>
              <th>Téléphone</th>
              <th>Email</th>
              <th class="text-center">Actions</th>
            </tr>
          </thead>
          <tbody id="listOfPatient"></tbody>
        </table>
      </div>
      <p class="small text-muted">Recherchez ou ajoutez un patient. Modifiez depuis les actions.</p>
    </section>

    <!-- Modal “Ajouter un Patient” -->
    <div
      class="modal fade"
      id="modalAddPatientGlobal"
      tabindex="-1"
      aria-labelledby="modalAddPatientGlobalLabel"
      aria-hidden="true">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="modalAddPatientGlobalLabel">Ajouter un nouveau patient</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
          </div>
          <div class="modal-body">
            <form id="formDoctorNewPatient">
              <input type="hidden" name="formName" id="formDoctorNewPatient_formName" value="formDoctorNewPatient" />
              <input type="hidden" name="csrfToken" id="formDoctorNewPatient_csrfToken" value="" />

              <div class="row mb-3">
                <div class="col-md-6">
                  <label for="lastName" class="form-label">Nom</label>
                  <input type="text" class="form-control" id="lastName" name="lastName" placeholder="Nom du patient" required />
                </div>
                <div class="col-md-6">
                  <label for="firstName" class="form-label">Prénom</label>
                  <input type="text" class="form-control" id="firstName" name="firstName" placeholder="Prénom du patient" required />
                </div>
              </div>

              <div class="row mb-3">
                <div class="col-md-4">
                  <label for="birthDate" class="form-label">Date de naissance</label>
                  <input type="date" class="form-control" id="birthDate" name="birthDate" required />
                </div>
                <div class="col-md-4">
                  <label for="gender" class="form-label">Sexe</label>
                  <select class="form-select" id="gender" name="gender" required>
                    <option value="" selected disabled>Choisir...</option>
                    <option value="Homme">Homme</option>
                    <option value="Femme">Femme</option>
                    <option value="Autre">Autre</option>
                  </select>
                </div>
                <div class="col-md-4">
                  <label for="bloodGroup" class="form-label">Groupe sanguin</label>
                  <select class="form-select" id="bloodGroup" name="bloodGroup">
                    <option value="" selected disabled>Choisir...</option>
                    <option value="A+">A+</option>
                    <option value="A-">A-</option>
                    <option value="B+">B+</option>
                    <option value="B-">B-</option>
                    <option value="AB+">AB+</option>
                    <option value="AB-">AB-</option>
                    <option value="O+">O+</option>
                    <option value="O-">O-</option>
                  </select>
                </div>
              </div>

              <div class="row mb-3">
                <div class="col-md-4">
                  <label for="phone" class="form-label">Téléphone</label>
                  <input type="tel" class="form-control" id="phone" name="phone" placeholder="06 12 34 56 78" required />
                </div>
                <div class="col-md-4">
                  <label for="email" class="form-label">Email</label>
                  <input type="email" class="form-control" id="email" name="email" placeholder="exemple@domaine.com" />
                </div>
                <div class="col-md-4">
                  <label for="socialSecurity" class="form-label">N° de sécurité sociale</label>
                  <input type="text" class="form-control" id="socialSecurity" name="socialSecurity" placeholder="1 99 01..." required />
                </div>
              </div>

              <div class="mb-3">
                <label for="address" class="form-label">Adresse</label>
                <input type="text" class="form-control" id="address" name="address" placeholder="Adresse complète" />
              </div>

              <div class="mb-3">
                <label for="allergy" class="form-label">Allergies</label>
                <textarea class="form-control" id="allergy" name="allergy" rows="2" placeholder="Ex. : Pollen, arachide..."></textarea>
              </div>

              <div class="mb-3">
                <label for="medicalHistory" class="form-label">Antécédents médicaux</label>
                <textarea class="form-control" id="medicalHistory" name="medicalHistory" rows="3" placeholder="Ex. : Diabète, hypertension..."></textarea>
              </div>

              <div class="text-end">
                <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">Annuler</button>
                <button type="submit" class="btn btn-primary" id="btn-save-patient">Enregistrer</button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
    <div class="modal fade" id="modalViewPatient" tabindex="-1" aria-labelledby="modalViewPatientLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="modalViewPatientLabel">Informations du patient</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
          </div>
          <div class="modal-body">
            <p><strong>Nom :</strong> <span id="view-patient-nom"></span></p>
            <p><strong>Prénom :</strong> <span id="view-patient-prenom"></span></p>
            <p><strong>Date de naissance :</strong> <span id="view-patient-naissance"></span></p>
            <p><strong>Téléphone :</strong> <span id="view-patient-phone"></span></p>
            <p><strong>Email :</strong> <span id="view-patient-email"></span></p>
            <p><strong>Adresse :</strong> <span id="view-patient-address"></span></p>

            <p><strong>Allergies :</strong> <span id="view-patient-allergy"></span></p>
            <p><strong>Groupe sanguin :</strong> <span id="view-patient-blood-group"></span></p>
            <p><strong>Genre :</strong> <span id="view-patient-gender"></span></p>
            <p><strong>Antécédents médicaux :</strong> <span id="view-patient-medical-history"></span></p>
            <p><strong>Numéro sécurité sociale :</strong> <span id="view-patient-social-security"></span></p>

          </div>
        </div>
      </div>
    </div>
    <div class="modal fade" id="modalEditPatient" tabindex="-1" aria-labelledby="modalEditPatientLabel" aria-hidden="true">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="modalEditPatientLabel">Modifier les informations du patient</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
          </div>
          <div class="modal-body">
            <form id="formEditPatient">
              <input type="hidden" name="formName" id="formDoctorEditPatient_formName" value="formDoctorEditPatient" />
              <input type="hidden" name="csrfToken" id="formDoctorEditPatient_csrfToken" value="" />

              <input type="hidden" name="patientid" id="edit-patient-id" />

              <div class="row mb-3">
                <div class="col-md-6">
                  <label for="edit-patient-nom" class="form-label">Nom</label>
                  <input type="text" name="lastName" id="edit-patient-nom" class="form-control" />
                </div>
                <div class="col-md-6">
                  <label for="edit-patient-prenom" class="form-label">Prénom</label>
                  <input type="text" name="firstName" id="edit-patient-prenom" class="form-control" />
                </div>
              </div>

              <div class="row mb-3">
                <div class="col-md-4">
                  <label for="edit-patient-naissance" class="form-label">Date de naissance</label>
                  <input type="date" name="birthDate" id="edit-patient-naissance" class="form-control" />
                </div>
                <div class="col-md-4">
                  <label for="edit-patient-phone" class="form-label">Téléphone</label>
                  <input type="tel" name="phone" id="edit-patient-phone" class="form-control" />
                </div>
                <div class="col-md-4">
                  <label for="edit-patient-email" class="form-label">Email</label>
                  <input type="email" name="email" id="edit-patient-email" class="form-control" />
                </div>
              </div>

              <div class="mb-3">
                <label for="edit-patient-address" class="form-label">Adresse</label>
                <input type="text" name="address" id="edit-patient-address" class="form-control" />
              </div>

              <div class="row mb-3">
                <div class="col-md-4">
                  <label for="edit-patient-allergy" class="form-label">Allergies</label>
                  <input type="text" name="allergy" id="edit-patient-allergy" class="form-control" />
                </div>
                <div class="col-md-4">
                  <label for="edit-patient-blood-group" class="form-label">Groupe sanguin</label>
                  <input type="text" name="bloodGroup" id="edit-patient-blood-group" class="form-control" />
                </div>
                <div class="col-md-4">
                  <label for="edit-patient-gender" class="form-label">Genre</label>
                  <select id="edit-patient-gender" name="gender" class="form-select">
                    <option value="" disabled selected>-- Sélectionner --</option>
                    <option value="Homme">Homme</option>
                    <option value="Femme">Femme</option>
                    <option value="Autre">Autre</option>
                  </select>
                </div>
              </div>

              <div class="mb-3">
                <label for="edit-patient-medical-history" class="form-label">Antécédents médicaux</label>
                <textarea id="edit-patient-medical-history" name="medicalHistory" class="form-control" rows="3"></textarea>
              </div>

              <div class="mb-3">
                <label for="edit-patient-social-security" class="form-label">Numéro de sécurité sociale</label>
                <input type="text" id="edit-patient-social-security" name="socialSecurity" class="form-control" />
              </div>

              <div class="text-end">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <button type="submit" class="btn btn-primary">Enregistrer</button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
    <!-- Section RDV docteur -->
    <section id="rendez-vous" class="content-section">
      <div class="card mb-4">
        <div class="card-header">
          <h6 class="m-0 fw-bold text-primary">Gestion des Rendez‑vous</h6>
        </div>
        <div class="card-body">
          <div class="table-responsive mb-4">
            <table class="table table-bordered">
              <thead class="table-light">
                <tr>
                  <th>Date</th>
                  <th>Heure</th>
                  <th>Patient</th>
                  <th>Type</th>
                  <th>Statut</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody id="doctor-appointments-tbody-global">

              </tbody>
            </table>
          </div>
          <div class="d-flex justify-content-end mb-3">
            <button
              id="btn-add-doctor-rdv"
              class="btn btn-primary btn-sm"
              data-bs-toggle="modal"
              data-bs-target="#modalAddAppointment">
              <i class="fas fa-plus me-1"></i> Ajouter un Rendez‑vous
            </button>
          </div>
        </div>
      </div>
    </section>
    <!-- Modal Modifier un RDV -->
    <div class="modal fade" id="modalModifyAppointment" tabindex="-1" aria-labelledby="modalModifyAppointmentLabel" aria-hidden="true">
      <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
          <form id="formModifyAppointment">
            <input type="hidden" id="formModifyAppointment_formName" name="formName" value="formModifyAppointment">
            <input type="hidden" id="formModifyAppointment_csrfToken" name="csrfToken" value="">

            <input type="hidden" id="formModifyAppointment_appointmentId" value="">
            <input type="hidden" id="modify-selectedPatientId" value="">

            <div class="modal-header">
              <h5 class="modal-title" id="modalModifyAppointmentLabel">Modifier le Rendez‑vous</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
              <div class="row gx-2">
                <div class="mb-3">
                  <label for="modify-appointment-patient" class="form-label">Patient</label>
                  <input id="search-modify-appointment-patient" class="form-control" placeholder="Recherche Patient" required>
                  <div id="modify-patient-suggestions" style="position: absolute; z-index: 1050; width: 100%; background: white; border: 1px solid #ddd; max-height: 200px; overflow-y: auto;"></div>

                </div>

                <div class="col-md-4 mb-3">
                  <label for="modify-appointment-date" class="form-label">Date</label>
                  <input type="date" id="modify-appointment-date" class="form-control" required>
                </div>
                <div class="col-md-4 mb-3">
                  <label for="modify-appointment-time" class="form-label">Heure</label>
                  <input type="time" id="modify-appointment-time" class="form-control" required>
                </div>
                <div class="col-md-4 mb-3">
                  <label for="modify-appointment-type" class="form-label">Motif</label>
                  <select id="modify-appointment-type" class="form-select" required>
                    <option value="" disabled>Choisir…</option>
                    <option value="Consultation">Consultation</option>
                    <option value="Suivi">Suivi</option>
                    <option value="Examens">Examen</option>
                  </select>
                </div>

                <div class="mb-3">
                  <label for="modify-appointment-comment" class="form-label">Commentaires (optionnel)</label>
                  <textarea id="modify-appointment-comment" class="form-control" rows="2"></textarea>
                </div>
              </div>
            </div>

            <div class="modal-footer">
              <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">Annuler</button>
              <button type="submit" id='btnModifyAppointment' class="btn btn-warning">Modifier</button>
            </div>

          </form>
        </div>
      </div>
    </div>


    <!-- Modal “Ajouter un RDV” -->
    <div class="modal fade" id="modalAddAppointment" tabindex="-1" aria-labelledby="modalAddAppointmentLabel" aria-hidden="true">
      <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
          <form id="formAddAppointment">
            <input type="hidden" id="formAddAppointment_formName" name="formName" value="formAddAppointment">
            <input type="hidden" id="formAddAppointment_csrfToken" name="csrfToken" value="">

            <div class="modal-header">
              <h5 class="modal-title" id="modalAddAppointmentLabel">Ajouter un Rendez‑vous</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
              <div class="row gx-2">
                <div class="mb-3">
                  <label for="add-appointment-patient" class="form-label">Patient</label>
                  <div class="position-relative">
                    <input id="add-appointment-patient" class="form-control" placeholder="Recherche Patient" required>
                    <div id="patient-suggestions" class="dropdown-menu position-absolute" style="z-index:9999;"></div>
                  </div>
                </div>

                <div class="col-md-4 mb-3">
                  <label for="add-appointment-date" class="form-label">Date</label>
                  <input type="date" id="add-appointment-date" class="form-control" required>
                </div>
                <div class="col-md-4 mb-3">
                  <label for="add-appointment-time" class="form-label">Heure</label>
                  <input type="time" id="add-appointment-time" class="form-control" required>
                </div>
                <div class="col-md-4 mb-3">
                  <label for="add-appointment-type" class="form-label">Motif</label>
                  <select id="add-appointment-type" class="form-select" required>
                    <option value="" disabled selected>Choisir…</option>
                    <option value="Consultation">Consultation</option>
                    <option value="Suivi">Suivi</option>
                    <option value="Examen">Examen</option>
                  </select>
                </div>

                <div class="mb-3">
                  <label for="add-appointment-notes" class="form-label">Notes (optionnel)</label>
                  <textarea id="add-appointment-notes" class="form-control" rows="2"></textarea>
                </div>

                <div class="mb-3">
                  <label for="add-appointment-comment" class="form-label">Commentaires (optionnel)</label>
                  <textarea id="add-appointment-comment" class="form-control" rows="2"></textarea>
                </div>
              </div>
            </div>

            <div class="modal-footer">
              <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">Annuler</button>
              <button type="submit" id='btnAddAppointment' class="btn btn-success">Ajouter</button>
            </div>

          </form>
        </div>
      </div>
    </div>

    <!-- Statistiques -->
    <section id="statistiques" class="content-section">
      <div class="row mb-4">
        <div class="col-lg-4 mb-4">
          <div class="card h-100 py-2">
            <div class="card-body">
              <div class="text-xs fw-bold text-primary text-uppercase mb-1">Patients ce mois</div>
              <div id='patientMonth' class="h5 mb-0 fw-bold text-gray-800">-</div>
            </div>
          </div>
        </div>
        <div class="col-lg-4 mb-4">
          <div class="card h-100 py-2">
            <div class="card-body">
              <div class="text-xs fw-bold text-success text-uppercase mb-1">Consultations ce mois</div>
              <div id='consultationMonth' class="h5 mb-0 fw-bold text-gray-800">-</div>
            </div>
          </div>
        </div>
        <div class="col-lg-4 mb-4">
          <div class="card h-100 py-2">
            <div class="card-body">
              <div class="text-xs fw-bold text-info text-uppercase mb-1">Taux de présence</div>
              <div class="d-flex align-items-center">
                <div class="progress w-100">
                  <div class="progress-bar bg-info" id='progressbar' role="progressbar" style="width: 87%"></div>
                </div>
                <div id='presenceRate' class="ms-3 small text-gray-800">-</div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="card">
        <div class="card-header">
          <h6 class="m-0 fw-bold text-primary">Graphique des consultations (6 derniers mois)</h6>
        </div>
        <div class="card-body">
          <p>Gains Medecin</p>
          <canvas id="doctorCreditStatSixMonth"></canvas>
          <p>Gains Cabinet</p>
          <canvas id="officeCreditStatSixMonth"></canvas>
          <p>Evolution prise de Rdv</p>
          <canvas id="appointmentStatSixMonth"></canvas>
          <p>Evolution nombre patient</p>
          <canvas id="patientStatSixMonth"></canvas>
        </div>
      </div>
    </section>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- Contact Secrétariat -->
    <section id="contact-secretariat" class="content-section">
      <div class="card mb-4">
        <div class="card-header">
          <h6 class="m-0 fw-bold text-primary">Contact Secrétariat</h6>
        </div>
        <div class="card-body">

          <!-- Onglets : Envoyer vs Inbox -->
          <ul class="nav nav-pills mb-4" id="cs-tabs" role="tablist">
            <li class="nav-item" role="presentation">
              <button
                class="nav-link active"
                id="cs-envoyer-tab"
                data-bs-toggle="pill"
                data-bs-target="#cs-envoyer"
                type="button"
                role="tab"
                aria-controls="cs-envoyer"
                aria-selected="true">
                Envoyer un message
              </button>
            </li>
            <li class="nav-item" role="presentation">
              <button
                class="nav-link"
                id="cs-inbox-tab"
                data-bs-toggle="pill"
                data-bs-target="#cs-inbox"
                type="button"
                role="tab"
                aria-controls="cs-inbox"
                aria-selected="false">
                Messages reçus <span class="badge bg-secondary ms-1">2</span>
              </button>
            </li>
          </ul>

          <!-- Contenu des onglets -->
          <div class="tab-content" id="cs-tabContent">

            <!-- 1. Envoyer un message -->
            <div
              class="tab-pane fade show active"
              id="cs-envoyer"
              role="tabpanel"
              aria-labelledby="cs-envoyer-tab">
              <form id='formSendMessage'>
                <input type="hidden" id="formSendMessage_formName" name="formName" value="formSendMessage">
            <input type="hidden" id="formSendMessage_csrfToken" name="csrfToken" value="">
                <div class="mb-3">
                  <label for="cs-sujet" class="form-label">Sujet</label>
                  <input
                    type="text"
                    class="form-control"
                    id="cs-sujet"
                    placeholder="Objet du message"
                    required>
                </div>
                <div class="mb-3">
                  <label for="cs-message" class="form-label">Message</label>
                  <textarea
                    class="form-control"
                    id="cs-message"
                    rows="4"
                    placeholder="Votre message…"
                    required></textarea>
                </div>
                <button type="button" id='sendMessageButton' class="btn btn-primary">Envoyer</button>
              </form>
            </div>

            <div
              class="tab-pane fade"
              id="cs-inbox"
              role="tabpanel"
              aria-labelledby="cs-inbox-tab">
              <div class="table-responsive mb-4">
                <table class="table table-hover align-middle">
                  <thead>
                    <tr>
                      <th>Date</th>
                      <th>Expéditeur</th>
                      <th>Sujet</th>
                      <th>Actions</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr>
                      <td>02/06/2025</td>
                      <td>Sécrétariat</td>
                      <td>Demande de report RDV</td>
                      <td>
                        <button
                          class="btn btn-sm btn-outline-primary"
                          data-bs-toggle="collapse"
                          data-bs-target="#cs-msg-1"
                          aria-expanded="false"
                          aria-controls="cs-msg-1"
                          title="Voir / Répondre">
                          <i class="fas fa-eye"></i> Voir
                        </button>
                      </td>
                    </tr>
                    <tr>
                      <td>28/05/2025</td>
                      <td>Sécrétariat</td>
                      <td>Demande informations patient</td>
                      <td>
                        <button
                          class="btn btn-sm btn-outline-primary"
                          data-bs-toggle="collapse"
                          data-bs-target="#cs-msg-2"
                          aria-expanded="false"
                          aria-controls="cs-msg-2"
                          title="Voir / Répondre">
                          <i class="fas fa-eye"></i> Voir
                        </button>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>

              <!-- 2.2. Contenu des messages -->
              <div id="cs-messages-collapse">

                <!-- Message #1 -->
                <div class="collapse mb-4" id="cs-msg-1">
                  <div class="card border rounded">
                    <div class="card-header bg-light">
                    </div>
                    <div class="card-body">
                      <!-- Formulaire de réponse -->
                      <form class="mt-3">
                        <div class="mb-3">
                          <label for="cs-rep-1" class="form-label">Votre réponse</label>
                          <textarea
                            class="form-control"
                            id="cs-rep-1"
                            rows="3"
                            placeholder="Écrire votre réponse…"
                            required></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary btn-sm">Répondre</button>
                      </form>
                    </div>
                  </div>
                </div>

                <!-- Message #2 -->
                <div class="collapse mb-4" id="cs-msg-2">
                  <div class="card border rounded">
                    <div class="card-header bg-light">
                      <strong>Expéditeur :</strong> Secrétariat —
                      <small class="text-muted">28/05/2025, 16:40</small>
                    </div>
                    <div class="card-body">
                      <p>Dr Martin,</p>
                      <p>Nous aurions besoin des coordonnées actualisées de votre patient M. Moreau.</p>
                      <p>Cordialement.</p>
                      <hr>
                      <!-- Formulaire de réponse -->
                      <form class="mt-3">
                        <div class="mb-3">
                          <label for="cs-rep-2" class="form-label">Votre réponse</label>
                          <textarea
                            class="form-control"
                            id="cs-rep-2"
                            rows="3"
                            placeholder="Écrire votre réponse…"
                            required></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary btn-sm">Répondre</button>
                      </form>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>



  </div>


  <!-- Bootstrap JS Bundle -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js" defer></script>
</body>
<?php include __DIR__ . '/../views/includes/footer.php'; ?>

</html>