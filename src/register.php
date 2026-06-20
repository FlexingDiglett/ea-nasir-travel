<?php
require_once 'includes/header.php'; 

if ($auth->isLoggedIn()) {
    echo "<script>window.location.href = 'index.php';</script>";
    exit();
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $contact_name = trim($_POST['contact_name'] ?? '');
    $user_type = $_POST['user_type'] ?? 'personal';
    
    if (!empty($email) && !empty($password) && !empty($contact_name)) {
        $result = $auth->register($email, $password, $user_type, $contact_name);
        
        if ($result['success']) {
            $auth->login($email, $password);
            
            if (isset($_GET['departure']) && isset($_GET['arrival'])) {
                $queryString = http_build_query($_GET);
                $redirectUrl = 'book_flight.php?' . $queryString;
            } else {
                $redirectUrl = 'index.php';
            }
            
            echo "<script>window.location.href = '" . $redirectUrl . "';</script>";
            exit;
        } else {
            $message = "<div class='alert alert-danger shadow-sm border-0 border-start border-4 border-danger fw-bold'>" . htmlspecialchars($result['message']) . "</div>";
        }
    } else {
        $message = "<div class='alert alert-warning shadow-sm border-0 border-start border-4 border-warning fw-bold'>Please fill out all fields.</div>";
    }
}

$actionUrl = 'register.php';
if (!empty($_SERVER['QUERY_STRING'])) {
    $actionUrl .= '?' . htmlspecialchars($_SERVER['QUERY_STRING']);
}
?>

<div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
        <div class="card shadow-sm border-0 mt-5">
            <div class="card-body p-5">
                <h2 class="text-center mb-4" style="color: var(--travel-blue);"><?php echo $translator->get('join_us'); ?></h2>
                
                <?= $message ?>

                <form method="POST" action="<?php echo $actionUrl; ?>">
                    <div class="mb-3">
                        <label for="email" class="form-label text-muted"><?php echo $translator->get('email_address'); ?></label>
                        <input type="email" class="form-control form-control-lg" id="email" name="email" required>
                    </div>

                    <div class="mb-3">
                        <label for="contact_name" class="form-label text-muted"><?php echo $translator->get('full_name'); ?></label>
                        <input type="text" class="form-control form-control-lg" id="contact_name" name="contact_name" required>
                    </div>

                    <div class="mb-3">
                        <label for="user_type" class="form-label text-muted"><?php echo $translator->get('account_type'); ?></label>
                        <select name="user_type" class="form-control form-control-lg" id="user_type">
                            <option value="personal"><?php echo $translator->get('private_user'); ?></option>
                            <option value="agency"><?php echo $translator->get('travel_agency'); ?></option>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label for="password" class="form-label text-muted"><?php echo $translator->get('password'); ?></label>
                        <input type="password" class="form-control form-control-lg" id="password" name="password" required>
                    </div>

                    <button type="submit" class="btn btn-copper w-100 btn-lg mb-3"><?php echo $translator->get('create_account'); ?></button>
                    
                    <p class="text-center text-muted mb-0">
                        <?php echo $translator->get('already_account'); ?> 
                        <a href="login.php<?php echo !empty($_SERVER['QUERY_STRING']) ? '?' . htmlspecialchars($_SERVER['QUERY_STRING']) : ''; ?>" style="color: var(--fine-copper);">
                            <?php echo $translator->get('login_here'); ?>
                        </a>
                    </p>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>