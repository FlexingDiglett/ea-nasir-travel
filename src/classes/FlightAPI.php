<?php

class FlightAPI {
    private $apiKey;
    private $baseUrl = "https://api.duffel.com/air/";

    public function __construct() {
        $this->apiKey = getenv('DUFFEL_API_KEY') ?: $_ENV['DUFFEL_API_KEY'] ?? '';
    }

    public function searchFlights($origin, $destination, $departure_date, $return_date = null) {
        
        ini_set('memory_limit', '512M');
    
        $endpoint = $this->baseUrl . "offer_requests";

        $slices = [
            [
                "origin" => $origin,
                "destination" => $destination,
                "departure_date" => $departure_date
            ]
        ];

        if ($return_date != null) {
            $slices[] = [
                "origin" => $destination,
                "destination" => $origin,
                "departure_date" => $return_date
            ];
        }

        $payload = [
            "data" => [
                "slices" => $slices,
                "passengers" => [
                    ["type" => "adult"]
                ],
                "cabin_class" => "economy"
            ]
        ];
    
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);                # returns the text as a variable rather than echoing it
        curl_setopt($ch, CURLOPT_POST, true);                          # we change the request type to POST
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));   # we we specify what to send through the POST request
        curl_setopt($ch, CURLOPT_HTTPHEADER, [                         # sending headers (hidden pieces of metadata) which act as strict instructions
            "Accept: application/json",
            "Content-Type: application/json",
            "Duffel-Version: v2",
            "Authorization: Bearer " . $this->apiKey
        ]);

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            return ["error" => "Network error: " . curl_error($ch)];
        }

        curl_close($ch);

        return json_decode($response, true);
    }
}
?>
