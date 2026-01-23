// ============================================
// PROGRESSIVE FORM LOGIC
// ============================================

document.addEventListener('DOMContentLoaded', function() {
    // Variables globales
    let currentStep = 1;
    const totalSteps = 5;
    
    // Éléments DOM
    const progressBar = document.getElementById('progressBar');
    const progressText = document.getElementById('progressText');
    const btnPrev = document.getElementById('btnPrev');
    const btnNext = document.getElementById('btnNext');
    const btnSubmit = document.getElementById('btnSubmit');
    const stepSections = document.querySelectorAll('.step-section');
    
    // Toggle Protocole
    const protocoleToggle = document.getElementById('protocoleToggle');
    const protocoleContent = document.getElementById('protocoleContent');
    
    protocoleToggle.addEventListener('click', function() {
        if (protocoleContent.style.display === 'none') {
            protocoleContent.style.display = 'block';
            protocoleToggle.classList.add('active');
        } else {
            protocoleContent.style.display = 'none';
            protocoleToggle.classList.remove('active');
        }
    });
    
    // ============================================
    // GESTION DES ÉTAPES
    // ============================================
    
    function updateProgress() {
        const percentage = (currentStep / totalSteps) * 100;
        progressBar.style.width = percentage + '%';
        progressText.textContent = `Étape ${currentStep}/${totalSteps}`;
    }
    
    function showStep(step) {
        stepSections.forEach((section, index) => {
            section.classList.remove('active');
            if (index + 1 === step) {
                section.classList.add('active');
            }
        });
        
        // Gérer l'affichage des boutons
        btnPrev.style.display = step > 1 ? 'block' : 'none';
        
        if (step < totalSteps) {
            btnNext.style.display = 'block';
            btnSubmit.style.display = 'none';
        } else {
            btnNext.style.display = 'none';
            btnSubmit.style.display = 'block';
        }
        
        updateProgress();
        
        // Scroll vers le haut
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }
    
    // Validation des étapes
    function validateStep(step) {
        const currentSection = document.querySelector(`.step-section[data-step="${step}"]`);
        const requiredFields = currentSection.querySelectorAll('[required]');
        
        for (let field of requiredFields) {
            if (!field.value) {
                field.focus();
                alert('Veuillez remplir tous les champs obligatoires.');
                return false;
            }
        }
        
        // Validation spécifique pour l'étape 3 (poids)
        if (step === 3) {
            const allWeightInputs = document.querySelectorAll('input[type="number"][name*="_"]');
            let hasWeight = false;
            
            allWeightInputs.forEach(input => {
                if (parseFloat(input.value) > 0) {
                    hasWeight = true;
                }
            });
            
            if (!hasWeight) {
                alert('Veuillez renseigner au moins un poids de linge.');
                return false;
            }
        }
        
        return true;
    }
    
    // Navigation
    btnNext.addEventListener('click', function() {
        if (validateStep(currentStep)) {
            currentStep++;
            showStep(currentStep);
        }
    });
    
    btnPrev.addEventListener('click', function() {
        currentStep--;
        showStep(currentStep);
    });
    
    // Initialiser
    showStep(currentStep);
    
    // ============================================
    // GESTION TYPE DE LINGE (Volumineux/Ordinaire)
    // ============================================
    
    const btnVolumineux = document.getElementById('btnVolumineux');
    const btnOrdinaire = document.getElementById('btnOrdinaire');
    const volumineuxSection = document.getElementById('volumineuxSection');
    const ordinaireSection = document.getElementById('ordinaireSection');
    
    btnVolumineux.addEventListener('click', function() {
        this.classList.toggle('active');
        
        if (this.classList.contains('active')) {
            volumineuxSection.style.display = 'block';
        } else {
            volumineuxSection.style.display = 'none';
            // Réinitialiser les sélections de couleurs
            volumineuxSection.querySelectorAll('.color-card').forEach(card => {
                card.classList.remove('active');
            });
            volumineuxSection.querySelectorAll('.poids-group').forEach(group => {
                group.style.display = 'none';
            });
        }
    });
    
    btnOrdinaire.addEventListener('click', function() {
        this.classList.toggle('active');
        
        if (this.classList.contains('active')) {
            ordinaireSection.style.display = 'block';
        } else {
            ordinaireSection.style.display = 'none';
            // Réinitialiser les sélections de couleurs
            ordinaireSection.querySelectorAll('.color-card').forEach(card => {
                card.classList.remove('active');
            });
            ordinaireSection.querySelectorAll('.poids-group').forEach(group => {
                group.style.display = 'none';
            });
        }
    });
    
    // ============================================
    // GESTION DES COULEURS
    // ============================================
    
    const colorCards = document.querySelectorAll('.color-card');
    
    colorCards.forEach(card => {
        card.addEventListener('click', function() {
            const color = this.getAttribute('data-color');
            const volume = this.getAttribute('data-volume');
            const groupId = color + volume.charAt(0).toUpperCase() + volume.slice(1);
            const poidsGroup = document.getElementById(groupId);
            
            this.classList.toggle('active');
            
            if (this.classList.contains('active')) {
                poidsGroup.style.display = 'block';
            } else {
                poidsGroup.style.display = 'none';
                // Réinitialiser les champs de poids
                poidsGroup.querySelectorAll('input').forEach(input => {
                    input.value = '';
                });
            }
        });
    });
    
    // ============================================
    // GESTION DATES (code existant préservé)
    // ============================================
    
    function getDateAujourdhui() {
        const aujourd = new Date();
        const annee = aujourd.getFullYear();
        const mois = String(aujourd.getMonth() + 1).padStart(2, '0');
        const jour = String(aujourd.getDate()).padStart(2, '0');
        return `${annee}-${mois}-${jour}`;
    }
    
    const dateCollecte = document.getElementById('dateCollecte');
    const dateLivraison = document.getElementById('dateLivraison');
    const dateMin = getDateAujourdhui();
    
    if (dateCollecte && dateLivraison) {
        dateCollecte.setAttribute('min', dateMin);
        dateLivraison.setAttribute('min', dateMin);
        
        dateCollecte.addEventListener('input', function() {
            const dateSelectionnee = this.value;
            
            if (dateSelectionnee && dateSelectionnee < dateMin) {
                this.value = '';
                this.setCustomValidity('Vous ne pouvez pas sélectionner une date passée.');
                alert('❌ Date de collecte invalide : vous ne pouvez pas choisir une date passée.');
            } else {
                this.setCustomValidity('');
                
                if (dateSelectionnee) {
                    dateLivraison.setAttribute('min', dateSelectionnee);
                    
                    if (dateLivraison.value && dateLivraison.value < dateSelectionnee) {
                        dateLivraison.value = '';
                        alert('⚠️ La date de livraison a été réinitialisée car elle était antérieure à la date de collecte.');
                    }
                }
            }
        });
        
        dateLivraison.addEventListener('input', function() {
            const dateSelectionnee = this.value;
            const dateCollecteVal = dateCollecte.value;
            
            if (dateSelectionnee && dateSelectionnee < dateMin) {
                this.value = '';
                this.setCustomValidity('Vous ne pouvez pas sélectionner une date passée.');
                alert('❌ Date de livraison invalide : vous ne pouvez pas choisir une date passée.');
                return;
            }
            
            if (dateCollecteVal && dateSelectionnee && dateSelectionnee < dateCollecteVal) {
                this.value = '';
                this.setCustomValidity('La date de livraison doit être égale ou postérieure à la date de collecte.');
                alert('❌ La date de livraison ne peut pas être antérieure à la date de collecte.');
            } else {
                this.setCustomValidity('');
            }
        });
        
        dateCollecte.addEventListener('change', function() {
            this.dispatchEvent(new Event('input'));
        });
        
        dateLivraison.addEventListener('change', function() {
            this.dispatchEvent(new Event('input'));
        });
    }
});