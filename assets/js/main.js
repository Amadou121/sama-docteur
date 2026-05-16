// Fichier: assets/js/main.js

// Attendre que le DOM soit chargé
document.addEventListener('DOMContentLoaded', function() {
    console.log('Sama Docteur - Application chargée');
    
    // Initialiser AOS (animations)
    if (typeof AOS !== 'undefined') {
        AOS.init({
            duration: 1000,
            once: true,
            offset: 100
        });
    }
    
    // Initialiser les tooltips Bootstrap
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // Initialiser les animations
    initAnimations();
    
    // Initialiser les formulaires
    initForms();
    
    // Initialiser les boutons de rendez-vous
    initAppointmentButtons();
    
    // Masquer le loader
    const loader = document.getElementById('loader');
    if (loader) {
        setTimeout(function() {
            loader.classList.add('hide');
        }, 500);
    }
});

// Initialiser les animations
function initAnimations() {
    // Animation des cartes au survol
    const cards = document.querySelectorAll('.card, .card-specialite, .card-medecin');
    cards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transition = 'all 0.3s ease';
        });
    });
}

// Initialiser les formulaires
function initForms() {
    // Ajouter la validation en temps réel
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        const inputs = form.querySelectorAll('input, select, textarea');
        inputs.forEach(input => {
            input.addEventListener('blur', function() {
                validateField(this);
            });
        });
    });
}

// Valider un champ individuel
function validateField(field) {
    if (field.hasAttribute('required') && !field.value.trim()) {
        field.classList.add('is-invalid');
        return false;
    }
    
    if (field.type === 'email' && field.value) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(field.value)) {
            field.classList.add('is-invalid');
            return false;
        }
    }
    
    if (field.type === 'tel' && field.value) {
        const phoneRegex = /^[0-9]{2}[0-9]{3}[0-9]{3}$/;
        const phoneClean = field.value.replace(/\s/g, '');
        if (!phoneRegex.test(phoneClean)) {
            field.classList.add('is-invalid');
            return false;
        }
    }
    
    field.classList.remove('is-invalid');
    field.classList.add('is-valid');
    return true;
}

// Initialiser les boutons de rendez-vous
function initAppointmentButtons() {
    const rdvButtons = document.querySelectorAll('.btn-appointment, .btn-rdv, .btn-primary[onclick*="prendreRendezVous"]');
    rdvButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            if (!this.hasAttribute('onclick')) {
                e.preventDefault();
                const doctorId = this.dataset.doctorId;
                if (doctorId) {
                    prendreRendezVous(doctorId);
                } else {
                    showNotification('Veuillez vous connecter pour prendre un rendez-vous', 'warning');
                }
            }
        });
    });
}

// Prendre un rendez-vous
function prendreRendezVous(doctorId) {
    Swal.fire({
        title: 'Prendre rendez-vous',
        html: `
            <form id="appointmentForm">
                <div class="mb-3">
                    <label class="form-label">Date du rendez-vous</label>
                    <input type="date" id="appointmentDate" class="form-control" required min="${new Date().toISOString().split('T')[0]}">
                </div>
                <div class="mb-3">
                    <label class="form-label">Heure</label>
                    <select id="appointmentTime" class="form-control" required>
                        <option value="">Sélectionnez une heure</option>
                        <option value="09:00">09:00</option>
                        <option value="09:30">09:30</option>
                        <option value="10:00">10:00</option>
                        <option value="10:30">10:30</option>
                        <option value="11:00">11:00</option>
                        <option value="11:30">11:30</option>
                        <option value="14:00">14:00</option>
                        <option value="14:30">14:30</option>
                        <option value="15:00">15:00</option>
                        <option value="15:30">15:30</option>
                        <option value="16:00">16:00</option>
                        <option value="16:30">16:30</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Motif de la consultation</label>
                    <textarea id="appointmentReason" class="form-control" rows="3" placeholder="Décrivez brièvement le motif de votre consultation"></textarea>
                </div>
            </form>
        `,
        showCancelButton: true,
        confirmButtonColor: '#2563EB',
        cancelButtonColor: '#64748B',
        confirmButtonText: 'Confirmer le rendez-vous',
        cancelButtonText: 'Annuler',
        preConfirm: () => {
            const date = document.getElementById('appointmentDate').value;
            const time = document.getElementById('appointmentTime').value;
            const reason = document.getElementById('appointmentReason').value;
            
            if (!date || !time) {
                Swal.showValidationMessage('Veuillez sélectionner une date et une heure');
                return false;
            }
            
            return { date, time, reason };
        }
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Demande envoyée !',
                text: 'Votre demande de rendez-vous a été envoyée. Vous recevrez une confirmation sous 24h.',
                icon: 'success',
                confirmButtonColor: '#2563EB'
            });
        }
    });
}

// Filtrer les médecins par spécialité
function filterDoctors(specialtyId) {
    const doctorCards = document.querySelectorAll('.doctor-card, .card-medecin');
    
    if (specialtyId === 'all') {
        doctorCards.forEach(card => {
            card.style.display = 'block';
        });
    } else {
        doctorCards.forEach(card => {
            const cardSpecialty = card.dataset.specialty;
            if (cardSpecialty === specialtyId) {
                card.style.display = 'block';
            } else {
                card.style.display = 'none';
            }
        });
    }
}

