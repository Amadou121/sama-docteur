<?php
// Fichier: includes/fonctions.php

// Fonction pour obtenir les spécialités
function getSpecialites($pdo) {
    $stmt = $pdo->query("SELECT * FROM specialites ORDER BY nom");
    return $stmt->fetchAll();
}

// Fonction pour obtenir les médecins par spécialité
function getMedecinsBySpecialite($pdo, $specialite_id) {
    $stmt = $pdo->prepare("
        SELECT m.*, s.nom as specialite_nom 
        FROM medecins m
        JOIN specialites s ON m.specialite_id = s.id
        WHERE m.specialite_id = ? AND m.est_disponible = 1
        ORDER BY m.nom_complet
    ");
    $stmt->execute([$specialite_id]);
    return $stmt->fetchAll();
}

// Fonction pour obtenir les horaires d'un médecin
function getHorairesMedecin($pdo, $medecin_id) {
    $stmt = $pdo->prepare("
        SELECT * FROM horaires_medecins 
        WHERE medecin_id = ? AND est_disponible = 1
        ORDER BY FIELD(jour_semaine, 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi', 'Dimanche')
    ");
    $stmt->execute([$medecin_id]);
    return $stmt->fetchAll();
}

// Fonction pour créer un rendez-vous
function creerRendezVous($pdo, $utilisateur_id, $medecin_id, $date_rendez_vous, $motif) {
    try {
        // Vérifier si le créneau est disponible
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as count 
            FROM rendez_vous 
            WHERE medecin_id = ? AND date_rendez_vous = ? 
            AND statut NOT IN ('annule', 'termine')
        ");
        $stmt->execute([$medecin_id, $date_rendez_vous]);
        $result = $stmt->fetch();
        
        if ($result['count'] > 0) {
            return ['success' => false, 'message' => 'Ce créneau n\'est pas disponible'];
        }
        
        // Créer le rendez-vous
        $stmt = $pdo->prepare("
            INSERT INTO rendez_vous (utilisateur_id, medecin_id, date_rendez_vous, motif)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([$utilisateur_id, $medecin_id, $date_rendez_vous, $motif]);
        
        // Créer une notification
        $stmt = $pdo->prepare("
            INSERT INTO notifications (utilisateur_id, titre, message, type)
            VALUES (?, 'Rendez-vous créé', 'Votre rendez-vous a été enregistré avec succès', 'confirmation')
        ");
        $stmt->execute([$utilisateur_id]);
        
        return ['success' => true, 'message' => 'Rendez-vous créé avec succès'];
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Erreur lors de la création du rendez-vous'];
    }
}

// Fonction pour obtenir les rendez-vous d'un patient
function getRendezVousPatient($pdo, $utilisateur_id) {
    $stmt = $pdo->prepare("
        SELECT r.*, m.nom_complet as medecin_nom, s.nom as specialite_nom
        FROM rendez_vous r
        JOIN medecins m ON r.medecin_id = m.id
        LEFT JOIN specialites s ON m.specialite_id = s.id
        WHERE r.utilisateur_id = ?
        ORDER BY r.date_rendez_vous DESC
    ");
    $stmt->execute([$utilisateur_id]);
    return $stmt->fetchAll();
}

// Fonction pour obtenir les statistiques du médecin
function getStatsMedecin($pdo, $medecin_id) {
    $stmt = $pdo->prepare("
        SELECT 
            COUNT(*) as total_rdv,
            SUM(CASE WHEN statut = 'confirme' THEN 1 ELSE 0 END) as confirme,
            SUM(CASE WHEN statut = 'termine' THEN 1 ELSE 0 END) as termine,
            SUM(CASE WHEN statut = 'annule' THEN 1 ELSE 0 END) as annule
        FROM rendez_vous
        WHERE medecin_id = ?
    ");
    $stmt->execute([$medecin_id]);
    return $stmt->fetch();
}

// Fonction pour ajouter un avis
function ajouterAvis($pdo, $medecin_id, $patient_id, $rendez_vous_id, $note, $commentaire) {
    $stmt = $pdo->prepare("
        INSERT INTO avis_patients (medecin_id, patient_id, rendez_vous_id, note, commentaire, est_approuve)
        VALUES (?, ?, ?, ?, ?, FALSE)
    ");
    return $stmt->execute([$medecin_id, $patient_id, $rendez_vous_id, $note, $commentaire]);
}

// Fonction pour obtenir les médecins populaires
function getMedecinsPopulaires($pdo, $limit = 6) {
    $stmt = $pdo->prepare("
        SELECT m.*, s.nom as specialite_nom, 
               AVG(a.note) as note_moyenne,
               COUNT(DISTINCT a.id) as nombre_avis
        FROM medecins m
        JOIN specialites s ON m.specialite_id = s.id
        LEFT JOIN avis_patients a ON m.id = a.medecin_id AND a.est_approuve = TRUE
        WHERE m.est_disponible = 1
        GROUP BY m.id
        ORDER BY note_moyenne DESC, nombre_avis DESC
        LIMIT ?
    ");
    $stmt->execute([$limit]);
    return $stmt->fetchAll();
}

// Fonction pour envoyer un message de contact
function envoyerMessageContact($nom, $email, $message) {
    $to = ADMIN_EMAIL;
    $sujet = "Message de contact - Sama Docteur";
    $corps = "Nom: $nom\nEmail: $email\n\nMessage:\n$message";
    $headers = "From: $email\r\nReply-To: $email";
    
    return mail($to, $sujet, $corps, $headers);
}
?>