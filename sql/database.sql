-- Fichier: sql/database.sql
-- Base de données pour Sama Docteur

-- Création de la base de données
CREATE DATABASE IF NOT EXISTS sama_docteur;
USE sama_docteur;

-- Table des utilisateurs (patients et médecins)
CREATE TABLE IF NOT EXISTS utilisateurs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom_complet VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    mot_de_passe VARCHAR(255) NOT NULL,
    telephone VARCHAR(20),
    role ENUM('patient', 'medecin', 'admin') DEFAULT 'patient',
    date_inscription DATETIME DEFAULT CURRENT_TIMESTAMP,
    est_actif BOOLEAN DEFAULT TRUE,
    derniere_connexion DATETIME NULL,
    INDEX idx_email (email),
    INDEX idx_role (role)
);

-- Table des spécialités
CREATE TABLE IF NOT EXISTS specialites (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(50) UNIQUE NOT NULL,
    description TEXT,
    icone VARCHAR(50) DEFAULT 'fas fa-stethoscope',
    date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_nom (nom)
);

-- Table des médecins
CREATE TABLE IF NOT EXISTS medecins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom_complet VARCHAR(100) NOT NULL,
    specialite_id INT,
    email VARCHAR(100) UNIQUE,
    telephone VARCHAR(20),
    adresse TEXT,
    ville VARCHAR(50),
    tarif_consultation DECIMAL(10,2),
    photo VARCHAR(255),
    numero_ordre VARCHAR(50),
    biographie TEXT,
    annees_experience INT DEFAULT 0,
    est_disponible BOOLEAN DEFAULT TRUE,
    date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (specialite_id) REFERENCES specialites(id) ON DELETE SET NULL,
    INDEX idx_specialite (specialite_id),
    INDEX idx_email (email),
    INDEX idx_disponible (est_disponible)
);

-- Table des horaires des médecins
CREATE TABLE IF NOT EXISTS horaires_medecins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    medecin_id INT NOT NULL,
    jour_semaine ENUM('Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi', 'Dimanche'),
    heure_debut TIME,
    heure_fin TIME,
    pause_debut TIME,
    pause_fin TIME,
    duree_consultation INT DEFAULT 30 COMMENT 'Durée en minutes',
    est_disponible BOOLEAN DEFAULT TRUE,
    date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
    date_modification DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (medecin_id) REFERENCES medecins(id) ON DELETE CASCADE,
    UNIQUE KEY unique_horaire (medecin_id, jour_semaine),
    INDEX idx_medecin (medecin_id),
    INDEX idx_jour (jour_semaine)
);

-- Table des rendez-vous
CREATE TABLE IF NOT EXISTS rendez_vous (
    id INT AUTO_INCREMENT PRIMARY KEY,
    utilisateur_id INT,
    medecin_id INT,
    date_rendez_vous DATETIME NOT NULL,
    statut ENUM('en_attente', 'confirme', 'annule', 'termine', 'no_show') DEFAULT 'en_attente',
    motif TEXT,
    notes_medecin TEXT,
    date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
    date_modification DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    rappel_envoye BOOLEAN DEFAULT FALSE,
    duree_minutes INT DEFAULT 30,
    FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(id) ON DELETE CASCADE,
    FOREIGN KEY (medecin_id) REFERENCES medecins(id) ON DELETE CASCADE,
    INDEX idx_utilisateur (utilisateur_id),
    INDEX idx_medecin (medecin_id),
    INDEX idx_date (date_rendez_vous),
    INDEX idx_statut (statut),
    INDEX idx_medecin_date (medecin_id, date_rendez_vous)
);

-- Table des notes médicales (dossiers patients)
CREATE TABLE IF NOT EXISTS notes_medicales (
    id INT AUTO_INCREMENT PRIMARY KEY,
    rendez_vous_id INT NOT NULL,
    medecin_id INT NOT NULL,
    patient_id INT NOT NULL,
    notes TEXT,
    diagnostic TEXT,
    prescriptions TEXT,
    recommandations TEXT,
    tension_arterielle VARCHAR(20),
    poids DECIMAL(5,2),
    taille DECIMAL(5,2),
    temperature DECIMAL(4,1),
    frequence_cardiaque INT,
    date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
    date_modification DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (rendez_vous_id) REFERENCES rendez_vous(id) ON DELETE CASCADE,
    FOREIGN KEY (medecin_id) REFERENCES medecins(id) ON DELETE CASCADE,
    FOREIGN KEY (patient_id) REFERENCES utilisateurs(id) ON DELETE CASCADE,
    INDEX idx_rendez_vous (rendez_vous_id),
    INDEX idx_medecin (medecin_id),
    INDEX idx_patient (patient_id),
    INDEX idx_date (date_creation)
);

