<?php
/**
 * ✅ TRAITEMENT COMMANDES - CYCLE FIDÉLITÉ 11 LAVAGES
 * Correction : Lit depuis points_counter, calcule tout côté serveur
 */

session_start();
header('Content-Type: application/json; charset=utf-8');
require_once 'config.php';

try {
    // Vérification session
    if (!isset($_SESSION['user_id'])) {
        throw new Exception('Utilisateur non connecté');
    }

    $userId = $_SESSION['user_id'];
    
    // Récupération données
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    if (!$data) {
        throw new Exception('Données invalides');
    }
    
    $requiredFields = [
        'nomClient', 'telephone', 'adresseCollecte', 'dateCollecte',
        'communeCollecte', 'adresseLivraison', 'dateLivraison',
        'communeLivraison', 'paiement', 'poids'
    ];
    
    foreach ($requiredFields as $field) {
        if (!isset($data[$field])) {
            throw new Exception("Le champ $field est requis");
        }
    }
    
    // Connexion BDD
    $conn = getDBConnection();
    $conn->beginTransaction();
    
    // ✅ Récupérer utilisateur avec points_counter
    $stmt = $conn->prepare("
        SELECT customer_code, points_counter 
        FROM users 
        WHERE id = ? 
        FOR UPDATE
    ");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();
    
    if (!$user) {
        throw new Exception('Utilisateur introuvable');
    }
    
    // ✅ points_counter = nombre de lavages
    $ancienNombreLavage = intval($user['points_counter']);
    
    // ============================================
    // GRILLE TARIFAIRE
    // ============================================
    $tarifs = [
        'froid' => [
            ['min' => 0, 'max' => 6, 'prix' => 2500],
            ['min' => 6, 'max' => 8, 'prix' => 3000],
            ['min' => 8, 'max' => 10, 'prix' => 5000]
        ],
        'tiede' => [
            ['min' => 0, 'max' => 6, 'prix' => 3000],
            ['min' => 6, 'max' => 8, 'prix' => 3500],
            ['min' => 8, 'max' => 10, 'prix' => 6000]
        ],
        'chaud' => [
            ['min' => 0, 'max' => 6, 'prix' => 3500],
            ['min' => 6, 'max' => 8, 'prix' => 4000],
            ['min' => 8, 'max' => 10, 'prix' => 7000]
        ]
    ];
    
    $tarifsCommunePrix = [
        'godomey' => 500,
        'cotonou' => 1000,
        'calavi' => 800,
        'autres' => 1500
    ];
    
    // ============================================
    // FONCTIONS DE CALCUL
    // ============================================
    function calculerPrixLavageVolumineux($poids, $temperature, $tarifs) {
        if ($poids <= 0) return ['prix' => 0, 'lav' => 0];
        
        $grille = $tarifs[$temperature];
        
        $prix10kg = 0;
        foreach ($grille as $tranche) {
            if (10 > $tranche['min'] && 10 <= $tranche['max']) {
                $prix10kg = $tranche['prix'];
                break;
            }
        }
        
        $prixTotal = 0;
        $poidsRestant = $poids;
        $lav = 0;
        
        while ($poidsRestant >= 10) {
            $prixPremierPartie = $prix10kg;
            $prixDeuxiemePartie = ceil($prix10kg * 0.55);
            
            $prixTotal += $prixPremierPartie + $prixDeuxiemePartie;
            $lav += 2;
            
            $poidsRestant -= 10;
        }
        
        if ($poidsRestant > 0) {
            if ($poidsRestant >= 9) {
                $prixPremierPartie = $prix10kg;
                $prixDeuxiemePartie = ceil($prix10kg * 0.55);
                $prixTotal += $prixPremierPartie + $prixDeuxiemePartie;
                $lav += 2;
            } else {
                $prixTotal += $prix10kg;
                $lav += 1;
            }
        }
        
        return ['prix' => $prixTotal, 'lav' => $lav];
    }
    
    function calculerPrixLavageOrdinaire($poids, $temperature, $tarifs) {
        if ($poids <= 0) return ['prix' => 0, 'lav' => 0];
        
        $grille = $tarifs[$temperature];
        $prixTotal = 0;
        $lav = 0;
        $poidsRestant = $poids;
        
        while ($poidsRestant > 0) {
            $poidsTraite = min($poidsRestant, 10);
            
            foreach ($grille as $tranche) {
                if ($poidsTraite > $tranche['min'] && $poidsTraite <= $tranche['max']) {
                    $prixTotal += $tranche['prix'];
                    $lav += 1;
                    break;
                }
            }
            
            $poidsRestant -= 10;
        }
        
        return ['prix' => $prixTotal, 'lav' => $lav];
    }
    
    function calculerPrixSechage($poids) {
        if ($poids <= 0) return 0;
        if ($poids <= 2) return 1000;
        if ($poids <= 3) return 1500;
        if ($poids <= 4) return 2000;
        if ($poids <= 6) return 2500;
        if ($poids <= 8) return 3000;
        return 3000 + calculerPrixSechage($poids - 8);
    }
    
    function calculerPrixPliage($poidsTotal) {
        if ($poidsTotal < 4) return 0;
        $quotient = floor($poidsTotal / 8);
        $reste = $poidsTotal % 8;
        $prix = $quotient * 500;
        if ($reste >= 4) $prix += 500;
        return $prix;
    }
    
    function calculerPrixRepassage($poidsVolumineux, $poidsOrdinaire) {
        $prixTotal = 0;
        if ($poidsVolumineux >= 4) {
            $prixTotal += floor($poidsVolumineux / 4) * 200;
        }
        if ($poidsOrdinaire >= 4) {
            $prixTotal += floor($poidsOrdinaire / 4) * 150;
        }
        return $prixTotal;
    }
    
    // ============================================
    // TRAITEMENT DES POIDS
    // ============================================
    $poidsData = $data['poids'];
    
    $categoriesVolumineux = ['a1', 'b1', 'c1'];
    $categoriesOrdinaire = ['a2', 'b2', 'c2'];
    $temperatures = ['chaud', 'tiede', 'froid'];
    
    $prixLavageTotal = 0;
    $lavTotal = 0;
    $poidsVolumineuxTotal = 0;
    $poidsOrdinaireTotal = 0;
    $poidsGrandTotal = 0;
    $detailsPoids = [];
    
    // VOLUMINEUX
    foreach ($categoriesVolumineux as $cat) {
        foreach ($temperatures as $temp) {
            $key = "{$cat}_{$temp}";
            $poids = floatval($poidsData[$key] ?? 0);
            
            if ($poids < 0 || $poids > 1000) {
                throw new Exception("Poids invalide pour $key");
            }
            
            if ($poids > 0) {
                $result = calculerPrixLavageVolumineux($poids, $temp, $tarifs);
                $prixLavageTotal += $result['prix'];
                $lavTotal += $result['lav'];
                $poidsVolumineuxTotal += $poids;
                $poidsGrandTotal += $poids;
                
                $detailsPoids[] = [
                    'categorie' => $key,
                    'poids' => $poids,
                    'temperature' => $temp,
                    'prix' => $result['prix'],
                    'lav' => $result['lav'],
                    'type' => 'volumineux'
                ];
            }
        }
    }
    
    // ORDINAIRE
    foreach ($categoriesOrdinaire as $cat) {
        foreach ($temperatures as $temp) {
            $key = "{$cat}_{$temp}";
            $poids = floatval($poidsData[$key] ?? 0);
            
            if ($poids < 0 || $poids > 1000) {
                throw new Exception("Poids invalide pour $key");
            }
            
            if ($poids > 0) {
                $result = calculerPrixLavageOrdinaire($poids, $temp, $tarifs);
                $prixLavageTotal += $result['prix'];
                $lavTotal += $result['lav'];
                $poidsOrdinaireTotal += $poids;
                $poidsGrandTotal += $poids;
                
                $detailsPoids[] = [
                    'categorie' => $key,
                    'poids' => $poids,
                    'temperature' => $temp,
                    'prix' => $result['prix'],
                    'lav' => $result['lav'],
                    'type' => 'ordinaire'
                ];
            }
        }
    }
    
    if ($prixLavageTotal == 0) {
        throw new Exception('Aucun linge à laver');
    }
    
    // ============================================
    // ✅ LOGIQUE FIDÉLITÉ - CYCLE DE 11 LAVAGES
    // ============================================
    $totalLavages = $ancienNombreLavage + $lavTotal;
    $nombreReductions = floor($totalLavages / 11);
    $nouveauNombreLavage = $totalLavages % 11;
    $reductionFidelite = $nombreReductions * 2500;
    
    // Appliquer réduction
    $prixLavageFinal = max(0, $prixLavageTotal - $reductionFidelite);
    
    // ============================================
    // AUTRES CALCULS
    // ============================================
    $prixSechage = calculerPrixSechage($poidsGrandTotal);
    $prixPliage = calculerPrixPliage($poidsGrandTotal);
    $prixRepassage = calculerPrixRepassage($poidsVolumineuxTotal, $poidsOrdinaireTotal);
    
    $commune1 = $data['communeCollecte'];
    $commune2 = $data['communeLivraison'];
    $prixCollecte = ($tarifsCommunePrix[$commune1] ?? 0) + ($tarifsCommunePrix[$commune2] ?? 0);
    
    $totalCommande = $prixLavageFinal + $prixSechage + $prixPliage + $prixRepassage + $prixCollecte;
    
    // ============================================
    // GÉNÉRATION NUMÉRO COMMANDE
    // ============================================
    $orderNumber = 'CMD-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
    
    // ============================================
    // PRÉPARATION ORDER_DETAILS
    // ============================================
    $orderDetails = [
        'nomClientSaisi' => cleanInput($data['nomClient']),
        'telephoneSaisi' => cleanInput($data['telephone']),
        'poids' => $poidsData,
        'detailsPoidsComplets' => $detailsPoids,
        'communeCollecte' => $commune1,
        'communeLivraison' => $commune2,
        'prixLavageBrut' => $prixLavageTotal,
        'prixSechage' => $prixSechage,
        'prixPliage' => $prixPliage,
        'prixRepassage' => $prixRepassage,
        'prixCollecte' => $prixCollecte,
        'reductionFidelite' => $reductionFidelite,
        'moyenPaiement' => cleanInput($data['paiement']),
        'ancienNombreLavage' => $ancienNombreLavage,
        'lavCommande' => $lavTotal,
        'nouveauNombreLavage' => $nouveauNombreLavage
    ];
    
    // ============================================
    // INSERTION COMMANDE
    // ============================================
    $stmt = $conn->prepare("
        INSERT INTO orders (
            user_id,
            order_number,
            customer_code,
            service_type,
            pickup_address,
            pickup_date,
            delivery_address,
            delivery_date,
            total_amount,
            washing_price,
            drying_price,
            folding_price,
            ironing_price,
            delivery_price,
            loyalty_discount,
            total_weight,
            order_details,
            payment_method,
            status,
            nombre_lavage_commande,
            nombre_lavage_avant,
            nombre_lavage_apres
        ) VALUES (
            ?, ?, ?, 'lavage_complet',
            ?, ?, ?, ?,
            ?, ?, ?, ?, ?, ?, ?, ?,
            ?, ?, 'pending', ?, ?, ?
        )
    ");
    
    $stmt->execute([
        $userId,
        $orderNumber,
        $user['customer_code'],
        cleanInput($data['adresseCollecte']),
        $data['dateCollecte'],
        cleanInput($data['adresseLivraison']),
        $data['dateLivraison'],
        $totalCommande,
        $prixLavageFinal,
        $prixSechage,
        $prixPliage,
        $prixRepassage,
        $prixCollecte,
        $reductionFidelite,
        $poidsGrandTotal,
        json_encode($orderDetails, JSON_UNESCAPED_UNICODE),
        cleanInput($data['paiement']),
        $lavTotal,
        $ancienNombreLavage,
        $nouveauNombreLavage
    ]);
    
    $orderId = $conn->lastInsertId();
    
    // Valider transaction
    $conn->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'Commande enregistrée avec succès',
        'orderId' => $orderId,
        'orderNumber' => $orderNumber,
        'debug' => [
            'ancienNombreLavage' => $ancienNombreLavage,
            'lavCommande' => $lavTotal,
            'totalLavages' => $totalLavages,
            'nombreReductions' => $nombreReductions,
            'nouveauNombreLavage' => $nouveauNombreLavage,
            'reductionFidelite' => $reductionFidelite,
            'prixLavageBrut' => $prixLavageTotal,
            'prixLavageFinal' => $prixLavageFinal
        ]
    ]);
    
} catch (Exception $e) {
    if (isset($conn) && $conn->inTransaction()) {
        $conn->rollBack();
    }
    
    error_log("Erreur process_order: " . $e->getMessage());
    
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>