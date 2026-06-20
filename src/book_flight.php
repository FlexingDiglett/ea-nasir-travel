<?php
require_once 'includes/header.php';
require_once 'classes/Booking.php';

if (!$auth->isLoggedIn()) {
    $_SESSION['pending_redirect'] = $_SERVER['REQUEST_URI'];
    
    echo "<script>window.location.href = 'login.php';</script>";
    exit();
}

$profile = $auth->getProfileData();
$message = '';

$prefillDep         = isset($_GET['departure'])   ? htmlspecialchars($_GET['departure'])   : (isset($_GET['origin']) ? htmlspecialchars($_GET['origin']) : '');
$prefillArr         = isset($_GET['arrival'])     ? htmlspecialchars($_GET['arrival'])     : (isset($_GET['destination']) ? htmlspecialchars($_GET['destination']) : '');
$prefillDate        = isset($_GET['date'])        ? htmlspecialchars($_GET['date'])        : '';
$prefillTrip        = isset($_GET['trip_type'])   ? htmlspecialchars($_GET['trip_type'])   : 'one_way';
$prefillRet         = isset($_GET['return_date']) ? htmlspecialchars($_GET['return_date']) : '';
$prefillPrice       = isset($_GET['price'])       ? htmlspecialchars($_GET['price'])       : '';
$prefillCurrency    = isset($_GET['currency'])    ? htmlspecialchars($_GET['currency'])    : '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $booking = new Booking();

    $passengerName = trim($_POST['passenger_name']);
    $tripType = $_POST['trip_type'];

    $outboundData = [
        'departure' => ['airport' => trim($_POST['out_departure']), 'date' => $_POST['out_date']],
        'arrival' => ['airport' => trim($_POST['out_arrival'])],
        'flight_number' => 'EA' . rand(1000, 9999)
    ];

    $returnData = null;
    if ($tripType === 'round_trip') {
        $returnData = [
            'departure' => ['airport' => trim($_POST['out_arrival']), 'date' => $_POST['ret_date']],
            'arrival' => ['airport' => trim($_POST['out_departure'])],
            'flight_number' => 'EA' . rand(1000, 9999)
        ];
    }

    $result = $booking->createFlightTicket($_SESSION['user_id'], $passengerName, $tripType, $outboundData, $returnData, $_POST['price'] ?? null, $_POST['currency'] ?? null);

    if ($result['success']) {
        if ($profile['user_type'] === 'personal') {
            echo "<script>window.location.href = 'bookings.php';</script>";
            exit();
        } else {
            $message = "<div class='alert alert-success shadow-sm border-0 border-start border-4 border-success fw-bold text-center mb-4'>" . 
                       $translator->get('booking_success') . 
                       " <a href='bookings.php' class='alert-link text-copper text-decoration-underline'>" . 
                       $result['ticket_id'] . 
                       "</a></div>";
            
            $prefillDep  = htmlspecialchars($_POST['out_departure']);
            $prefillArr  = htmlspecialchars($_POST['out_arrival']);
            $prefillDate = htmlspecialchars($_POST['out_date']);
            $prefillTrip = htmlspecialchars($_POST['trip_type']);
            $prefillRet  = htmlspecialchars($_POST['ret_date'] ?? '');
            $prefillPrice = htmlspecialchars($_POST['price'] ?? '');
            $prefillCurrency = htmlspecialchars($_POST['currency'] ?? '');
        }
    } else {
        $message = "<div class='alert alert-danger shadow-sm border-0 border-start border-4 border-danger fw-bold text-center mb-4'>Booking failed. Please try again.</div>";
    }
}
?>