-- Table des ordonnances
CREATE TABLE IF NOT EXISTS ordonnances (
    id INT AUTO_INCREMENT PRIMARY KEY,
    notes_medicales_id INT NOT NULL,
    medicament VARCHAR(100) NOT NULL,
    dosage VARCHAR(50),
    duree VARCHAR(50),
    instructions TEXT,
    FOREIGN KEY (notes_medicales_id) REFERENCES notes_medicales(id) ON DELETE CASCADE,
    INDEX idx_notes (notes_medicales_id)
);

-- Table des disponibilités exceptionnelles (congés, formations)
CREATE TABLE IF NOT EXISTS disponibilites_exceptionnelles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    medecin_id INT NOT NULL,
    date_debut DATETIME NOT NULL,
    date_fin DATETIME NOT NULL,
    motif VARCHAR(255),
    type ENUM('conge', 'formation', 'reunion', 'autre') DEFAULT 'autre',
    est_absence BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (medecin_id) REFERENCES medecins(id) ON DELETE CASCADE,
    INDEX idx_medecin (medecin_id),
    INDEX idx_dates (date_debut, date_fin)
);

-- Table des avis patients
CREATE TABLE IF NOT EXISTS avis_patients (
    id INT AUTO_INCREMENT PRIMARY KEY,
    medecin_id INT NOT NULL,
    patient_id INT NOT NULL,
    rendez_vous_id INT NOT NULL,
    note INT CHECK (note >= 1 AND note <= 5),
    commentaire TEXT,
    date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
    est_approuve BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (medecin_id) REFERENCES medecins(id) ON DELETE CASCADE,
    FOREIGN KEY (patient_id) REFERENCES utilisateurs(id) ON DELETE CASCADE,
    FOREIGN KEY (rendez_vous_id) REFERENCES rendez_vous(id) ON DELETE CASCADE,
    INDEX idx_medecin (medecin_id),
    INDEX idx_note (note),
    UNIQUE KEY unique_avis (rendez_vous_id)
);

-- Table des notifications
CREATE TABLE IF NOT EXISTS notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    utilisateur_id INT NOT NULL,
    titre VARCHAR(255),
    message TEXT,
    type ENUM('rappel', 'confirmation', 'annulation', 'info') DEFAULT 'info',
    est_lu BOOLEAN DEFAULT FALSE,
    date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
    lien VARCHAR(255),
    FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(id) ON DELETE CASCADE,
    INDEX idx_utilisateur (utilisateur_id),
    INDEX idx_lu (est_lu),
    INDEX idx_date (date_creation)
);

-- Table des logs système
CREATE TABLE IF NOT EXISTS logs_systeme (
    id INT AUTO_INCREMENT PRIMARY KEY,
    utilisateur_id INT NULL,
    action VARCHAR(255),
    details TEXT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_utilisateur (utilisateur_id),
    INDEX idx_action (action),
    INDEX idx_date (date_creation)
);

-- ============================================
-- INSERTION DES DONNÉES DE TEST
-- ============================================

-- Insertion des spécialités
INSERT INTO specialites (nom, description, icone) VALUES
('Cardiologie', 'Spécialiste du cœur et des vaisseaux sanguins', 'fas fa-heartbeat'),
('Dermatologie', 'Spécialiste de la peau, des cheveux et des ongles', 'fas fa-allergies'),
('Pédiatrie', 'Spécialiste des enfants et adolescents', 'fas fa-baby'),
('Gynécologie', 'Spécialiste de la santé féminine', 'fas fa-female'),
('ORL', 'Spécialiste des oreilles, nez et gorge', 'fas fa-ear-deaf'),
('Généraliste', 'Médecin de premier recours', 'fas fa-user-md'),
('Ophtalmologie', 'Spécialiste des yeux', 'fas fa-eye'),
('Orthopédie', 'Spécialiste des os et articulations', 'fas fa-bone'),
('Neurologie', 'Spécialiste du système nerveux', 'fas fa-brain'),
('Psychiatrie', 'Spécialiste de la santé mentale', 'fas fa-mental-health'),
('Dentiste', 'Spécialiste des dents et de la bouche', 'fas fa-tooth'),
('Urologie', 'Spécialiste des voies urinaires', 'fas fa-kidneys');

