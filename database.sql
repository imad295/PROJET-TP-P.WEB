CREATE DATABASE IF NOT EXISTS university_portal;
USE university_portal;

-- Étudiants
CREATE TABLE students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(50),
    prenom VARCHAR(50),
    matricule VARCHAR(20) UNIQUE,
    date_naissance DATE,
    niveau VARCHAR(20),
    email VARCHAR(100) UNIQUE,
    password VARCHAR(255)
);

-- Enseignants
CREATE TABLE teachers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(50),
    prenom VARCHAR(50),
    email VARCHAR(100) UNIQUE,
    password VARCHAR(255)
);

-- Administrateurs
CREATE TABLE admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(50),
    prenom VARCHAR(50),
    email VARCHAR(100) UNIQUE,
    password VARCHAR(255)
);

-- Notes
CREATE TABLE notes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT,
    subject VARCHAR(50),
    note FLOAT,
    teacher_id INT,
    FOREIGN KEY(student_id) REFERENCES students(id),
    FOREIGN KEY(teacher_id) REFERENCES teachers(id)
);