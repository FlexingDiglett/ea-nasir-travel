<?php
require_once __DIR__ . '/includes/db_functions.php'; 
require_once __DIR__ . '/classes/FlightAPI.php';

$mongo = connect_mongodb();

$pdo = null; 
try {
    $host = 'db-sql';
    $dbname = 'travel_app'; 
    $username = 'root';
    
    $password = getenv('DB_PASSWORD');

    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    error_log("MariaDB Connection Failed: " . $e->getMessage());
}

require_once 'includes/header.php'; 

$isRoundTrip = (isset($_GET['trip_type']) && $_GET['trip_type'] === 'round_trip');

if (isset($_GET['destination']) && !empty($_GET['destination'])) {
    require_once __DIR__ . '/classes/Neo4jTracker.php';
    $tracker = new Neo4jTracker();
    $tracker->trackSearch($_GET['destination']);
}
?>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

<div class="row align-items-center mb-5 hero-banner">
    <div class="col-md-8 col-lg-10 text-white">
        <h1 class="display-4 fw-bold mb-3"><?php echo $translator->get('hero_title'); ?></h1>
        <p class="lead mb-0" style="opacity: 0.9;"><?php echo $translator->get('hero_subtitle'); ?></p>
    </div>
    <div class="col-md-4 col-lg-2 d-none d-md-block text-center">
        <h1 class="hero-icon">✈️</h1>
    </div>
</div>

