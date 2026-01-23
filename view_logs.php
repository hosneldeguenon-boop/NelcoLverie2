<?php
/**
 * Affiche les logs PHP en temps réel
 * Ouvrir: http://localhost/laverie/view_logs.php
 */
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logs de débogage</title>
    <style>
        body {
            font-family: 'Courier New', monospace;
            background: #1e1e1e;
            color: #d4d4d4;
            padding: 20px;
            margin: 0;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        h1 {
            color: #4ec9b0;
            border-bottom: 2px solid #4ec9b0;
            padding-bottom: 10px;
        }
        .controls {
            margin: 20px 0;
            display: flex;
            gap: 10px;
        }
        .btn {
            padding: 10px 20px;
            background: #007acc;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .btn:hover { background: #005a9e; }
        .btn-danger { background: #d73a49; }
        .btn-danger:hover { background: #b52a3a; }
        .logs {
            background: #252526;
            padding: 20px;
            border-radius: 4px;
            white-space: pre-wrap;
            font-size: 13px;
            line-height: 1.6;
            overflow-x: auto;
            max-height: 600px;
            overflow-y: auto;
        }
        .success { color: #4ec9b0; }
        .error { color: #f48771; }
        .warning { color: #dcdcaa; }
        .info { color: #9cdcfe; }
        .highlight { background: #d73a49; color: white; padding: 2px 4px; }
        .auto-refresh {
            background: #1a3a52;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 Logs de débogage</h1>
        
        <div class="auto-refresh">
            <label>
                <input type="checkbox" id="autoRefresh" checked>
                Actualisation automatique (5 secondes)
            </label>
            <span id="countdown"></span>
        </div>
        
        <div class="controls">
            <button class="btn" onclick="refresh()">🔄 Actualiser</button>
            <button class="btn" onclick="clearLogs()">🗑️ Vider les logs</button>
            <button class="btn" onclick="scrollBottom()">⬇️ Bas</button>
        </div>
        
        <div class="logs" id="logs">Chargement...</div>
    </div>

    <script>
        let autoRefreshInterval;
        let countdownInterval;
        let countdown = 5;

        function refresh() {
            fetch('?action=get_logs')
                .then(r => r.json())
                .then(data => {
                    const logsDiv = document.getElementById('logs');
                    
                    if (data.logs === '') {
                        logsDiv.innerHTML = '<span class="info">Aucun log pour le moment.</span>';
                    } else {
                        // Coloriser les logs
                        let html = data.logs;
                        html = html.replace(/✅[^\n]*/g, '<span class="success">$&</span>');
                        html = html.replace(/❌[^\n]*/g, '<span class="error">$&</span>');
                        html = html.replace(/⚠️[^\n]*/g, '<span class="warning">$&</span>');
                        html = html.replace(/ERREUR/g, '<span class="highlight">ERREUR</span>');
                        html = html.replace(/SUCCÈS/g, '<span class="highlight">SUCCÈS</span>');
                        html = html.replace(/Code: (\d{6})/g, 'Code: <span class="highlight">$1</span>');
                        
                        logsDiv.innerHTML = html;
                    }
                    
                    resetCountdown();
                })
                .catch(err => {
                    console.error('Erreur:', err);
                    document.getElementById('logs').innerHTML = 
                        '<span class="error">Erreur de chargement des logs</span>';
                });
        }

        function clearLogs() {
            if (confirm('Vider tous les logs ?')) {
                fetch('?action=clear_logs')
                    .then(() => {
                        refresh();
                        alert('Logs vidés');
                    });
            }
        }

        function scrollBottom() {
            const logsDiv = document.getElementById('logs');
            logsDiv.scrollTop = logsDiv.scrollHeight;
        }

        function resetCountdown() {
            countdown = 5;
        }

        function updateCountdown() {
            const countdownSpan = document.getElementById('countdown');
            countdownSpan.textContent = ` (${countdown}s)`;
            countdown--;
            
            if (countdown < 0) {
                if (document.getElementById('autoRefresh').checked) {
                    refresh();
                }
                countdown = 5;
            }
        }

        document.getElementById('autoRefresh').addEventListener('change', function() {
            if (this.checked) {
                refresh();
            }
        });

        // Démarrer
        refresh();
        countdownInterval = setInterval(updateCountdown, 1000);
    </script>
</body>
</html>

<?php
// Gestion des actions
if (isset($_GET['action'])) {
    $logFile = __DIR__ . '/debug.log';
    
    if ($_GET['action'] === 'get_logs') {
        $logs = file_exists($logFile) ? file_get_contents($logFile) : '';
        
        // Limiter aux 500 dernières lignes
        $lines = explode("\n", $logs);
        $lines = array_slice($lines, -500);
        $logs = implode("\n", $lines);
        
        header('Content-Type: application/json');
        echo json_encode(['logs' => $logs]);
        exit;
    }
    
    if ($_GET['action'] === 'clear_logs') {
        if (file_exists($logFile)) {
            unlink($logFile);
        }
        header('Content-Type: application/json');
        echo json_encode(['success' => true]);
        exit;
    }
}
?>