-- Insertion des utilisateurs (patients)
-- Mot de passe: samadocteur2
INSERT INTO utilisateurs (nom_complet, email, mot_de_passe, telephone, role, est_actif) VALUES
('Marie Dupont', 'marie@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '77 111 22 33', 'patient', TRUE),
('Jean Mendy', 'jean.mendy@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '77 222 33 44', 'patient', TRUE),
('Aminata Diop', 'aminata.diop@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '78 333 44 55', 'patient', TRUE),
('Papa Sow', 'papa.sow@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '76 444 55 66', 'patient', TRUE),
('Fatou Ndiaye', 'fatou.ndiaye@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '70 555 66 77', 'patient', TRUE);

-- Insertion des médecins avec leur spécialité
INSERT INTO medecins (nom_complet, specialite_id, email, telephone, adresse, ville, tarif_consultation, numero_ordre, biographie, annees_experience) VALUES
('Dr Martin Dupuis', 1, 'martin.dupuis@samadocteur.sn', '77 123 45 67', '15 rue des Lilas, Mermoz', 'Dakar', 15000, 'MED-001-2024', 'Cardiologue expérimenté avec 15 ans de pratique', 15),
('Dr Sophie Diallo', 2, 'sophie.diallo@samadocteur.sn', '77 234 56 78', '12 Avenue Lamine Guèye', 'Dakar', 12000, 'MED-002-2024', 'Dermatologue spécialiste des maladies de peau', 10),
('Dr Aliou Ndiaye', 3, 'aliou.ndiaye@samadocteur.sn', '77 345 67 89', 'Rue 10, Sicap Liberté', 'Dakar', 10000, 'MED-003-2024', 'Pédiatre dévoué à la santé des enfants', 12),
('Dr Fatou Sow', 4, 'fatou.sow@samadocteur.sn', '77 456 78 90', 'Boulevard du Sud', 'Thiès', 13000, 'MED-004-2024', 'Gynécologue obstétricienne', 8),
('Dr Abdoulaye Kane', 5, 'abdoulaye.kane@samadocteur.sn', '77 567 89 01', 'Point E, Immeuble Diallo', 'Dakar', 12500, 'MED-005-2024', 'ORL spécialiste des troubles auditifs', 11),
('Dr Adja Diop', 6, 'adja.diop@samadocteur.sn', '77 678 90 12', 'Cité Tally', 'Rufisque', 8000, 'MED-006-2024', 'Médecin généraliste', 7),
('Dr Oumar Fall', 1, 'oumar.fall@samadocteur.sn', '77 789 01 23', 'HLM Grand Yoff', 'Dakar', 15000, 'MED-007-2024', 'Cardiologue interventionnel', 9),
('Dr Aïssatou Ba', 7, 'aissatou.ba@samadocteur.sn', '77 890 12 34', 'Mermoz, Villa 23', 'Dakar', 14000, 'MED-008-2024', 'Ophtalmologiste spécialiste de la rétine', 13),
('Dr Cheikh Diagne', 8, 'cheikh.diagne@samadocteur.sn', '77 901 23 45', 'Sacre Coeur 3', 'Dakar', 13500, 'MED-009-2024', 'Orthopédiste traumatologue', 14),
('Dr Mame Diarra Fall', 9, 'mame.fall@samadocteur.sn', '78 012 34 56', 'Fann Residence', 'Dakar', 16000, 'MED-010-2024', 'Neurologue spécialiste de l\'épilepsie', 10);

-- Insertion des horaires pour chaque médecin
INSERT INTO horaires_medecins (medecin_id, jour_semaine, heure_debut, heure_fin, pause_debut, pause_fin, duree_consultation, est_disponible)
SELECT 
    m.id,
    jours.jour,
    CASE 
        WHEN jours.jour IN ('Samedi') THEN '09:00:00'
        ELSE '08:30:00'
    END,
    CASE 
        WHEN jours.jour IN ('Samedi') THEN '13:00:00'
        ELSE '17:00:00'
    END,
    '12:30:00',
    '14:00:00',
    30,
    CASE WHEN jours.jour = 'Dimanche' THEN FALSE ELSE TRUE END
FROM medecins m
CROSS JOIN (
    SELECT 'Lundi' as jour UNION SELECT 'Mardi' UNION SELECT 'Mercredi' 
    UNION SELECT 'Jeudi' UNION SELECT 'Vendredi' UNION SELECT 'Samedi' 
    UNION SELECT 'Dimanche'
) jours
WHERE NOT EXISTS (
    SELECT 1 FROM horaires_medecins h 
    WHERE h.medecin_id = m.id AND h.jour_semaine = jours.jour
);

-- Insertion des rendez-vous passés et futurs
INSERT INTO rendez_vous (utilisateur_id, medecin_id, date_rendez_vous, statut, motif) VALUES
(1, 1, DATE_SUB(NOW(), INTERVAL 2 DAY), 'termine', 'Douleurs thoraciques'),
(2, 2, DATE_SUB(NOW(), INTERVAL 5 DAY), 'termine', 'Éruption cutanée'),
(3, 3, DATE_ADD(NOW(), INTERVAL 1 DAY), 'confirme', 'Consultation pédiatrique'),
(4, 4, DATE_ADD(NOW(), INTERVAL 3 DAY), 'en_attente', 'Contrôle annuel'),
(5, 5, DATE_ADD(NOW(), INTERVAL 2 DAY), 'confirme', 'Problèmes d\'audition'),
(1, 6, DATE_ADD(NOW(), INTERVAL 4 DAY), 'en_attente', 'Grippe'),
(2, 7, DATE_SUB(NOW(), INTERVAL 1 DAY), 'termine', 'Palpitations'),
(3, 8, DATE_ADD(NOW(), INTERVAL 5 DAY), 'en_attente', 'Consultation ophtalmo'),
(4, 9, DATE_ADD(NOW(), INTERVAL 2 DAY), 'confirme', 'Douleurs au genou'),
(5, 10, DATE_SUB(NOW(), INTERVAL 3 DAY), 'annule', 'Maux de tête');

-- Insertion des notes médicales
INSERT INTO notes_medicales (rendez_vous_id, medecin_id, patient_id, notes, diagnostic, prescriptions, recommandations, tension_arterielle, poids, taille, temperature, frequence_cardiaque)
SELECT 
    r.id,
    r.medecin_id,
    r.utilisateur_id,
    'Patient examiné, aucun signe inquiétant',
    'Examen normal',
    'Repos recommandé',
    'Revoir dans 3 mois',
    '120/80',
    75.5,
    175,
    36.8,
    72
FROM rendez_vous r
WHERE r.statut = 'termine'
AND NOT EXISTS (SELECT 1 FROM notes_medicales n WHERE n.rendez_vous_id = r.id);

-- Insertion des ordonnances
INSERT INTO ordonnances (notes_medicales_id, medicament, dosage, duree, instructions)
SELECT 
    n.id,
    'Paracétamol',
    '500mg',
    '3 jours',
    'Prendre 2 comprimés par jour'
FROM notes_medicales n
WHERE NOT EXISTS (SELECT 1 FROM ordonnances o WHERE o.notes_medicales_id = n.id)
LIMIT 3;

-- Insertion des avis patients
INSERT INTO avis_patients (medecin_id, patient_id, rendez_vous_id, note, commentaire, est_approuve)
SELECT 
    r.medecin_id,
    r.utilisateur_id,
    r.id,
    FLOOR(4 + RAND() * 2),
    CASE 
        WHEN RAND() > 0.5 THEN 'Excellent médecin, très à l\'écoute'
        ELSE 'Très professionnel, je recommande'
    END,
    TRUE
FROM rendez_vous r
WHERE r.statut = 'termine'
AND NOT EXISTS (SELECT 1 FROM avis_patients a WHERE a.rendez_vous_id = r.id)
LIMIT 5;

-- Insertion des notifications
INSERT INTO notifications (utilisateur_id, titre, message, type, lien)
SELECT 
    u.id,
    'Bienvenue sur Sama Docteur',
    CONCAT('Bienvenue ', u.nom_complet, ', votre compte a été créé avec succès'),
    'info',
    '/dashboard.php'
FROM utilisateurs u
WHERE NOT EXISTS (SELECT 1 FROM notifications n WHERE n.utilisateur_id = u.id);

-- Insertion d'un utilisateur médecin
INSERT INTO utilisateurs (nom_complet, email, mot_de_passe, telephone, role, est_actif) 
SELECT 'Dr Jean Dupont', 'jean.dupont@samadocteur.sn', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '77 123 45 67', 'medecin', TRUE
WHERE NOT EXISTS (SELECT 1 FROM utilisateurs WHERE email = 'jean.dupont@samadocteur.sn');

-- Insertion d'un utilisateur admin
INSERT INTO utilisateurs (nom_complet, email, mot_de_passe, telephone, role, est_actif) 
SELECT 'Administrateur', 'admin@samadocteur.sn', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '77 000 00 00', 'admin', TRUE
WHERE NOT EXISTS (SELECT 1 FROM utilisateurs WHERE email = 'admin@samadocteur.sn');

-- ============================================
-- CRÉATION DES VUES
-- ============================================

-- Vue des rendez-vous du jour
CREATE OR REPLACE VIEW vue_rendez_vous_jour AS
SELECT 
    r.id,
    u.nom_complet as patient_nom,
    u.telephone as patient_telephone,
    m.nom_complet as medecin_nom,
    s.nom as specialite,
    r.date_rendez_vous,
    r.statut,
    r.motif
FROM rendez_vous r
JOIN utilisateurs u ON r.utilisateur_id = u.id
JOIN medecins m ON r.medecin_id = m.id
LEFT JOIN specialites s ON m.specialite_id = s.id
WHERE DATE(r.date_rendez_vous) = CURDATE();

-- Vue des statistiques par médecin
CREATE OR REPLACE VIEW vue_stats_medecin AS
SELECT 
    m.id as medecin_id,
    m.nom_complet,
    s.nom as specialite,
    COUNT(DISTINCT r.utilisateur_id) as total_patients,
    COUNT(r.id) as total_rendez_vous,
    SUM(CASE WHEN r.statut = 'termine' THEN 1 ELSE 0 END) as consultations_terminees,
    SUM(CASE WHEN r.statut = 'annule' THEN 1 ELSE 0 END) as consultations_annulees,
    AVG(a.note) as note_moyenne,
    COUNT(a.id) as total_avis
FROM medecins m
LEFT JOIN specialites s ON m.specialite_id = s.id
LEFT JOIN rendez_vous r ON m.id = r.medecin_id
LEFT JOIN avis_patients a ON m.id = a.medecin_id AND a.est_approuve = TRUE
GROUP BY m.id;

-- Vue des prochains rendez-vous patients
CREATE OR REPLACE VIEW vue_prochains_rendez_vous AS
SELECT 
    u.id as patient_id,
    u.nom_complet as patient_nom,
    m.nom_complet as medecin_nom,
    s.nom as specialite,
    r.date_rendez_vous,
    r.statut,
    DATEDIFF(r.date_rendez_vous, NOW()) as jours_restants
FROM rendez_vous r
JOIN utilisateurs u ON r.utilisateur_id = u.id
JOIN medecins m ON r.medecin_id = m.id
LEFT JOIN specialites s ON m.specialite_id = s.id
WHERE r.date_rendez_vous > NOW()
AND r.statut NOT IN ('annule', 'termine')
ORDER BY r.date_rendez_vous ASC;


-- Ajouter une table pour les logs si ce n'est pas déjà fait
CREATE TABLE IF NOT EXISTS logs_systeme (
    id INT AUTO_INCREMENT PRIMARY KEY,
    utilisateur_id INT NULL,
    action VARCHAR(100),
    details TEXT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_utilisateur (utilisateur_id),
    INDEX idx_action (action),
    INDEX idx_date (date_creation)
);
-- ============================================
-- FIN DU SCRIPT
-- ============================================