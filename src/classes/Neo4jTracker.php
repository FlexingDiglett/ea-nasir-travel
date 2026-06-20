<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Laudis\Neo4j\ClientBuilder;

class Neo4jTracker {
    private $client;

    public function __construct() {
        $password = getenv('NEO4J_PASSWORD');
        
        $this->client = ClientBuilder::create()
            ->withDriver('default', 'bolt://neo4j:' . $password . '@neo4j:7687')
            ->build();
    }

    public function trackSearch($iataCode) {
        try {
            $query = '
                MERGE (a:Airport {iata_code: $iata})
                CREATE (s:SearchEvent {timestamp: datetime()})
                CREATE (s)-[:SEARCHED_FOR]->(a)
            ';
            
            $this->client->run($query, ['iata' => $iataCode]);
            
        } catch (Exception $e) {
            error_log("Neo4j Tracking Failed: " . $e->getMessage());
        }
    }

    public function getTrendingDestinations($limit = 5) {
        try {
            $query = '
                MATCH (s:SearchEvent)-[:SEARCHED_FOR]->(a:Airport)
                RETURN a.iata_code AS iata, count(s) AS total_searches
                ORDER BY total_searches DESC
                LIMIT $limit
            ';
            
            $result = $this->client->run($query, ['limit' => (int)$limit]);
            
            $trending = [];
            foreach ($result as $record) {
                $trending[] = $record->get('iata'); 
            }
            
            return $trending;
            
        } catch (Exception $e) {
            error_log("Neo4j Fetch Failed: " . $e->getMessage());
            return [];
        }
    }
}