// Annuler un rendez-vous
function annulerRendezVous(id) {
    Swal.fire({
        title: 'Annuler le rendez-vous',
        text: 'Êtes-vous sûr de vouloir annuler ce rendez-vous ?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#EF4444',
        cancelButtonColor: '#64748B',
        confirmButtonText: 'Oui, annuler',
        cancelButtonText: 'Non, garder'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Annulé !',
                text: 'Votre rendez-vous a été annulé.',
                icon: 'success',
                confirmButtonColor: '#2563EB'
            });
        }
    });
}

// Voir les détails d'un rendez-vous
function voirDetails(id) {
    Swal.fire({
        title: 'Détails du rendez-vous',
        html: `
            <div class="text-start">
                <p><strong>Médecin:</strong> Dr Jean Dupuis</p>
                <p><strong>Spécialité:</strong> Cardiologie</p>
                <p><strong>Date:</strong> 15/12/2024</p>
                <p><strong>Heure:</strong> 10:30</p>
                <p><strong>Motif:</strong> Consultation régulière</p>
                <p><strong>Statut:</strong> <span class="status status-confirme">Confirmé</span></p>
            </div>
        `,
        confirmButtonColor: '#2563EB',
        confirmButtonText: 'Fermer'
    });
}

// Démarrer une consultation (médecin)
function demarrerConsultation(id) {
    Swal.fire({
        title: 'Démarrer la consultation',
        text: 'La consultation a été démarrée.',
        icon: 'success',
        confirmButtonColor: '#2563EB'
    });
}

// Voir les détails d'un patient (médecin)
function voirPatient(id) {
    Swal.fire({
        title: 'Informations patient',
        html: `
            <div class="text-start">
                <p><strong>Nom:</strong> Marie Dupont</p>
                <p><strong>Email:</strong> marie@test.com</p>
                <p><strong>Téléphone:</strong> 77 111 22 33</p>
                <p><strong>Dernière visite:</strong> 01/12/2024</p>
            </div>
        `,
        confirmButtonColor: '#2563EB',
        confirmButtonText: 'Fermer'
    });
}

// Navigation dans le dashboard
function initDashboardNavigation() {
    const menuLinks = document.querySelectorAll('.dashboard-menu a');
    if (!menuLinks.length) return;
    
    menuLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const page = this.dataset.page;
            
            // Cacher toutes les sections
            document.querySelectorAll('#dashboardSection, #historySection, #profileSection, #scheduleSection, #appointmentsSection, #patientsSection, #statsSection').forEach(section => {
                if (section) section.style.display = 'none';
            });
            
            // Afficher la section correspondante
            if (page === 'dashboard' && document.getElementById('dashboardSection')) {
                document.getElementById('dashboardSection').style.display = 'block';
            } else if (page === 'history' && document.getElementById('historySection')) {
                document.getElementById('historySection').style.display = 'block';
            } else if (page === 'profile' && document.getElementById('profileSection')) {
                document.getElementById('profileSection').style.display = 'block';
            } else if (page === 'schedule' && document.getElementById('scheduleSection')) {
                document.getElementById('scheduleSection').style.display = 'block';
            } else if (page === 'appointments' && document.getElementById('appointmentsSection')) {
                document.getElementById('appointmentsSection').style.display = 'block';
            } else if (page === 'patients' && document.getElementById('patientsSection')) {
                document.getElementById('patientsSection').style.display = 'block';
            } else if (page === 'stats' && document.getElementById('statsSection')) {
                document.getElementById('statsSection').style.display = 'block';
            }
            
            // Mettre à jour la classe active
            menuLinks.forEach(a => a.classList.remove('active'));
            this.classList.add('active');
        });
    });
}

// Exporter les fonctions pour une utilisation globale
window.prendreRendezVous = prendreRendezVous;
window.filterDoctors = filterDoctors;
window.annulerRendezVous = annulerRendezVous;
window.voirDetails = voirDetails;
window.demarrerConsultation = demarrerConsultation;
window.voirPatient = voirPatient;
window.validerFormulaire = validerFormulaire;
window.initDashboardNavigation = initDashboardNavigation;

// Fonction globale showNotification
window.showNotification = function(message, type = 'success') {
    Swal.fire({
        title: type === 'success' ? 'Succès !' : 'Information',
        text: message,
        icon: type,
        confirmButtonColor: '#2563EB',
        timer: 3000,
        timerProgressBar: true,
        showConfirmButton: false
    });
};

// Fonction globale confirmSuppression
window.confirmSuppression = function(message = 'Êtes-vous sûr de vouloir effectuer cette action ?') {
    return Swal.fire({
        title: 'Confirmation',
        text: message,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#EF4444',
        cancelButtonColor: '#64748B',
        confirmButtonText: 'Oui, confirmer',
        cancelButtonText: 'Annuler'
    }).then((result) => {
        return result.isConfirmed;
    });
};

// Fonction globale validerFormulaire
window.validerFormulaire = function(formId) {
    const form = document.getElementById(formId);
    if (!form) return true;
    
    let isValid = true;
    const inputs = form.querySelectorAll('input[required], select[required], textarea[required]');
    
    inputs.forEach(input => {
        if (!input.value.trim()) {
            input.classList.add('is-invalid');
            isValid = false;
        } else {
            input.classList.remove('is-invalid');
        }
        
        if (input.type === 'email' && input.value) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(input.value)) {
                input.classList.add('is-invalid');
                isValid = false;
            }
        }
    });
    
    if (!isValid) {
        window.showNotification('Veuillez remplir tous les champs correctement', 'warning');
    }
    
    return isValid;
};