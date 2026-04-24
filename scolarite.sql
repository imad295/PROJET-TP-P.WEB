-- ==========================================
-- TABLES PRINCIPALES
-- ==========================================

-- 1. Niveau (L1, L2, L3)
CREATE TABLE niveau (
    id_niveau INT PRIMARY KEY AUTO_INCREMENT,
    nom_niveau VARCHAR(50) NOT NULL,
    ordre INT NOT NULL
);

-- 2. Spécialité (Informatique, etc.)
CREATE TABLE specialite (
    id_specialite INT PRIMARY KEY AUTO_INCREMENT,
    nom_specialite VARCHAR(100) NOT NULL,
    id_niveau INT NOT NULL,
    FOREIGN KEY (id_niveau) REFERENCES niveau(id_niveau)
);

-- 3. Section (A1, A2, B1, etc.)
CREATE TABLE section (
    id_section INT PRIMARY KEY AUTO_INCREMENT,
    nom_section VARCHAR(50) NOT NULL,
    id_specialite INT NOT NULL,
    FOREIGN KEY (id_specialite) REFERENCES specialite(id_specialite)
);

-- 4. Groupe (Groupe 1, 2, 3, 4 par section)
CREATE TABLE `groupe` (
    id_groupe INT PRIMARY KEY AUTO_INCREMENT,
    nom_groupe VARCHAR(50) NOT NULL,
    id_section INT NOT NULL,
    FOREIGN KEY (id_section) REFERENCES section(id_section)
);

-- 5. Étudiant
CREATE TABLE etudiant (
    id_etudiant INT PRIMARY KEY AUTO_INCREMENT,
    matricule VARCHAR(20) UNIQUE NOT NULL,
    nom VARCHAR(50) NOT NULL,
    prenom VARCHAR(50) NOT NULL,
    date_naissance DATE NOT NULL,
    id_groupe INT NULL,
    FOREIGN KEY (id_groupe) REFERENCES `groupe`(id_groupe)
);

-- 6. Enseignant
CREATE TABLE enseignant (
    id_enseignant INT PRIMARY KEY AUTO_INCREMENT,
    nom VARCHAR(50) NOT NULL,
    prenom VARCHAR(50) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    mot_de_passe VARCHAR(255) NOT NULL
);

-- 7. Administrateur
CREATE TABLE administrateur (
    id_admin INT PRIMARY KEY AUTO_INCREMENT,
    login VARCHAR(50) UNIQUE NOT NULL,
    mot_de_passe VARCHAR(255) NOT NULL
);

-- 8. Module
CREATE TABLE module (
    id_module INT PRIMARY KEY AUTO_INCREMENT,
    nom_module VARCHAR(100) NOT NULL,
    coefficient DECIMAL(3,1) NOT NULL,
    id_enseignant INT NULL,
    FOREIGN KEY (id_enseignant) REFERENCES enseignant(id_enseignant)
);

-- 9. Note
CREATE TABLE note (
    id_note INT PRIMARY KEY AUTO_INCREMENT,
    id_etudiant INT NOT NULL,
    id_module INT NOT NULL,
    note_cc DECIMAL(4,2) NULL,
    note_examen DECIMAL(4,2) NULL,
    note_ratrapage DECIMAL(4,2) NULL,
    session VARCHAR(20) DEFAULT 'Normale',
    FOREIGN KEY (id_etudiant) REFERENCES etudiant(id_etudiant),
    FOREIGN KEY (id_module) REFERENCES module(id_module)
);
