<?php
namespace App\Services;

class CinetPayService {
    private $config;
    private $apikey;
    private $site_id;
    private $secret_key;
    private $base_url;
    
    public function __construct() {
        $this->config = require __DIR__ . '/../../config/cinetpay.php';
        $env = $this->config['environment'];
        
        $this->apikey = $this->config[$env]['apikey'];
        $this->site_id = $this->config[$env]['site_id'];
        $this->secret_key = $this->config[$env]['secret_key'];
        $this->base_url = $this->config[$env]['base_url'];
    }
    
    /**
     * Initier un paiement
     * 
     * @param array $data Données du paiement
     * @return array Réponse de l'API
     */
    public function initiatePayment($data) {
        $payload = [
            'apikey' => $this->apikey,
            'site_id' => $this->site_id,
            'transaction_id' => $data['transaction_id'],
            'amount' => $data['amount'],
            'currency' => $this->config['currency'],
            'description' => $data['description'] ?? 'Contribution AfricAvenir',
            'customer_id' => $data['customer_id'] ?? '',
            'customer_name' => $data['customer_name'] ?? 'Membre',
            'customer_surname' => $data['customer_surname'] ?? 'AfricAvenir',
            'customer_email' => $data['customer_email'] ?? 'contact@africavenir.cm',
            'customer_phone_number' => $data['customer_phone'] ?? '237655000001', // Default sandbox number
            'customer_address' => $data['customer_address'] ?? 'Cameroun',
            'customer_city' => $data['customer_city'] ?? 'Douala',
            'customer_country' => 'CM', // Code pays Cameroun
            'customer_state' => 'CM',
            'customer_zip_code' => '00000',
            'notify_url' => $this->config['notify_url'],
            'return_url' => $data['return_url'] ?? $this->config['return_url'], // Use custom return URL if provided
            'channels' => 'ALL', // ALL, MOBILE_MONEY, CREDIT_CARD, etc.
            'metadata' => $data['metadata'] ?? '',
            'lang' => 'fr', // Langue de l'interface de paiement
        ];
        
        $response = $this->makeRequest('payment', $payload);
        
        // Log the request and response for debugging
        $this->log('PAYMENT_INIT', [
            'transaction_id' => $data['transaction_id'],
            'amount' => $data['amount'],
            'response' => $response
        ]);
        
        return $response;
    }
    
    /**
     * Vérifier le statut d'un paiement
     * 
     * @param string $transaction_id ID de la transaction
     * @return array Statut du paiement
     */
    public function checkPaymentStatus($transaction_id) {
        $payload = [
            'apikey' => $this->apikey,
            'site_id' => $this->site_id,
            'transaction_id' => $transaction_id,
        ];
        
        $response = $this->makeRequest('payment/check', $payload);

        // Standardize response
        if (isset($response['code']) && $response['code'] == '00') {
             // CinetPay success code is '00'
             return [
                 'code' => '00',
                 'message' => 'Succès',
                 'data' => [
                     'status' => 'ACCEPTED', // Normalize to ACCEPTED
                     'amount' => $response['data']['amount'],
                     'currency' => $response['data']['currency'],
                     'metadata' => $response['data']['metadata'] ?? null
                 ]
             ];
        }

        return $response;
    }
    
    /**
     * Faire une requête HTTP à l'API CinetPay
     * 
     * @param string $endpoint Point de terminaison de l'API
     * @param array $data Données à envoyer
     * @return array Réponse décodée
     */
    private function makeRequest($endpoint, $data) {
        $url = $this->base_url . $endpoint;
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Accept: application/json'
        ]);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            return [
                'success' => false,
                'message' => 'Erreur cURL: ' . $error,
                'code' => 'ERROR'
            ];
        }
        
        if ($httpCode !== 200) {
            return [
                'success' => false,
                'message' => 'Erreur de communication avec CinetPay',
                'http_code' => $httpCode,
                'code' => 'HTTP_ERROR'
            ];
        }
        
        $decoded = json_decode($response, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            return [
                'success' => false,
                'message' => 'Erreur de décodage JSON',
                'code' => 'JSON_ERROR'
            ];
        }
        
        return $decoded;
    }
    
    /**
     * Vérifier la signature de notification (sécurité HMAC)
     * 
     * @param array $headers Headers de la requête (pour x-token)
     * @param array $postData Données POST
     * @return bool True si la signature est valide
     */
    public function verifyHmacToken($headers, $postData) {
        // Find x-token case-insensitively
        $xToken = null;
        foreach ($headers as $key => $value) {
            if (strtolower($key) === 'x-token') {
                $xToken = $value;
                break;
            }
        }

        if (!$xToken) {
            $this->log('HMAC_ERROR', 'Missing x-token header');
            return false;
        }

        // La chaîne à signer doit respecter l'ordre exact imposé par CinetPay
        // Source: https://docs.cinetpay.com/api/1.0-fr/checkout/hmac
        $dataString = ($postData['cpm_site_id'] ?? '') .
                      ($postData['cpm_trans_id'] ?? '') .
                      ($postData['cpm_trans_date'] ?? '') .
                      ($postData['cpm_amount'] ?? '') .
                      ($postData['cpm_currency'] ?? '') .
                      ($postData['signature'] ?? '') .
                      ($postData['payment_method'] ?? '') .
                      ($postData['cel_phone_num'] ?? '') .
                      ($postData['cpm_phone_prefixe'] ?? '') .
                      ($postData['cpm_language'] ?? '') .
                      ($postData['cpm_version'] ?? '') .
                      ($postData['cpm_payment_config'] ?? '') .
                      ($postData['cpm_page_action'] ?? '') .
                      ($postData['cpm_custom'] ?? '') .
                      ($postData['cpm_designation'] ?? '') .
                      ($postData['cpm_error_message'] ?? '');

        $generatedToken = hash_hmac('SHA256', $dataString, $this->secret_key);

        if (hash_equals($generatedToken, $xToken)) {
            return true;
        } else {
            $this->log('HMAC_ERROR', [
                'received' => $xToken,
                'generated' => $generatedToken,
                'dataString' => $dataString
            ]);
            return false;
        }
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
        
        $logFile = $logDir . '/cinetpay_' . date('Y-m-d') . '.log';
        $message = date('Y-m-d H:i:s') . ' [' . $type . '] ' . 
                   (is_array($data) ? json_encode($data) : $data) . PHP_EOL;
        
        file_put_contents($logFile, $message, FILE_APPEND);
    }
}
