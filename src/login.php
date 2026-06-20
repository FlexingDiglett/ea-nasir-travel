<?php
require_once 'includes/header.php'; 

if ($auth->isLoggedIn()) {
    if (isset($_SESSION['pending_redirect'])) {
        $target = $_SESSION['pending_redirect'];
        unset($_SESSION['pending_redirect']);
        echo "<script>window.location.href = '" . htmlspecialchars($target, ENT_QUOTES) . "';</script>";
    } else {
        echo "<script>window.location.href = 'index.php';</script>";
    }
    exit();
}   

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    $result = $auth->login($email, $password);
    
    if ($result['success']) {
        if (isset($_SESSION['pending_redirect'])) {
            $target = $_SESSION['pending_redirect'];
            unset($_SESSION['pending_redirect']);
            echo "<script>window.location.href = '" . htmlspecialchars($target, ENT_QUOTES) . "';</script>";
        } else {
            echo "<script>window.location.href = 'index.php';</script>";
        }
        exit();
    } else {
        $message = "<div class='alert alert-danger shadow-sm border-0 border-start border-4 border-danger fw-bold'>" . htmlspecialchars($result['message']) . "</div>";
    }
}
?>

<div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
        <div class="card shadow-sm border-0 mt-5">
            <div class="card-body p-5">
                <h2 class="text-center mb-4" style="color: var(--travel-blue);"><?php echo $translator->get('welcome_back'); ?></h2>
                
                <?= $message ?>

                <form method="POST" action="login.php">
                    <div class="mb-3">
                        <label for="email" class="form-label text-muted"><?php echo $translator->get('email_address'); ?></label>
                        <input type="email" class="form-control form-control-lg" id="email" name="email" required>
                    </div>
                    <div class="mb-4">
                        <label for="password" class="form-label text-muted"><?php echo $translator->get('password'); ?></label>
                        <input type="password" class="form-control form-control-lg" id="password" name="password" required>
                    </div>
                    <button type="submit" class="btn btn-copper w-100 btn-lg mb-3"><?php echo $translator->get('login_btn'); ?></button>
                    
                    <p class="text-center text-muted mb-0">
                        <?php echo $translator->get('no_account'); ?> <a href="register.php" style="color: var(--fine-copper);"><?php echo $translator->get('sign_up'); ?></a>
                    </p>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>