<?php
/**
 * Shiprocket API Integration Wrapper
 */

class Shiprocket {
    private string $baseUrl = 'https://apiv2.shiprocket.in/v1/payloads';
    private string $email;
    private string $password;
    private string $token = '';

    public function __construct(string $email, string $password) {
        $this->email = $email;
        $this->password = $password;
    }

    /**
     * Authenticate and get Bearer Token
     */
    public function authenticate(): bool {
        if (empty($this->email) || empty($this->password)) {
            error_log('[SHIPROCKET] API Credentials Missing');
            return false;
        }

        $res = $this->request('POST', '/auth/login', [
            'email' => $this->email,
            'password' => $this->password
        ]);

        if (isset($res['token'])) {
            $this->token = $res['token'];
            return true;
        }

        error_log('[SHIPROCKET] Auth Failed: ' . json_encode($res));
        return false;
    }

    /**
     * Create Order in Shiprocket
     */
    public function createOrder(array $orderData): array {
        if (!$this->token && !$this->authenticate()) {
            return ['success' => false, 'message' => 'Authentication Failed'];
        }

        // Standard custom order creation
        $res = $this->request('POST', '/orders/create/adhoc', $orderData, true);

        if (isset($res['order_id']) && isset($res['shipment_id'])) {
            return [
                'success' => true, 
                'shipment_id' => $res['shipment_id'], 
                'shiprocket_order_id' => $res['order_id']
            ];
        }

        error_log('[SHIPROCKET] Create Order Failed: ' . json_encode($res));
        return ['success' => false, 'message' => 'Failed to push order to Shiprocket', 'debug' => $res];
    }

    /**
     * Generate AWB for a Shipment
     */
    public function generateAWB(int $shipmentId): array {
        if (!$this->token && !$this->authenticate()) {
            return ['success' => false, 'message' => 'Authentication Failed'];
        }

        $res = $this->request('POST', '/courier/assign/awb', ['shipment_id' => $shipmentId], true);

        if (isset($res['response']['data']['awb_code'])) {
            return [
                'success' => true,
                'awb_code' => $res['response']['data']['awb_code'],
                'courier_name' => $res['response']['data']['courier_name'],
                'tracking_url' => $res['response']['data']['track_url'] ?? 'https://shiprocket.co/tracking/' . $res['response']['data']['awb_code']
            ];
        }

        error_log('[SHIPROCKET] AWB Generation Failed: ' . json_encode($res));
        return ['success' => false, 'message' => 'Failed to generate AWB.', 'debug' => $res];
    }

    /**
     * Internal generic cURL request method
     */
    private function request(string $method, string $endpoint, array $data = [], bool $useAuth = false): array {
        $ch = curl_init();
        
        $url = $this->baseUrl . $endpoint;
        
        $headers = [
            'Content-Type: application/json'
        ];
        
        if ($useAuth && $this->token) {
            $headers[] = 'Authorization: Bearer ' . $this->token;
        }

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }

        $result = curl_exec($ch);
        $err = curl_error($ch);
        curl_close($ch);

        if ($err) {
            error_log('[SHIPROCKET] cURL Error: ' . $err);
            return [];
        }

        $decoded = json_decode($result, true);
        return is_array($decoded) ? $decoded : [];
    }
}
