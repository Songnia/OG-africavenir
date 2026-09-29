<?php 
ob_start();
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/src/Controllers/MemberController.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$success = false;
$error = null;
$contributionId = null;
$displayAmount = 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $inputAmount = (float) ($_POST['amount'] ?? 0);
    $motif = 'Ma contribution'; // Fixed motif as requested
    $mode = $_POST['mode_paiement'] ?? 'maketou';
    
    $firstName = trim($_POST['first_name'] ?? '');
    $lastName = trim($_POST['last_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $telephone = trim($_POST['telephone'] ?? '');
    
    if (
        empty($firstName)
        || empty($lastName)
        || empty($telephone)
        || ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL))
        || $inputAmount < 1000
    ) {
        $error = "Veuillez remplir correctement les champs obligatoires. Si une adresse email est renseignée, elle doit être valide. Le montant minimum est de 1 000 FCFA.";
    } else {
        $memberController = new \App\Controllers\MemberController();
        
        $guestData = [
            'first_name' => $firstName,
            'last_name'  => $lastName,
            'email'      => $email,
            'telephone'  => $telephone,
            'amount'     => $inputAmount,
            'motif'      => $motif,
            'mode_paiement' => $mode,
            'payment_provider' => 'maketou'
        ];

        $result = $memberController->initiateGuestDonation($guestData);

        if (!empty($result['success']) && !empty($result['payment_url'])) {
            // Store registration ID in session so the success page can retrieve it
            $_SESSION['guest_registration_id'] = $result['registration_id'] ?? null;
            header('Location: ' . $result['payment_url']);
            exit;
        } else {
            $error = $result['message'] ?? "Erreur lors de l'initialisation du paiement.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Faire une contribution | Fondation AfricAvenir International</title>
    <meta name="description" content="Engagez-vous et soutenez la Fondation AfricAvenir International en faisant une contribution libre et sans engagement.">
    <link rel="icon" href="assets/images/favicon.ico" type="image/x-icon">
    
    <!-- Polices Google Fonts élégantes inspirées de devenir-membre -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,600;0,700;1,400;1,600&family=Inter:wght@300;400;500;600;700&family=Playfair+Display:ital,wght@0,600;0,700;0,900;1,400;1,700&family=Space+Mono:wght@400;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --ink: #0e0c0a;
            --cream: #f6efe4;
            --cream-light: #fbf8f3;
            --parchment: #e8dcc8;
            --gold: #c9902a;
            --gold-light: #e8b84b;
            --terracotta: #b84c2b;
            --forest: #1e3a2f;
            --forest-light: #2d5441;
            --sand: #d4b896;
            --white: #ffffff;
            --border-subtle: #e6dfd5;
            --border-input: #d8cfc4;
            --text-muted: #6b6357;
            --danger: #b02a37;
            --danger-bg: #fdf2f2;
        }

        *, *::before, *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        html, body {
            min-height: 100%;
            background-color: var(--cream);
            color: var(--ink);
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            font-size: 15px;
            line-height: 1.5;
            -webkit-font-smoothing: antialiased;
        }

        /* ── SPLIT SCREEN LAYOUT ── */
        .split-layout {
            display: flex;
            flex-direction: row-reverse;
            min-height: 100vh;
            width: 100%;
            background-color: var(--cream);
        }

        /* ── SECTION GAUCHE : HERO ÉPINGLÉ (STICKY) ── */
        .split-hero {
            flex: 0 0 45%;
            position: sticky;
            top: 0;
            height: 100vh;
            background: linear-gradient(175deg, #f7f3ec 0%, #ebe2d4 100%);
            padding: 3.8rem 4rem 3rem 4.5rem;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            overflow-y: auto;
            border-right: 1px solid rgba(212, 184, 150, 0.45);
        }

        /* Halo et texture subtile */
        .split-hero::before {
            content: '';
            position: absolute;
            top: -20%;
            right: -20%;
            width: 520px;
            height: 520px;
            background: radial-gradient(circle, rgba(201, 144, 42, 0.12) 0%, rgba(246, 239, 228, 0) 70%);
            pointer-events: none;
            z-index: 0;
        }

        .split-hero::after {
            content: '';
            position: absolute;
            bottom: -15%;
            left: -15%;
            width: 420px;
            height: 420px;
            background: radial-gradient(circle, rgba(30, 58, 47, 0.07) 0%, rgba(246, 239, 228, 0) 70%);
            pointer-events: none;
            z-index: 0;
        }

        .hero-inner {
            position: relative;
            z-index: 1;
        }

        .hero-brand {
            margin-bottom: 3.5rem;
        }

        .hero-brand img {
            height: 56px;
            width: auto;
            max-width: 230px;
            object-fit: contain;
            display: block;
        }

        .hero-kicker {
            display: inline-flex;
            align-items: center;
            gap: 0.6rem;
            font-family: 'Space Mono', monospace;
            font-size: 0.68rem;
            letter-spacing: 0.24em;
            text-transform: uppercase;
            color: var(--terracotta);
            font-weight: 700;
            margin-bottom: 1.6rem;
        }

        .hero-kicker::before {
            content: '';
            width: 1.8rem;
            height: 1.5px;
            background: var(--terracotta);
            display: inline-block;
        }

        .hero-title {
            font-family: 'Playfair Display', Georgia, serif;
            font-size: clamp(2.4rem, 3.4vw, 3.6rem);
            font-weight: 900;
            line-height: 1.1;
            color: var(--ink);
            letter-spacing: -0.025em;
            margin-bottom: 1.8rem;
        }

        .hero-title em {
            font-style: italic;
            color: var(--gold);
            font-weight: 400;
            font-family: 'Cormorant Garamond', Georgia, serif;
            font-size: 1.15em;
        }

        .hero-subtitle {
            font-family: 'Cormorant Garamond', Georgia, serif;
            font-size: 1.45rem;
            color: rgba(14, 12, 10, 0.85);
            line-height: 1.65;
            margin-bottom: 2.5rem;
            max-width: 32rem;
        }

        .hero-trust-list {
            list-style: none;
            display: flex;
            flex-direction: column;
            gap: 0.9rem;
            margin-bottom: 2rem;
        }

        .hero-trust-item {
            display: flex;
            align-items: center;
            gap: 0.8rem;
            font-size: 0.92rem;
            color: var(--forest);
            font-weight: 500;
        }

        .hero-trust-icon {
            width: 22px;
            height: 22px;
            border-radius: 50%;
            background: rgba(30, 58, 47, 0.1);
            color: var(--forest);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.75rem;
            font-weight: bold;
            flex-shrink: 0;
        }

        .hero-footer-signature {
            position: relative;
            z-index: 1;
            padding-top: 2.2rem;
            border-top: 1px solid rgba(212, 184, 150, 0.5);
        }

        .signature-label {
            font-family: 'Space Mono', monospace;
            font-size: 0.65rem;
            letter-spacing: 0.22em;
            text-transform: uppercase;
            color: var(--text-muted);
            display: block;
            margin-bottom: 0.4rem;
        }

        .signature-headline {
            font-size: 0.95rem;
            font-weight: 700;
            color: var(--ink);
            letter-spacing: -0.01em;
        }

        /* ── SECTION DROITE : FORMULAIRE ── */
        .split-form-panel {
            flex: 1;
            background: var(--white);
            padding: 3.5rem 5rem 3rem 5rem;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            overflow-y: auto;
            min-height: 100vh;
        }

        .form-top-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2.8rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid var(--border-subtle);
        }

        .btn-back-link {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            text-decoration: none;
            color: var(--ink);
            font-family: 'Space Mono', monospace;
            font-size: 0.75rem;
            font-weight: 700;
            letter-spacing: 0.05em;
            transition: color 0.2s, transform 0.2s;
        }

        .btn-back-link:hover {
            color: var(--terracotta);
            transform: translateX(-3px);
        }

        .contact-pill {
            font-size: 0.78rem;
            color: var(--text-muted);
            text-decoration: none;
            font-family: 'Space Mono', monospace;
            transition: color 0.2s;
        }

        .contact-pill:hover {
            color: var(--forest);
        }

        .form-header-area {
            margin-bottom: 2rem;
        }

        .form-header-area h2 {
            font-family: 'Playfair Display', Georgia, serif;
            font-size: 2rem;
            font-weight: 800;
            color: var(--ink);
            line-height: 1.2;
            margin-bottom: 0.4rem;
        }

        .form-header-area p {
            color: var(--text-muted);
            font-size: 0.95rem;
        }

        /* Alerte d'erreur */
        .alert-error-box {
            padding: 1rem 1.25rem;
            background: var(--danger-bg);
            border: 1px solid rgba(176, 42, 55, 0.25);
            border-radius: 8px;
            color: var(--danger);
            margin-bottom: 1.8rem;
            font-size: 0.9rem;
            display: flex;
            align-items: flex-start;
            gap: 0.6rem;
        }

        /* Formulaire */
        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.2rem;
            margin-bottom: 1.4rem;
        }

        .col-full {
            grid-column: 1 / -1;
        }

        .form-field {
            display: flex;
            flex-direction: column;
            gap: 0.45rem;
        }

        .form-field label {
            font-size: 0.82rem;
            font-weight: 600;
            color: var(--ink);
            letter-spacing: 0.01em;
        }

        .required-star {
            color: var(--terracotta);
            margin-left: 2px;
        }

        .form-input {
            width: 100%;
            height: 48px;
            padding: 0 1rem;
            border: 1.5px solid var(--border-input);
            border-radius: 8px;
            background: #ffffff;
            font-size: 0.95rem;
            color: var(--ink);
            outline: none;
            transition: border-color 0.2s, box-shadow 0.2s;
            font-family: inherit;
        }

        .form-input:focus {
            border-color: var(--gold);
            box-shadow: 0 0 0 3px rgba(201, 144, 42, 0.15);
        }

        .form-input::placeholder {
            color: #a89f92;
            font-size: 0.9rem;
        }

        .field-hint {
            font-size: 0.75rem;
            color: var(--text-muted);
            margin-top: 0.15rem;
        }

        /* ── SECTION MONTANT AVEC CHIPS ── */
        .amount-selection-area {
            background: #faf7f2;
            border: 1px solid var(--border-subtle);
            border-radius: 12px;
            padding: 1.3rem;
            margin-top: 0.5rem;
            margin-bottom: 1.5rem;
        }

        .amount-chips-row {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            margin-top: 0.6rem;
            margin-bottom: 1rem;
        }

        .amount-chip {
            padding: 0.55rem 1rem;
            border: 1.5px solid var(--border-input);
            background: #ffffff;
            border-radius: 6px;
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--ink);
            cursor: pointer;
            transition: all 0.2s ease;
            font-family: 'Space Mono', monospace;
        }

        .amount-chip:hover {
            border-color: var(--gold);
            background: #fdfaf5;
        }

        .amount-chip.active {
            background: var(--forest);
            color: #ffffff;
            border-color: var(--forest);
        }

        .amount-input-wrap {
            position: relative;
            display: flex;
            align-items: center;
        }

        .amount-input-wrap .form-input {
            padding-right: 5rem;
            font-family: 'Space Mono', monospace;
            font-size: 1.15rem;
            font-weight: 700;
            letter-spacing: 0.05em;
            color: var(--forest);
        }

        .currency-badge {
            position: absolute;
            right: 1rem;
            font-family: 'Space Mono', monospace;
            font-size: 0.8rem;
            font-weight: 700;
            color: var(--gold);
            background: rgba(201, 144, 42, 0.12);
            padding: 0.25rem 0.6rem;
            border-radius: 4px;
            pointer-events: none;
        }

        /* ── SÉLECTEUR DE MODE DE PAIEMENT (Cartes style PetroSteps) ── */
        .payment-methods-title {
            font-size: 0.82rem;
            font-weight: 600;
            color: var(--ink);
            margin-bottom: 0.6rem;
        }

        .payment-cards-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
            margin-bottom: 1.8rem;
        }

        .payment-card-option {
            border: 1.5px solid var(--border-input);
            border-radius: 10px;
            padding: 1rem 1.1rem;
            cursor: pointer;
            transition: all 0.2s ease;
            position: relative;
            background: #ffffff;
            display: flex;
            flex-direction: column;
            gap: 0.35rem;
        }

        .payment-card-option:hover {
            border-color: var(--gold);
            background: #fcfbfa;
        }

        .payment-card-option.selected {
            border-color: var(--forest);
            background: rgba(30, 58, 47, 0.03);
            box-shadow: 0 0 0 2px var(--forest);
        }

        .payment-card-option .option-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .payment-card-option .option-title {
            font-size: 0.95rem;
            font-weight: 700;
            color: var(--ink);
        }

        .payment-card-option .option-icon {
            font-size: 1.25rem;
        }

        .payment-card-option .option-desc {
            font-size: 0.78rem;
            color: var(--text-muted);
            line-height: 1.35;
        }

        /* ── BOUTON SUBMIT NOIR / PILL ── */
        .btn-submit-pill {
            width: 100%;
            height: 54px;
            border: none;
            border-radius: 30px;
            background: var(--ink);
            color: #ffffff;
            font-family: 'Space Mono', monospace;
            font-size: 0.85rem;
            font-weight: 700;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            cursor: pointer;
            transition: background 0.2s, transform 0.15s, box-shadow 0.2s;
            box-shadow: 0 6px 18px rgba(14, 12, 10, 0.18);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.6rem;
        }

        .btn-submit-pill:hover {
            background: var(--forest);
            transform: translateY(-2px);
            box-shadow: 0 10px 24px rgba(30, 58, 47, 0.28);
        }

        .btn-submit-pill:active {
            transform: translateY(0);
        }

        .form-footer-caption {
            text-align: center;
            font-size: 0.78rem;
            color: var(--text-muted);
            margin-top: 1rem;
            line-height: 1.4;
        }

        /* ── RESPONSIVE DESIGN (Moyens écrans, Petits écrans & Très petits écrans) ── */
        
        /* 1. Moyens écrans (Tablettes & Écrans intermédiaires <= 1024px) */
        @media (max-width: 1024px) {
            .split-layout {
                flex-direction: column;
            }

            .split-form-panel {
                order: 1;
                flex: none;
                min-height: auto;
                padding: 2.8rem 2.2rem 2.2rem 2.2rem;
            }

            .split-hero {
                order: 2;
                flex: none;
                position: relative;
                top: auto;
                height: auto;
                overflow-y: visible;
                padding: 2.8rem 2.2rem;
                border-right: none;
                border-bottom: none;
                border-top: 1px solid var(--border-subtle);
            }

            .form-grid {
                grid-template-columns: 1fr;
            }

            .payment-cards-grid {
                grid-template-columns: 1fr;
            }
        }

        /* 2. Petits écrans (Smartphones classiques <= 768px) */
        @media (max-width: 768px) {
            .split-form-panel {
                padding: 2rem 1.5rem;
            }

            .split-hero {
                padding: 2.2rem 1.5rem;
            }

            .form-top-bar {
                margin-bottom: 1.8rem;
            }

            .form-header-area h2 {
                font-size: 1.75rem;
            }

            .hero-title {
                font-size: 2.2rem;
            }

            .hero-subtitle {
                font-size: 1.25rem;
            }
        }

        /* 3. Tout petits écrans (<= 480px) */
        @media (max-width: 480px) {
            .split-form-panel {
                padding: 1.5rem 1rem;
            }

            .split-hero {
                padding: 1.8rem 1rem;
            }

            .form-top-bar {
                margin-bottom: 1.3rem;
                gap: 0.5rem;
            }

            .btn-back-link {
                font-size: 0.7rem;
            }

            .contact-pill {
                font-size: 0.72rem;
            }

            .form-header-area h2 {
                font-size: 1.5rem;
            }

            .amount-selection-area {
                padding: 1rem 0.75rem;
            }

            .amount-chips-row {
                display: grid;
                grid-template-columns: repeat(3, 1fr);
                gap: 0.4rem;
            }

            .amount-chip {
                padding: 0.5rem 0.2rem;
                font-size: 0.78rem;
                text-align: center;
            }

            .btn-submit-pill {
                height: 50px;
                font-size: 0.78rem;
                letter-spacing: 0.05em;
                padding: 0 0.8rem;
            }

            .hero-brand img {
                max-width: 180px;
            }

            .hero-title {
                font-size: 1.85rem;
                line-height: 1.15;
            }

            .hero-kicker {
                font-size: 0.62rem;
                letter-spacing: 0.16em;
            }
        }
    </style>
