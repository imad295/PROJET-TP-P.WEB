// ========== FERMETURE AUTOMATIQUE DES ALERTES ==========
document.addEventListener('DOMContentLoaded', function() {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.opacity = '0';
            setTimeout(() => {
                if (alert.parentNode) alert.remove();
            }, 300);
        }, 5000);

        const closeBtn = alert.querySelector('.close-btn');
        if (closeBtn) {
            closeBtn.addEventListener('click', () => alert.remove());
        }
    });
});

// ========== FONCTIONS POUR LES MODALS ==========
function openModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = 'block';
        document.body.style.overflow = 'hidden';
    }
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = 'auto';
    }
}

// Fermer les modals en cliquant en dehors
window.onclick = function(event) {
    if (event.target.classList.contains('modal')) {
        event.target.style.display = 'none';
        document.body.style.overflow = 'auto';
    }
}

// ========== VALIDATION DES FORMULAIRES ==========
function validateForm(formId) {
    const form = document.getElementById(formId);
    if (!form) return true;

    const inputs = form.querySelectorAll('input[required], select[required]');
    let isValid = true;

    inputs.forEach(input => {
        if (!input.value.trim()) {
            input.style.borderColor = '#f44336';
            isValid = false;
        } else {
            input.style.borderColor = '#ddd';
        }

        // Validation email
        if (input.type === 'email' && input.value) {
            const emailRegex = /^[^\s@]+@([^\s@]+\.)+[^\s@]+$/;
            if (!emailRegex.test(input.value)) {
                input.style.borderColor = '#f44336';
                isValid = false;
            }
        }

        // Validation note (0-20)
        if (input.name && (input.name.includes('note') || input.name === 'note_cc' || input.name === 'note_examen')) {
            const note = parseFloat(input.value);
            if (!isNaN(note) && (note < 0 || note > 20)) {
                input.style.borderColor = '#f44336';
                isValid = false;
            }
        }
    });

    if (!isValid) {
        alert('Veuillez remplir correctement tous les champs');
    }

    return isValid;
}

// ========== CONFIRMATION AVANT SUPPRESSION ==========
function confirmDelete(message) {
    return confirm(message || 'Êtes-vous sûr de vouloir supprimer cet élément ?');
}

// ========== TOGGLE DATE DE NAISSANCE (REGISTER) ==========
function toggleDateNaissance() {
    const roleSelect = document.getElementById('role');
    const dateGroup = document.getElementById('date_naissance_group');

    if (roleSelect && dateGroup) {
        const dateInput = dateGroup.querySelector('input');
        if (roleSelect.value == 'etudiant') {
            dateGroup.style.display = 'block';
            if (dateInput) dateInput.required = true;
        } else {
            dateGroup.style.display = 'none';
            if (dateInput) dateInput.required = false;
        }
    }
}

// ========== VALIDATION FORMULAIRE D'INSCRIPTION ==========
function validateRegisterForm() {
    const password = document.getElementById('password');
    const confirm = document.getElementById('confirm_password');

    if (password && confirm) {
        if (password.value != confirm.value) {
            alert('Les mots de passe ne correspondent pas');
            return false;
        }

        if (password.value.length < 6) {
            alert('Le mot de passe doit contenir au moins 6 caractères');
            return false;
        }
    }

    return true;
}

// ========== RECHERCHE EN TEMPS RÉEL ==========
function searchTable(inputId, tableId) {
    const input = document.getElementById(inputId);
    const table = document.getElementById(tableId);

    if (input && table) {
        input.addEventListener('keyup', function() {
            const filter = this.value.toLowerCase();
            const rows = table.getElementsByTagName('tr');

            for (let i = 1; i < rows.length; i++) {
                const cells = rows[i].getElementsByTagName('td');
                let found = false;

                for (let j = 0; j < cells.length; j++) {
                    if (cells[j]) {
                        const text = cells[j].textContent || cells[j].innerText;
                        if (text.toLowerCase().indexOf(filter) > -1) {
                            found = true;
                            break;
                        }
                    }
                }

                rows[i].style.display = found ? '' : 'none';
            }
        });
    }
}

// ========== INITIALISATION ==========
document.addEventListener('DOMContentLoaded', function() {
    // Initialiser toggle date naissance si présent
    if (document.getElementById('role')) {
        toggleDateNaissance();
    }

    // Ajouter écouteur pour formulaire d'inscription
    const registerForm = document.getElementById('registerForm');
    if (registerForm) {
        registerForm.addEventListener('submit', function(e) {
            if (!validateRegisterForm()) {
                e.preventDefault();
            }
        });
    }
});