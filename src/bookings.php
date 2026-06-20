<?php
require_once 'includes/header.php';

if (!$auth->isLoggedIn()) {
    echo "<script>window.location.href = 'login.php';</script>";
    exit();
}

$profile = $auth->getProfileData();
$userId = $_SESSION['user_id'];
$errorMessage = '';
$successMessage = '';

try {
    $manager = new MongoDB\Driver\Manager("mongodb://admin:password@mongodb:27017");
} catch (Exception $e) {
    $errorMessage = $translator->get('database_error') . " " . $e->getMessage();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    try {
        $ticketId = $_POST['ticket_id'];
        
        $bulk = new MongoDB\Driver\BulkWrite;
        $bulk->delete([
            '_id' => new MongoDB\BSON\ObjectId($ticketId),
            'user_id' => (int)$userId
        ]);

        $deleteResult = $manager->executeBulkWrite('travel_app.tickets', $bulk);

        if ($deleteResult->getDeletedCount() > 0) {
            $successMessage = $translator->get('delete_success');
        } else {
            $errorMessage = $translator->get('ticket_not_found');
        }
    } catch (Exception $e) {
        $errorMessage = $translator->get('deleting_error') . " " . $e->getMessage();
    }
}

$upcomingTickets = [];
$pastTickets = [];

if (empty($errorMessage)) {
    try {
        $query = new MongoDB\Driver\Query(
            ['user_id' => (int)$userId],
            ['sort' => ['created_at' => -1]]
        );
        
        $cursor = $manager->executeQuery('travel_app.tickets', $query);
        $cursor->setTypeMap(['root' => 'array', 'document' => 'array', 'array' => 'array']);
        
        $allTickets = $cursor->toArray();
        $today = new DateTime('today'); 
        
        foreach ($allTickets as $ticket) {
            $flightDateRaw = $ticket['itinerary']['outbound_flight']['departure']['date'];
            $flightDate = new DateTime($flightDateRaw);

            if ($flightDate >= $today) {
                $upcomingTickets[] = $ticket;
            } else {
                $pastTickets[] = $ticket;
            }
        }
    } catch (Exception $e) {
        $errorMessage = $translator->get('database_error') . " " . $e->getMessage();
    }
}

function renderTicketCard($ticket) {
    global $translator; 
    
    $bookingDate = $ticket['created_at']->toDateTime()->format('d M Y, H:i');
    
    $tripTypeLabel = ($ticket['trip_type'] === 'round_trip') ? $translator->get('round_trip') : $translator->get('one_way');
    $ticketIdString = (string)$ticket['_id'];
    ?>
    <div class="card shadow-sm border-0 mb-4 overflow-hidden">
        <div class="card-header border-0 py-3 d-flex justify-content-between align-items-center flex-wrap gap-2" style="background-color: var(--travel-blue);">
            <span class="text-white fw-bold mb-0">
                <?php echo $translator->get('ticket_id'); ?>: <span class="fw-normal font-monospace opacity-75"><?php echo substr($ticketIdString, -8); ?></span>
            </span>
            <div class="d-flex align-items-center">
                <span class="badge bg-white text-travel-blue fw-bold px-3 py-2 rounded-pill shadow-sm me-2">
                    <?php echo $translator->get('confirmed'); ?>
                </span>
                
                <form method="POST" action="bookings.php" class="m-0" onsubmit="return confirm(appTranslations.DeleteConfirm);">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="ticket_id" value="<?php echo $ticketIdString; ?>">
                    <button type="submit" class="btn btn-sm btn-danger rounded-pill px-3 shadow-sm border-0 fw-bold">
                        <?php echo $translator->get('delete'); ?>
                    </button>
                </form>
            </div>
        </div>

        <div class="card-body p-4 bg-light">
            <div class="row mb-4 border-bottom pb-3">
                <div class="col-sm-4 mb-2 mb-sm-0">
                    <small class="text-muted fw-bold text-uppercase d-block mb-1"><?php echo $translator->get('passenger'); ?></small>
                    <h5 class="fw-bold text-dark m-0"><?php echo htmlspecialchars($ticket['contact_name']); ?></h5>
                </div>
                
                <div class="col-sm-4 mb-2 mb-sm-0 text-sm-center">
                    <small class="text-muted fw-bold text-uppercase d-block mb-1"><?php echo $translator->get('total_price'); ?></small>
                    <h5 class="fw-bold text-travel-blue m-0">
                        <?php 
                        if (isset($ticket['price']) && isset($ticket['currency'])) {
                            echo htmlspecialchars($ticket['price'] . ' ' . $ticket['currency']);
                        } else {
                            echo 'N/A';
                        }
                        ?>
                    </h5>
                </div>
                <div class="col-sm-4 text-sm-end">
                    <small class="text-muted fw-bold text-uppercase d-block mb-1"><?php echo $translator->get('purchased_on'); ?></small>
                    <span class="text-dark fw-semibold"><?php echo $bookingDate; ?></span>
                </div>
            </div>

            <div class="mb-3">
                <span class="badge bg-secondary px-3 py-2 shadow-sm"><?php echo $tripTypeLabel; ?></span>
            </div>

            <div class="p-3 bg-white border-start border-4 border-primary rounded shadow-sm mb-3">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h6 class="fw-bold m-0 text-travel-blue"><?php echo $translator->get('outbound_flight'); ?></h6>
                    <span class="text-muted font-monospace small">Flight: <?php echo htmlspecialchars($ticket['itinerary']['outbound_flight']['flight_number']); ?></span>
                </div>
                <div class="row align-items-center text-center">
                    <div class="col-4">
                        <h4 class="fw-bold m-0"><?php echo htmlspecialchars($ticket['itinerary']['outbound_flight']['departure']['airport']); ?></h4>
                    </div>
                    <div class="col-4 text-muted">
                        <div class="small fw-bold"><?php echo htmlspecialchars($ticket['itinerary']['outbound_flight']['departure']['date']); ?></div>
                        <div>➔</div>
                    </div>
                    <div class="col-4">
                        <h4 class="fw-bold m-0"><?php echo htmlspecialchars($ticket['itinerary']['outbound_flight']['arrival']['airport']); ?></h4>
                    </div>
                </div>
            </div>

            <?php if (isset($ticket['itinerary']['return_flight'])): ?>
                <div class="p-3 bg-white border-start border-4 border-copper rounded shadow-sm">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="fw-bold m-0 text-copper"><?php echo $translator->get('return_flight'); ?></h6>
                        <span class="text-muted font-monospace small">Flight: <?php echo htmlspecialchars($ticket['itinerary']['return_flight']['flight_number']); ?></span>
                    </div>
                    <div class="row align-items-center text-center">
                        <div class="col-4">
                            <h4 class="fw-bold m-0"><?php echo htmlspecialchars($ticket['itinerary']['return_flight']['departure']['airport']); ?></h4>
                        </div>
                        <div class="col-4 text-muted">
                            <div class="small fw-bold"><?php echo htmlspecialchars($ticket['itinerary']['return_flight']['departure']['date']); ?></div>
                            <div>➔</div>
                        </div>
                        <div class="col-4">
                            <h4 class="fw-bold m-0"><?php echo htmlspecialchars($ticket['itinerary']['return_flight']['arrival']['airport']); ?></h4>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <?php
}
?>

