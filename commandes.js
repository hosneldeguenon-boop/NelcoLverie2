// ============================================
// SYSTÈME DE COMMANDE PROGRESSIVE - VERSION COMPLÈTE
// ============================================

// ============================================
// GRILLE TARIFAIRE COMPLÈTE (du fichier fourni)
// ============================================
const tarifs = {
    froid: [
        { min: 0, max: 6, prix: 2500 },
        { min: 6, max: 8, prix: 3000 },
        { min: 8, max: 10, prix: 5000 }
    ],
    tiede: [
        { min: 0, max: 6, prix: 3000 },
        { min: 6, max: 8, prix: 3500 },
        { min: 8, max: 10, prix: 6000 }
    ],
    chaud: [
        { min: 0, max: 6, prix: 3500 },
        { min: 6, max: 8, prix: 4000 },
        { min: 8, max: 10, prix: 7000 }
    ]
};

const tarifsCommunePrix = {
    godomey: 500,
    cotonou: 1000,
    calavi: 800,
    autres: 1500
};

// ============================================
// ✅ SYSTÈME DE FIDÉLITÉ - CYCLE DE 11 LAVAGES
// ============================================
let userNombreLavage = 0;

function getNombreLavage() {
    return userNombreLavage;
}

function loadUserPoints() {
    fetch('get_user_points.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                userNombreLavage = parseInt(data.nombre_lavage) || 0;
            } else {
                userNombreLavage = 0;
            }
        })
        .catch(() => {
            userNombreLavage = 0;
        });
}

// ============================================
// CALCUL DU PRIX DE SÉCHAGE
// ============================================
function calculerPrixSechage(poids) {
    if (poids <= 0) return 0;
    if (poids <= 2) return 1000;
    if (poids <= 3) return 1500;
    if (poids <= 4) return 2000;
    if (poids <= 6) return 2500;
    if (poids <= 8) return 3000;
    return 3000 + calculerPrixSechage(poids - 8);
}

// ============================================
// CALCUL DU PRIX DE PLIAGE
// ============================================
function calculerPrixPliage(poidsTotal) {
    if (poidsTotal < 4) return 0;

    const quotient = Math.floor(poidsTotal / 8);
    const reste = poidsTotal % 8;

    let prix = quotient * 500;
    if (reste >= 4) prix += 500;

    return prix;
}

// ============================================
// CALCUL DU PRIX DE REPASSAGE
// ============================================
function calculerPrixRepassage(poidsVolumineux, poidsOrdinaire) {
    let prixTotal = 0;

    if (poidsVolumineux >= 4) {
        prixTotal += Math.floor(poidsVolumineux / 4) * 200;
    }

    if (poidsOrdinaire >= 4) {
        prixTotal += Math.floor(poidsOrdinaire / 4) * 150;
    }

    return prixTotal;
}

// ============================================
// CALCUL DU PRIX DE LAVAGE - LINGE VOLUMINEUX
// ============================================
function calculerPrixLavageVolumineux(poids, temperature) {
    if (poids <= 0) return { prix: 0, lav: 0 };

    const grille = tarifs[temperature];
    let prix10kg = 0;

    for (let tranche of grille) {
        if (10 > tranche.min && 10 <= tranche.max) {
            prix10kg = tranche.prix;
            break;
        }
    }

    let prixTotal = 0;
    let poidsRestant = poids;
    let lav = 0;

    while (poidsRestant >= 10) {
        prixTotal += prix10kg + Math.ceil(prix10kg * 0.55);
        lav += 2;
        poidsRestant -= 10;
    }

    if (poidsRestant > 0) {
        if (poidsRestant >= 9) {
            prixTotal += prix10kg + Math.ceil(prix10kg * 0.55);
            lav += 2;
        } else {
            prixTotal += prix10kg;
            lav += 1;
        }
    }

    return { prix: prixTotal, lav };
}

