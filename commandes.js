// ============================================
// GRILLE TARIFAIRE COMPLÈTE
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
                console.log('Nombre de lavages chargé:', userNombreLavage);
                calculerPrixTotal();
            } else {
                console.error('Erreur chargement nombre de lavages:', data.message);
                userNombreLavage = 0;
            }
        })
        .catch(error => {
            console.error('Erreur:', error);
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
    
    if (reste >= 4) {
        prix += 500;
    }
    
    return prix;
}

// ============================================
// CALCUL DU PRIX DE REPASSAGE
// ============================================
function calculerPrixRepassage(poidsVolumineux, poidsOrdinaire) {
    let prixTotal = 0;
    
    if (poidsVolumineux >= 4) {
        const tranchesVolumineux = Math.floor(poidsVolumineux / 4);
        prixTotal += tranchesVolumineux * 200;
    }
    
    if (poidsOrdinaire >= 4) {
        const tranchesOrdinaire = Math.floor(poidsOrdinaire / 4);
        prixTotal += tranchesOrdinaire * 150;
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
        const prixPremierPartie = prix10kg;
        const prixDeuxiemePartie = Math.ceil(prix10kg * 0.55);
        
        prixTotal += prixPremierPartie + prixDeuxiemePartie;
        lav += 2;
        
        poidsRestant -= 10;
    }
    
    if (poidsRestant > 0) {
        if (poidsRestant >= 9) {
            const prixPremierPartie = prix10kg;
            const prixDeuxiemePartie = Math.ceil(prix10kg * 0.55);
            prixTotal += prixPremierPartie + prixDeuxiemePartie;
            lav += 2;
        } else {
            prixTotal += prix10kg;
            lav += 1;
        }
    }
    
    return { prix: prixTotal, lav: lav };
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
        let poidsTraite = Math.min(poidsRestant, 10);
        
        for (let tranche of grille) {
            if (poidsTraite > tranche.min && poidsTraite <= tranche.max) {
                prixTotal += tranche.prix;
                lav += 1;
                break;
            }
        }
        
        poidsRestant -= 10;
    }
    
    return { prix: prixTotal, lav: lav };
}

