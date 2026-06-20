<?php
require_once 'includes/header.php';

if (!$auth->isLoggedIn()) {
    echo "<script>window.location.href = 'login.php';</script>";
    exit();
}

$profile = $auth->getProfileData();
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['new_contact_name'])) {
    $updateResult = $auth->updateProfile(trim($_POST['new_contact_name']));
    
    if ($updateResult['success']) {
        $message = "<div class='alert alert-success shadow-sm border-0 border-start border-4 border-success fw-bold text-center mb-4'>" . htmlspecialchars($updateResult['message']) . "</div>";
        $profile = $auth->getProfileData();
    } else {
        $message = "<div class='alert alert-danger shadow-sm border-0 border-start border-4 border-danger fw-bold text-center mb-4'>" . htmlspecialchars($updateResult['message']) . "</div>";
    }
}

$nameParts = explode(' ', trim($profile['contact_name']));
$initials = strtoupper(substr($nameParts[0], 0, 1) . (isset($nameParts[1]) ? substr($nameParts[1], 0, 1) : ''));
?>

<div class="container mt-5 mb-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            
            <div class="card shadow-sm border-0 profile-card">
                
                <div class="text-center py-5 position-relative profile-banner">
                    <div class="d-inline-flex justify-content-center align-items-center bg-white border border-4 border-white rounded-circle shadow-sm profile-avatar">
                        <h2 class="text-copper profile-initials"><?php echo $initials; ?></h2>
                    </div>
                </div>
                
                <div class="card-body pt-5 px-sm-5 pb-5 text-center mt-4">
                    
                    <?= $message ?>

                    <div id="display-mode">
                        <h2 class="mb-2 text-travel-blue profile-name">
                            <?php echo htmlspecialchars($profile['contact_name']); ?>
                        </h2>
                    </div>

                    <div id="edit-mode" class="d-none mb-3">
                        <form method="POST" action="profile.php" class="d-flex justify-content-center gap-2 mx-auto" style="max-width: 350px;">
                            <input type="text" name="new_contact_name" class="form-control" value="<?php echo htmlspecialchars($profile['contact_name']); ?>" required>
                            <button type="submit" class="btn btn-copper px-3"><?php echo $translator->get('save'); ?></button>
                            <button type="button" class="btn btn-outline-secondary px-3" onclick="toggleEdit()"><?php echo $translator->get('cancel'); ?></button>
                        </form>
                    </div>
                    
                    <div class="mb-4">
                        <?php if ($profile['user_type'] === 'agency'): ?>
                            <span class="badge px-3 py-2 rounded-pill bg-copper profile-badge">
                                <?php echo $translator->get('travel_agency'); ?>
                            </span>
                        <?php else: ?>
                            <span class="badge bg-secondary px-3 py-2 rounded-pill profile-badge">
                                <?php echo $translator->get('private_user'); ?>
                            </span>
                        <?php endif; ?>
                    </div>

                    <hr class="mb-4 opacity-25">

                    <div class="row g-4 mb-5">
                        <div class="col-sm-6">
                            <div class="profile-stat-box">
                                <label class="text-muted small fw-bold text-uppercase mb-2 profile-label"><?php echo $translator->get('email_address'); ?></label>
                                <p class="fs-5 mb-0 text-dark text-truncate" title="<?php echo htmlspecialchars($profile['email']); ?>">
                                    <?php echo htmlspecialchars($profile['email']); ?>
                                </p>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="profile-stat-box">
                                <label class="text-muted small fw-bold text-uppercase mb-2 profile-label"><?php echo $translator->get('member_since'); ?></label>
                                <p class="fs-5 mb-0 text-dark">
                                    <?php echo date('F j, Y', strtotime($profile['created_at'])); ?>
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-center gap-3" id="action-buttons">
                        <button onclick="toggleEdit()" class="btn btn-outline-secondary px-4 py-2 btn-fw-semibold"><?php echo $translator->get('edit_profile'); ?></button>
                        <a href="logout.php" class="btn btn-copper px-4 py-2 btn-fw-semibold"><?php echo $translator->get('nav_logout'); ?></a>
                    </div>
                </div>
            </div>
            
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
