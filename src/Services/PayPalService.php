<?php
namespace App\Services;

class PayPalService {
    private $config;
    private $clientId;
    private $secret;
    private $baseUrl;
    private $accessToken;
    
    public function __construct() {
        $this->config = require __DIR__ . '/../../config/paypal.php';
        $env = $this->config['environment'];
        
        $this->clientId = $this->config[$env]['client_id'];
        $this->secret = $this->config[$env]['secret'];
        $this->baseUrl = $this->config[$env]['base_url'];
    }
    
    /**
     * Obtenir un token d'accès OAuth2 de PayPal
     * 
     * @return string|null Token d'accès ou null en cas d'erreur
     */
    private function getAccessToken() {
        // Cache le token s'il existe déjà
        if ($this->accessToken) {
            return $this->accessToken;
        }
        
        $url = $this->baseUrl . '/v1/oauth2/token';
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, 'grant_type=client_credentials');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERPWD, $this->clientId . ':' . $this->secret);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Accept: application/json',
            'Accept-Language: en_US'
        ]);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            $this->log('TOKEN_ERROR', 'cURL error: ' . $error);
            return null;
        }
        
        if ($httpCode !== 200) {
            $this->log('TOKEN_ERROR', 'HTTP ' . $httpCode . ': ' . $response);
            return null;
        }
        
        $data = json_decode($response, true);
        
        if (isset($data['access_token'])) {
            $this->accessToken = $data['access_token'];
            return $this->accessToken;
        }
        
        return null;
    }
    
    /**
     * Créer une commande PayPal
     * 
     * @param array $data Données de la commande
     * @return array Réponse de l'API
     */
    public function createOrder($data) {
        $token = $this->getAccessToken();
        
        if (!$token) {
            return [
                'success' => false,
                'message' => 'Impossible d\'obtenir le token d\'accès PayPal'
            ];
        }
        
        $url = $this->baseUrl . '/v2/checkout/orders';
        
        // Préparer le payload pour PayPal
        $payload = [
            'intent' => 'CAPTURE',
            'purchase_units' => [
                [
                    'reference_id' => $data['transaction_id'],
                    'description' => $data['description'] ?? 'Contribution AfricAvenir',
                    'amount' => [
                        'currency_code' => $data['currency'] ?? $this->config['currency'],
                        'value' => number_format($data['amount'], 2, '.', '')
                    ]
                ]
            ],
            'application_context' => [
                'brand_name' => $this->config['brand_name'],
                'return_url' => $data['return_url'] ?? $this->config['return_url'],
                'cancel_url' => $data['cancel_url'] ?? $this->config['cancel_url'],
                'user_action' => 'PAY_NOW'
            ]
        ];
        
        $response = $this->makeRequest('POST', $url, $payload, $token);
        
        // Log la requête
        $this->log('CREATE_ORDER', [
            'transaction_id' => $data['transaction_id'],
            'amount' => $data['amount'],
            'response' => $response
        ]);
        
        return $response;
    }
    
    /**
     * Capturer un paiement PayPal
     * 
     * @param string $orderId ID de la commande PayPal
     * @return array Réponse de l'API
     */
    public function captureOrder($orderId) {
        $token = $this->getAccessToken();
        
        if (!$token) {
            return [
                'success' => false,
                'message' => 'Impossible d\'obtenir le token d\'accès PayPal'
            ];
        }
        
        $url = $this->baseUrl . '/v2/checkout/orders/' . $orderId . '/capture';
        
        $response = $this->makeRequest('POST', $url, [], $token);
        
        $this->log('CAPTURE_ORDER', [
            'order_id' => $orderId,
            'response' => $response
        ]);
        
        return $response;
    }
    
    /**
     * Vérifier le statut d'une commande PayPal
     * 
     * @param string $orderId ID de la commande
     * @return array Détails de la commande
     */
    public function getOrderDetails($orderId) {
        $token = $this->getAccessToken();
        
        if (!$token) {
            return [
                'success' => false,
                'message' => 'Impossible d\'obtenir le token d\'accès PayPal'
            ];
        }
        
        $url = $this->baseUrl . '/v2/checkout/orders/' . $orderId;
        
        $response = $this->makeRequest('GET', $url, null, $token);
        
        $this->log('GET_ORDER', [
            'order_id' => $orderId,
            'response' => $response
        ]);
        
        return $response;
    }
    
    /**
     * Faire une requête HTTP à l'API PayPal
     * 
     * @param string $method Méthode HTTP (GET, POST, etc.)
     * @param string $url URL complète
     * @param array|null $data Données à envoyer (null pour GET)
     * @param string $token Token d'accès
     * @return array Réponse décodée
     */
    private function makeRequest($method, $url, $data, $token) {
        $ch = curl_init($url);
        
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        
        $headers = [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $token
        ];
        
        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            if ($data !== null && !empty($data)) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            }
        }
        
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            return [
                'success' => false,
                'message' => 'Erreur cURL: ' . $error,
                'code' => 'CURL_ERROR'
            ];
        }
        
        $decoded = json_decode($response, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            return [
                'success' => false,
                'message' => 'Erreur de décodage JSON',
                'code' => 'JSON_ERROR',
                'raw_response' => $response
            ];
        }
        
        // Ajouter le code HTTP à la réponse
        $decoded['http_code'] = $httpCode;
        
        // Déterminer le succès basé sur le code HTTP
        if ($httpCode >= 200 && $httpCode < 300) {
            $decoded['success'] = true;
        } else {
            $decoded['success'] = false;
        }
        
        return $decoded;
    }
    
    /**
     * Logger une transaction (pour debug)
     * 
     * @param string $type Type de log
     * @param mixed $data Données à logger
     */
    public function log($type, $data) {
        $logDir = __DIR__ . '/../../logs';
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        
        $logFile = $logDir . '/paypal_' . date('Y-m-d') . '.log';
        $message = date('Y-m-d H:i:s') . ' [' . $type . '] ' . 
                   (is_array($data) ? json_encode($data) : $data) . PHP_EOL;
        
        file_put_contents($logFile, $message, FILE_APPEND);
    }
}
