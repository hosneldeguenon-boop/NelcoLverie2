<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formulaire de Commande de Lavage</title>
    <link rel="stylesheet" href="commandes.css">
    <link rel="stylesheet" href="progressive-form.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>🧼 Formulaire de Commande de Lavage chez Nelco Laverie</h1>
        </header>

        <!-- BARRE DE PROGRESSION -->
        <div class="progress-bar-container">
            <div class="progress-bar" id="progressBar"></div>
            <div class="progress-text" id="progressText">Étape 1/5</div>
        </div>

        <!-- PROTOCOLE DE TRI (Collapsible) -->
        <section class="protocole">
            <button type="button" class="protocole-toggle" id="protocoleToggle">
                <span>📋 Voir le protocole de tri</span>
                <span class="toggle-icon">▼</span>
            </button>
            
            <div class="protocole-content" id="protocoleContent" style="display: none;">
                <div class="protocole-section">
                    <h3>📋 RÉSUMÉ DES TEMPÉRATURES</h3>
                    <ul>
                        <li><strong>FROID</strong> = Couleurs foncées, délicat, jeans, économie</li>
                        <li><strong>TIÈDE</strong> = Couleurs normales, serviettes, sportwear</li>
                        <li><strong>CHAUD</strong> = Blanc, très sale, hygiène (linge de maison)</li>
                    </ul>
                </div>

                <div class="protocole-section">
                    <h3>🔄 PROTOCOLE DE TRI ÉTAPE PAR ÉTAPE</h3>
                    
                    <h4>ÉTAPE 1 : SÉPARATION PAR COULEUR</h4>
                    <ul>
                        <li>Tas A → LINGE BLANC</li>
                        <li>Tas B → LINGE COULEUR CLAIRE</li>
                        <li>Tas C → LINGE COULEUR FONCÉE</li>
                    </ul>

                    <h4>ÉTAPE 2 : SÉPARATION PAR VOLUME</h4>
                    <div class="sous-section">
                        <p><strong>Sous-tas 1 → LINGE VOLUMINEUX</strong></p>
                        <ul>
                            <li>Draps et housses de couette</li>
                            <li>Serviettes de bain</li>
                            <li>Couvertures</li>
                            <li>Sweats et pulls épais</li>
                        </ul>
                    </div>

                    <div class="sous-section">
                        <p><strong>Sous-tas 2 → LINGE ORDINAIRE</strong></p>
                        <ul>
                            <li>T-shirts et hauts</li>
                            <li>Sous-vêtements</li>
                            <li>Chaussettes</li>
                            <li>Leggings et shorts</li>
                            <li>Chemises</li>
                        </ul>
                    </div>

                    <h4>ÉTAPE 3 : SÉPARATION PAR TEMPÉRATURE</h4>
                    <ul>
                        <li>Groupe FINAL 1 → LAVAGE CHAUD (50-60°C)</li>
                        <li>Groupe FINAL 2 → LAVAGE TIÈDE (30-40°C)</li>
                        <li>Groupe FINAL 3 → LAVAGE FROID (0-20°C)</li>
                    </ul>
                </div>
            </div>
        </section>

        <!-- FORMULAIRE -->
        <form id="commandeForm">
            
            <!-- ÉTAPE 1: INFORMATIONS CLIENT -->
            <section class="form-section step-section active" data-step="1">
                <h2>👤 Informations Client</h2>
                
                <div class="form-group">
                    <label for="nomClient">Nom complet <span class="required">*</span></label>
                    <input type="text" id="nomClient" name="nomClient" required>
                </div>

                <div class="form-group">
                    <label for="telephone">Numéro de téléphone <span class="required">*</span></label>
                    <input type="tel" id="telephone" name="telephone" required>
                </div>
            </section>

            <!-- ÉTAPE 2: ADRESSES -->
            <section class="form-section step-section" data-step="2">
                <h2>📍 Adresses de collecte et livraison</h2>
                
                <div class="form-group">
                    <label for="adresseCollecte">Adresse de collecte <span class="required">*</span></label>
                    <input type="text" id="adresseCollecte" name="adresseCollecte" required>
                </div>

                <div class="form-group">
                    <label for="communeCollecte">Commune de collecte <span class="required">*</span></label>
                    <select id="communeCollecte" name="communeCollecte" required>
                        <option value="">-- Sélectionnez --</option>
                        <option value="godomey">Godomey (500 FCFA)</option>
                        <option value="cotonou">Cotonou (1000 FCFA)</option>
                        <option value="calavi">Calavi (800 FCFA)</option>
                        <option value="autres">Autres zones (1500 FCFA)</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="dateCollecte">Date de collecte <span class="required">*</span></label>
                    <input type="date" id="dateCollecte" name="dateCollecte" required>
                </div>

                <div class="form-group">
                    <label for="adresseLivraison">Adresse de livraison <span class="required">*</span></label>
                    <input type="text" id="adresseLivraison" name="adresseLivraison" required>
                </div>

                <div class="form-group">
                    <label for="communeLivraison">Commune de livraison <span class="required">*</span></label>
                    <select id="communeLivraison" name="communeLivraison" required>
                        <option value="">-- Sélectionnez --</option>
                        <option value="godomey">Godomey (500 FCFA)</option>
                        <option value="cotonou">Cotonou (1000 FCFA)</option>
                        <option value="calavi">Calavi (800 FCFA)</option>
                        <option value="autres">Autres zones (1500 FCFA)</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="dateLivraison">Date de livraison <span class="required">*</span></label>
                    <input type="date" id="dateLivraison" name="dateLivraison" required>
                </div>
            </section>

            <!-- ÉTAPE 3: TYPE DE LINGE -->
            <section class="form-section step-section" data-step="3">
                <h2>⚖️ Quel type de linge souhaitez-vous laver ?</h2>
                <p class="instruction">✨ Cliquez sur les catégories qui vous concernent. Les champs de poids apparaîtront automatiquement.</p>

                <!-- BOUTONS TYPE DE LINGE -->
                <div class="linge-type-selector">
                    <button type="button" class="linge-type-card" id="btnVolumineux" data-type="volumineux">
                        <div class="card-icon">🛏️</div>
                        <div class="card-title">Linge Volumineux</div>
                        <div class="card-desc">Draps, couvertures, serviettes, pulls épais</div>
                    </button>

                    <button type="button" class="linge-type-card" id="btnOrdinaire" data-type="ordinaire">
                        <div class="card-icon">👕</div>
                        <div class="card-title">Linge Ordinaire</div>
                        <div class="card-desc">T-shirts, sous-vêtements, chemises, pantalons</div>
                    </button>
                </div>

                <!-- SECTION VOLUMINEUX (masquée par défaut) -->
                <div class="linge-category-section" id="volumineuxSection" style="display: none;">
                    <h3 class="category-title">🛏️ Linge Volumineux - Sélectionnez vos couleurs</h3>
                    
                    <div class="color-selector">
                        <button type="button" class="color-card" data-color="blanc" data-volume="volumineux">
                            <span class="color-icon">⚪</span>
                            <span>Blanc</span>
                        </button>
                        <button type="button" class="color-card" data-color="claire" data-volume="volumineux">
                            <span class="color-icon">🟡</span>
                            <span>Couleur Claire</span>
                        </button>
                        <button type="button" class="color-card" data-color="foncee" data-volume="volumineux">
                            <span class="color-icon">⚫</span>
                            <span>Couleur Foncée</span>
                        </button>
                    </div>

                    <!-- BLANC VOLUMINEUX -->
                    <div class="poids-group" id="blancVolumineux" style="display: none;">
                        <h4>⚪ Blanc Volumineux - Températures</h4>
                        <div class="temperature-grid">
                            <div class="temp-item">
                                <label>🔥 Chaud (50-60°C)</label>
                                <input type="number" name="a1_chaud" min="0" step="0.1" placeholder="0 kg">
                            </div>
                            <div class="temp-item">
                                <label>🌡️ Tiède (30-40°C)</label>
                                <input type="number" name="a1_tiede" min="0" step="0.1" placeholder="0 kg">
                            </div>
                            <div class="temp-item">
                                <label>❄️ Froid (0-20°C)</label>
                                <input type="number" name="a1_froid" min="0" step="0.1" placeholder="0 kg">
                            </div>
                        </div>
                    </div>

                    <!-- COULEUR CLAIRE VOLUMINEUX -->
                    <div class="poids-group" id="claireVolumineux" style="display: none;">
                        <h4>🟡 Couleur Claire Volumineux - Températures</h4>
                        <div class="temperature-grid">
                            <div class="temp-item">
                                <label>🔥 Chaud</label>
                                <input type="number" name="b1_chaud" min="0" step="0.1" placeholder="0 kg">
                            </div>
                            <div class="temp-item">
                                <label>🌡️ Tiède</label>
                                <input type="number" name="b1_tiede" min="0" step="0.1" placeholder="0 kg">
                            </div>
                            <div class="temp-item">
                                <label>❄️ Froid</label>
                                <input type="number" name="b1_froid" min="0" step="0.1" placeholder="0 kg">
                            </div>
                        </div>
                    </div>

                    <!-- COULEUR FONCÉE VOLUMINEUX -->
                    <div class="poids-group" id="fonceeVolumineux" style="display: none;">
                        <h4>⚫ Couleur Foncée Volumineux - Températures</h4>
                        <div class="temperature-grid">
                            <div class="temp-item">
                                <label>🔥 Chaud</label>
                                <input type="number" name="c1_chaud" min="0" step="0.1" placeholder="0 kg">
                            </div>
                            <div class="temp-item">
                                <label>🌡️ Tiède</label>
                                <input type="number" name="c1_tiede" min="0" step="0.1" placeholder="0 kg">
                            </div>
                            <div class="temp-item">
                                <label>❄️ Froid</label>
                                <input type="number" name="c1_froid" min="0" step="0.1" placeholder="0 kg">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SECTION ORDINAIRE (masquée par défaut) -->
                <div class="linge-category-section" id="ordinaireSection" style="display: none;">
                    <h3 class="category-title">👕 Linge Ordinaire - Sélectionnez vos couleurs</h3>
                    
                    <div class="color-selector">
                        <button type="button" class="color-card" data-color="blanc" data-volume="ordinaire">
                            <span class="color-icon">⚪</span>
                            <span>Blanc</span>
                        </button>
                        <button type="button" class="color-card" data-color="claire" data-volume="ordinaire">
                            <span class="color-icon">🟡</span>
                            <span>Couleur Claire</span>
                        </button>
                        <button type="button" class="color-card" data-color="foncee" data-volume="ordinaire">
                            <span class="color-icon">⚫</span>
                            <span>Couleur Foncée</span>
                        </button>
                    </div>

                    <!-- BLANC ORDINAIRE -->
                    <div class="poids-group" id="blancOrdinaire" style="display: none;">
                        <h4>⚪ Blanc Ordinaire - Températures</h4>
                        <div class="temperature-grid">
                            <div class="temp-item">
                                <label>🔥 Chaud</label>
                                <input type="number" name="a2_chaud" min="0" step="0.1" placeholder="0 kg">
                            </div>
                            <div class="temp-item">
                                <label>🌡️ Tiède</label>
                                <input type="number" name="a2_tiede" min="0" step="0.1" placeholder="0 kg">
                            </div>
                            <div class="temp-item">
                                <label>❄️ Froid</label>
                                <input type="number" name="a2_froid" min="0" step="0.1" placeholder="0 kg">
                            </div>
                        </div>
                    </div>

                    <!-- COULEUR CLAIRE ORDINAIRE -->
                    <div class="poids-group" id="claireOrdinaire" style="display: none;">
                        <h4>🟡 Couleur Claire Ordinaire - Températures</h4>
                        <div class="temperature-grid">
                            <div class="temp-item">
                                <label>🔥 Chaud</label>
                                <input type="number" name="b2_chaud" min="0" step="0.1" placeholder="0 kg">
                            </div>
                            <div class="temp-item">
                                <label>🌡️ Tiède</label>
                                <input type="number" name="b2_tiede" min="0" step="0.1" placeholder="0 kg">
                            </div>
                            <div class="temp-item">
                                <label>❄️ Froid</label>
                                <input type="number" name="b2_froid" min="0" step="0.1" placeholder="0 kg">
                            </div>
                        </div>
                    </div>

                    <!-- COULEUR FONCÉE ORDINAIRE -->
                    <div class="poids-group" id="fonceeOrdinaire" style="display: none;">
                        <h4>⚫ Couleur Foncée Ordinaire - Températures</h4>
                        <div class="temperature-grid">
                            <div class="temp-item">
                                <label>🔥 Chaud</label>
                                <input type="number" name="c2_chaud" min="0" step="0.1" placeholder="0 kg">
                            </div>
                            <div class="temp-item">
                                <label>🌡️ Tiède</label>
                                <input type="number" name="c2_tiede" min="0" step="0.1" placeholder="0 kg">
                            </div>
                            <div class="temp-item">
                                <label>❄️ Froid</label>
                                <input type="number" name="c2_froid" min="0" step="0.1" placeholder="0 kg">
                            </div>
                        </div>
                    </div>
                </div>

                <p class="help-text">💡 Astuce : Vous pouvez laisser vide les champs qui ne vous concernent pas</p>
            </section>

            <!-- ÉTAPE 4: MOYEN DE PAIEMENT -->
            <section class="form-section step-section" data-step="4">
                <h2>💳 Moyen de Paiement</h2>
                
                <div class="form-group">
                    <label>Choisissez votre moyen de paiement <span class="required">*</span></label>
                    <div class="radio-group">
                        <label class="radio-option">
                            <input type="radio" name="paiement" value="mtn" required>
                            <span>MTN Momo</span>
                        </label>
                        <label class="radio-option">
                            <input type="radio" name="paiement" value="moov">
                            <span>Moov Money</span>
                        </label>
                        <label class="radio-option">
                            <input type="radio" name="paiement" value="celtiis">
                            <span>Celtiis Money</span>
                        </label>
                        <label class="radio-option">
                            <input type="radio" name="paiement" value="livraison">
                            <span>Paiement à la livraison</span>
                        </label>
                    </div>
                </div>
            </section>

            <!-- ÉTAPE 5: RÉCAPITULATIF -->
            <section class="form-section step-section recap" data-step="5">
                <h2>💰 Récapitulatif des Prix</h2>
                
                <div class="prix-ligne">
                    <label>Prix lavage :</label>
                    <span><span id="prixLavageOutput">0</span> FCFA</span>
                </div>

                <div class="prix-ligne" id="reductionFidelite" style="display: none;">
                    <label>🎁 Réduction fidélité :</label>
                    <span><span>0</span> FCFA</span>
                </div>

                <div class="prix-ligne">
                    <label>Prix séchage :</label>
                    <span><span id="prixSechageOutput">0</span> FCFA</span>
                </div>

                <div class="prix-ligne">
                    <label>Prix pliage :</label>
                    <span><span id="prixPliageOutput">0</span> FCFA</span>
                </div>

                <div class="prix-ligne">
                    <label>Prix repassage :</label>
                    <span><span id="prixRepassageOutput">0</span> FCFA</span>
                </div>

                <div class="prix-ligne">
                    <label>Prix collecte/livraison :</label>
                    <span><span id="prixCollecteOutput">0</span> FCFA</span>
                </div>

                <div class="prix-ligne total">
                    <label><strong>Total à payer :</strong></label>
                    <span><strong><span id="totalPayerOutput">0</span> FCFA</strong></span>
                </div>
            </section>

            <!-- NAVIGATION BUTTONS -->
            <div class="form-navigation">
                <button type="button" class="btn-nav btn-prev" id="btnPrev" style="display: none;">
                    ← Précédent
                </button>
                <button type="button" class="btn-nav btn-next" id="btnNext">
                    Suivant →
                </button>
                <button type="submit" class="btn-principal" id="btnSubmit" style="display: none;">
                    Valider la commande
                </button>
            </div>
        </form>
    </div>

    <script src="commandes.js"></script>
    <script src="progressive-form.js"></script>
</body>
</html>