// ============================================
// CALCUL DU PRIX DE LAVAGE - LINGE ORDINAIRE
// ============================================
function calculerPrixLavageOrdinaire(poids, temperature) {
    if (poids <= 0) return { prix: 0, lav: 0 };

    const grille = tarifs[temperature];
    let prixTotal = 0;
    let lav = 0;
    let poidsRestant = poids;

    while (poidsRestant > 0) {
        const poidsTraite = Math.min(poidsRestant, 10);

        for (let tranche of grille) {
            if (poidsTraite > tranche.min && poidsTraite <= tranche.max) {
                prixTotal += tranche.prix;
                lav++;
                break;
            }
        }

        poidsRestant -= 10;
    }

    return { prix: prixTotal, lav };
}

document.addEventListener('DOMContentLoaded', function() {
    // ============================================
    // VARIABLES GLOBALES
    // ============================================
    let currentStep = 1;
    const totalSteps = 9;
    let skipProtocol = false;
    
    // Éléments DOM
    const progressBar = document.getElementById('progressBar');
    const progressText = document.getElementById('progressText');
    const btnPrev = document.getElementById('btnPrev');
    const btnNext = document.getElementById('btnNext');
    const btnSubmit = document.getElementById('btnSubmit');
    const stepSections = document.querySelectorAll('.step-section');
    const commandeForm = document.getElementById('commandeForm');
    
    // Charger les points de fidélité
    loadUserPoints();
    
    // ============================================
    // GESTION LOCALSTORAGE POUR LES POIDS/TEMPÉRATURES
    // ============================================
    
    function saveFormData() {
        const formData = {
            poids: {},
            temperatures: {}
        };
        
        // Sauvegarder tous les poids (incluant c1 et c2)
        const categories = ['a1', 'b1', 'c1', 'a2', 'b2', 'c2'];
        categories.forEach(cat => {
            const poidsInput = document.querySelector(`input[name="${cat}_poids"]`);
            const tempInput = document.getElementById(`${cat}_temperature`);
            
            if (poidsInput) {
                formData.poids[cat] = poidsInput.value;
            }
            
            if (tempInput) {
                formData.temperatures[cat] = tempInput.value;
            }
        });
        
        localStorage.setItem('commandeFormData', JSON.stringify(formData));
    }
    
    function loadFormData() {
        const savedData = localStorage.getItem('commandeFormData');
        if (savedData) {
            try {
                const formData = JSON.parse(savedData);
                
                // Restaurer les poids
                Object.keys(formData.poids).forEach(cat => {
                    const poidsInput = document.querySelector(`input[name="${cat}_poids"]`);
                    if (poidsInput && formData.poids[cat]) {
                        poidsInput.value = formData.poids[cat];
                    }
                });
                
                // Restaurer les températures
                Object.keys(formData.temperatures).forEach(cat => {
                    const tempInput = document.getElementById(`${cat}_temperature`);
                    const tempValue = formData.temperatures[cat];
                    
                    if (tempInput && tempValue) {
                        tempInput.value = tempValue;
                        
                        // Réactiver le bouton correspondant
                        const activeButton = document.querySelector(`.temp-btn[data-category="${cat}"][data-temp="${tempValue}"]`);
                        if (activeButton) {
                            activeButton.classList.add('active');
                        }
                    }
                });
            } catch (e) {
                console.error('Erreur lors du chargement des données:', e);
            }
        }
    }
    
    // Charger les données au démarrage
    loadFormData();
    
    // Sauvegarder à chaque changement
    commandeForm.addEventListener('input', saveFormData);
    
    // ============================================
    // GESTION DE LA BARRE DE PROGRESSION
    // ============================================
    function updateProgress() {
        const percentage = (currentStep / totalSteps) * 100;
        progressBar.style.width = percentage + '%';
        progressText.textContent = `Étape ${currentStep}/${totalSteps}`;
    }
    
    // ============================================
    // AFFICHAGE DES ÉTAPES
    // ============================================
    function showStep(step) {
        stepSections.forEach((section, index) => {
            section.classList.remove('active');
            if (index + 1 === step) {
                section.classList.add('active');
            }
        });
        
        // Gestion des boutons de navigation
        btnPrev.style.display = step > 1 ? 'block' : 'none';
        
        if (step < totalSteps) {
            btnNext.style.display = 'block';
            btnSubmit.style.display = 'none';
        } else {
            btnNext.style.display = 'none';
            btnSubmit.style.display = 'block';
        }
        
        updateProgress();
        
        // ✨ Calcul automatique du récapitulatif à l'étape 8
        if (step === 8) {
            calculerRecapitulatif();
        }
        
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }
    
    // ============================================
    // SYNCHRONISATION AUTOMATIQUE DES ADRESSES
    // ============================================
    const adresseCollecte = document.getElementById('adresseCollecte');
    const adresseLivraison = document.getElementById('adresseLivraison');
    
    if (adresseCollecte && adresseLivraison) {
        adresseCollecte.addEventListener('input', function() {
            if (!adresseLivraison.value.trim()) {
                adresseLivraison.value = this.value;
            }
        });
        
        adresseLivraison.addEventListener('input', function() {
            if (!adresseCollecte.value.trim()) {
                adresseCollecte.value = this.value;
            }
        });
    }
    
    // ============================================
    // GESTION DES DATES ET HEURES
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
    const heureCollecte = document.getElementById('heureCollecte');
    const heureLivraison = document.getElementById('heureLivraison');
    const heureWarning = document.getElementById('heureWarning');
    
    const dateMin = getDateAujourdhui();
    
    if (dateCollecte) dateCollecte.setAttribute('min', dateMin);
    if (dateLivraison) dateLivraison.setAttribute('min', dateMin);
    
    // Synchronisation des dates
    if (dateCollecte && dateLivraison) {
        dateCollecte.addEventListener('input', function() {
            if (!dateLivraison.value) {
                dateLivraison.value = this.value;
            }
            if (this.value) {
                dateLivraison.setAttribute('min', this.value);
            }
            validateHeureEcart();
        });
        
        dateLivraison.addEventListener('input', function() {
            if (!dateCollecte.value) {
                dateCollecte.value = this.value;
            }
            validateHeureEcart();
        });
    }
    
    // Synchronisation des heures avec écart de 12h
    function addHours(time, hours) {
        if (!time) return '';
        const [h, m] = time.split(':').map(Number);
        let newHour = h + hours;
        if (newHour >= 24) newHour -= 24;
        if (newHour < 0) newHour += 24;
        return `${String(newHour).padStart(2, '0')}:${String(m).padStart(2, '0')}`;
    }
    
    if (heureCollecte && heureLivraison) {
        heureCollecte.addEventListener('input', function() {
            if (!heureLivraison.value && this.value) {
                heureLivraison.value = addHours(this.value, 12);
            }
            validateHeureEcart();
        });
        
        heureLivraison.addEventListener('input', function() {
            if (!heureCollecte.value && this.value) {
                heureCollecte.value = addHours(this.value, -12);
            }
            validateHeureEcart();
        });
    }
    
    // ✨ Validation conditionnelle de l'écart de 12h
    function validateHeureEcart() {
        const dateC = dateCollecte.value;
        const dateL = dateLivraison.value;
        const heureC = heureCollecte.value;
        const heureL = heureLivraison.value;
        
        if (!dateC || !dateL || !heureC || !heureL) {
            if (heureWarning) heureWarning.style.display = 'none';
            return true;
        }
        
        // La contrainte de 12h ne s'applique que si les dates sont identiques
        if (dateC !== dateL) {
            if (heureWarning) heureWarning.style.display = 'none';
            return true;
        }
        
        // Dates identiques : vérifier l'écart de 12h
        const dtCollecte = new Date(dateC + 'T' + heureC);
        const dtLivraison = new Date(dateL + 'T' + heureL);
        const diff = (dtLivraison - dtCollecte) / (1000 * 60 * 60);
        
        if (diff < 12) {
            if (heureWarning) {
                heureWarning.style.display = 'block';
                heureWarning.innerHTML = `<i class="fas fa-exclamation-triangle"></i> L'écart actuel est de ${diff.toFixed(1)}h. Pour une livraison le même jour, un minimum de 12h est requis.`;
            }
            return false;
        } else {
            if (heureWarning) heureWarning.style.display = 'none';
            return true;
        }
    }
    
    // ============================================
    // GESTION DISPONIBILITÉ PÈSE
    // ============================================
    const peseOui = document.getElementById('peseOui');
    const peseNon = document.getElementById('peseNon');
    const optionsNonPese = document.getElementById('optionsNonPese');
    const optionLaverie = document.getElementById('optionLaverie');
    const optionCommandeBlanc = document.getElementById('optionCommandeBlanc');
    const explanationCommandeBlanc = document.getElementById('explanationCommandeBlanc');
    
    if (peseNon && optionsNonPese) {
        peseNon.addEventListener('change', function() {
            if (this.checked) {
                optionsNonPese.style.display = 'block';
            }
        });
    }
    
    if (peseOui && optionsNonPese) {
        peseOui.addEventListener('change', function() {
            if (this.checked) {
                optionsNonPese.style.display = 'none';
                if (explanationCommandeBlanc) explanationCommandeBlanc.style.display = 'none';
            }
        });
    }
    
    if (optionCommandeBlanc && explanationCommandeBlanc) {
        optionCommandeBlanc.addEventListener('change', function() {
            if (this.checked) {
                explanationCommandeBlanc.style.display = 'block';
            }
        });
    }
    
    if (optionLaverie && explanationCommandeBlanc) {
        optionLaverie.addEventListener('change', function() {
            if (this.checked) {
                explanationCommandeBlanc.style.display = 'none';
            }
        });
    }
    
    // ============================================
    // GESTION BOUTON "CONTINUER AVEC COMMANDE À BLANC"
    // ============================================
    const btnConfirmerCommandeBlanc = document.getElementById('btnConfirmerCommandeBlanc');
    
    if (btnConfirmerCommandeBlanc) {
        btnConfirmerCommandeBlanc.addEventListener('click', function() {
            if (!validateStep(1) || !validateStep(2) || !validateStep(3)) {
                alert('Veuillez d\'abord remplir correctement les informations client, adresses et dates.');
                return;
            }
            
            const commandeBlancData = {
                nomClient: document.getElementById('nomClient').value,
                telephone: document.getElementById('telephone').value,
                adresseCollecte: adresseCollecte.value || adresseLivraison.value,
                descriptionCollecte: document.getElementById('descriptionCollecte').value,
                adresseLivraison: adresseLivraison.value || adresseCollecte.value,
                descriptionLivraison: document.getElementById('descriptionLivraison').value,
                dateCollecte: dateCollecte.value || dateLivraison.value,
                heureCollecte: heureCollecte.value || heureLivraison.value,
                dateLivraison: dateLivraison.value || dateCollecte.value,
                heureLivraison: heureLivraison.value || heureCollecte.value
            };
            
            sessionStorage.setItem('commandeBlancData', JSON.stringify(commandeBlancData));
            window.location.href = 'confirmation_commande_blanc.html';
        });
    }
    
    // ============================================
    // GESTION TYPE DE LINGE (Étape 7)
    // ============================================
    const btnVolumineux = document.getElementById('btnVolumineux');
    const btnOrdinaire = document.getElementById('btnOrdinaire');
    const volumineuxSection = document.getElementById('volumineuxSection');
    const ordinaireSection = document.getElementById('ordinaireSection');
    
    if (btnVolumineux && volumineuxSection) {
        btnVolumineux.addEventListener('click', function() {
            this.classList.toggle('active');
            
            if (this.classList.contains('active')) {
                volumineuxSection.style.display = 'block';
            } else {
                volumineuxSection.style.display = 'none';
                volumineuxSection.querySelectorAll('.color-card').forEach(card => {
                    card.classList.remove('active');
                });
                volumineuxSection.querySelectorAll('.poids-group').forEach(group => {
                    group.style.display = 'none';
                });
            }
        });
    }
    
    if (btnOrdinaire && ordinaireSection) {
        btnOrdinaire.addEventListener('click', function() {
            this.classList.toggle('active');
            
            if (this.classList.contains('active')) {
                ordinaireSection.style.display = 'block';
            } else {
                ordinaireSection.style.display = 'none';
                ordinaireSection.querySelectorAll('.color-card').forEach(card => {
                    card.classList.remove('active');
                });
                ordinaireSection.querySelectorAll('.poids-group').forEach(group => {
                    group.style.display = 'none';
                });
            }
        });
    }
    
    // ============================================
    // ✨ GESTION DES COULEURS AVEC CONSERVATION DES DONNÉES
    // ============================================
    const colorCards = document.querySelectorAll('.color-card');
    
    colorCards.forEach(card => {
        card.addEventListener('click', function() {
            const color = this.getAttribute('data-color');
            const volume = this.getAttribute('data-volume');
            const groupId = color + volume.charAt(0).toUpperCase() + volume.slice(1);
            const poidsGroup = document.getElementById(groupId);
            
            // Toggle active
            this.classList.toggle('active');
            
            if (this.classList.contains('active')) {
                // Afficher le groupe
                poidsGroup.style.display = 'block';
            } else {
                // Masquer le groupe SANS réinitialiser les valeurs
                // Les valeurs sont conservées grâce au localStorage
                poidsGroup.style.display = 'none';
            }
            
            // Sauvegarder immédiatement
            saveFormData();
        });
    });
    
    // ============================================
    // GESTION BOUTONS TEMPÉRATURE
    // ============================================
    const tempButtons = document.querySelectorAll('.temp-btn');
    
    tempButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            
            const temp = this.getAttribute('data-temp');
            const category = this.getAttribute('data-category');
            const hiddenInput = document.getElementById(`${category}_temperature`);
            
            // Retirer l'active de tous les boutons de la même catégorie
            const categoryButtons = document.querySelectorAll(`.temp-btn[data-category="${category}"]`);
            categoryButtons.forEach(btn => btn.classList.remove('active'));
            
            // Activer ce bouton
            this.classList.add('active');
            
            // Mettre à jour le champ caché
            if (hiddenInput) {
                hiddenInput.value = temp;
            }
            
            // Sauvegarder
            saveFormData();
        });
    });
    
    // ============================================
    // VALIDATION DES ÉTAPES
    // ============================================
    function validateStep(step) {
        // Étape 1 : Informations client
        if (step === 1) {
            const nomClient = document.getElementById('nomClient').value.trim();
            const telephone = document.getElementById('telephone').value.trim();
            
            if (!nomClient) {
                alert('Veuillez renseigner le nom du client.');
                return false;
            }
            
            if (!telephone) {
                alert('Veuillez renseigner le numéro de téléphone.');
                return false;
            }
            
            const telRegex = /^(\d{8}|\d{11})$/;
            if (!telRegex.test(telephone.replace(/\s/g, ''))) {
                alert('Le numéro de téléphone doit contenir 8 ou 11 chiffres.');
                return false;
            }
            
            return true;
        }
        
        // Étape 2 : Adresses
        if (step === 2) {
            const adresseC = adresseCollecte.value.trim();
            const adresseL = adresseLivraison.value.trim();
            
            if (!adresseC && !adresseL) {
                alert('Veuillez renseigner au moins une adresse (collecte ou livraison).');
                return false;
            }
            
            return true;
        }
        
        // Étape 3 : Dates et heures
        if (step === 3) {
            const dateC = dateCollecte.value;
            const dateL = dateLivraison.value;
            const heureC = heureCollecte.value;
            const heureL = heureLivraison.value;
            
            if (!dateC && !dateL) {
                alert('Veuillez renseigner au moins une date (collecte ou livraison).');
                return false;
            }
            
            if (!heureC && !heureL) {
                alert('Veuillez renseigner au moins une heure (collecte ou livraison).');
                return false;
            }
            
            if (!validateHeureEcart()) {
                alert('L\'écart entre la collecte et la livraison doit être d\'au moins 12 heures pour une livraison le même jour.');
                return false;
            }
            
            return true;
        }
        
        // Étape 4 : Disponibilité pèse
        if (step === 4) {
            const peseChoice = document.querySelector('input[name="disponibilitePese"]:checked');
            
            if (!peseChoice) {
                alert('Veuillez indiquer si vous disposez d\'une pèse.');
                return false;
            }
            
            if (peseChoice.value === 'non') {
                const optionChoice = document.querySelector('input[name="optionNonPese"]:checked');
                
                if (!optionChoice) {
                    alert('Veuillez choisir une option (laverie ou commande à blanc).');
                    return false;
                }
                
                if (optionChoice.value === 'commandeBlanc') {
                    return false;
                }
            }
            
            return true;
        }
        
        // Étape 5 : Protocole (pas de validation)
        if (step === 5) {
            return true;
        }
        
        // Étape 6 : Poids total
        if (step === 6) {
            const poidsTotal = parseFloat(document.getElementById('poidsTotal').value);
            
            if (!poidsTotal || poidsTotal <= 0) {
                alert('Veuillez renseigner le poids total de votre linge.');
                return false;
            }
            
            return true;
        }
        
        // Étape 7 : Poids par sous-tas + températures + repassage
        if (step === 7) {
            const poidsTotal = parseFloat(document.getElementById('poidsTotal').value);
            
            // Collecter tous les poids (seulement blanc et couleur, pas noir)
            const categories = ['a1', 'b1', 'a2', 'b2'];
            let sommePoids = 0;
            let auMoinsUnPoids = false;
            let validationError = false;
            
            categories.forEach(cat => {
                const poidsInput = document.querySelector(`input[name="${cat}_poids"]`);
                const tempInput = document.getElementById(`${cat}_temperature`);
                
                if (poidsInput) {
                    const poids = parseFloat(poidsInput.value) || 0;
                    
                    if (poids > 0) {
                        auMoinsUnPoids = true;
                        sommePoids += poids;
                        
                        // Vérifier qu'une température est sélectionnée SEULEMENT si poids > 0
                        if (!tempInput || !tempInput.value) {
                            alert(`Veuillez sélectionner une température pour le sous-tas ${cat.toUpperCase()}.`);
                            validationError = true;
                        }
                    }
                }
            });
            
            if (validationError) {
                return false;
            }
            
            if (!auMoinsUnPoids) {
                alert('Veuillez renseigner au moins un poids de linge.');
                return false;
            }
            
            // Vérifier la cohérence avec le poids total (±1kg de tolérance)
            const diff = Math.abs(sommePoids - poidsTotal);
            
            if (diff > 1) {
                const msgElement = document.getElementById('poidsValidationMessage');
                const txtElement = document.getElementById('poidsValidationText');
                
                if (msgElement && txtElement) {
                    txtElement.textContent = `La somme des poids des sous-tas (${sommePoids.toFixed(1)} kg) ne correspond pas au poids total (${poidsTotal.toFixed(1)} kg). Différence : ${diff.toFixed(1)} kg (maximum autorisé : ± 1 kg)`;
                    msgElement.style.display = 'block';
                }
                
                alert(`La somme des poids (${sommePoids.toFixed(1)} kg) ne correspond pas au poids total (${poidsTotal.toFixed(1)} kg).\nDifférence : ${diff.toFixed(1)} kg (max : ± 1 kg)`);
                return false;
            } else {
                const msgElement = document.getElementById('poidsValidationMessage');
                if (msgElement) msgElement.style.display = 'none';
            }
            
            // Validation repassage
            const repassageChoice = document.querySelector('input[name="repassage"]:checked');
            if (!repassageChoice) {
                alert('Veuillez indiquer si vous souhaitez le service de repassage.');
                return false;
            }
            
            return true;
        }
        
        // Étape 8 : Récapitulatif (pas de validation)
        if (step === 8) {
            return true;
        }
        
        // Étape 9 : Moyen de paiement
        if (step === 9) {
            const paiementChoice = document.querySelector('input[name="paiement"]:checked');
            
            if (!paiementChoice) {
                alert('Veuillez choisir un moyen de paiement.');
                return false;
            }
            
            return true;
        }
        
        return true;
    }
    
    // ============================================
    // ✅ CALCUL DU RÉCAPITULATIF (nouvelle logique)
    // ============================================
    function calculerRecapitulatif() {
        console.log('=== DÉBUT CALCUL RÉCAPITULATIF ===');
        
        let prixLavageTotal = 0;
        let lavTotal = 0;
        let poidsVolumineuxTotal = 0;
        let poidsOrdinaireTotal = 0;
        let poidsGrandTotal = 0;
        
        // Champs à traiter (seulement blanc et couleur)
        const champs = [
            { cat: 'a1', temp: null, type: 'volumineux' },
            { cat: 'b1', temp: null, type: 'volumineux' },
            { cat: 'a2', temp: null, type: 'ordinaire' },
            { cat: 'b2', temp: null, type: 'ordinaire' }
        ];
        
        champs.forEach(field => {
            const poidsInput = document.querySelector(`input[name="${field.cat}_poids"]`);
            const tempInput = document.getElementById(`${field.cat}_temperature`);
            
            if (poidsInput && tempInput) {
                const poids = parseFloat(poidsInput.value) || 0;
                const temp = tempInput.value;
                
                console.log(`Champ ${field.cat}: poids=${poids}, temp=${temp}`);
                
                if (poids > 0 && temp) {
                    const result = field.type === 'volumineux'
                        ? calculerPrixLavageVolumineux(poids, temp)
                        : calculerPrixLavageOrdinaire(poids, temp);
                    
                    console.log(`  -> Résultat: prix=${result.prix}, lav=${result.lav}`);
                    
                    prixLavageTotal += result.prix;
                    lavTotal += result.lav;
                    
                    poidsGrandTotal += poids;
                    if (field.type === 'volumineux') {
                        poidsVolumineuxTotal += poids;
                    } else {
                        poidsOrdinaireTotal += poids;
                    }
                }
            }
        });
        
        console.log(`Totaux: lavage=${prixLavageTotal}, lav=${lavTotal}, poids=${poidsGrandTotal}`);
        
        // Calcul de la réduction fidélité
        const totalLavages = userNombreLavage + lavTotal;
        const reductionFidelite = Math.floor(totalLavages / 11) * 2500;
        const prixLavageFinal = Math.max(0, prixLavageTotal - reductionFidelite);
        
        console.log(`Fidélité: userNombreLavage=${userNombreLavage}, totalLavages=${totalLavages}, réduction=${reductionFidelite}`);
        
        // Calcul séchage, pliage, repassage
        const prixSechage = calculerPrixSechage(poidsGrandTotal);
        const prixPliage = calculerPrixPliage(poidsGrandTotal);
        
        console.log(`Séchage=${prixSechage}, Pliage=${prixPliage}`);
        
        const repassageChoice = document.querySelector('input[name="repassage"]:checked');
        let prixRepassage = 0;
        
        if (repassageChoice && repassageChoice.value === 'oui') {
            prixRepassage = calculerPrixRepassage(poidsVolumineuxTotal, poidsOrdinaireTotal);
            console.log(`Repassage=${prixRepassage}`);
        }
        
        // Total
        const total = prixLavageFinal + prixSechage + prixPliage + prixRepassage;
        
        console.log(`TOTAL FINAL=${total}`);
        
        // Vérifier que les éléments existent avant de les mettre à jour
        const elemRecapPrixLavage = document.getElementById('recapPrixLavage');
        const elemRecapPrixSechage = document.getElementById('recapPrixSechage');
        const elemRecapPrixPliage = document.getElementById('recapPrixPliage');
        const elemRecapTotal = document.getElementById('recapTotal');
        
        if (!elemRecapPrixLavage || !elemRecapPrixSechage || !elemRecapPrixPliage || !elemRecapTotal) {
            console.error('ERREUR: Un ou plusieurs éléments de récapitulatif sont introuvables!');
            console.error({
                elemRecapPrixLavage: !!elemRecapPrixLavage,
                elemRecapPrixSechage: !!elemRecapPrixSechage,
                elemRecapPrixPliage: !!elemRecapPrixPliage,
                elemRecapTotal: !!elemRecapTotal
            });
            return;
        }
        
        // Affichage
        elemRecapPrixLavage.textContent = Math.round(prixLavageTotal).toLocaleString() + ' FCFA';
        elemRecapPrixSechage.textContent = Math.round(prixSechage).toLocaleString() + ' FCFA';
        elemRecapPrixPliage.textContent = Math.round(prixPliage).toLocaleString() + ' FCFA';
        elemRecapTotal.textContent = Math.round(total).toLocaleString() + ' FCFA';
        
        console.log('Affichage mis à jour');
        
        // Réduction fidélité
        const recapReductionContainer = document.getElementById('recapReductionContainer');
        if (reductionFidelite > 0 && recapReductionContainer) {
            recapReductionContainer.style.display = 'flex';
            document.getElementById('recapReduction').textContent = '- ' + Math.round(reductionFidelite).toLocaleString() + ' FCFA';
        } else if (recapReductionContainer) {
            recapReductionContainer.style.display = 'none';
        }
        
        // Repassage
        const recapRepassageContainer = document.getElementById('recapRepassageContainer');
        if (repassageChoice && repassageChoice.value === 'oui' && recapRepassageContainer) {
            recapRepassageContainer.style.display = 'flex';
            document.getElementById('recapPrixRepassage').textContent = Math.round(prixRepassage).toLocaleString() + ' FCFA';
        } else if (recapRepassageContainer) {
            recapRepassageContainer.style.display = 'none';
        }
        
        console.log('=== FIN CALCUL RÉCAPITULATIF ===');
    }
    
    // ============================================
    // NAVIGATION
    // ============================================
    btnNext.addEventListener('click', function() {
        if (validateStep(currentStep)) {
            if (currentStep === 4) {
                const peseChoice = document.querySelector('input[name="disponibilitePese"]:checked');
                if (peseChoice && peseChoice.value === 'non') {
                    return;
                }
            }
            
            currentStep++;
            showStep(currentStep);
        }
    });
    
    btnPrev.addEventListener('click', function() {
        currentStep--;
        showStep(currentStep);
    });
    
    // ============================================
    // SOUMISSION DU FORMULAIRE
    // ============================================
    commandeForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        if (!validateStep(currentStep)) {
            return;
        }
        
        const formData = collectFormData();
        submitOrder(formData);
    });
    
    function collectFormData() {
        const categories = ['a1', 'b1', 'a2', 'b2'];
        const poids = {};
        
        categories.forEach(cat => {
            const poidsInput = document.querySelector(`input[name="${cat}_poids"]`);
            const tempInput = document.getElementById(`${cat}_temperature`);
            
            if (poidsInput && tempInput) {
                const poidsVal = parseFloat(poidsInput.value) || 0;
                const tempVal = tempInput.value;
                
                if (poidsVal > 0 && tempVal) {
                    poids[`${cat}_${tempVal}`] = poidsVal;
                }
            }
        });
        
        return {
            nomClient: document.getElementById('nomClient').value,
            telephone: document.getElementById('telephone').value,
            adresseCollecte: adresseCollecte.value || adresseLivraison.value,
            descriptionCollecte: document.getElementById('descriptionCollecte').value,
            adresseLivraison: adresseLivraison.value || adresseCollecte.value,
            descriptionLivraison: document.getElementById('descriptionLivraison').value,
            dateCollecte: dateCollecte.value || dateLivraison.value,
            heureCollecte: heureCollecte.value || heureLivraison.value,
            dateLivraison: dateLivraison.value || dateCollecte.value,
            heureLivraison: heureLivraison.value || heureCollecte.value,
            poidsTotal: parseFloat(document.getElementById('poidsTotal').value),
            poids: poids,
            repassage: document.querySelector('input[name="repassage"]:checked').value,
            paiement: document.querySelector('input[name="paiement"]:checked').value
        };
    }
    
    function submitOrder(data) {
        btnSubmit.disabled = true;
        btnSubmit.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Traitement...';
        
        fetch('process_order.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                // Nettoyer le localStorage après soumission réussie
                localStorage.removeItem('commandeFormData');
                window.location.href = 'payment.php?orderId=' + result.orderId;
            } else {
                alert('Erreur : ' + result.message);
                btnSubmit.disabled = false;
                btnSubmit.innerHTML = '<i class="fas fa-check"></i> Valider la commande';
            }
        })
        .catch(error => {
            console.error('Erreur:', error);
            alert('Une erreur est survenue lors de l\'enregistrement de la commande.');
            btnSubmit.disabled = false;
            btnSubmit.innerHTML = '<i class="fas fa-check"></i> Valider la commande';
        });
    }
    
    // Initialiser
    showStep(currentStep);
});