<<<<<<< HEAD
# 📚 Gestion Scolarité USTHB

## Description du projet

Application web complète de gestion de scolarité pour la Faculté d'Informatique de l'USTHB (Université des Sciences et de la Technologie Houari Boumediene). L'application permet de gérer les étudiants, les enseignants, les modules, les notes et de générer des relevés de notes avec une hiérarchie complète : Niveaux → Spécialités → Sections → Groupes.

---

## 📁 Structure et description des fichiers

### Dossier includes (fichiers de configuration)

**`includes/config.php`** : Ce fichier centralise la configuration de l'application. Il établit la connexion à la base de données MySQL via PDO, démarre la session PHP, et définit des fonctions utilitaires globales comme `isLoggedIn()` pour vérifier si un utilisateur est connecté, `hasRole()` pour vérifier son rôle (admin, enseignant ou étudiant), et `redirect()` pour faciliter les redirections entre les pages.

**`includes/header.php`** : Ce fichier génère l'en-tête HTML et la barre latérale (sidebar) de l'application. Il inclut les feuilles de style CSS, les polices Google Fonts, et construit dynamiquement le menu de navigation en fonction du rôle de l'utilisateur connecté (administrateur, enseignant ou étudiant). La sidebar affiche également le nom et le rôle de l'utilisateur ainsi qu'un bouton de déconnexion.

**`includes/footer.php`** : Ce simple fichier ferme les balises HTML ouvertes dans le header, inclut les scripts JavaScript nécessaires, et termine la structure de la page.

---

### Pages publiques (racine)

**`index.php`** : C'est la page d'accueil du site, accessible à tout visiteur. Elle présente la plateforme avec une section hero, des statistiques animées (nombre d'étudiants, d'enseignants et de modules), une grille des fonctionnalités, une présentation des rôles, et un footer complet contenant les coordonnées et horaires de la faculté. Elle redirige automatiquement les utilisateurs déjà connectés vers leur tableau de bord respectif.

**`public_etudiants.php`** : Cette page d'annuaire public permet à tout visiteur (sans authentification) de consulter la liste des étudiants avec leurs informations académiques : matricule, nom, prénom, niveau, spécialité, section et groupe. Elle intègre des fonctionnalités de recherche par nom ou matricule, ainsi que des filtres par niveau et par section. Aucune note ou information personnelle sensible n'est affichée.

**`create_first_admin.php`** : Ce fichier est utilisé lors de l'installation initiale du système. Il vérifie si aucun administrateur n'existe en base de données et, si c'est le cas, permet de créer le premier compte administrateur en saisissant un login et un mot de passe. Une fois le premier admin créé, la page devient inaccessible et redirige vers la page de connexion.

---

### Pages d'authentification (dossier pages)

**`pages/login.php`** : Cette page gère la connexion des trois types d'utilisateurs. Elle vérifie d'abord si un administrateur existe, sinon elle redirige vers la création du premier admin. Ensuite, elle tente d'authentifier l'utilisateur successivement comme administrateur (login + mot de passe hashé), comme enseignant (email + mot de passe hashé), ou comme étudiant (matricule + date de naissance au format AAAAMMJJ). En cas de succès, elle ouvre une session et redirige vers le tableau de bord correspondant.

**`pages/logout.php`** : Ce fichier très simple détruit la session en cours (via `session_destroy()`) et redirige l'utilisateur vers la page d'accueil du site.

---

### Pages Administrateur (dossier pages)

**`pages/dashboard_admin.php`** : Le tableau de bord de l'administrateur affiche des cartes statistiques avec les totaux d'étudiants, d'enseignants et de modules, des boutons d'accès rapide vers toutes les sections de gestion, et une liste des cinq derniers étudiants inscrits.

**`pages/etudiants.php`** : Ce fichier permet la gestion complète (CRUD) des étudiants. L'administrateur peut rechercher des étudiants par nom, prénom ou matricule, ajouter de nouveaux étudiants (matricule, nom, prénom, date de naissance, groupe), modifier leurs informations, ou les supprimer de la base de données.

**`pages/enseignants.php`** : Ce fichier gère les comptes enseignants avec les actions suivantes : ajouter un enseignant (nom, prénom, email, mot de passe), modifier ses informations, changer son mot de passe, ou supprimer son compte. Une barre de recherche permet de filtrer les enseignants par nom ou email.

**`pages/admins.php`** : Ce fichier gère les comptes administrateurs secondaires (en plus du compte principal). L'administrateur principal peut ajouter, modifier, changer le mot de passe ou supprimer d'autres administrateurs. Le compte 'admin' est protégé et ne peut pas être supprimé.

**`pages/modules.php`** : Ce fichier gère les modules d'enseignement. L'administrateur peut ajouter un module (nom, coefficient), modifier ses propriétés, l'assigner à un enseignant responsable, ou le supprimer. Une recherche par nom de module est disponible.

**`pages/niveaux.php`** : Ce fichier gère les niveaux d'étude (1ère Année L1, 2ème Année L2, 3ème Année L3). L'administrateur peut ajouter, modifier ou supprimer des niveaux. Chaque niveau possède un ordre (1,2,3) pour le tri.

**`pages/specialites.php`** : Ce fichier gère les spécialités (ex: Informatique) associées à chaque niveau. L'administrateur peut ajouter, modifier ou supprimer des spécialités, en les reliant à un niveau spécifique.

**`pages/sections.php`** : Ce fichier gère les sections (A1, A2, A3, B1, B2, B3, C1, C2, C3) rattachées aux spécialités. L'administrateur peut ajouter, modifier ou supprimer des sections, avec un total de 9 sections (3 par spécialité).

