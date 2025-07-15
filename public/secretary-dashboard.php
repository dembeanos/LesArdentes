<?php
require_once __DIR__ . '/../src/auth/Auth.php';

use App\auth\Auth;

Auth::requireSecretaryLogin();
?>
<!DOCTYPE html>
<html lang="fr">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Espace Secrétaire – Cabinet des Ardentes</title>
  <!-- Bootstrap CSS -->
  <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.2.3/css/bootstrap.min.css" rel="stylesheet" />
  <!-- Font Awesome (pour les icônes : œil, crayon, poubelle) -->
  <link
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css"
    rel="stylesheet" />
  <!-- CSS personnalisé -->
  <link rel="stylesheet" href="/Ardentes/public/assets/css/secretary-dashboard.css">
  <link rel="stylesheet" href="/Ardentes/public/assets/css/popup.css">
  <!-- Script de gestion des onglets et comportements JS -->
  <script type="module" src="/Ardentes/public/js/boot/bootSecretary.js" defer></script>
</head>

<header>
  <?php include __DIR__ . '/../views/includes/menu.php' ?>
</header>

<body>
  <div class="container-fluid">
    <div class="row gx-0">
      <!-- Sidebar -->
      <nav class="col-md-3 col-lg-2 sidebar d-flex flex-column py-4">
        <ul class="nav nav-pills flex-column mb-auto">
          <li class="nav-item">
            <a href="#" class="nav-link active" data-target="rdv-section">Rendez-vous</a>
          </li>
          <li class="nav-item">
            <a href="#" class="nav-link" data-target="patients-section">Fiches Patients</a>
          </li>
          <li class="nav-item">
            <a href="#" class="nav-link" data-target="notify-section">Notification Médecin</a>
          </li>
          <li class="nav-item">
            <a href="#" class="nav-link" data-target="hours-section">Horaires d’ouverture</a>
          </li>
        </ul>
      </nav>
      <!-- Contenu principal -->
      <main class="col-md-9 col-lg-10 py-4">
        <!-- Statistiques rapides -->
        <div class="row mb-4">
          <div class="col-md-4 mb-4">
            <div class="card stat-card h-100 py-2">
              <div class="card-body">
                <div class="row align-items-center">
                  <div class="col me-2">
                    <div class="text-xs fw-bold text-primary text-uppercase mb-1">RDV aujourd'hui</div>
                    <div id='rdvCount' class="h5 mb-0 fw-bold text-gray-800">-</div>
                  </div>
                  <div class="col-auto">
                    <i class="fas fa-calendar-check fa-2x text-gray-300"></i>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="col-md-4 mb-4">
            <div class="card stat-card h-100 py-2">
              <div class="card-body">
                <div class="row align-items-center">
                  <div class="col me-2">
                    <div class="text-xs fw-bold text-warning text-uppercase mb-1">Messages non lus</div>
                    <div id='unreadMessage' class="h5 mb-0 fw-bold text-gray-800">-</div>
                  </div>
                  <div class="col-auto">
                    <i class="fas fa-envelope-open-text fa-2x text-gray-300"></i>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <section id="rdv-section" class="content-section active">
          <div class="d-flex justify-content-between align-items-center mb-3">
            <h2 class="h5">Rendez-vous</h2>
            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalAddRdv" id="btn-add-rdv">
              <i class="fas fa-plus me-1"></i> Nouveau RDV
            </button>
          </div>
          <!-- Barre de recherche / filtres -->
          <div class="row mb-3">
            <div class="col-md-4 mb-2">
              <input
                type="search"
                id="search-rdv"
                class="form-control"
                placeholder="Rechercher par patient…" />
            </div>
            <div class="col-md-3 mb-2">
              <input type="date" id="filter-date" class="form-control" />
            </div>
            <div class="col-md-3 mb-2">
              <select id="doctorSelect" class="form-select">
                <option value="">Tous les médecins</option>
              </select>
            </div>
            <div class="col-md-2 mb-2">
              <select id="filter-status" class="form-select">
                <option value="">Tous statuts</option>
                <option value="En attente">En attente</option>
                <option value="Validé">Validé</option>
                <option value="Annulé">Annulé</option>
              </select>
            </div>
          </div>
          <!-- Tableau des RDV -->
          <div class="table-responsive">
            <table class="table table-hover mb-3">
              <thead>
                <tr>
                  <th>Patient</th>
                  <th>Date &amp; Heure</th>
                  <th>Médecin</th>
                  <th>Statut</th>
                  <th class="text-center">Actions</th>
                </tr>
              </thead>
              <tbody id="rdv-tbody">
              </tbody>
            </table>
          </div>
          <p class="small text-muted">Filtrez, validez ou annulez les RDV selon les disponibilités.</p>
        </section>


       <!-- Modal Ajouter un RDV -->
