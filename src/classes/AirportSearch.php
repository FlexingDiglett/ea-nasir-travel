<?php

class AirportSearch {
    private $db;
    private $duffelToken;

    public function __construct() {
        $this->duffelToken = getenv('DUFFEL_API_KEY') ?: $_ENV['DUFFEL_API_KEY'] ?? '';

        $this->db = @new mysqli('db-sql', 'root', 'password', 'travel_app');

        if ($this->db->connect_error) {
            die(json_encode(['error' => 'Database connection failed: ' . $this->db->connect_error]));
        }
    }

    public function search($searchTerm) {
        $localResults = $this->searchLocalDB($searchTerm);

        if (!empty($localResults)) {
            return $localResults;
        }

        return $this->fetchAndSaveFromDuffel($searchTerm);
    }

    private function searchLocalDB($searchTerm) {
        $searchTermLike = "%" . $searchTerm . "%";
        $sql = "SELECT iata_code, city_name, airport_name FROM airports
                WHERE city_name LIKE ? OR airport_name LIKE ?";
        
        $stmt = $this->db->prepare($sql);
        
        if (!$stmt) {
            return [];
        }

        $stmt->bind_param("ss", $searchTermLike, $searchTermLike);
        $stmt->execute();

        $result = $stmt->get_result();
        $airports = $result->fetch_all(MYSQLI_ASSOC);

        $stmt->close();
        return $airports;
    }

    private function fetchAndSaveFromDuffel($searchTerm) {
        $ch = curl_init("https://api.duffel.com/places/suggestions?query=" . urlencode($searchTerm));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Accept: application/json",
            "Duffel-Version: v2",
            "Authorization: Bearer " . $this->duffelToken
        ]);

        $response = curl_exec($ch);
        curl_close($ch);
        
        if (!$response) return [];
        
        $duffelData = json_decode($response, true);
        $newAirports = [];

        if (isset($duffelData['data']) && is_array($duffelData['data'])) {
            $insertSql = "INSERT IGNORE INTO airports (iata_code, city_name, airport_name, country_code) VALUES (?, ?, ?, ?)";
            $insertStmt = $this->db->prepare($insertSql);

            foreach ($duffelData['data'] as $place) {
                if (isset($place['type']) && $place['type'] === 'airport') {
                    
                    $iata = $place['iata_code'] ?? '';
                    $city = $place['city_name'] ?? $place['name'] ?? 'Unknown';
                    $name = $place['name'] ?? 'Unknown';
                    $country = $place['iata_country_code'] ?? '';

                    if ($insertStmt) {
                        $insertStmt->bind_param("ssss", $iata, $city, $name, $country);
                        $insertStmt->execute();
                    }

                    $newAirports[] = [
                        'iata_code' => $iata,
                        'city_name' => $city,
                        'airport_name' => $name
                    ];
                }
            }
            if ($insertStmt) {
                $insertStmt->close();
            }
        }

        return $newAirports;
    }

    public function __destruct() {
        if ($this->db && empty($this->db->connect_error)) {
            $this->db->close();
        }
    }
}