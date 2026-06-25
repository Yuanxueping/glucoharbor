<?php
if (!defined('ROOT_PATH')) { define('ROOT_PATH',__DIR__.'/../..'); require ROOT_PATH.'/includes/bootstrap.php'; }
$page_title = '404 Not Found';
require ROOT_PATH.'/templates/front/_head.php';
?>
<section class="section"><div class="container narrow">
  <div class="error-page">
    <div class="error-code">404</div>
    <h2>Page Not Found</h2>
    <p>The page you're looking for doesn't exist or has been moved.</p>
    <a href="/" class="btn-home"><i class="fas fa-home"></i> Back to Home</a>
  </div>
</div></section>
<?php require ROOT_PATH.'/templates/front/_foot.php'; ?>