<div class="modal fade" id="modalAddRdv" tabindex="-1" aria-labelledby="modalAddRdvLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <form id="formSecretaryAddRdv">
        <!-- Champs techniques -->
        <input type="hidden" name ='formName' id="formSecretaryAddRdv_formName" value="formSecretaryAddRdv">
        <input type="hidden" name='csrfToken' id="formSecretaryAddRdv_csrfToken" value="">
        <input type="hidden" id="selectedPatientId" value="">

        <div class="modal-header">
          <h5 class="modal-title" id="modalAddRdvLabel">Ajouter un Rendez‑vous</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
        </div>

        <div class="modal-body">
          <!-- Recherche Patient -->
          <div class="mb-3">
            <label for="searchPatientWithDoctors" class="form-label">Rechercher un patient</label>
            <input
              type="text"
              class="form-control"
              id="searchPatientWithDoctors"
              placeholder="Tapez nom ou prénom…"
              autocomplete="off"
              required>
            <div id="patientListAddRdv" class="list-group mt-1"></div>
          </div>

          <!-- Sélection du médecin -->
          <div class="mb-3">
            <label for="doctorListAddRdv" class="form-label">Médecin</label>
            <select id="doctorListAddRdv" class="form-select" required>
              <option value="" disabled selected>Choisir un médecin…</option>

            </select>
          </div>

          <!-- Date et heure -->
          <div class="row gx-2">
            <div class="col-md-6 mb-3">
              <label for="rdv-date" class="form-label">Date</label>
              <input type="date" class="form-control" id="rdv-date" required>
            </div>
            <div class="col-md-6 mb-3">
              <label for="rdv-heure" class="form-label">Heure</label>
              <input type="time" class="form-control" id="rdv-heure" required>
            </div>
          </div>

          <!-- Type de RDV -->
          <div class="mb-3">
            <label for="rdv-type" class="form-label">Type de rendez-vous</label>
            <select id="rdv-type" class="form-select" required>
              <option value="" disabled selected>Choisir…</option>
              <option value="Consultation">Consultation</option>
              <option value="Suivi">Suivi</option>
              <option value="Examen">Examen</option>
            </select>
          </div>

          <!-- Notes -->
          <div class="mb-3">
            <label for="rdv-notes" class="form-label">Notes (optionnel)</label>
            <textarea class="form-control" id="rdv-notes" rows="2"></textarea>
          </div>

          <!-- Commentaires -->
          <div class="mb-3">
            <label for="rdv-comment" class="form-label">Commentaires (optionnel)</label>
            <textarea class="form-control" id="rdv-comment" rows="2"></textarea>
          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
          <button type="button" class="btn btn-success" id="saveRdv">Ajouter</button>
        </div>
      </form>
    </div>
  </div>