<div class="row justify-content-center mb-5">
    <div class="col-12">
        <div class="card shadow-sm border-0">
            <div class="card-body p-4 p-md-5">
                <h4 class="mb-4 text-travel-blue"><?php echo $translator->get('search_title'); ?></h4>
                
                <form action="index.php" method="GET" onsubmit="return validateSearch()" class="row g-3 align-items-end">
    
                    <div class="col-12 mb-2">
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="trip_type" id="one_way" value="one_way" <?php echo !$isRoundTrip ? 'checked' : ''; ?> onchange="toggleReturnFlight()">
                            <label class="form-check-label text-travel-blue fw-bold" for="one_way"><?php echo $translator->get('one_way'); ?></label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="trip_type" id="round_trip" value="round_trip" <?php echo $isRoundTrip ? 'checked' : ''; ?> onchange="toggleReturnFlight()">
                            <label class="form-check-label text-travel-blue fw-bold" for="round_trip"><?php echo $translator->get('round_trip'); ?></label>
                        </div>
                    </div>

                    <div class="col-md-3 position-relative">
                        <label class="form-label text-muted fw-bold"><?php echo $translator->get('origin'); ?></label>
                        <input type="text" id="origin_input" class="form-control form-control-lg" placeholder="<?php echo $translator->get('ph_origin'); ?>" value="<?php echo isset($_GET['origin']) ? htmlspecialchars($_GET['origin']) : ''; ?>" onkeyup="searchPlaces(this.value, 'origin_results', 'origin_code')" autocomplete="off" required>
                        <input type="hidden" name="origin" id="origin_code" value="<?php echo isset($_GET['origin']) ? htmlspecialchars($_GET['origin']) : ''; ?>">
                        <div id="origin_results" class="autocomplete-results position-absolute w-100 bg-white shadow-sm border rounded mt-1" style="z-index: 1050; display: none; max-height: 250px; overflow-y: auto;"></div>
                    </div>

                    <div class="col-md-3 position-relative">
                        <label class="form-label text-muted fw-bold"><?php echo $translator->get('destination'); ?></label>
                        <input type="text" id="dest_input" class="form-control form-control-lg" placeholder="<?php echo $translator->get('ph_dest'); ?>" value="<?php echo isset($_GET['destination']) ? htmlspecialchars($_GET['destination']) : ''; ?>" onkeyup="searchPlaces(this.value, 'dest_results', 'dest_code')" autocomplete="off" required>
                        <input type="hidden" name="destination" id="dest_code" value="<?php echo isset($_GET['destination']) ? htmlspecialchars($_GET['destination']) : ''; ?>">
                        <div id="dest_results" class="autocomplete-results position-absolute w-100 bg-white shadow-sm border rounded mt-1" style="z-index: 1050; display: none; max-height: 250px; overflow-y: auto;"></div>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label text-muted fw-bold"><?php echo $translator->get('departure'); ?></label>
                        <input type="text" name="date" id="flight_date" class="form-control form-control-lg bg-white" placeholder="<?php echo $translator->get('select'); ?>" value="<?php echo isset($_GET['date']) ? htmlspecialchars($_GET['date']) : ''; ?>" required>
                    </div>

                    <div class="col-md-2" id="return_flight_section">
                        <label id="return_label" class="form-label text-muted fw-bold <?php echo !$isRoundTrip ? 'opacity-50' : ''; ?>"><?php echo $translator->get('return'); ?></label>
                        <input type="text" name="return_date" id="ret_date_input" class="form-control form-control-lg <?php echo !$isRoundTrip ? 'bg-light' : 'bg-white'; ?>" placeholder="<?php echo $translator->get('select'); ?>" value="<?php echo isset($_GET['return_date']) ? htmlspecialchars($_GET['return_date']) : ''; ?>" <?php echo !$isRoundTrip ? 'disabled' : ''; ?>>
                    </div>
                    
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-copper w-100 btn-lg"><?php echo $translator->get('find_button'); ?></button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="mb-5">
    <?php if (!isset($_GET['origin']) || empty($_GET['origin'])): ?>
        <?php
        require_once __DIR__ . '/classes/Neo4jTracker.php';
        $tracker = new Neo4jTracker();
        $trendingIatas = $tracker->getTrendingDestinations(6); 

        if (!empty($trendingIatas)): ?>
            <div class="recommendations mt-5 pt-4">
                <h3 class="fw-bold mb-4" style="color: var(--travel-blue);">🔥 <?php echo $translator->get('trending_destinations'); ?></h3>
                <div class="row g-4">
                
                <?php
                foreach ($trendingIatas as $iata) {
                    $airportData = null;
                    
                    if ($pdo) {
                        $stmt = $pdo->prepare("SELECT city_name, airport_name FROM airports WHERE iata_code = ?");
                        $stmt->execute([$iata]);
                        $airportData = $stmt->fetch(PDO::FETCH_ASSOC);
                    }
                    
                    if ($airportData) {
                        echo '<div class="col-md-4 col-sm-6 mb-4">';
                        echo '  <div class="card shadow-sm border-0 h-100 trending-destination-card">';
                        echo '      <div class="card-body d-flex flex-column text-center p-4">';
                        echo '          <div class="mb-auto">'; 
                        echo '              <h4 class="card-title fw-bold mb-1" style="color: var(--travel-blue);">' . htmlspecialchars($airportData['city_name']) . '</h4>';
                        echo '              <h6 class="text-secondary mb-3">' . htmlspecialchars($iata) . '</h6>';
                        echo '              <p class="card-text text-muted small mb-4">' . htmlspecialchars($airportData['airport_name']) . '</p>';
                        echo '          </div>';
                        echo '          <a href="index.php?trip_type=one_way&destination=' . htmlspecialchars($iata) . '" class="btn btn-copper w-100 fw-bold shadow-sm" style="border-radius: 8px;">' . $translator->get('find_button') . '</a>';
                        echo '      </div></div></div>';
                    } else {
                        echo '<div class="col-md-4 col-sm-6 mb-4">';
                        echo '  <div class="card shadow-sm border-0 h-100 trending-destination-card">';
                        echo '      <div class="card-body d-flex flex-column text-center p-4">';
                        echo '          <div class="mb-auto d-flex align-items-center justify-content-center" style="min-height: 100px;">';
                        echo '              <h2 class="card-title fw-bold mb-0" style="color: var(--travel-blue); letter-spacing: 2px;">' . htmlspecialchars($iata) . '</h2>';
                        echo '          </div>';
                        echo '          <a href="index.php?trip_type=one_way&destination=' . htmlspecialchars($iata) . '" class="btn btn-copper w-100 fw-bold shadow-sm" style="border-radius: 8px;">' . $translator->get('find_button') . '</a>';
                        echo '      </div></div></div>';
                    }
                }
                ?>
                </div>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

    <div class="results mt-4">
        <?php
        if (isset($_GET['origin']) && !empty(trim($_GET['origin'])) && isset($_GET['destination']) && isset($_GET['date'])) {

            $origin = strtoupper(trim($_GET['origin']));
            $destination = strtoupper(trim($_GET['destination']));
            
            $raw_date = trim($_GET['date']);
            $parsed_date = DateTime::createFromFormat('d/m/Y', $raw_date);
            $departure_date = ($parsed_date !== false) ? $parsed_date->format('Y-m-d') : date('Y-m-d', strtotime($raw_date));
            $display_date = date('d/m/Y', strtotime($departure_date));

            $return_date = null;
            $display_return = "";
            if (isset($_GET['trip_type']) && $_GET['trip_type'] === 'round_trip' && !empty($_GET['return_date'])) {
                $raw_return = trim($_GET['return_date']);
                $parsed_return = DateTime::createFromFormat('d/m/Y', $raw_return);
                $return_date = ($parsed_return !== false) ? $parsed_return->format('Y-m-d') : date('Y-m-d', strtotime($raw_return));
                $display_return = " - " . date('d/m/Y', strtotime($return_date));
            }

            echo "<h3 class='mb-4 text-travel-blue'>Results for $origin to $destination ($display_date$display_return)</h3>";

            $searchKey = $origin . "_" . $destination . "_" . $departure_date;
            if ($return_date) {
                $searchKey .= "_RT_" . $return_date;
            }
            $flightData = get_cached_flights($mongo, $searchKey);

            if (!$flightData) {
                $api = new FlightAPI();
                $flightData = $api->searchFlights($origin, $destination, $departure_date, $return_date);

                if (!isset($flightData['errors']) && !empty($flightData)) {
                    if (isset($flightData['data']['offers'])) {
                        
                        $filtered_offers = [];
                        foreach ($flightData['data']['offers'] as $offer) {
                            $actual_dep_date = date('Y-m-d', strtotime($offer['slices'][0]['segments'][0]['departing_at']));
                            if ($actual_dep_date === $departure_date) {
                                $filtered_offers[] = $offer;
                            }
                        }
                        
                        $flightData['data']['offers'] = $filtered_offers;

                        if (count($flightData['data']['offers']) > 50) {
                            $flightData['data']['offers'] = array_slice($flightData['data']['offers'], 0, 50);
                        }
                    }

                    save_flights_to_cache($mongo, $searchKey, $flightData);
                }
            }
            ?>
            
            <div>
                <?php if (isset($flightData['errors'])): ?>
                    <div class="alert alert-danger shadow-sm border-0 border-start border-5 border-danger">
                        <h5 class="alert-heading text-danger">⚠️ Duffel API Error(s)</h5>
                        <?php foreach ($flightData['errors'] as $err): ?>
                            <p class="mb-0"><strong><?php echo htmlspecialchars($err['type'] ?? 'Error'); ?>:</strong> <?php echo htmlspecialchars($err['message'] ?? 'Unknown issue'); ?></p>
                        <?php endforeach; ?>
                    </div>
                    
                <?php elseif (empty($flightData) || !isset($flightData['data']['offers']) || count($flightData['data']['offers']) === 0): ?>
                    <div class="text-center p-5 bg-white shadow-sm rounded border">
                        <h4 class="text-muted">✈️ <?php echo $translator->get('no_flights'); ?></h4>
                    </div>
                    
                <?php else: ?>
                    <div class="row g-4">
                        <?php foreach ($flightData['data']['offers'] as $offer): 
                            $price = $offer['total_amount'];
                            $currency = $offer['total_currency'];
                            $airline = $offer['owner']['name'] ?? 'Unknown Airline';
                            
                            $outbound = $offer['slices'][0];
                            $outbound_dep = date('H:i', strtotime($outbound['segments'][0]['departing_at']));
                            $outbound_arr = date('H:i', strtotime(end($outbound['segments'])['arriving_at']));

                            $has_return = isset($offer['slices'][1]);
                            $return_dep = '';
                            $return_arr = '';

                            if ($has_return) {
                                $return = $offer['slices'][1];
                                $return_dep = date('H:i', strtotime($return['segments'][0]['departing_at']));
                                $return_arr = date('H:i', strtotime(end($return['segments'])['arriving_at']));
                            }
                        ?>
                            <div class="col-12">
                                <div class="card shadow-sm border-0 p-4 transition-hover bg-white">
                                    <div class="row align-items-center">
                                        
                                        <div class="col-md-8">
                                            <h4 class="mb-3 text-travel-blue"><?php echo htmlspecialchars($airline); ?></h4>
                                            
                                            <div class="d-flex align-items-center mb-2">
                                                <?php if ($has_return): ?>
                                                    <span class="badge text-white me-3" style="min-width: 80px; background-color: var(--travel-blue);">Outbound</span>
                                                <?php endif; ?>
                                                
                                                <strong class="me-2 fs-5"><?php echo htmlspecialchars($origin); ?></strong>
                                                <span class="text-muted me-3"><?php echo $outbound_dep; ?></span>
                                                <span class="me-3">➔</span>
                                                <strong class="me-2 fs-5"><?php echo htmlspecialchars($destination); ?></strong>
                                                <span class="text-muted"><?php echo $outbound_arr; ?></span>
                                            </div>

                                            <?php if ($has_return): ?>
                                            <div class="d-flex align-items-center mt-3">
                                                <span class="badge bg-copper me-3 text-white" style="min-width: 80px;">Inbound</span>
                                                <strong class="me-2 fs-5"><?php echo htmlspecialchars($destination); ?></strong>
                                                <span class="text-muted me-3"><?php echo $return_dep; ?></span>
                                                <span class="me-3">➔</span>
                                                <strong class="me-2 fs-5"><?php echo htmlspecialchars($origin); ?></strong>
                                                <span class="text-muted"><?php echo $return_arr; ?></span>
                                            </div>
                                            <?php endif; ?>
                                        </div>

                                        <div class="col-md-4 text-md-end mt-4 mt-md-0 border-start border-light border-2">
                                            <h3 class="mb-2 fw-bold text-travel-blue"><?php echo $price . ' ' . $currency; ?></h3>
                                            
                                            <?php if ($has_return): ?>
                                                <p class="text-muted small mb-3"><?php echo $translator->get('total_price'); ?></p>
                                            <?php endif; ?>
                                            
                                            <form action="book_flight.php" method="GET">
                                                <input type="hidden" name="departure" value="<?php echo htmlspecialchars($origin); ?>">
                                                <input type="hidden" name="arrival" value="<?php echo htmlspecialchars($destination); ?>">
                                                <input type="hidden" name="date" value="<?php echo htmlspecialchars($departure_date); ?>">
                                                <input type="hidden" name="price" value="<?php echo htmlspecialchars($price); ?>">
                                                <input type="hidden" name="currency" value="<?php echo htmlspecialchars($currency); ?>">
                                                
                                                <?php if ($has_return): ?>
                                                    <input type="hidden" name="trip_type" value="round_trip">
                                                    <input type="hidden" name="return_date" value="<?php echo htmlspecialchars($return_date); ?>">
                                                <?php else: ?>
                                                    <input type="hidden" name="trip_type" value="one_way">
                                                <?php endif; ?>

                                                <button type="submit" class="btn btn-copper w-100 text-white fw-bold shadow-sm"><?php echo $translator->get('select_ticket'); ?></button>
                                            </form>
                                        </div>

                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php } ?>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        flatpickr("#ret_date_input", {
            dateFormat: "Y-m-d",
            altInput: true,
            altFormat: "d/m/Y",
            minDate: "today"
        });
    });
</script>

<?php require_once 'includes/footer.php'; ?>