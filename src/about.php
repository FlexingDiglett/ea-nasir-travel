<?php
require_once 'classes/Translator.php';
$translator = new Translator();

require_once 'includes/header.php';
?>

<div class="container mt-5 mb-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            
            <div class="card shadow-sm border-0 profile-card mb-4">
                
                <div class="text-center py-4" style="background-color: var(--travel-blue);">
                    <h1 class="text-white m-0 fw-bold" style="letter-spacing: 1px;"><?php echo $translator->get('origin_name'); ?></h1>
                </div>
                
                <div class="card-body p-sm-5 text-center text-md-start">
                    <h3 class="mb-4 text-travel-blue fw-bold"><?php echo $translator->get('where_name_from'); ?></h3>
                    
                    <p class="lead text-muted mb-4"><?php echo $translator->get('long_text'); ?></p>
                </div>
            </div>

            <div class="card shadow-sm border-0 profile-card text-center text-md-start" style="background-color: var(--bg-light);">
                <div class="card-body p-4 p-sm-5 d-flex flex-column flex-md-row align-items-center justify-content-between gap-4">
                    
                    <div>
                        <h4 class="text-travel-blue fw-bold mb-2"><?php echo $translator->get('technical_doc'); ?></h4>
                        <p class="text-muted mb-0 small">
                            <?php echo $translator->get('only_eng'); ?>
                        </p>
                    </div>

                    <div class="flex-shrink-0">
                        <a href="img/eanasirtravel.pdf" download class="btn btn-copper px-4 py-3 btn-fw-semibold d-inline-flex align-items-center gap-2 shadow-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 16 16">
                                <path d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5"/>
                                <path d="M7.646 11.854a.5.5 0 0 0 .708 0l3-3a.5.5 0 0 0-.708-.708L8.5 10.293V1.5a.5.5 0 0 0-1 0v8.793L5.354 8.146a.5.5 0 1 0-.708.708l3 3z"/>
                            </svg>
                            <?php echo $translator->get('pdf'); ?>
                        </a>
                    </div>
                    
                </div>
            </div>
            
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>