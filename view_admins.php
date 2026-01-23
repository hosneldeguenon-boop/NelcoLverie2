<?php
/**
 * Page de visualisation des administrateurs
 * Accessible uniquement aux administrateurs connectés
 */

session_start();

// Vérifier si l'admin est connecté
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: admin_login.php");
    exit();
}

require_once 'config.php';

// Récupérer les statistiques
try {
    $conn = getDBConnection();
    
    // Total des admins
    $stmt = $conn->query("SELECT COUNT(*) as total FROM admins");
    $totalAdmins = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Admins actifs
    $stmt = $conn->query("SELECT COUNT(*) as total FROM admins WHERE status = 'actif'");
    $activeAdmins = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Admins suspendus
    $stmt = $conn->query("SELECT COUNT(*) as total FROM admins WHERE status = 'suspendu'");
    $suspendedAdmins = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Admins inactifs
    $stmt = $conn->query("SELECT COUNT(*) as total FROM admins WHERE status = 'inactif'");
    $inactiveAdmins = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
} catch (Exception $e) {
    error_log("Erreur récupération stats: " . $e->getMessage());
    $totalAdmins = $activeAdmins = $suspendedAdmins = $inactiveAdmins = 0;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Administrateurs - Nelco Laverie</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
        }

        /* Header */
        .header {
            background: white;
            padding: 20px 30px;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }

        .header h1 {
            color: #333;
            font-size: 28px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .header h1 i {
            color: #667eea;
        }

        .header-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102,126,234,0.4);
        }

        .btn-secondary {
            background: #6c757d;
            color: white;
        }

        .btn-secondary:hover {
            background: #5a6268;
            transform: translateY(-2px);
        }

        /* Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            display: flex;
            align-items: center;
            gap: 20px;
            transition: transform 0.3s;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
        }

        .stat-icon.purple {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .stat-icon.green {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            color: white;
        }

        .stat-icon.orange {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
        }

        .stat-icon.gray {
            background: linear-gradient(135deg, #6c757d 0%, #495057 100%);
            color: white;
        }

        .stat-info h3 {
            font-size: 32px;
            color: #333;
            margin-bottom: 5px;
        }

        .stat-info p {
            color: #666;
            font-size: 14px;
        }

        /* Filters */
        .filters {
            background: white;
            padding: 20px 25px;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }

        .filters-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            align-items: end;
        }

        .filter-group {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .filter-group label {
            font-size: 14px;
            font-weight: 500;
            color: #333;
        }

        .filter-group input,
        .filter-group select {
            padding: 10px 15px;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.3s;
        }

        .filter-group input:focus,
        .filter-group select:focus {
            outline: none;
            border-color: #667eea;
        }

        .btn-filter {
            padding: 10px 20px;
            background: #667eea;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s;
            align-self: end;
        }

        .btn-filter:hover {
            background: #5568d3;
        }

        .btn-reset {
            padding: 10px 20px;
            background: #6c757d;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s;
            align-self: end;
        }

        .btn-reset:hover {
            background: #5a6268;
        }

        /* Table */
        .table-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow: hidden;
        }

        .table-header {
            padding: 20px 25px;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }

        .table-header h2 {
            color: #333;
            font-size: 20px;
        }

        .table-actions {
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .search-box {
            position: relative;
        }

        .search-box input {
            padding: 8px 35px 8px 15px;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            font-size: 14px;
            min-width: 250px;
        }

        .search-box i {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #999;
        }

        .table-wrapper {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        thead {
            background: #f8f9fa;
        }

        th {
            padding: 15px;
            text-align: left;
            font-weight: 600;
            color: #333;
            font-size: 14px;
            white-space: nowrap;
        }

        td {
            padding: 15px;
            border-top: 1px solid #e5e7eb;
            font-size: 14px;
            color: #666;
        }

        tbody tr {
            transition: background 0.2s;
        }

        tbody tr:hover {
            background: #f8f9fa;
        }

        .status-badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            display: inline-block;
        }

        .status-actif {
            background: #d4edda;
            color: #155724;
        }

        .status-inactif {
            background: #f8d7da;
            color: #721c24;
        }

        .status-suspendu {
            background: #fff3cd;
            color: #856404;
        }

        .gender-badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .gender-M {
            background: #d1ecf1;
            color: #0c5460;
        }

        .gender-F {
            background: #f8d7da;
            color: #721c24;
        }

        .action-buttons {
            display: flex;
            gap: 8px;
        }

        .btn-action {
            padding: 6px 12px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 12px;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .btn-view {
            background: #17a2b8;
            color: white;
        }

        .btn-view:hover {
            background: #138496;
        }

        .btn-edit {
            background: #ffc107;
            color: #333;
        }

        .btn-edit:hover {
            background: #e0a800;
        }

        .btn-suspend {
            background: #dc3545;
            color: white;
        }

        .btn-suspend:hover {
            background: #c82333;
        }

        .btn-activate {
            background: #28a745;
            color: white;
        }

        .btn-activate:hover {
            background: #218838;
        }

        /* Pagination */
        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
            padding: 20px;
            flex-wrap: wrap;
        }

        .pagination button {
            padding: 8px 15px;
            border: 2px solid #667eea;
            background: white;
            color: #667eea;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s;
        }

        .pagination button:hover:not(:disabled) {
            background: #667eea;
            color: white;
        }

        .pagination button:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .pagination button.active {
            background: #667eea;
            color: white;
        }

        .loading {
            text-align: center;
            padding: 40px;
            color: #666;
        }

        .spinner {
            border: 3px solid #f3f3f3;
            border-top: 3px solid #667eea;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
            margin: 20px auto;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #666;
        }

        .empty-state i {
            font-size: 64px;
            color: #ccc;
            margin-bottom: 20px;
        }

        .empty-state h3 {
            font-size: 20px;
            margin-bottom: 10px;
        }

        /* Modal */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            animation: fadeIn 0.3s;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .modal-content {
            background: white;
            margin: 5% auto;
            padding: 30px;
            border-radius: 15px;
            max-width: 600px;
            max-height: 80vh;
            overflow-y: auto;
            animation: slideDown 0.3s;
        }

        @keyframes slideDown {
            from {
                transform: translateY(-50px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #e5e7eb;
        }

        .modal-header h2 {
            color: #333;
            font-size: 24px;
        }

        .close-modal {
            font-size: 28px;
            cursor: pointer;
            color: #999;
            transition: color 0.3s;
        }

        .close-modal:hover {
            color: #333;
        }

        .modal-body {
            color: #666;
        }

        .info-row {
            display: flex;
            padding: 15px 0;
            border-bottom: 1px solid #f0f0f0;
        }

        .info-row:last-child {
            border-bottom: none;
        }

        .info-label {
            font-weight: 600;
            color: #333;
            min-width: 150px;
        }

        .info-value {
            color: #666;
            flex: 1;
        }

        /* Responsive */
        @media (max-width: 1024px) {
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 768px) {
            body {
                padding: 10px;
            }

            .header {
                padding: 15px 20px;
            }

            .header h1 {
                font-size: 22px;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }

            .filters-row {
                grid-template-columns: 1fr;
            }

            .search-box input {
                min-width: 100%;
            }

            .table-wrapper {
                overflow-x: scroll;
            }

            table {
                min-width: 800px;
            }

            .action-buttons {
                flex-direction: column;
            }

            .modal-content {
                margin: 10% 10px;
                padding: 20px;
            }

            .info-row {
                flex-direction: column;
                gap: 5px;
            }

            .info-label {
                min-width: auto;
            }
        }

        @media (max-width: 480px) {
            .header h1 {
                font-size: 18px;
            }

            .btn {
                padding: 8px 15px;
                font-size: 13px;
            }

            .stat-card {
                padding: 20px;
            }

            .stat-icon {
                width: 50px;
                height: 50px;
                font-size: 24px;
            }

            .stat-info h3 {
                font-size: 24px;
            }

            th, td {
                padding: 10px;
                font-size: 13px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1>
                <i class="fas fa-user-shield"></i>
                Gestion des Administrateurs
            </h1>
            <div class="header-actions">
                <a href="admin_dashboard.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i>
                    Retour au Dashboard
                </a>
                <button onclick="refreshData()" class="btn btn-primary">
                    <i class="fas fa-sync-alt"></i>
                    Actualiser
                </button>
            </div>
        </div>

        <!-- Stats -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon purple">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo $totalAdmins; ?></h3>
                    <p>Total Administrateurs</p>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon green">
                    <i class="fas fa-user-check"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo $activeAdmins; ?></h3>
                    <p>Admins Actifs</p>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon orange">
                    <i class="fas fa-user-slash"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo $suspendedAdmins; ?></h3>
                    <p>Admins Suspendus</p>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon gray">
                    <i class="fas fa-user-times"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo $inactiveAdmins; ?></h3>
                    <p>Admins Inactifs</p>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="filters">
            <div class="filters-row">
                <div class="filter-group">
                    <label>Statut</label>
                    <select id="filterStatus">
                        <option value="">Tous les statuts</option>
                        <option value="actif">Actif</option>
                        <option value="inactif">Inactif</option>
                        <option value="suspendu">Suspendu</option>
                    </select>
                </div>

                <div class="filter-group">
                    <label>Sexe</label>
                    <select id="filterGender">
                        <option value="">Tous</option>
                        <option value="M">Masculin</option>
                        <option value="F">Féminin</option>
                    </select>
                </div>

                <div class="filter-group">
                    <label>Recherche</label>
                    <input type="text" id="filterSearch" placeholder="Nom, prénom, username, email...">
                </div>

                <button class="btn-filter" onclick="applyFilters()">
                    <i class="fas fa-filter"></i> Filtrer
                </button>

                <button class="btn-reset" onclick="resetFilters()">
                    <i class="fas fa-redo"></i> Réinitialiser
                </button>
            </div>
        </div>

        <!-- Table -->
        <div class="table-container">
            <div class="table-header">
                <h2>Liste des Administrateurs</h2>
                <div class="table-actions">
                    <div class="search-box">
                        <input type="text" id="quickSearch" placeholder="Recherche rapide...">
                        <i class="fas fa-search"></i>
                    </div>
                </div>
            </div>

            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nom Complet</th>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Téléphone</th>
                            <th>Sexe</th>
                            <th>Statut</th>
                            <th>Inscrit le</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="adminsTableBody">
                        <tr>
                            <td colspan="9" class="loading">
                                <div class="spinner"></div>
                                <p>Chargement des administrateurs...</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="pagination" id="paginationContainer"></div>
        </div>
    </div>

    <!-- Modal Détails -->
    <div id="detailsModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2><i class="fas fa-user-shield"></i> Détails Administrateur</h2>
                <span class="close-modal" onclick="closeModal()">&times;</span>
            </div>
            <div class="modal-body" id="modalBody">
                <!-- Contenu dynamique -->
            </div>
        </div>
    </div>

    <script>
        let currentPage = 1;
        let adminsData = [];
        let filteredData = [];
        const itemsPerPage = 10;

        // Charger les données au démarrage
        document.addEventListener('DOMContentLoaded', function() {
            loadAdmins();

            // Recherche rapide
            document.getElementById('quickSearch').addEventListener('input', function(e) {
                const searchTerm = e.target.value.toLowerCase();
                filterDataQuick(searchTerm);
            });
        });

        // Charger les administrateurs
        async function loadAdmins() {
            try {
                const response = await fetch('get_admins.php');
                const data = await response.json();

                if (data.success) {
                    adminsData = data.admins;
                    filteredData = adminsData;
                    displayAdmins();
                } else {
                    showError(data.message || 'Erreur de chargement');
                }
            } catch (error) {
                console.error('Erreur:', error);
                showError('Erreur de connexion au serveur');
            }
        }

        // Afficher les administrateurs
        function displayAdmins() {
            const tbody = document.getElementById('adminsTableBody');
            
            if (filteredData.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="9" class="empty-state">
                            <i class="fas fa-users-slash"></i>
                            <h3>Aucun administrateur trouvé</h3>
                            <p>Essayez de modifier vos critères de recherche</p>
                        </td>
                    </tr>
                `;
                document.getElementById('paginationContainer').innerHTML = '';
                return;
            }

            const startIndex = (currentPage - 1) * itemsPerPage;
            const endIndex = startIndex + itemsPerPage;
            const pageData = filteredData.slice(startIndex, endIndex);

            tbody.innerHTML = pageData.map(admin => `
                <tr>
                    <td><strong>#${admin.id}</strong></td>
                    <td><strong>${admin.firstname} ${admin.lastname}</strong></td>
                    <td>${admin.username}</td>
                    <td>${admin.email}</td>
                    <td>${admin.phone}</td>
                    <td>
                        <span class="gender-badge gender-${admin.gender}">
                            <i class="fas fa-${admin.gender === 'M' ? 'mars' : 'venus'}"></i>
                            ${admin.gender === 'M' ? 'Masculin' : 'Féminin'}
                        </span>
                    </td>
                    <td>
                        <span class="status-badge status-${admin.status}">
                            ${admin.status.charAt(0).toUpperCase() + admin.status.slice(1)}
                        </span>
                    </td>
                    <td>${formatDate(admin.created_at)}</td>
                    <td>
                        <div class="action-buttons">
                            <button class="btn-action btn-view" onclick="viewAdmin(${admin.id})">
                                <i class="fas fa-eye"></i> Voir
                            </button>
                            ${admin.status === 'actif' ? `
                                <button class="btn-action btn-suspend" onclick="changeStatus(${admin.id}, 'suspendu')">
                                    <i class="fas fa-ban"></i> Suspendre
                                </button>
                            ` : `
                                <button class="btn-action btn-activate" onclick="changeStatus(${admin.id}, 'actif')">
                                    <i class="fas fa-check"></i> Activer
                                </button>
                            `}
                        </div>
                    </td>
                </tr>
            `).join('');

            renderPagination();
        }

        // Pagination
        function renderPagination() {
            const totalPages = Math.ceil(filteredData.length / itemsPerPage);
            const container = document.getElementById('paginationContainer');

            if (totalPages <= 1) {
                container.innerHTML = '';
                return;
            }

            let html = `
                <button onclick="changePage(${currentPage - 1})" ${currentPage === 1 ? 'disabled' : ''}>
                    <i class="fas fa-chevron-left"></i>
                </button>
            `;

            for (let i = 1; i <= totalPages; i++) {
                if (i === 1 || i === totalPages || (i >= currentPage - 2 && i <= currentPage + 2)) {
                    html += `
                        <button onclick="changePage(${i})" class="${i === currentPage ? 'active' : ''}">
                            ${i}
                        </button>
                    `;
                } else if (i === currentPage - 3 || i === currentPage + 3) {
                    html += '<span>...</span>';
                }
            }

            html += `
                <button onclick="changePage(${currentPage + 1})" ${currentPage === totalPages ? 'disabled' : ''}>
                    <i class="fas fa-chevron-right"></i>
                </button>
            `;

            container.innerHTML = html;
        }

        function changePage(page) {
            const totalPages = Math.ceil(filteredData.length / itemsPerPage);
            if (page >= 1 && page <= totalPages) {
                currentPage = page;
                displayAdmins();
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }
        }

        // Filtres
        function applyFilters() {
            const status = document.getElementById('filterStatus').value;
            const gender = document.getElementById('filterGender').value;
            const search = document.getElementById('filterSearch').value.toLowerCase();

            filteredData = adminsData.filter(admin => {
                const matchStatus = !status || admin.status === status;
                const matchGender = !gender || admin.gender === gender;
                const matchSearch = !search || 
                    admin.firstname.toLowerCase().includes(search) ||
                    admin.lastname.toLowerCase().includes(search) ||
                    admin.username.toLowerCase().includes(search) ||
                    admin.email.toLowerCase().includes(search) ||
                    admin.phone.includes(search);

                return matchStatus && matchGender && matchSearch;
            });

            currentPage = 1;
            displayAdmins();
        }

        function filterDataQuick(searchTerm) {
            if (!searchTerm) {
                filteredData = adminsData;
            } else {
                filteredData = adminsData.filter(admin =>
                    admin.firstname.toLowerCase().includes(searchTerm) ||
                    admin.lastname.toLowerCase().includes(searchTerm) ||
                    admin.username.toLowerCase().includes(searchTerm) ||
                    admin.email.toLowerCase().includes(searchTerm) ||
                    admin.phone.includes(searchTerm) ||
                    admin.id.toString().includes(searchTerm)
                );
            }
            currentPage = 1;
            displayAdmins();
        }

        function resetFilters() {
            document.getElementById('filterStatus').value = '';
            document.getElementById('filterGender').value = '';
            document.getElementById('filterSearch').value = '';
            document.getElementById('quickSearch').value = '';
            filteredData = adminsData;
            currentPage = 1;
            displayAdmins();
        }

        // Voir détails
        async function viewAdmin(id) {
            const admin = adminsData.find(a => a.id === id);
            if (!admin) return;

            const modalBody = document.getElementById('modalBody');
            modalBody.innerHTML = `
                <div class="info-row">
                    <div class="info-label"><i class="fas fa-id-badge"></i> ID</div>
                    <div class="info-value">#${admin.id}</div>
                </div>
                <div class="info-row">
                    <div class="info-label"><i class="fas fa-user"></i> Nom complet</div>
                    <div class="info-value">${admin.firstname} ${admin.lastname}</div>
                </div>
                <div class="info-row">
                    <div class="info-label"><i class="fas fa-at"></i> Username</div>
                    <div class="info-value">${admin.username}</div>
                </div>
                <div class="info-row">
                    <div class="info-label"><i class="fas fa-envelope"></i> Email</div>
                    <div class="info-value">${admin.email}</div>
                </div>
                <div class="info-row">
                    <div class="info-label"><i class="fas fa-phone"></i> Téléphone</div>
                    <div class="info-value">${admin.phone}</div>
                </div>
                <div class="info-row">
                    <div class="info-label"><i class="fas fa-venus-mars"></i> Sexe</div>
                    <div class="info-value">
                        <span class="gender-badge gender-${admin.gender}">
                            <i class="fas fa-${admin.gender === 'M' ? 'mars' : 'venus'}"></i>
                            ${admin.gender === 'M' ? 'Masculin' : 'Féminin'}
                        </span>
                    </div>
                </div>
                <div class="info-row">
                    <div class="info-label"><i class="fas fa-toggle-on"></i> Statut</div>
                    <div class="info-value">
                        <span class="status-badge status-${admin.status}">
                            ${admin.status.charAt(0).toUpperCase() + admin.status.slice(1)}
                        </span>
                    </div>
                </div>
                <div class="info-row">
                    <div class="info-label"><i class="fas fa-calendar-plus"></i> Inscrit le</div>
                    <div class="info-value">${formatDate(admin.created_at)}</div>
                </div>
                <div class="info-row">
                    <div class="info-label"><i class="fas fa-calendar-check"></i> Dernière mise à jour</div>
                    <div class="info-value">${formatDate(admin.updated_at)}</div>
                </div>
            `;

            document.getElementById('detailsModal').style.display = 'block';
        }

        // Changer le statut
        async function changeStatus(id, newStatus) {
            const admin = adminsData.find(a => a.id === id);
            if (!admin) return;

            const action = newStatus === 'actif' ? 'activer' : 'suspendre';
            if (!confirm(`Voulez-vous vraiment ${action} l'administrateur ${admin.username} ?`)) {
                return;
            }

            try {
                const response = await fetch('update_admin_status.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        admin_id: id,
                        status: newStatus
                    })
                });

                const data = await response.json();

                if (data.success) {
                    alert(`Administrateur ${action === 'activer' ? 'activé' : 'suspendu'} avec succès`);
                    refreshData();
                } else {
                    alert(data.message || 'Erreur lors du changement de statut');
                }
            } catch (error) {
                console.error('Erreur:', error);
                alert('Erreur de connexion au serveur');
            }
        }

        function closeModal() {
            document.getElementById('detailsModal').style.display = 'none';
        }

        // Fermer modal en cliquant en dehors
        window.onclick = function(event) {
            const modal = document.getElementById('detailsModal');
            if (event.target === modal) {
                modal.style.display = 'none';
            }
        }

        // Actualiser les données
        function refreshData() {
            currentPage = 1;
            loadAdmins();
        }

        // Formater les dates
        function formatDate(dateString) {
            const date = new Date(dateString);
            return date.toLocaleDateString('fr-FR', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        }

        // Afficher une erreur
        function showError(message) {
            const tbody = document.getElementById('adminsTableBody');
            tbody.innerHTML = `
                <tr>
                    <td colspan="9" class="empty-state">
                        <i class="fas fa-exclamation-circle"></i>
                        <h3>Erreur</h3>
                        <p>${message}</p>
                        <button class="btn btn-primary" onclick="refreshData()" style="margin-top: 15px;">
                            <i class="fas fa-sync-alt"></i> Réessayer
                        </button>
                    </td>
                </tr>
            `;
        }
    </script>
</body>
</html>