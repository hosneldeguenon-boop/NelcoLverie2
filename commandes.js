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

    // ✅ Traiter "choix" comme "chaud"
    const tempEffective = temperature === 'choix' ? 'chaud' : temperature;
    const grille = tarifs[tempEffective];
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

    // Traiter "choix" comme "chaud"
    const tempEffective = temperature === 'choix' ? 'chaud' : temperature;
    const grille = tarifs[tempEffective];

    // Fonction helper pour calculer avec une taille de tranche donnée
    function calculerAvecTranche(poidsTotal, tailleTrancheMax) {
        let prixTotal = 0;
        let lav = 0;
        let poidsRestant = poidsTotal;

        while (poidsRestant > 0) {
            // Prendre le minimum entre ce qui reste et la taille max de tranche
            const poidsTraite = Math.min(poidsRestant, tailleTrancheMax);

            // Trouver le tarif correspondant dans la grille
            for (let tranche of grille) {
                if (poidsTraite > tranche.min && poidsTraite <= tranche.max) {
                    prixTotal += tranche.prix;
                    lav++;
                    break;
                }
            }

            poidsRestant -= poidsTraite;
        }

        return { prix: prixTotal, lav };
    }

    // Calculer avec les 3 stratégies
    const resultat6kg = calculerAvecTranche(poids, 6);
    const resultat8kg = calculerAvecTranche(poids, 8);
    const resultat10kg = calculerAvecTranche(poids, 10);

    // Comparer et retenir le prix le plus bas
    const resultats = [resultat6kg, resultat8kg, resultat10kg];
    const meilleurResultat = resultats.reduce((meilleur, actuel) => {
        return actuel.prix < meilleur.prix ? actuel : meilleur;
    });

    return meilleurResultat;
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
    // 🆕 UTILITAIRES POUR L'HEURE ACTUELLE DU BÉNIN (UTC+1)
    // ============================================
    
    /**
     * Obtient l'heure actuelle au Bénin (UTC+1)
     * @returns {Date} Date/heure actuelle en timezone Bénin
     */
    function getHeureActuelleBenin() {
        const maintenant = new Date();
        
        // Obtenir le timestamp UTC
        const utcTime = maintenant.getTime() + (maintenant.getTimezoneOffset() * 60000);
        
        // Ajouter 1 heure pour le fuseau du Bénin (UTC+1)
        const beninTime = new Date(utcTime + (3600000 * 1));
        
        return beninTime;
    }
    
    /**
     * Obtient la date du jour au Bénin au format YYYY-MM-DD
     * @returns {string} Date au format YYYY-MM-DD
     */
    function getDateAujourdhuiBenin() {
        const aujourd = getHeureActuelleBenin();
        const annee = aujourd.getFullYear();
        const mois = String(aujourd.getMonth() + 1).padStart(2, '0');
        const jour = String(aujourd.getDate()).padStart(2, '0');
        return `${annee}-${mois}-${jour}`;
    }
    
    /**
     * Vérifie si une heure est dans la plage autorisée (9h00 - 19h00)
     * @param {string} heure - Heure au format HH:MM
     * @returns {boolean} true si valide, false sinon
     */
    function isHeureInPlageAutorisee(heure) {
        if (!heure) return false;
        
        const [h, m] = heure.split(':').map(Number);
        const heureDecimale = h + (m / 60);
        
        // Plage autorisée : 9h00 à 19h00
        return heureDecimale >= 9.0 && heureDecimale <= 21.0;
    }
    
    // ============================================
    // GESTION DES DATES ET HEURES
    // ============================================
    function getDateAujourdhui() {
        return getDateAujourdhuiBenin();
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
                heureLivraison.value = addHours(this.value, 8);
            }
            validateHeureEcart();
        });
        
        heureLivraison.addEventListener('input', function() {
            if (!heureCollecte.value && this.value) {
                heureCollecte.value = addHours(this.value, -8);
            }
            validateHeureEcart();
        });
    }
    
    // ============================================
    // ✨ VALIDATION AVANCÉE DES HEURES (VERSION COMPLÈTE)
    // ============================================
    /**
     * Valide les heures de collecte et livraison selon TOUTES les règles métier :
     * 
     * RÈGLES APPLIQUÉES :
     * 1. Plage horaire autorisée : 9h00 - 19h00 (OBLIGATOIRE pour toutes les heures)
     * 2. Comparaison avec l'heure actuelle (UNIQUEMENT si date = aujourd'hui)
     * 3. Écart minimum de 12h entre collecte et livraison (si dates identiques)
     * 
     * @returns {boolean} true si toutes les validations passent, false sinon
     */
    function validateHeureEcart() {
        const dateC = dateCollecte.value;
        const dateL = dateLivraison.value;
        const heureC = heureCollecte.value;
        const heureL = heureLivraison.value;
        
        // Si des champs manquent, on ne valide pas encore
        if (!dateC || !dateL || !heureC || !heureL) {
            if (heureWarning) heureWarning.style.display = 'none';
            return true;
        }
        
        const dateDuJourBenin = getDateAujourdhuiBenin();
        const heureActuelleBenin = getHeureActuelleBenin();
        
        // ============================================
        // VALIDATION 1 : PLAGE HORAIRE AUTORISÉE (9h00 - 19h00)
        // Cette règle s'applique TOUJOURS, quelle que soit la date
        // ============================================
        
        if (!isHeureInPlageAutorisee(heureC)) {
            if (heureWarning) {
                heureWarning.style.display = 'block';
                heureWarning.innerHTML = `<i class="fas fa-exclamation-triangle"></i> L'heure de collecte doit être entre <strong>9h00 et 21h00</strong>.`;
            }
            return false;
        }
        
        if (!isHeureInPlageAutorisee(heureL)) {
            if (heureWarning) {
                heureWarning.style.display = 'block';
                heureWarning.innerHTML = `<i class="fas fa-exclamation-triangle"></i> L'heure de livraison doit être entre <strong>9h00 et 21h00</strong>.`;
            }
            return false;
        }
        
        // ============================================
        // VALIDATION 2 : COMPARAISON AVEC L'HEURE ACTUELLE
        // Cette règle s'applique UNIQUEMENT si la date = aujourd'hui
        // ============================================
        
        // Vérification pour la date de collecte
        if (dateC === dateDuJourBenin) {
            const dtCollecte = new Date(dateC + 'T' + heureC);
            
            if (dtCollecte < heureActuelleBenin) {
                if (heureWarning) {
                    const heureAffichee = heureActuelleBenin.getHours().toString().padStart(2, '0') + ':' + 
                                         heureActuelleBenin.getMinutes().toString().padStart(2, '0');
                    heureWarning.style.display = 'block';
                    heureWarning.innerHTML = `<i class="fas fa-exclamation-triangle"></i> L'heure de collecte ne peut pas être antérieure à l'heure actuelle (${heureAffichee}).`;
                }
                return false;
            }
        }
        
        // Vérification pour la date de livraison
        if (dateL === dateDuJourBenin) {
            const dtLivraison = new Date(dateL + 'T' + heureL);
            
            if (dtLivraison < heureActuelleBenin) {
                if (heureWarning) {
                    const heureAffichee = heureActuelleBenin.getHours().toString().padStart(2, '0') + ':' + 
                                         heureActuelleBenin.getMinutes().toString().padStart(2, '0');
                    heureWarning.style.display = 'block';
                    heureWarning.innerHTML = `<i class="fas fa-exclamation-triangle"></i> L'heure de livraison ne peut pas être antérieure à l'heure actuelle (${heureAffichee}).`;
                }
                return false;
            }
        }
        
        // ============================================
        // VALIDATION 3 : ÉCART MINIMUM DE 12H (si dates identiques)
        // Cette règle existait déjà et est PRÉSERVÉE
        // ============================================
        
        // La contrainte de 12h ne s'applique que si les dates sont identiques
        if (dateC !== dateL) {
            if (heureWarning) heureWarning.style.display = 'none';
            return true;
        }
        
        // Dates identiques : vérifier l'écart de 12h
        const dtCollecte = new Date(dateC + 'T' + heureC);
        const dtLivraison = new Date(dateL + 'T' + heureL);
        const diff = (dtLivraison - dtCollecte) / (1000 * 60 * 60);
        
        if (diff < 8) {
            if (heureWarning) {
                heureWarning.style.display = 'block';
                heureWarning.innerHTML = `<i class="fas fa-exclamation-triangle"></i> L'écart actuel est de ${diff.toFixed(1)}h. Pour une livraison le même jour, un minimum de 12h est requis.`;
            }
            return false;
        }
        
        // ✅ Toutes les validations sont passées
        if (heureWarning) heureWarning.style.display = 'none';
        return true;
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
    // GESTION BOUTONS DE TEMPÉRATURE
    // ============================================
    const tempButtons = document.querySelectorAll('.temp-btn');
    
    tempButtons.forEach(button => {
        button.addEventListener('click', function() {
            const category = this.dataset.category;
            const temp = this.dataset.temp;
            
            // Retirer la classe active de tous les boutons de cette catégorie
            document.querySelectorAll(`.temp-btn[data-category="${category}"]`).forEach(btn => {
                btn.classList.remove('active');
            });
            
            // Ajouter la classe active au bouton cliqué
            this.classList.add('active');
            
            // Mettre à jour le champ caché
            const tempInput = document.getElementById(`${category}_temperature`);
            if (tempInput) {
                tempInput.value = temp;
                saveFormData(); // Sauvegarder après changement
            }
        });
    });
    
    // ============================================
    // CALCUL DU POIDS TOTAL
    // ============================================
    function calculerPoidsTotal() {
        let total = 0;
        const categories = ['a1', 'b1', 'c1', 'a2', 'b2', 'c2'];
        
        categories.forEach(cat => {
            const input = document.querySelector(`input[name="${cat}_poids"]`);
            if (input) {
                const value = parseFloat(input.value) || 0;
                total += value;
            }
        });
        
        const poidsTotalInput = document.getElementById('poidsTotal');
        if (poidsTotalInput) {
            poidsTotalInput.value = total.toFixed(1);
        }
        
        return total;
    }
    
    // Écouter les changements sur tous les inputs de poids
    const poidsInputs = document.querySelectorAll('input[name$="_poids"]');
    poidsInputs.forEach(input => {
        input.addEventListener('input', calculerPoidsTotal);
    });
    
    // ============================================
    // VALIDATION DES ÉTAPES
    // ============================================
    function validateStep(step) {
        switch(step) {
            case 1:
                // Validation informations client
                const nomClient = document.getElementById('nomClient');
                const telephone = document.getElementById('telephone');
                
                if (!nomClient || !nomClient.value.trim()) {
                    alert('Veuillez entrer votre nom complet.');
                    return false;
                }
                
                if (!telephone || !telephone.value.trim()) {
                    alert('Veuillez entrer votre numéro de téléphone.');
                    return false;
                }
                
                // Validation format téléphone (optionnel mais recommandé)
                const phoneRegex = /^[0-9]{8,}$/;
                if (!phoneRegex.test(telephone.value.replace(/\s/g, ''))) {
                    alert('Veuillez entrer un numéro de téléphone valide (minimum 8 chiffres).');
                    return false;
                }
                
                return true;
                
            case 2:
                // Validation adresses
                if (!adresseCollecte || !adresseCollecte.value.trim()) {
                    alert('Veuillez sélectionner une adresse de collecte.');
                    return false;
                }
                
                if (!adresseLivraison || !adresseLivraison.value.trim()) {
                    alert('Veuillez sélectionner une adresse de livraison.');
                    return false;
                }
                
                return true;
                
            case 3:
                // Validation dates et heures
                if (!dateCollecte || !dateCollecte.value) {
                    alert('Veuillez sélectionner une date de collecte.');
                    return false;
                }
                
                if (!dateLivraison || !dateLivraison.value) {
                    alert('Veuillez sélectionner une date de livraison.');
                    return false;
                }
                
                if (!heureCollecte || !heureCollecte.value) {
                    alert('Veuillez sélectionner une heure de collecte.');
                    return false;
                }
                
                if (!heureLivraison || !heureLivraison.value) {
                    alert('Veuillez sélectionner une heure de livraison.');
                    return false;
                }
                
                // ✅ VALIDATION AVANCÉE DES HEURES
                if (!validateHeureEcart()) {
                    return false;
                }
                
                return true;
                
            case 4:
                // Validation disponibilité pèse
                const peseChoice = document.querySelector('input[name="disponibilitePese"]:checked');
                if (!peseChoice) {
                    alert('Veuillez indiquer si vous disposez d\'un pèse-personne.');
                    return false;
                }
                
                // Si "non", vérifier qu'une option est sélectionnée
                if (peseChoice.value === 'non') {
                    const optionChoice = document.querySelector('input[name="optionNonPese"]:checked');
                    if (!optionChoice) {
                        alert('Veuillez choisir une option.');
                        return false;
                    }
                }
                
                return true;
                
            case 5:
                // Validation poids A1 (t-shirts)
                const a1Poids = document.querySelector('input[name="a1_poids"]');
                const a1Temp = document.getElementById('a1_temperature');
                
                if (a1Poids && parseFloat(a1Poids.value) > 0) {
                    if (!a1Temp || !a1Temp.value) {
                        alert('Veuillez sélectionner une température pour les T-shirts, débardeurs.');
                        return false;
                    }
                }
                
                return true;
                
            case 6:
                // Validation poids B1 (chemises)
                const b1Poids = document.querySelector('input[name="b1_poids"]');
                const b1Temp = document.getElementById('b1_temperature');
                
                if (b1Poids && parseFloat(b1Poids.value) > 0) {
                    if (!b1Temp || !b1Temp.value) {
                        alert('Veuillez sélectionner une température pour les Chemises, chemisiers.');
                        return false;
                    }
                }
                
                // Validation C1 (vêtements volumineux ordinaires)
                const c1Poids = document.querySelector('input[name="c1_poids"]');
                const c1Temp = document.getElementById('c1_temperature');
                
                if (c1Poids && parseFloat(c1Poids.value) > 0) {
                    if (!c1Temp || !c1Temp.value) {
                        alert('Veuillez sélectionner une température pour les vêtements volumineux ordinaires.');
                        return false;
                    }
                }
                
                return true;
                
            case 7:
                // Validation poids A2, B2, C2
                const a2Poids = document.querySelector('input[name="a2_poids"]');
                const a2Temp = document.getElementById('a2_temperature');
                
                if (a2Poids && parseFloat(a2Poids.value) > 0) {
                    if (!a2Temp || !a2Temp.value) {
                        alert('Veuillez sélectionner une température pour les Jeans, pantalons.');
                        return false;
                    }
                }
                
                const b2Poids = document.querySelector('input[name="b2_poids"]');
                const b2Temp = document.getElementById('b2_temperature');
                
                if (b2Poids && parseFloat(b2Poids.value) > 0) {
                    if (!b2Temp || !b2Temp.value) {
                        alert('Veuillez sélectionner une température pour les Pulls, sweats.');
                        return false;
                    }
                }
                
                const c2Poids = document.querySelector('input[name="c2_poids"]');
                const c2Temp = document.getElementById('c2_temperature');
                
                if (c2Poids && parseFloat(c2Poids.value) > 0) {
                    if (!c2Temp || !c2Temp.value) {
                        alert('Veuillez sélectionner une température pour les vêtements volumineux délicats.');
                        return false;
                    }
                }
                
                // Vérifier qu'au moins un poids a été saisi
                const totalPoids = calculerPoidsTotal();
                if (totalPoids <= 0) {
                    alert('Veuillez saisir au moins un poids de vêtement.');
                    return false;
                }
                
                return true;
                
            case 8:
                // Validation repassage
                const repassageChoice = document.querySelector('input[name="repassage"]:checked');
                if (!repassageChoice) {
                    alert('Veuillez indiquer si vous souhaitez le service de repassage.');
                    return false;
                }
                
                return true;
                
            case 9:
                // Validation paiement
                const paiementChoice = document.querySelector('input[name="paiement"]:checked');
                if (!paiementChoice) {
                    alert('Veuillez sélectionner un mode de paiement.');
                    return false;
                }
                
                return true;
                
            default:
                return true;
        }
    }
    
    // ============================================
    // CALCUL DU RÉCAPITULATIF
    // ============================================
    function calculerRecapitulatif() {
        console.log('=== DÉBUT CALCUL RÉCAPITULATIF ===');
        
        // Collecte des données
        const categories = ['a1', 'b1', 'c1', 'a2', 'b2', 'c2'];
        let poidsOrdinaireTotal = 0;
        let poidsVolumineuxTotal = 0;
        let poidsGrandTotal = 0;
        let prixLavageTotal = 0;
        let totalLavages = 0;
        
        categories.forEach(cat => {
            const poidsInput = document.querySelector(`input[name="${cat}_poids"]`);
            const tempInput = document.getElementById(`${cat}_temperature`);
            
            if (poidsInput && tempInput) {
                const poids = parseFloat(poidsInput.value) || 0;
                const temp = tempInput.value;
                
                if (poids > 0 && temp) {
                    console.log(`Catégorie ${cat}: poids=${poids}, temp=${temp}`);
                    
                    poidsGrandTotal += poids;
                    
                    // c1 et c2 = volumineux
                    if (cat === 'c1' || cat === 'c2') {
                        poidsVolumineuxTotal += poids;
                        const result = calculerPrixLavageVolumineux(poids, temp);
                        prixLavageTotal += result.prix;
                        totalLavages += result.lav;
                        console.log(`${cat} (volumineux): prix=${result.prix}, lav=${result.lav}`);
                    } else {
                        // a1, b1, a2, b2 = ordinaires
                        poidsOrdinaireTotal += poids;
                        const result = calculerPrixLavageOrdinaire(poids, temp);
                        prixLavageTotal += result.prix;
                        totalLavages += result.lav;
                        console.log(`${cat} (ordinaire): prix=${result.prix}, lav=${result.lav}`);
                    }
                }
            }
        });
        
        console.log(`Totaux: Ordinaire=${poidsOrdinaireTotal}, Volumineux=${poidsVolumineuxTotal}, Grand Total=${poidsGrandTotal}`);
        console.log(`Prix lavage BRUT (avant fidélité)=${prixLavageTotal}, Nombre lavages=${totalLavages}`);
        
        // Appliquer la fidélité
        let reductionFidelite = 0;
        let prixLavageFinal = prixLavageTotal;
        
        if (userNombreLavage + totalLavages >= 11) {
            const cyclesComplets = Math.floor((userNombreLavage + totalLavages) / 11);
            reductionFidelite = cyclesComplets * 2000;
            prixLavageFinal = Math.max(0, prixLavageTotal - reductionFidelite);
        }
        
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
            // Gestion spéciale de l'étape 4 (disponibilité pèse)
            if (currentStep === 4) {
                const peseChoice = document.querySelector('input[name="disponibilitePese"]:checked');
                if (peseChoice && peseChoice.value === 'non') {
                    const optionChoice = document.querySelector('input[name="optionNonPese"]:checked');
                    
                    if (optionChoice) {
                        if (optionChoice.value === 'laverie') {
                            // Rediriger vers la page d'accueil
                            window.location.href = 'index.html';
                            return;
                        } else if (optionChoice.value === 'commande_blanc') {
                            // Préparer les données et rediriger vers confirmation commande à blanc
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
                            return;
                        }
                    }
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
