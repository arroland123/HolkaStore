<?php
/**
 * RAJAONGKIR CONFIGURATION
 */
define('RAJAONGKIR_API_KEY', 'l0EG2SfGf651bd08cfd8b78bcdNLyhBl');

// City ID asal pengiriman. Default: 86 (Boyolali Kabupaten) atau 445 (Surakarta/Solo)
// Pembeli sebelumnya sering menulis "Boyolali" di alamat, jadi kita defaultkan ke 86 (Boyolali).
define('RAJAONGKIR_ORIGIN_CITY_ID', '86'); 

/**
 * Helper function to send API Request to RajaOngkir
 */
function callRajaOngkirAPI($endpoint, $params = [], $method = 'GET') {
    $curl = curl_init();
    
    // Pemetaan ke endpoint Komerce RajaOngkir yang baru dan stabil
    if ($endpoint === 'province') {
        $url = "https://rajaongkir.komerce.id/api/v1/destination/province";
    } elseif ($endpoint === 'city') {
        $province_id = isset($params['province']) ? (int)$params['province'] : 0;
        $url = "https://rajaongkir.komerce.id/api/v1/destination/city/" . $province_id;
    } elseif ($endpoint === 'cost') {
        $url = "https://rajaongkir.komerce.id/api/v1/calculate/domestic-cost";
    } else {
        $url = "https://rajaongkir.komerce.id/api/v1/" . $endpoint;
    }
    
    $headers = [
        "key: " . RAJAONGKIR_API_KEY
    ];
    
    if ($method == 'POST') {
        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => http_build_query($params),
            CURLOPT_HTTPHEADER => array_merge($headers, ["content-type: application/x-www-form-urlencoded"]),
            CURLOPT_SSL_VERIFYPEER => false
        ]);
    } else {
        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_SSL_VERIFYPEER => false
        ]);
    }
    
    $response = curl_exec($curl);
    $err = curl_error($curl);
    curl_close($curl);
    
    if ($err) {
        return ['success' => false, 'message' => 'cURL Error: ' . $err];
    }
    
    $data = json_decode($response, true);
    
    if (isset($data['meta']['code']) && $data['meta']['code'] == 200) {
        $results = [];
        
        if ($endpoint === 'province') {
            foreach ($data['data'] as $p) {
                $results[] = [
                    'province_id' => $p['id'],
                    'province' => ucwords(strtolower($p['name']))
                ];
            }
        } elseif ($endpoint === 'city') {
            foreach ($data['data'] as $c) {
                $results[] = [
                    'city_id' => $c['id'],
                    'city_name' => ucwords(strtolower($c['name'])),
                    'type' => ''
                ];
            }
        } elseif ($endpoint === 'cost') {
            $costs = [];
            foreach ($data['data'] as $item) {
                $costs[] = [
                    'service' => $item['service'],
                    'description' => $item['description'],
                    'cost' => [
                        [
                            'value' => $item['cost'],
                            'etd' => $item['etd'],
                            'note' => ''
                        ]
                    ]
                ];
            }
            $results = [
                [
                    'code' => $params['courier'] ?? '',
                    'name' => $data['data'][0]['name'] ?? '',
                    'costs' => $costs
                ]
            ];
        } else {
            $results = $data['data'];
        }
        
        return ['success' => true, 'results' => $results];
    } else {
        $msg = $data['meta']['message'] ?? 'Gagal menghubungi RajaOngkir';
        return ['success' => false, 'message' => $msg];
    }
}
?>