<div class="container mt-5 mb-5">
    <div class="row mb-4">
        <div class="col-12 text-center">
            <h2 class="fw-bold text-travel-blue"><?php echo $translator->get('my_bookings'); ?></h2>
            <p class="text-muted"><?php echo $translator->get('manage_upcoming_past'); ?></p>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            
            <?php if ($errorMessage): ?>
                <div class="alert alert-danger shadow-sm border-0 border-start border-4 border-danger fw-bold"><?php echo htmlspecialchars($errorMessage); ?></div>
            <?php endif; ?>
            <?php if ($successMessage): ?>
                <div class="alert alert-success shadow-sm border-0 border-start border-4 border-success fw-bold"><?php echo htmlspecialchars($successMessage); ?></div>
            <?php endif; ?>

            <?php if (empty($upcomingTickets) && empty($pastTickets) && !$errorMessage): ?>
                <div class="card shadow-sm border-0 text-center py-5 bg-light">
                    <div class="card-body">
                        <div class="display-1 mb-3">✈️</div>
                        <h4 class="text-muted fw-bold"><?php echo $translator->get('no_tickets_found'); ?></h4>
                        <p class="text-muted mb-4"><?php echo $translator->get('itinerary_empty'); ?></p>
                        <a href="index.php" class="btn btn-copper px-4 py-3 fw-bold shadow-sm"><?php echo $translator->get('search_flights'); ?></a>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (!empty($upcomingTickets)): ?>
                <h4 class="fw-bold text-dark border-bottom pb-2 mb-4"><?php echo $translator->get('upcoming_trips'); ?></h4>
                <?php foreach ($upcomingTickets as $ticket) { renderTicketCard($ticket); } ?>
            <?php endif; ?>

            <?php if (!empty($pastTickets)): ?>
                <div class="accordion mt-5 shadow-sm rounded" id="pastFlightsAccordion">
                    <div class="accordion-item border-0">
                        <h2 class="accordion-header" id="headingPastFlights">
                            <button class="accordion-button collapsed fw-bold text-muted bg-white p-4" type="button" data-bs-toggle="collapse" data-bs-target="#collapsePastFlights" aria-expanded="false" aria-controls="collapsePastFlights" style="font-size: 1.1rem;">
                                <?php echo $translator->get('past_flights'); ?> (<?php echo count($pastTickets); ?>)
                            </button>
                        </h2>
                        <div id="collapsePastFlights" class="accordion-collapse collapse" aria-labelledby="headingPastFlights" data-bs-parent="#pastFlightsAccordion">
                            <div class="accordion-body bg-light p-4">
                                <?php foreach ($pastTickets as $ticket) { renderTicketCard($ticket); } ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

        </div>
    </div>
</div>

<script>
    const appTranslations = {
        DeleteConfirm: "<?php echo addslashes($translator->get('delete_confirm_msg')); ?>"
    };
</script>

<?php require_once 'includes/footer.php'; ?>