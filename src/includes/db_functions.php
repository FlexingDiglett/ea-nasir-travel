<?php

function connect_mongodb() {
    try {
        return new MongoDB\Driver\Manager("mongodb://admin:password@mongodb:27017");
    } catch (Exception $e) {
        return null; 
    }
}

function save_flights_to_cache($manager, $searchKey, $apiData) {
    if (!$manager) {
        return false;
    }

    try {
        $bulk = new MongoDB\Driver\BulkWrite;

        $bulk->delete(['search_key' => $searchKey]);

        $document = [
            'search_key' => $searchKey,
            'data' => $apiData,
            'timestamp' => time()
        ];

        $bulk->insert($document);
        $manager->executeBulkWrite('travel_app.flight_cache', $bulk);
        
        return true;
    } catch (Exception $e) {
        return false;
    }
}

function get_cached_flights($manager, $searchKey) {
    if (!$manager) {
        return false;
    }

    try {
        $filter = ['search_key' => $searchKey];
        $options = [
            'sort' => ['timestamp' => -1],
            'limit' => 1
        ];
        $query = new MongoDB\Driver\Query($filter, $options);

        $cursor = $manager->executeQuery('travel_app.flight_cache', $query);
        $results = $cursor->toArray();

        if (!empty($results)) {
            $cachedTime = $results[0]->timestamp;
            
            if ((time() - $cachedTime) < 3600) {
                return json_decode(json_encode($results[0]->data), true);
            }
        }
    } catch (Exception $e) {
        return false;
    }
    
    return false;
}