// ============================================
// ✅ FONCTION PRINCIPALE - CYCLE DE 11 LAVAGES
// ============================================
function calculerPrixTotal() {
    const form = document.getElementById('commandeForm');
    const formData = new FormData(form);
    
    let prixLavageTotal = 0;
    let lavTotal = 0;
    let poidsVolumineuxTotal = 0;
    let poidsOrdinaireTotal = 0;
    let poidsGrandTotal = 0;
    const detailsPoids = [];
    
    const poidsFieldsVolumineux = [
        { name: 'a1_chaud', temp: 'chaud', label: 'Blanc Volumineux Chaud' },
        { name: 'a1_tiede', temp: 'tiede', label: 'Blanc Volumineux Tiède' },
        { name: 'a1_froid', temp: 'froid', label: 'Blanc Volumineux Froid' },
        { name: 'b1_chaud', temp: 'chaud', label: 'Couleur Claire Volumineux Chaud' },
        { name: 'b1_tiede', temp: 'tiede', label: 'Couleur Claire Volumineux Tiède' },
        { name: 'b1_froid', temp: 'froid', label: 'Couleur Claire Volumineux Froid' },
        { name: 'c1_chaud', temp: 'chaud', label: 'Couleur Foncée Volumineux Chaud' },
        { name: 'c1_tiede', temp: 'tiede', label: 'Couleur Foncée Volumineux Tiède' },
        { name: 'c1_froid', temp: 'froid', label: 'Couleur Foncée Volumineux Froid' }
    ];
    
    const poidsFieldsOrdinaire = [
        { name: 'a2_chaud', temp: 'chaud', label: 'Blanc Ordinaire Chaud' },
        { name: 'a2_tiede', temp: 'tiede', label: 'Blanc Ordinaire Tiède' },
        { name: 'a2_froid', temp: 'froid', label: 'Blanc Ordinaire Froid' },
        { name: 'b2_chaud', temp: 'chaud', label: 'Couleur Claire Ordinaire Chaud' },
        { name: 'b2_tiede', temp: 'tiede', label: 'Couleur Claire Ordinaire Tiède' },
        { name: 'b2_froid', temp: 'froid', label: 'Couleur Claire Ordinaire Froid' },
        { name: 'c2_chaud', temp: 'chaud', label: 'Couleur Foncée Ordinaire Chaud' },
        { name: 'c2_tiede', temp: 'tiede', label: 'Couleur Foncée Ordinaire Tiède' },
        { name: 'c2_froid', temp: 'froid', label: 'Couleur Foncée Ordinaire Froid' }
    ];
    
    // Calculer linge VOLUMINEUX
    poidsFieldsVolumineux.forEach(field => {
        const poids = parseFloat(formData.get(field.name)) || 0;
        if (poids > 0) {
            const result = calculerPrixLavageVolumineux(poids, field.temp);
            prixLavageTotal += result.prix;
            lavTotal += result.lav;
            
            poidsVolumineuxTotal += poids;
            poidsGrandTotal += poids;
            detailsPoids.push({
                label: field.label,
                poids: poids,
                temperature: field.temp,
                prix: result.prix,
                lav: result.lav,
                type: 'volumineux'
            });
        }
    });
    
    // Calculer linge ORDINAIRE
    poidsFieldsOrdinaire.forEach(field => {
        const poids = parseFloat(formData.get(field.name)) || 0;
        if (poids > 0) {
            const result = calculerPrixLavageOrdinaire(poids, field.temp);
            prixLavageTotal += result.prix;
            lavTotal += result.lav;
            
            poidsOrdinaireTotal += poids;
            poidsGrandTotal += poids;
            detailsPoids.push({
                label: field.label,
                poids: poids,
                temperature: field.temp,
                prix: result.prix,
                lav: result.lav,
                type: 'ordinaire'
            });
        }
    });
    
    console.log('Prix lavage AVANT réduction:', prixLavageTotal);
    console.log('lav total:', lavTotal);
    console.log('nombre_lavage client:', userNombreLavage);
    
    // ============================================
    // ✅ LOGIQUE FIDÉLITÉ - CYCLE DE 11 LAVAGES
    // ============================================
    const totalLavages = userNombreLavage + lavTotal;
    const nombreReductions = Math.floor(totalLavages / 11);
    const nouveauNombreLavage = totalLavages % 11;
    const reductionFidelite = nombreReductions * 2500;
    
    console.log('Total lavages:', totalLavages);
    console.log('Nombre réductions:', nombreReductions);
    console.log('Nouveau nombre_lavage:', nouveauNombreLavage);
    console.log('Réduction appliquée:', reductionFidelite);
    
    // Appliquer la réduction sur le prix de lavage
    const prixLavageFinal = Math.max(0, prixLavageTotal - reductionFidelite);
    
    console.log('Prix lavage APRÈS réduction:', prixLavageFinal);
    
    // Autres calculs
    const prixSechage = calculerPrixSechage(poidsGrandTotal);
    const prixPliage = calculerPrixPliage(poidsGrandTotal);
    const prixRepassage = calculerPrixRepassage(poidsVolumineuxTotal, poidsOrdinaireTotal);
    
    const commune1 = formData.get('communeCollecte');
    const commune2 = formData.get('communeLivraison');
    const prixCollecte = (tarifsCommunePrix[commune1] || 0) + (tarifsCommunePrix[commune2] || 0);
    
    const total = prixLavageFinal + prixSechage + prixPliage + prixRepassage + prixCollecte;
    
    // Mise à jour affichage
    document.getElementById('prixLavageOutput').textContent = prixLavageTotal.toLocaleString();
    document.getElementById('prixSechageOutput').textContent = prixSechage.toLocaleString();
    document.getElementById('prixPliageOutput').textContent = prixPliage.toLocaleString();
    document.getElementById('prixRepassageOutput').textContent = prixRepassage.toLocaleString();
    document.getElementById('prixCollecteOutput').textContent = prixCollecte.toLocaleString();
    document.getElementById('totalPayerOutput').textContent = total.toLocaleString();
    
    const reductionElement = document.getElementById('reductionFidelite');
    if (reductionElement) {
        if (reductionFidelite > 0) {
            reductionElement.style.display = 'flex';
            const spanElement = reductionElement.querySelector('span:nth-child(2)');
            if (spanElement) {
                spanElement.textContent = '-' + reductionFidelite.toLocaleString();
            }
        } else {
            reductionElement.style.display = 'none';
        }
    }
    
    return {
        prixLavage: prixLavageTotal,
        prixLavageFinal: prixLavageFinal,
        prixSechage: prixSechage,
        prixPliage: prixPliage,
        prixRepassage: prixRepassage,
        prixCollecte: prixCollecte,
        total: total,
        detailsPoids: detailsPoids,
        poidsTotal: poidsGrandTotal,
        reductionFidelite: reductionFidelite,
        nombreLavageClient: userNombreLavage,
        lav: lavTotal,
        nouveauNombreLavage: nouveauNombreLavage
    };
}

