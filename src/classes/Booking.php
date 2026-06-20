<?php

class Booking {
    private $manager;
    private $namespace = 'travel_app.tickets';

    public function __construct() {
        try {
            $this->manager = new MongoDB\Driver\Manager("mongodb://admin:password@mongodb:27017");
        } catch (Exception $e) {
            die(json_encode(['success' => false, 'message' => "Error connecting to MongoDB: " . $e->getMessage()]));
        }
    }

    public function createFlightTicket($userId, $contactName, $tripType, $outboundData, $returnData = null, $price = null, $currency = null) {
        if (empty($userId) || empty(trim($contactName))) {
            return ['success' => false, 'message' => 'Missing user or contact information.'];
        }

        $bulk = new MongoDB\Driver\BulkWrite;        # used to queue up database queries, std approach even if single ticket
        $ticketId = new MongoDB\BSON\ObjectId();     # generates a unique 24-character hex string which acts as the booking ref

        $document = [
            '_id' => $ticketId,
            'user_id' => (int)$userId,
            'contact_name' => trim($contactName),
            'trip_type' => $tripType,
            'status' => 'confirmed',
            'price' => $price,
            'currency' => $currency,
            'itinerary' => [
                'outbound_flight' => $outboundData
            ],
            'created_at' => new MongoDB\BSON\UTCDateTime()
        ];

        if ($tripType === 'round_trip' && $returnData !== null) {
            $document['itinerary']['return_flight'] = $returnData;
        }

        $bulk->insert($document);

        try {
            $this->manager->executeBulkWrite($this->namespace, $bulk);
            
            return [
                'success' => true,
                'ticket_id' => (string)$ticketId
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to save ticket to database.'
            ];
        }
    }
}
