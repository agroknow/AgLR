<?php 

$bodyclass = 'page simple-page';
if (simple_pages_is_home_page(get_current_simple_page())) {
    $bodyclass .= ' simple-page-home';
} ?>

<?php head(array('title' => html_escape(simple_page('title')), 'bodyclass' => $bodyclass, 'bodyid' => html_escape(simple_page('slug')))); ?>
<div id="page-body" class="one_column">
<div class="column" id="column-c">
<!--<div id="primary"> -->
    <?php if (!simple_pages_is_home_page(get_current_simple_page())): ?>
    <p id="simple-pages-breadcrumbs"><?php echo simple_pages_display_breadcrumbs(); ?></p>
    <?php endif; ?>
    <h1><?php echo html_escape(simple_page('title')); ?></h1>
    <?php echo eval('?>' . simple_page('text')); ?>
<!--</div> -->
<?php if (!simple_pages_is_home_page(get_current_simple_page())): ?>
<!--<div id="secondary">
    <?php //echo simple_pages_navigation(); ?>
</div> -->
<?php endif; ?>
</div>
</div>
<div class="clear"></div><!--clear DIV NEEDS TO BE ADDED TO ALL TEMPLATES-->
</div><!--end page-body div-->
<div class="clear"></div><!--clear DIV NEEDS TO BE ADDED TO ALL TEMPLATES-->
</div><!--end page-container div-->
<?php echo foot(); ?>