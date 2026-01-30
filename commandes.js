// ============================================
// SYSTÈME DE COMMANDE PROGRESSIVE - VERSION COMPLÈTE
// ============================================

document.addEventListener('DOMContentLoaded', function() {
    // ============================================
    // VARIABLES GLOBALES
    // ============================================
    let currentStep = 1;
    const totalSteps = 9;
    let skipProtocol = false; // Indique si on saute le protocole (commande à blanc)
    
    // Éléments DOM
    const progressBar = document.getElementById('progressBar');
    const progressText = document.getElementById('progressText');
    const btnPrev = document.getElementById('btnPrev');
    const btnNext = document.getElementById('btnNext');
    const btnSubmit = document.getElementById('btnSubmit');
    const stepSections = document.querySelectorAll('.step-section');
    const commandeForm = document.getElementById('commandeForm');
    
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
        });
        
        dateLivraison.addEventListener('input', function() {
            if (!dateCollecte.value) {
                dateCollecte.value = this.value;
            }
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
    
    // Validation de l'écart de 12h
    function validateHeureEcart() {
        const dateC = dateCollecte.value;
        const dateL = dateLivraison.value;
        const heureC = heureCollecte.value;
        const heureL = heureLivraison.value;
        
        if (!dateC || !dateL || !heureC || !heureL) {
            if (heureWarning) heureWarning.style.display = 'none';
            return true;
        }
        
        const dtCollecte = new Date(dateC + 'T' + heureC);
        const dtLivraison = new Date(dateL + 'T' + heureL);
        const diff = (dtLivraison - dtCollecte) / (1000 * 60 * 60); // en heures
        
        if (diff < 12) {
            if (heureWarning) {
                heureWarning.style.display = 'block';
                heureWarning.textContent = `⚠️ L'écart actuel est de ${diff.toFixed(1)}h. Un minimum de 12h est requis.`;
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
    // GESTION TYPE DE LINGE (Volumineux/Ordinaire)
    // ============================================
    const btnVolumineux = document.getElementById('btnVolumineux');
    const btnOrdinaire = document.getElementById('btnOrdinaire');
    const volumineuxSection = document.getElementById('volumineuxSection');
    const ordinaireSection = document.getElementById('ordinaireSection');
    
    if (btnVolumineux && volumineuxSection) {
        btnVolumineux.addEventListener('click', function() {
            this.classList.toggle('active');
            volumineuxSection.style.display = this.classList.contains('active') ? 'block' : 'none';
            
            if (!this.classList.contains('active')) {
                // Réinitialiser
                volumineuxSection.querySelectorAll('.color-card').forEach(card => card.classList.remove('active'));
                volumineuxSection.querySelectorAll('.poids-group').forEach(group => {
                    group.style.display = 'none';
                    group.querySelectorAll('input').forEach(inp => inp.value = '');
                });
            }
        });
    }
    
    if (btnOrdinaire && ordinaireSection) {
        btnOrdinaire.addEventListener('click', function() {
            this.classList.toggle('active');
            ordinaireSection.style.display = this.classList.contains('active') ? 'block' : 'none';
            
            if (!this.classList.contains('active')) {
                // Réinitialiser
                ordinaireSection.querySelectorAll('.color-card').forEach(card => card.classList.remove('active'));
                ordinaireSection.querySelectorAll('.poids-group').forEach(group => {
                    group.style.display = 'none';
                    group.querySelectorAll('input').forEach(inp => inp.value = '');
                });
            }
        });
    }
    
    // ============================================
    // GESTION DES COULEURS
    // ============================================
    const colorCards = document.querySelectorAll('.color-card');
    
    const colorGroupMap = {
        'blanc-volumineux': 'blancVolumineux',
        'couleur-volumineux': 'couleurVolumineux',
        'noir-volumineux': 'noirVolumineux',
        'blanc-ordinaire': 'blancOrdinaire',
        'couleur-ordinaire': 'couleurOrdinaire',
        'noir-ordinaire': 'noirOrdinaire'
    };
    
    colorCards.forEach(card => {
        card.addEventListener('click', function() {
            const color = this.getAttribute('data-color');
            const volume = this.getAttribute('data-volume');
            const groupKey = `${color}-${volume}`;
            const groupId = colorGroupMap[groupKey];
            const poidsGroup = document.getElementById(groupId);
            
            this.classList.toggle('active');
            
            if (poidsGroup) {
                if (this.classList.contains('active')) {
                    poidsGroup.style.display = 'block';
                } else {
                    poidsGroup.style.display = 'none';
                    // Réinitialiser les champs
                    poidsGroup.querySelectorAll('input').forEach(input => {
                        input.value = '';
                    });
                }
            }
        });
    });
    
    // ============================================
    // GESTION DES BOUTONS DE TEMPÉRATURE
    // ============================================
    const tempButtons = document.querySelectorAll('.temp-btn');
    
    tempButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            
            const category = this.getAttribute('data-category');
            const temp = this.getAttribute('data-temp');
            
            // Désactiver les autres boutons de la même catégorie
            document.querySelectorAll(`.temp-btn[data-category="${category}"]`).forEach(btn => {
                btn.classList.remove('active');
            });
            
            // Activer le bouton cliqué
            this.classList.add('active');
            
            // Mettre à jour le champ caché
            const hiddenInput = document.getElementById(`${category}_temperature`);
            if (hiddenInput) {
                hiddenInput.value = temp;
            }
        });
    });
    
    // ============================================
    // VALIDATION DES ÉTAPES
    // ============================================
    function validateStep(step) {
        const currentSection = document.querySelector(`.step-section[data-step="${step}"]`);
        
        // Étape 1 : Informations client
        if (step === 1) {
            const nom = document.getElementById('nomClient').value.trim();
            const tel = document.getElementById('telephone').value.trim();
            
            if (!nom || !tel) {
                alert('Veuillez remplir tous les champs obligatoires.');
                return false;
            }
            
            // Validation téléphone
            const telClean = tel.replace(/[^0-9]/g, '');
            if (telClean.length !== 8 && telClean.length !== 11) {
                alert('Le numéro de téléphone doit contenir 8 ou 11 chiffres.');
                return false;
            }
            
            return true;
        }
        
        // Étape 2 : Adresses
        if (step === 2) {
            const addrC = adresseCollecte.value.trim();
            const addrL = adresseLivraison.value.trim();
            
            if (!addrC && !addrL) {
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
            
            // Au moins une date
            if (!dateC && !dateL) {
                alert('Veuillez renseigner au moins une date (collecte ou livraison).');
                return false;
            }
            
            // Au moins une heure
            if (!heureC && !heureL) {
                alert('Veuillez renseigner au moins une heure (collecte ou livraison).');
                return false;
            }
            
            // Validation écart 12h
            if (!validateHeureEcart()) {
                alert('L\'écart entre la collecte et la livraison doit être d\'au moins 12 heures.');
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
                    alert('Veuillez choisir une option.');
                    return false;
                }
                
                // Si option laverie : redirection
                if (optionChoice.value === 'laverie') {
                    window.location.href = 'index.html';
                    return false;
                }
                
                // Si option commande à blanc : préparer les données et rediriger
                if (optionChoice.value === 'commande_blanc') {
                    const commandeData = {
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
                    
                    sessionStorage.setItem('commandeBlancData', JSON.stringify(commandeData));
                    window.location.href = 'confirmation_commande_blanc.html';
                    return false;
                }
            }
            
            return true;
        }
        
        // Étape 5 : Protocole (pas de validation nécessaire)
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
            
            // Collecter tous les poids
            const categories = ['a1', 'b1', 'c1', 'a2', 'b2', 'c2'];
            let sommePoids = 0;
            let auMoinsUnPoids = false;
            
            categories.forEach(cat => {
                const poidsInput = document.querySelector(`input[name="${cat}_poids"]`);
                const tempInput = document.getElementById(`${cat}_temperature`);
                
                if (poidsInput) {
                    const poids = parseFloat(poidsInput.value) || 0;
                    
                    if (poids > 0) {
                        auMoinsUnPoids = true;
                        sommePoids += poids;
                        
                        // Vérifier qu'une température est sélectionnée
                        if (!tempInput || !tempInput.value) {
                            alert(`Veuillez sélectionner une température pour le sous-tas ${cat.toUpperCase()}.`);
                            return false;
                        }
                    }
                }
            });
            
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
            
            // Calculer et afficher le récapitulatif
            calculerRecapitulatif();
            
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
    // CALCUL DU RÉCAPITULATIF
    // ============================================
    function calculerRecapitulatif() {
        // Cette fonction sera appelée par le backend
        // Pour l'instant, on affiche juste un placeholder
        document.getElementById('recapPrixLavage').textContent = '(Calcul en cours...)';
        document.getElementById('recapPrixSechage').textContent = '(Calcul en cours...)';
        document.getElementById('recapPrixPliage').textContent = '(Calcul en cours...)';
        
        const repassageChoice = document.querySelector('input[name="repassage"]:checked');
        const recapRepassageContainer = document.getElementById('recapRepassageContainer');
        
        if (repassageChoice && repassageChoice.value === 'oui') {
            if (recapRepassageContainer) {
                recapRepassageContainer.style.display = 'flex';
                document.getElementById('recapPrixRepassage').textContent = '(Calcul en cours...)';
            }
        } else {
            if (recapRepassageContainer) {
                recapRepassageContainer.style.display = 'none';
            }
        }
        
        document.getElementById('recapTotal').textContent = '(Calcul en cours...)';
    }
    
    // ============================================
    // NAVIGATION
    // ============================================
    btnNext.addEventListener('click', function() {
        if (validateStep(currentStep)) {
            // Cas spécial : skip étape 5 (protocole) si pèse = non
            if (currentStep === 4) {
                const peseChoice = document.querySelector('input[name="disponibilitePese"]:checked');
                if (peseChoice && peseChoice.value === 'non') {
                    // Cette condition ne devrait jamais arriver car on redirige déjà
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
        
        // Préparer les données
        const formData = collectFormData();
        
        // Envoyer au serveur
        submitOrder(formData);
    });
    
    function collectFormData() {
        const categories = ['a1', 'b1', 'c1', 'a2', 'b2', 'c2'];
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
                // Rediriger vers le paiement
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