</div>



    <!-- 2. Fiches Patients -->
    <section id="patients-section" class="content-section">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="h5">Fiches Patients</h2>
        <button class="btn btn-primary btn-sm" id="btn-add-patient" data-bs-toggle="modal"
          data-bs-target="#modalAddPatientGlobal">
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
          <tbody id="patients-tbody-global">
            <tr>
              <td>Dupont</td>
              <td>Jean</td>
              <td>14/03/1985</td>
              <td>06 12 34 56 78</td>
              <td>jean.dupont@example.com</td>
              <td class="text-center">
                <!-- Bouton Voir -->
                <button
                  class="btn btn-sm btn-outline-primary me-1"
                  title="Voir"
                  data-bs-toggle="modal"
                  data-bs-target="#modalViewPatient">
                  <i class="fas fa-eye"></i>
                </button>

                <!-- Bouton Modifier -->
                <button
                  class="btn btn-sm btn-outline-secondary me-1"
                  title="Modifier"
                  data-bs-toggle="modal"
                  data-bs-target="#modalEditPatient"
                  data-patient-nom="Dupont"
                  data-patient-prenom="Jean"
                  data-patient-naissance="1985-03-14"
                  data-patient-phone="+33612345678"
                  data-patient-email="jean.dupont@example.com"
                  data-patient-address="12 rue de la Paix, 75002 Paris">
                  <i class="fas fa-edit"></i>
                </button>
              </td>
            </tr>
          </tbody>
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
            <form id="formSecretaryNewPatient">
              <input type="hidden" name="formName" id="formSecretaryNewPatient_formName" value="formSecretaryNewPatient" />
              <input type="hidden" name="csrfToken" id="formSecretaryNewPatient_csrfToken" value="" />

              <div class="mb-3">
                <label for="doctorSelectNwP" class="form-label">Médecin traitant</label>
                <select class="form-select" id="doctorSelectNwP" name="doctorId" required>
                  <option value="" selected disabled>Choisir un médecin...</option>
                </select>
              </div>

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
                <button type="submit" class="btn btn-primary">Enregistrer</button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
    <!-- Modal “Voir le Patient” -->
    <div
      class="modal fade"
      id="modalViewPatient"
      tabindex="-1"
      aria-labelledby="modalViewPatientLabel"
      aria-hidden="true">
      <div class="modal-dialog modal-md">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="modalViewPatientLabel">Détails du patient</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
          </div>
          <div class="modal-body">
            <dl class="row mb-0">

              <dt class="col-sm-4">Nom</dt>
              <dd class="col-sm-8" id="view-patient-nom"></dd>

              <dt class="col-sm-4">Prénom</dt>
              <dd class="col-sm-8" id="view-patient-prenom"></dd>

              <dt class="col-sm-4">Date de naissance</dt>
              <dd class="col-sm-8" id="view-patient-naissance"></dd>

              <dt class="col-sm-4">Téléphone</dt>
              <dd class="col-sm-8" id="view-patient-phone"></dd>

              <dt class="col-sm-4">E-mail</dt>
              <dd class="col-sm-8" id="view-patient-email"></dd>

              <dt class="col-sm-4">Adresse</dt>
              <dd class="col-sm-8" id="view-patient-address"></dd>
            </dl>
          </div>
          <div class="modal-footer">
            <button
              type="button"
              class="btn btn-secondary"
              data-bs-dismiss="modal">
              Fermer
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal “Modifier le Patient” -->
    <div
      class="modal fade"
      id="modalEditPatient"
      tabindex="-1"
      aria-labelledby="modalEditPatientLabel"
      aria-hidden="true">
      <div class="modal-dialog modal-md">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="modalEditPatientLabel">Modifier le patient</h5>
            <input type="hidden" name="patientId" id="edit-patient-id" />
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
          </div>
          <div class="modal-body">
            <form id="formSecretaryEditPatient">
              <input type="hidden" name="formName" id="formSecretaryEditPatient_formName" value="formSecretaryEditPatient" />
              <input type="hidden" name="csrfToken" id="formSecretaryEditPatient_csrfToken" value="" />

              <div class="mb-3">
                <label for="edit-patient-nom" class="form-label">Nom</label>
                <input
                  type="text"
                  class="form-control"
                  id="edit-patient-nom"
                  required />
              </div>
              <div class="mb-3">
                <label for="edit-patient-prenom" class="form-label">Prénom</label>
                <input
                  type="text"
                  class="form-control"
                  id="edit-patient-prenom"
                  required />
              </div>
              <div class="mb-3">
                <label for="edit-patient-naissance" class="form-label">Date de naissance</label>
                <input
                  type="date"
                  class="form-control"
                  id="edit-patient-naissance"
                  required />
              </div>
              <div class="mb-3">
                <label for="edit-patient-phone" class="form-label">Téléphone</label>
                <input
                  type="tel"
                  class="form-control"
                  id="edit-patient-phone"
                  required />
              </div>
              <div class="mb-3">
                <label for="edit-patient-email" class="form-label">E-mail</label>
                <input
                  type="email"
                  class="form-control"
                  id="edit-patient-email"
                  required />
              </div>
              <div class="mb-3">
                <label for="edit-patient-address" class="form-label">Adresse</label>
                <textarea
                  class="form-control"
                  id="edit-patient-address"
                  rows="2"
                  required></textarea>
              </div>
              <div class="text-end">
                <button
                  type="button"
                  class="btn btn-secondary me-2"
                  data-bs-dismiss="modal">
                  Annuler
                </button>
                <button type="SUBMIT" class="btn btn-primary">Enregistrer</button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>

    <!-- 3. Notification Médecin -->
    <section id="notify-section" class="content-section">
      <div class="card mb-4">
        <div class="card-header">
          <h2 class="h5 mb-0">Notification Médecin</h2>
        </div>

        <div class="card-body">
          <!-- Onglets Envoyer / Inbox -->
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
                Messages reçus <span class="badge bg-secondary ms-1" id="messageCount">0</span>
              </button>
            </li>
          </ul>

          <div class="tab-content" id="cs-tabContent">
            <!-- Envoyer un message -->
            <div
              class="tab-pane fade show active"
              id="cs-envoyer"
              role="tabpanel"
              aria-labelledby="cs-envoyer-tab">
              <form id="formSecretarySendMessage">
                <input type="hidden" name="formName" id="formSecretarySendMessage_formName" value="formSecretarySendMessage" />
                <input type="hidden" name="csrfToken" id="formSecretarySendMessage_csrfToken" value="" />

                <div class="mb-3">
                  <div class="col-md-3 mb-2">
                    <select id="doctorSelectMessage" class="form-select">
                      <option value="">Tous les médecins</option>
                    </select>
                  </div>

                  <label for="cs-sujet" class="form-label">Sujet</label>
                  <input
                    type="text"
                    class="form-control"
                    id="cs-sujet"
                    placeholder="Objet du message"
                    required />
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

                <button type="submit" id='sendMessage' class="btn btn-primary">Envoyer</button>
              </form>
            </div>

            <!-- Inbox : messages reçus -->
            <div
              class="tab-pane fade"
              id="cs-inbox"
              role="tabpanel"
              aria-labelledby="cs-inbox-tab">

              <!-- Liste des messages -->
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
                  <tbody id='receivedMessagelist'>
                    <!-- Messages JS dynamiques ici -->
                  </tbody>
                </table>
                <div id="modalsContainer"></div>

              </div>

              <div id="cs-messages-collapse"></div>
            </div>
          </div>
        </div>
      </div>
    </section>


    <!-- 4. Horaires d’ouverture -->
    <section id="hours-section" class="content-section">
      <div class="card mb-4">
        <div class="card-header">
          <h2 class="h5 mb-0">Horaires d’ouverture du Cabinet</h2>
        </div>
        <div class="card-body">
          <form id="formSecretaryChangeOpenHours">
            <input type="hidden" name="formName" id="formSecretaryChangeOpenHours_formName" value="formSecretaryChangeOpenHours" />
            <input type="hidden" name="csrfToken" id="formSecretaryChangeOpenHours_csrfToken" value="" />

            <div class="row mb-3">
              <div class="col-md-3">
                <label for="opening-time" class="form-label">Ouverture (matin)</label>
                <input type="time" id="opening-time" class="form-control" required>
              </div>
              <div class="col-md-3">
                <label for="closing-time" class="form-label">Fermeture (midi)</label>
                <input type="time" id="closing-time" class="form-control" required>
              </div>
              <div class="col-md-3">
                <label for="opening-afternoon" class="form-label">Ouverture (après-midi)</label>
                <input type="time" id="opening-afternoon" class="form-control" required>
              </div>
              <div class="col-md-3">
                <label for="closing-afternoon" class="form-label">Fermeture (soir)</label>
                <input type="time" id="closing-afternoon" class="form-control" required>
              </div>
            </div>

            <div class="mb-3">
              <label class="form-label">Jours fermés réguliers</label>
              <div class="form-check">
                <input class="form-check-input" type="checkbox" id="closed-sunday" value="0">
                <label class="form-check-label" for="closed-sunday">Dimanche</label>
              </div>
              <div class="form-check">
                <input class="form-check-input" type="checkbox" id="closed-saturday" value="6">
                <label class="form-check-label" for="closed-saturday">Samedi</label>
              </div>
              <!-- Tu peux ajouter mercredi ou autres ici -->
            </div>

            <div class="mb-3">
              <label for="closed-days" class="form-label">Jours fériés exceptionnels</label>
              <input
                type="text"
                class="form-control"
                id="closed-days"
                placeholder="Ex : 14/07/2025, 25/12/2025">
              <div class="form-text">Format jj/mm/aaaa, séparés par des virgules.</div>
            </div>

            <button type="submit" id="btnChangeHours" class="btn btn-primary">Mettre à jour</button>
            <p class="small text-muted mt-2">Ces horaires sont affichés sur le site public du cabinet.</p>
          </form>
        </div>
      </div>
    </section>

    <!-- Footer -->
    <?php include __DIR__ . '/../views/includes/footer.php'; ?>

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.2.3/js/bootstrap.bundle.min.js" defer></script>
</body>

</html>