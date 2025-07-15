## ⚠️ Note d'information importante

Les médecins, patients, documents, données et contenus utilisés dans ce projet sont **entièrement fictifs** et ont été créés uniquement à des fins pédagogiques et de démonstration.

---

## ⚠️ Clause de non-responsabilité

Ce projet est un prototype réalisé dans un cadre d’étude. **Il ne doit en aucun cas être utilisé en production, ni pour gérer de véritables données médicales.**

L’auteur décline toute responsabilité en cas d’usage frauduleux, abusif ou non conforme à la législation, notamment en ce qui concerne la protection des données personnelles (RGPD) et le secret médical.

---

Ce projet est amené à évoluer, notamment sur le plan sécuritaire, afin de répondre aux exigences de confidentialité et de robustesse attendues dans un usage professionnel.

---

# Projet Ardentes

## 1. Présentation générale

Cette application web permet de gérer un cabinet médical avec trois rôles distincts : **médecin**, **secrétaire** et **patient**. Développée en **PHP 8+** et **JavaScript moderne**, elle met l’accent sur :

- **Sécurité by design** : authentification robuste, gestion de sessions sécurisées, tokens CSRF, chiffrement des données
- **Architecture modulaire** : séparation nette des responsabilités (routeurs, contrôleurs, traits, scripts init)
- **Expérience utilisateur fluide** : navigation par onglets, chargement dynamique des modules JS, notifications en temps réel
- **Maintenabilité & extensibilité** : code typé, validation et sanitation centralisées, utilisation de traits et de classes ES6

---

## 2. Structure du projet

```text
public/                   # Front-end statique et point d’entrée client
├─ index.php              # Page d’accueil + includes CSS/JS
├─ js/
│  ├─ boot/               # Activation onglets et chargement des scripts init par rôle
│  ├─ init/doctor/        # Scripts init spécifiques au rôle docteur
│  ├─ init/secretary/     # Idem pour secrétaire
│  ├─ init/patient/       # Idem pour patient
│  ├─ classes/doctor/     # Classes métier JS (Patients, Appointments…)
│  ├─ components/         # UI Components (Popup.js, etc.)
│  ├─ promises/           # MasterFetch.js pour appels à MasterRouter
│  └─ classes/            # CsrfManager, services front
│
src/
├─ auth/                  # Authentification & gestion des sessions
│  ├─ Session.php
│  ├─ Csrf.php
│  └─ Auth.php
│
├─ secure/                # MasterToken & protection contre le hijacking
│  └─ Secure.php
│
├─ core/                  # Backend principal
│  ├─ MasterRouter.php
│  └─ Notifier.php
│
├─ core/<role>/           # Sous-routeurs (DoctorRouter, PatientRouter, SecretaryRouter)
│
├─ controllers/           # Logique métier (PatientManager, Consultations…)
│
├─ traits/                # Traits réutilisables
│  ├─ Validator.php
│  ├─ Sanitize.php
│  ├─ Dto.php
│  ├─ Link.php
│  └─ Clean.php
│
└─ config/                # Configuration & secrets
   ├─ Config.php          # Chargement du `.env`
   └─ .env                # Variables d’environnement (DB, CRYPTO_KEY)
```

---

## 3. Flux de requête & navigation

1. L’utilisateur clique sur un **onglet** (via `boot.js`)
2. `boot.js` active dynamiquement le script `init*.js` associé
3. Ce script instancie les **classes métiers** (ex. `Patients`)
4. Ces classes appellent `MasterFetch.call(action, data)` pour contacter le backend PHP
5. `MasterRouter.php` valide la session et délègue au routeur métier correspondant
6. `Validator`, `Sanitize`, `Dto` assurent la validation et la cohérence des données
7. Les contrôleurs traitent la logique métier et renvoient les résultats
8. `Notifier` compile les retours utilisateurs
9. `Popup.js` affiche les messages, modales, etc.

---

## 4. Composants clés

### 4.1 `boot.js`
- `ongletActivation(targetId)` : gère le changement de section active
- `launchScript(src)` : charge un module ES6 si non encore chargé

### 4.2 `init/doctor/init*.js`
- Instancie les gestionnaires (Patients, Appointments…)
- Gère les événements (formulaires, boutons…)
- Insère le token CSRF via `CsrfManager`

### 4.3 `MasterFetch.js`
- POST JSON vers `MasterRouter.php`
- Gère les retours : `input`, `popup`, `console`, `modale`, `redirect`
- Retourne `json.data` à traiter dans le front

---

## 5. Sécurité & bonnes pratiques

- **Sessions** : cookies `httponly`, `samesite=Strict`, durée 30 min, ID régénéré
- **MasterToken** : jeton 32 bytes, lié IP et user-agent, expiration 8h
- **CSRF** : token 32 bytes, rotation auto, durée 15 min, stocké en DB
- **Données sensibles** : chiffrées via `pgcrypto`, clé dans `.env`
- **Validation** : whitelist + typage via `Validator`
- **Sanitation** : nettoyage systématique via `Sanitize`
- **DTO** : objets métiers typés avec setters contrôlés

---

## 6. Installation & démarrage

```bash
git clone <repo_url>
cd project
composer install
cp .env.example .env        # Renseigner DB_*, CRYPTO_KEY
# Importer schema.sql dans Postgres
# Pointer votre serveur web sur public/
```

Lancer l’application dans votre navigateur, connecter un utilisateur, et naviguer dans les différents onglets.

---

## 7. ToDo & évolutions envisageables

- Prévoir la notion de foyer et de relation parents/enfants.
- Ajouter des **tests unitaires** (PHPUnit côté back, Jest côté front)
- Implémenter un système de **monitoring/logging** avancé
- Documenter l’API avec **OpenAPI** / Swagger
- Mettre en place un **système de permissions (RBAC)** plus fin
- Ajouter la possibilité de **changer son mot de passe** (docteur & secrétaire)
- Améliorer le système de **notification/retour utilisateur**
- Refondre la structure pour rendre l’application **cloud-native et modulaire**
- Intégrer une **authentification 2FA** pour les patients, avec récupération sécurisée
- Optimiser le **design et l’ergonomie générale**
- Étendre la logique à un système **multisite avec suivi géospatial**
- Ajouter une **fonction de réservation & paiement en ligne sécurisé**
- Ajouter une **IA côté médecin** avec base Vidal intégrée
- Ajouter une **IA côté patient** pour orientation et conseil
- Penser l’**évolutivité et le renommage** de l’application à l’échelle d’un groupe
- Reconsidérer à terme certaines **technologies utilisées**
- Faire évoluer le projet vers une **suite logicielle médicale complète**, moderne, et pérenne

---

> *« Ce projet d’étude a pour but de m’accompagner dans mon évolution académique et professionnelle. »*

**Licence** : MIT  
**Version** : 1.0.0