</head>
<body>

    <div class="split-layout">

        <!-- ════════════ SECTION FORMULAIRE (Affichée en haut sur mobile) ════════════ -->
        <main class="split-form-panel">
            <div>
                <!-- Barre du haut -->
                <div class="form-top-bar">
                    <a href="devenir-membre.php" class="btn-back-link">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="19" y1="12" x2="5" y2="12"></line>
                            <polyline points="12 19 5 12 12 5"></polyline>
                        </svg>
                        Retour aux adhésions
                    </a>
                    <a href="mailto:secretaria@africavenirinternationnal.org" class="contact-pill">secretaria@africavenirinternationnal.org</a>
                </div>

                <div class="form-header-area">
                    <h2>Faire une contribution</h2>
                    <p>Soutenez nos programmes de recherche, d'éducation et de publication.</p>
                </div>

                <?php if ($error): ?>
                    <div class="alert-error-box">
                        <span style="font-size: 1.1rem; line-height: 1;">⚠️</span>
                        <div><?php echo htmlspecialchars($error); ?></div>
                    </div>
                <?php endif; ?>

                <form id="publicDonationForm" action="" method="post">
                    
                    <div class="form-grid">
                        <div class="form-field">
                            <label for="first_name">Prénom<span class="required-star">*</span></label>
                            <input 
                                type="text" 
                                id="first_name" 
                                name="first_name" 
                                class="form-input" 
                                required 
                                placeholder="Votre prénom"
                                value="<?php echo htmlspecialchars($_POST['first_name'] ?? ''); ?>"
                            >
                        </div>

                        <div class="form-field">
                            <label for="last_name">Nom<span class="required-star">*</span></label>
                            <input 
                                type="text" 
                                id="last_name" 
                                name="last_name" 
                                class="form-input" 
                                required 
                                placeholder="Votre nom de famille"
                                value="<?php echo htmlspecialchars($_POST['last_name'] ?? ''); ?>"
                            >
                        </div>

                        <div class="form-field">
                            <label for="telephone">Numéro de Téléphone<span class="required-star">*</span></label>
                            <input 
                                type="tel" 
                                id="telephone" 
                                name="telephone" 
                                class="form-input" 
                                required
                                placeholder="Ex: +237 6XX XXX XXX"
                                pattern="[+0-9\s\-]{8,20}"
                                value="<?php echo htmlspecialchars($_POST['telephone'] ?? ''); ?>"
                            >
                            <span class="field-hint">Format avec indicatif : +237 pour le Cameroun</span>
                        </div>

                        <div class="form-field">
                            <label for="email">Adresse Email</label>
                            <input 
                                type="email" 
                                id="email" 
                                name="email" 
                                class="form-input" 
                                placeholder="nom@exemple.com"
                                value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                            >
                            <span class="field-hint">Pour recevoir votre reçu officiel</span>
                        </div>
                    </div>

                    <!-- Choix du montant -->
                    <div class="amount-selection-area">
                        <label for="donationAmount" style="font-size: 0.85rem; font-weight: 700; color: var(--ink);">
                            Montant de votre contribution (F CFA)<span class="required-star">*</span>
                        </label>
                        
                        <div class="amount-chips-row">
                            <button type="button" class="amount-chip" data-val="2000">2 000</button>
                            <button type="button" class="amount-chip" data-val="5000">5 000</button>
                            <button type="button" class="amount-chip" data-val="10000">10 000</button>
                            <button type="button" class="amount-chip" data-val="25000">25 000</button>
                            <button type="button" class="amount-chip" data-val="50000">50 000</button>
                            <button type="button" class="amount-chip" data-val="100000">100 000</button>
                        </div>

                        <div class="amount-input-wrap">
                            <input
                                type="number"
                                id="donationAmount"
                                name="amount"
                                class="form-input"
                                min="1000"
                                step="500"
                                required
                                placeholder="Ou saisissez un montant libre..."
                                value="<?php echo htmlspecialchars($_POST['amount'] ?? ''); ?>"
                            >
                            <!--<span class="currency-badge">FCFA</span>-->
                        </div>
                        <!--<span class="field-hint" style="margin-top: 0.4rem; display: block;">Montant minimum : 1 000 FCFA</span>-->
                    </div>

                    <!-- Cartes de choix du moyen de paiement (Style PetroSteps) -->
                    <!--<div class="payment-methods-title">Moyen de paiement pris en charge</div>
                    <div class="payment-cards-grid">
                        <div class="payment-card-option selected" id="cardMobile">
                            <div class="option-header">
                                <span class="option-title">Mobile Money</span>
                                <span class="option-icon">📱</span>
                            </div>
                            <span class="option-desc">Orange Money, MTN MoMo et réseaux partenaires en Afrique centrale.</span>
                        </div>

                        <div class="payment-card-option" id="cardBank">
                            <div class="option-header">
                                <span class="option-title">Carte Bancaire</span>
                                <span class="option-icon">💳</span>
                            </div>
                            <span class="option-desc">Cartes Visa, Mastercard &amp; paiements internationaux sécurisés.</span>
                        </div>
                    </div>-->

                    <input type="hidden" name="motif" value="Ma contribution">
                    <input type="hidden" name="mode_paiement" id="modePaiementInput" value="maketou">

                    <!-- Bouton Submit Pill -->
                    <button type="submit" class="btn-submit-pill" id="submitDonationButton">
                        <span>Valider &amp; Procéder au Paiement</span>
                        <svg width="18" height="12" viewBox="0 0 16 10" fill="none">
                            <path d="M1 5h14M9 1l6 4-6 4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </button>

                    <p class="form-footer-caption">
                        🔒 Vos informations sont traitées de manière chiffrée et confidentielle.
                    </p>
                </form>
            </div>

            <footer style="margin-top: 2rem; font-size: 0.75rem; color: #a89f92; text-align: center;">
                © <?php echo date('Y'); ?> Fondation AfricAvenir International · Tous droits réservés
            </footer>
        </main>

        <!-- ════════════ SECTION HERO (Affichée à gauche sur desktop, en bas sur mobile) ════════════ -->
        <aside class="split-hero">
            <div class="hero-inner">
                <div class="hero-brand">
                    <img src="assets/logo-africavenir.png" alt="Fondation AfricAvenir International">
                </div>

                <div class="hero-kicker">FONDATION AFRICAVENIR INTERNATIONAL</div>

                <h1 class="hero-title">
                    Engagez-vous &amp;<br>
                    faites partie de<br>
                    cette <em>belle aventure</em>
                </h1>

                <p class="hero-subtitle">
                    Passionné·e de l'Afrique, de sa culture, de coopération internationale et de paix durable ? Devenez acteur·rice d'un projet panafricain vivant et engagé.
                </p>

                <!--<ul class="hero-trust-list">
                    <li class="hero-trust-item">
                        <span class="hero-trust-icon">✓</span>
                        <span>Contribution 100% sécurisée via Maketou</span>
                    </li>
                    <li class="hero-trust-item">
                        <span class="hero-trust-icon">✓</span>
                        <span>Sans engagement &amp; montant totalement libre</span>
                    </li>
                    <li class="hero-trust-item">
                        <span class="hero-trust-icon">✓</span>
                        <span>Reçu officiel généré et envoyé par e-mail</span>
                    </li>
                </ul>-->
            </div>

            <div class="hero-footer-signature">
                <span class="signature-label">Ensemble</span>
                <p class="signature-headline">Pour la renaissance Africaine</p>
            </div>
        </aside>

    </div>

    <!-- Scripts interactifs pour les montants et les sélecteurs de méthode -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const amountInput = document.getElementById('donationAmount');
            const chips = document.querySelectorAll('.amount-chip');
            const donationForm = document.getElementById('publicDonationForm');
            const submitButton = document.getElementById('submitDonationButton');
            const cardMobile = document.getElementById('cardMobile');
            const cardBank = document.getElementById('cardBank');

            // Clic sur les suggestions de montants
            chips.forEach(chip => {
                chip.addEventListener('click', function () {
                    chips.forEach(c => c.classList.remove('active'));
                    this.classList.add('active');
                    amountInput.value = this.getAttribute('data-val');
                });
            });

            // Si l'utilisateur tape manuellement un montant
            if (amountInput) {
                amountInput.addEventListener('input', function () {
                    const currentVal = this.value;
                    chips.forEach(chip => {
                        if (chip.getAttribute('data-val') === currentVal) {
                            chip.classList.add('active');
                        } else {
                            chip.classList.remove('active');
                        }
                    });
                });
            }

            // Sélection visuelle du mode de paiement
            if (cardMobile && cardBank) {
                cardMobile.addEventListener('click', () => {
                    cardMobile.classList.add('selected');
                    cardBank.classList.remove('selected');
                });
                cardBank.addEventListener('click', () => {
                    cardBank.classList.add('selected');
                    cardMobile.classList.remove('selected');
                });
            }

            // Gestion de l'état du bouton lors de la soumission
            if (donationForm && submitButton) {
                donationForm.addEventListener('submit', function () {
                    if (submitButton.disabled) {
                        return;
                    }
                    submitButton.disabled = true;
                    submitButton.style.opacity = '0.75';
                    submitButton.innerHTML = '<span>Connexion sécurisée en cours...</span>';
                });
            }
        });
    </script>
</body>
</html>
