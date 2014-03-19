<?php
queue_js('login');
$pageTitle = __('Log In');
head(array('bodyclass' => 'login', 'title' => $pageTitle), $header);
?>
<h1><?php echo $pageTitle; ?></h1>

<p id="login-links">
<?php /*<span id="backtosite"><?php echo link_to_home_page(__('Go to Home Page')); ?></span>  |  <span id="forgotpassword"><?php echo link_to('users', 'forgot-password', __('Lost your password?')); ?></span> */ ?>
<span id="backtosite"><?php echo "<a href='http://wiki.agroknow.gr/agroknow/index.php/AgLR' target='_blank'>".__('Learn about AgLR')."</a>"; ?></span>  |  <span id="forgotpassword"><?php echo link_to('users', 'forgot-password', __('Lost your password?')); ?></span>
</p>

<?php echo flash(); ?>
    
<?php echo $this->form->setAction($this->url('users/login')); ?>

<?php foot(array(), $footer); ?>
