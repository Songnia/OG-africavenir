<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>503 - Service Indisponible | AfricAvenir</title>
    <link rel="stylesheet" href="../css/styles.css">
    <style>
        .error-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            text-align: center;
            padding: 20px;
            background-color: var(--bg-primary);
        }
        
        .error-content {
            max-width: 600px;
            background: var(--bg-secondary);
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 4px 6px var(--shadow-color);
            position: relative;
            z-index: 2;
        }
        
        .error-illustration {
            position: fixed;
            bottom: 0;
            right: 0;
            max-width: 500px;
            width: 40vw;
            opacity: 0.9;
            z-index: 1;
            pointer-events: none;
        }
        
        .error-code {
            font-size: 8em;
            font-weight: bold;
            color: var(--color-warning);
            margin: 0;
            line-height: 1;
        }
        
        .error-title {
            font-size: 2em;
            color: var(--text-primary);
            margin: 20px 0 10px;
        }
        
        .error-message {
            font-size: 1.1em;
            color: var(--text-secondary);
            margin-bottom: 30px;
            line-height: 1.6;
        }
        
        .error-actions {
            display: flex;
            gap: 15px;
            justify-content: center;
            flex-wrap: wrap;
        }
        
        .error-logo {
            width: 150px;
            margin-bottom: 30px;
        }
        
        .btn {
            padding: 12px 24px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s;
        }
        
        .btn-primary {
            background-color: var(--africavenir-yellow);
            color: #fff;
        }
        
        .btn-primary:hover {
            background-color: var(--africavenir-yellow-darker);
        }
        
        .btn-secondary {
            background-color: transparent;
            color: var(--text-primary);
            border: 2px solid var(--border-primary);
        }
        
        .btn-secondary:hover {
            border-color: var(--africavenir-yellow);
            color: var(--africavenir-yellow);
        }
        
        @media (max-width: 768px) {
            .error-illustration {
                max-width: 300px;
                width: 50vw;
            }
        }
    </style>
</head>
<body>
    <div class="error-container">
        <img src="../assets/errors/503.png" alt="503 Illustration" class="error-illustration">
        <div class="error-content">
            <img src="../assets/logo-africavenir.png" alt="AfricAvenir Logo" class="error-logo">
            <h1 class="error-code">503</h1>
            <h2 class="error-title">Maintenance en Cours</h2>
            <p class="error-message">
                Le site est actuellement en maintenance pour améliorer votre expérience.
                Nous serons de retour très bientôt. Merci de votre patience !
            </p>
            <div class="error-actions">
                <a href="javascript:location.reload()" class="btn btn-primary">Réessayer</a>
            </div>
        </div>
    </div>
</body>
</html>
