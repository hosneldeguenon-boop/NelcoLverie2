<?php
/**
 * Affiche les logs de forgot_passwords.php en temps réel
 * Ouvrir: http://localhost/laverie/view_forgot_logs.php
 */
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logs Forgot Password</title>
    <style>
        body {
            font-family: 'Courier New', monospace;
            background: #1e1e1e;
            color: #d4d4d4;
            padding: 20px;
            margin: 0;
        }
        .container {
            max-width: 1400px;
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
            align-items: center;
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
            max-height: 700px;
            overflow-y: auto;
        }
        .success { color: #4ec9b0; }
        .error { color: #f48771; }
        .warning { color: #dcdcaa; }
        .info { color: #9cdcfe; }
        .highlight { background: #d73a49; color: white; padding: 2px 4px; border-radius: 2px; }
        .code-highlight { background: #007acc; color: white; padding: 2px 6px; border-radius: 2px; font-weight: bold; }
        .auto-refresh {
            background: #1a3a52;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 10px;
        }
        .empty {
            text-align: center;
            padding: 40px;
            color: #666;
            font-style: italic;
        }
        .split-view {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        .log-section {
            background: #252526;
            border-radius: 4px;
            padding: 15px;
        }
        .log-section h3 {
            color: #4ec9b0;
            margin-bottom: 10px;
            font-size: 16px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 Logs Forgot Password (Temps réel)</h1>
        
        <div class="auto-refresh">
            <label>
                <input type="checkbox" id="autoRefresh" checked>
                Actualisation automatique (3 secondes)
            </label>
            <span id="countdown"></span>
        </div>
        
        <div class="controls">
            <button class="btn" onclick="refresh()">🔄 Actualiser</button>
            <button class="btn btn-danger" onclick="clearLogs()">🗑️ Vider les logs</button>
            <button class="btn" onclick="scrollBottom()">⬇️ Bas</button>
            <button class="btn" onclick="testNow()">🧪 Tester maintenant</button>
        </div>
        
        <div class="split-view">
            <div class="log-section">
                <h3>📋 Logs Forgot Password</h3>
                <div class="logs" id="logs">Chargement...</div>
            </div>
            <div class="log-section">
                <h3>📧 Logs Email Config</h3>
                <div class="logs" id="emailLogs">Chargement...</div>
            </div>
        </div>
    </div>

    <script>
        let countdown = 3;

        function refresh() {
            // Charger les logs forgot_password
            fetch('?action=get_logs')
                .then(r => r.json())
                .then(data => {
                    const logsDiv = document.getElementById('logs');
                    
                    if (data.logs === '') {
                        logsDiv.innerHTML = '<div class="empty">Aucun log pour le moment.</div>';
                    } else {
                        let html = colorize(data.logs);
                        logsDiv.innerHTML = html;
                    }
                    
                    resetCountdown();
                })
                .catch(err => {
                    console.error('Erreur:', err);
                    document.getElementById('logs').innerHTML = 
                        '<span class="error">Erreur de chargement</span>';
                });

            // Charger les logs PHP (error_log)
            fetch('?action=get_error_logs')
                .then(r => r.json())
                .then(data => {
                    const emailLogsDiv = document.getElementById('emailLogs');
                    
                    if (data.logs === '') {
                        emailLogsDiv.innerHTML = '<div class="empty">Aucun log email.</div>';
                    } else {
                        let html = colorize(data.logs);
                        emailLogsDiv.innerHTML = html;
                    }
                })
                .catch(err => {
                    console.error('Erreur email logs:', err);
                });
        }

        function colorize(text) {
            let html = text;
            html = html.replace(/✅[^\n]*/g, '<span class="success">$&</span>');
            html = html.replace(/❌[^\n]*/g, '<span class="error">$&</span>');
            html = html.replace(/⚠️[^\n]*/g, '<span class="warning">$&</span>');
            html = html.replace(/ERREUR/gi, '<span class="highlight">ERREUR</span>');
            html = html.replace(/SUCCÈS/gi, '<span class="highlight">SUCCÈS</span>');
            html = html.replace(/Code[:\s]+(\d{6})/gi, 'Code: <span class="code-highlight">$1</span>');
            html = html.replace(/Email[:\s]+([^\s\n]+@[^\s\n]+)/gi, 'Email: <span class="info">$1</span>');
            return html;
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
            document.querySelectorAll('.logs').forEach(div => {
                div.scrollTop = div.scrollHeight;
            });
        }

        function testNow() {
            window.open('test_form.html', '_blank');
        }

        function resetCountdown() {
            countdown = 3;
        }

        function updateCountdown() {
            const countdownSpan = document.getElementById('countdown');
            countdownSpan.textContent = ` (${countdown}s)`;
            countdown--;
            
            if (countdown < 0) {
                if (document.getElementById('autoRefresh').checked) {
                    refresh();
                }
                countdown = 3;
            }
        }

        document.getElementById('autoRefresh').addEventListener('change', function() {
            if (this.checked) {
                refresh();
            }
        });

        // Démarrer
        refresh();
        setInterval(updateCountdown, 1000);
    </script>
</body>
</html>

<?php
// Gestion des actions
if (isset($_GET['action'])) {
    
    if ($_GET['action'] === 'get_logs') {
        $logFile = __DIR__ . '/forgot_password_debug.log';
        $logs = file_exists($logFile) ? file_get_contents($logFile) : '';
        
        // Limiter aux 1000 dernières lignes
        $lines = explode("\n", $logs);
        $lines = array_slice($lines, -1000);
        $logs = implode("\n", $lines);
        
        header('Content-Type: application/json');
        echo json_encode(['logs' => $logs]);
        exit;
    }
    
    if ($_GET['action'] === 'get_error_logs') {
        // Lire les logs PHP standards (où error_log écrit)
        $phpLog = ini_get('error_log');
        $logs = '';
        
        if ($phpLog && file_exists($phpLog)) {
            $logs = file_get_contents($phpLog);
            $lines = explode("\n", $logs);
            // Filtrer seulement les lignes avec email
            $lines = array_filter($lines, function($line) {
                return stripos($line, 'email') !== false || 
                       stripos($line, 'sendResetCode') !== false ||
                       stripos($line, 'PHPMailer') !== false;
            });
            $lines = array_slice($lines, -500);
            $logs = implode("\n", $lines);
        }
        
        header('Content-Type: application/json');
        echo json_encode(['logs' => $logs]);
        exit;
    }
    
    if ($_GET['action'] === 'clear_logs') {
        $logFile = __DIR__ . '/forgot_password_debug.log';
        if (file_exists($logFile)) {
            unlink($logFile);
        }
        header('Content-Type: application/json');
        echo json_encode(['success' => true]);
        exit;
    }
}
?>