<div class="container mt-5 mb-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            
            <div class="card shadow-sm border-0 profile-card">
                <div class="text-center py-4" style="background-color: var(--travel-blue);">
                    <h2 class="text-white m-0 fw-bold"><?php echo $translator->get('book_a_flight'); ?></h2>
                </div>
                
                <div class="card-body p-4 p-sm-5 bg-light">
                    <?= $message ?>

                    <form method="POST" action="book_flight.php">
                        <input type="hidden" name="price" value="<?= $prefillPrice ?>">
                        <input type="hidden" name="currency" value="<?= $prefillCurrency ?>">
                        
                        <div class="mb-4 bg-white p-4 rounded shadow-sm border">
                            <label class="form-label text-muted small fw-bold text-uppercase profile-label mb-2"><?php echo $translator->get('passenger'); ?></label>
                            <?php if ($profile['user_type'] === 'personal'): ?>
                                <h4 class="fw-bold text-travel-blue mb-1"><?php echo htmlspecialchars($profile['contact_name']); ?></h4>
                                <small class="text-muted d-block"><?php echo $translator->get('booking_securely'); ?></small>
                                <input type="hidden" name="passenger_name" value="<?php echo htmlspecialchars($profile['contact_name']); ?>">
                            <?php else: ?>
                                <input type="text" name="passenger_name" class="form-control form-control-lg border-copper" placeholder="Enter client's full name" required autofocus>
                                <small class="text-copper mt-1 d-block fw-bold"><?php echo $translator->get('agency_mode'); ?></small>
                            <?php endif; ?>
                        </div>

                        <div class="mb-4 text-center">
                            <div class="btn-group shadow-sm" role="group">
                                <input type="radio" class="btn-check" name="trip_type" id="one_way" value="one_way" autocomplete="off" <?= $prefillTrip === 'one_way' ? 'checked' : '' ?> onchange="toggleBookingReturnFlight()">
                                <label class="btn btn-outline-secondary px-4 fw-semibold" for="one_way"><?php echo $translator->get('one_way'); ?></label>

                                <input type="radio" class="btn-check" name="trip_type" id="round_trip" value="round_trip" autocomplete="off" <?= $prefillTrip === 'round_trip' ? 'checked' : '' ?> onchange="toggleBookingReturnFlight()">
                                <label class="btn btn-outline-secondary px-4 fw-semibold" for="round_trip"><?php echo $translator->get('round_trip'); ?></label>
                            </div>
                        </div>

                        <div class="mb-4 border-start border-4 border-primary ps-4">
                            <h5 class="fw-bold mb-3"><?php echo $translator->get('outbound_flight'); ?></h5>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <span class="d-block text-muted small fw-bold text-uppercase mb-1"><?php echo $translator->get('origin'); ?></span>
                                    <h5 class="text-dark fw-bold"><?= $prefillDep ?: 'Not Selected' ?></h5>
                                    <input type="hidden" name="out_departure" value="<?= $prefillDep ?>">
                                </div>
                                <div class="col-md-4">
                                    <span class="d-block text-muted small fw-bold text-uppercase mb-1"><?php echo $translator->get('destination'); ?></span>
                                    <h5 class="text-dark fw-bold"><?= $prefillArr ?: 'Not Selected' ?></h5>
                                    <input type="hidden" name="out_arrival" value="<?= $prefillArr ?>">
                                </div>
                                <div class="col-md-4">
                                    <span class="d-block text-muted small fw-bold text-uppercase mb-1"><?php echo $translator->get('date'); ?></span>
                                    <h5 class="text-primary fw-bold"><?= $prefillDate ?: 'Not Selected' ?></h5>
                                    <input type="hidden" name="out_date" value="<?= $prefillDate ?>">
                                </div>
                            </div>
                        </div>

                        <div id="booking_return_section" class="mb-5 border-start border-4 border-copper ps-4 <?= $prefillTrip === 'one_way' ? 'd-none' : '' ?>">
                            <h5 class="fw-bold mb-3 text-copper"><?php echo $translator->get('return_flight'); ?></h5>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <span class="d-block text-muted small fw-bold text-uppercase mb-1"><?php echo $translator->get('origin'); ?></span>
                                    <h5 class="text-dark fw-bold"><?= $prefillArr ?: 'Not Selected' ?></h5>
                                </div>
                                <div class="col-md-4">
                                    <span class="d-block text-muted small fw-bold text-uppercase mb-1"><?php echo $translator->get('destination'); ?></span>
                                    <h5 class="text-dark fw-bold"><?= $prefillDep ?: 'Not Selected' ?></h5>
                                </div>
                                <div class="col-md-4">
                                    <span class="d-block text-muted small fw-bold text-uppercase mb-1"><?php echo $translator->get('date'); ?></span>
                                    <?php if($prefillRet): ?>
                                        <h5 class="text-copper fw-bold"><?= $prefillRet ?></h5>
                                        <input type="hidden" name="ret_date" id="booking_ret_input" value="<?= $prefillRet ?>">
                                    <?php else: ?>
                                        <input type="date" name="ret_date" id="booking_ret_input" class="form-control border-copper">
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-copper w-100 py-3 fs-5 btn-fw-semibold shadow-sm mt-2">
                            <?php echo $translator->get('confirm_booking'); ?>
                        </button>
                    </form>

                </div>
            </div>
            
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>