// ============================================
// INITIALISATION
// ============================================
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('commandeForm');
    
    loadUserPoints();
    
    const today = new Date().toISOString().split('T')[0];
    
    const dateCollecte = document.getElementById('dateCollecte');
    const dateLivraison = document.getElementById('dateLivraison');
    
    if (dateCollecte) {
        dateCollecte.setAttribute('min', today);
    }
    
    if (dateLivraison) {
        dateLivraison.setAttribute('min', today);
    }
    
    if (dateCollecte && dateLivraison) {
        dateCollecte.addEventListener('change', function() {
            const collecteDate = this.value;
            if (collecteDate) {
                const nextDay = new Date(collecteDate);
                nextDay.setDate(nextDay.getDate() + 1);
                const minDeliveryDate = nextDay.toISOString().split('T')[0];
                dateLivraison.setAttribute('min', minDeliveryDate);
                
                if (dateLivraison.value && dateLivraison.value <= collecteDate) {
                    dateLivraison.value = '';
                }
            }
        });
    }
    
    form.addEventListener('input', calculerPrixTotal);
    form.addEventListener('change', calculerPrixTotal);
    
    calculerPrixTotal();
    
    // ✅ SOUMISSION DU FORMULAIRE - REDIRECTION CORRIGÉE
    form.addEventListener('submit', function(event) {
        event.preventDefault();
        
        const formData = new FormData(form);
        const prix = calculerPrixTotal();
        
        if (prix.prixLavage === 0 && prix.reductionFidelite === 0) {
            alert('Veuillez renseigner au moins un poids de linge.');
            return;
        }
        
        const aujourdhui = new Date();
        aujourdhui.setHours(0, 0, 0, 0);
        
        const dateCollecteValue = formData.get('dateCollecte');
        const dateLivraisonValue = formData.get('dateLivraison');
        
        if (!dateCollecteValue || !dateLivraisonValue) {
            alert('Veuillez renseigner les dates de collecte et de livraison.');
            return;
        }
        
        const dateCollecte = new Date(dateCollecteValue);
        const dateLivraison = new Date(dateLivraisonValue);
        
        if (dateCollecte < aujourdhui) {
            alert('La date de collecte ne peut pas être antérieure à la date du jour.');
            return;
        }
        
        if (dateLivraison < aujourdhui) {
            alert('La date de livraison ne peut pas être antérieure à la date du jour.');
            return;
        }
        
        if (dateLivraison <= dateCollecte) {
            alert('La date de livraison doit être après la date de collecte.');
            return;
        }
        
        const orderData = {
            nomClient: formData.get('nomClient'),
            telephone: formData.get('telephone'),
            adresseCollecte: formData.get('adresseCollecte'),
            dateCollecte: formData.get('dateCollecte'),
            communeCollecte: formData.get('communeCollecte'),
            adresseLivraison: formData.get('adresseLivraison'),
            dateLivraison: formData.get('dateLivraison'),
            communeLivraison: formData.get('communeLivraison'),
            poids: {},
            detailsPoids: JSON.stringify(prix.detailsPoids),
            poidsTotal: prix.poidsTotal,
            nombreLavageClient: prix.nombreLavageClient,
            lav: prix.lav,
            paiement: formData.get('paiement')
        };
        
        const poidsFields = [
            'a1_chaud', 'a1_tiede', 'a1_froid',
            'a2_chaud', 'a2_tiede', 'a2_froid',
            'b1_chaud', 'b1_tiede', 'b1_froid',
            'b2_chaud', 'b2_tiede', 'b2_froid',
            'c1_chaud', 'c1_tiede', 'c1_froid',
            'c2_chaud', 'c2_tiede', 'c2_froid'
        ];
        
        poidsFields.forEach(field => {
            const value = parseFloat(formData.get(field)) || 0;
            orderData.poids[field] = value;
        });
        
        fetch('process_order.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(orderData)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // ✅ Redirection avec le moyen de paiement sélectionné
                const paymentUrl = `payment.php?orderId=${data.orderId}&orderNumber=${encodeURIComponent(data.orderNumber)}&method=${orderData.paiement}`;
                window.location.href = paymentUrl;
            } else {
                alert('Erreur : ' + data.message);
            }
        })
        .catch(error => {
            console.error('Erreur:', error);
            alert('Une erreur est survenue lors de l\'enregistrement de la commande.');
        });
    });
});