**`pages/groupes.php`** : Ce fichier gère les 36 groupes d'étudiants (4 groupes par section). L'administrateur peut ajouter, modifier ou supprimer des groupes en les rattachant à une section spécifique.

**`pages/notes.php`** : Ce fichier permet à l'administrateur de gérer les notes de tous les étudiants. Il permet de rechercher un étudiant par matricule, de sélectionner un module, et de saisir/modifier les notes de contrôle continu (CC), d'examen, et de rattrapage pour chaque étudiant.

**`pages/statistiques.php`** : Ce fichier génère des statistiques globales : moyennes par module avec appréciation (Excellent, Très bien, Bien, Insuffisant), et classement des étudiants avec leur moyenne générale et mention (Très bien, Bien, Assez bien, Passable, Insuffisant).

**`pages/releve.php`** : Ce fichier permet à l'administrateur de consulter le relevé de notes complet d'un étudiant en recherchant par son matricule. Le relevé affiche les informations académiques de l'étudiant (matricule, nom, prénom, date naissance, niveau, spécialité, section, groupe), le tableau détaillé de ses notes par module, sa moyenne générale, sa mention et sa décision d'admission. Un bouton d'impression est disponible.

---

### Pages Enseignant (dossier pages)

**`pages/dashboard_enseignant.php`** : Le tableau de bord de l'enseignant affiche ses modules assignés avec leur coefficient, des cartes statistiques, et la possibilité de voir la liste des étudiants inscrits dans chaque module (avec affichage de leur niveau, section, groupe et moyenne dans le module) ou de gérer directement leurs notes.

**`pages/notes.php`** : Version restreinte de la gestion des notes pour les enseignants. Ils ne voient et ne peuvent modifier que les notes des modules qui leur sont assignés. Le reste des fonctionnalités (recherche étudiant, saisie CC/examen/rattrapage) est identique à la version admin.

**`pages/releve.php`** : L'enseignant peut consulter le relevé de notes de n'importe quel étudiant en recherchant par son matricule, sans restriction.

---

### Pages Étudiant (dossier pages)

**`pages/dashboard_etudiant.php`** : Le tableau de bord de l'étudiant affiche sa carte d'identité académique complète (matricule, niveau, spécialité, section, groupe), sa moyenne générale avec la décision d'admission (Admis/Non admis), et un tableau détaillé de ses notes par module avec validation. Un bouton permet d'accéder à son relevé complet.

**`pages/releve.php`** : Version personnelle du relevé de notes. L'étudiant voit automatiquement son propre relevé (sans avoir à rechercher) avec toutes ses informations académiques, ses notes, sa moyenne générale, sa mention et sa décision. L'impression est également disponible.

---

### Fichiers de ressources (assets)

**`assets/css/index.css`** : Ce fichier contient tous les styles spécifiques à la page d'accueil : animations, effets de fond (orbes flottants), styles de la navbar, de la section hero, des statistiques, des fonctionnalités, des cartes de rôles, du footer, et la responsivité pour les mobiles.

**`assets/css/style.css`** : Ce fichier contient les styles généraux communs à toutes les pages de l'application (dashboard, formulaires, tables, modals, alertes, sidebar, etc.). Il définit l'apparence des composants réutilisables.

**`assets/js/script.js`** : Ce fichier contient les fonctions JavaScript globales : fermeture automatique des alertes, ouverture/fermeture des modals avec animations, validation des formulaires (email, notes entre 0-20), confirmation avant suppression, affichage/masquage dynamique des champs selon le rôle choisi dans le formulaire d'inscription, et recherche en temps réel dans les tableaux.

---

### Base de données (dossier database)

**`database/schema.sql`** : Ce fichier contient toutes les requêtes SQL nécessaires pour créer la structure complète de la base de données : les tables (niveau, specialite, section, groupe, etudiant, enseignant, administrateur, module, note) avec leurs relations via clés étrangères, ainsi que les données initiales (3 niveaux, 3 spécialités, 9 sections, 36 groupes). Il permet d'installer ou de réinitialiser la base de données en une seule exécution.

---

## 👥 Rôles et accès

| Rôle               | Accès                                               |
|--------------------|-----------------------------------------------------|
| **Administrateur** | Toutes les fonctionnalités (gestion complète)       |
| **Enseignant**     | Ses modules, saisie des notes, consultation relevés |
| **Étudiant**       | Son dashboard, ses notes, son relevé personnel      |
| **Public**         | Page d'accueil, annuaire des étudiants              |

---

## 🔑 Identifiants de connexion

| Rôle           | Identifiant       | Mot de passe                        |
|----------------|-------------------|-------------------------------------|
| Administrateur | login (ex: admin) | Défini lors de la création          | 
| Enseignant     | email             | Défini lors de la création          |
| Étudiant       | matricule         | date de naissance (format AAAAMMJJ) |

---

## 🛠️ Technologies utilisées

- PHP 7.4/8.x
- MySQL 5.7+
- HTML5/CSS3
- JavaScript
- Font Awesome 6
- Google Fonts (Inter)

---

## 📞 Contact

- **Email** : faculte.info@usthb.dz
- **Téléphone** : +213 (0) 23 15 10 00
- **Adresse** : BP 32, El Alia, Bab Ezzouar, 16111 Alger, Algérie

---

© 2025-2026 USTHB - Faculté d'Informatique. Tous droits réservés.
=======
# PROJET-TP-P.WEB
Conception et développement d’une application Web de gestion de la scolarité (PHP&amp;MySQL). 
>>>>>>> f2ce02e9d03b58292e85ab7e8918d4b9a3582eb5
