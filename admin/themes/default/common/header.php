<!DOCTYPE html>
<html lang="<?php echo get_html_lang(); ?>">
    <head>
        <meta charset="utf-8">
        <title><?php echo settings('site_title');
echo isset($title) ? ' | ' . strip_formatting($title) : ''; ?></title>

        <?php
        queue_css('default', 'all');
        queue_css('jquery-ui', 'screen');
        queue_js('globals');
        ?>

        <!-- Plugin Stuff -->
<?php admin_plugin_header(); ?>

        <!-- Stylesheets -->
<?php display_css(); ?>

        <!-- JavaScripts -->
<?php display_js(); ?>

    </head>
    <?php
    session_start();
// store session data
//echo $locale = Zend_Registry::get('Zend_Locale');
    $_SESSION['get_language'] = get_language_for_switch();
    $_SESSION['get_language_omeka'] = get_language_for_omeka_switch();
    $_SESSION['get_language_for_internal_xml'] = get_language_for_internal_xml();
    ?>
    <?php echo body_tag(array('id' => @$bodyid, 'class' => @$bodyclass)); ?>

    <!--Code for issue collector about bugs!!-->
    <script type="text/javascript" src="https://agroknow.atlassian.net/s/d41d8cd98f00b204e9800998ecf8427e/en_US-8bdlft-1988229788/6318/8/1.4.10/_/download/batch/com.atlassian.jira.collector.plugin.jira-issue-collector-plugin:issuecollector/com.atlassian.jira.collector.plugin.jira-issue-collector-plugin:issuecollector.js?collectorId=0d9e0abb"></script>

    <div class="hide"><a href="#content"><?php echo __('Skip Navigation'); ?></a></div>
    <div id="wrap">
        <div id="header">
            <div id="site-title"><?php echo link_to_admin_home_page(settings('site_title')); ?></div>

            <div id="site-info">
                <?php if (current_user()): ?>
                    <p id="welcome">
                        <?php $userLink = '<a href="' . html_escape(uri('users/edit/' . current_user()->id)) . '">' . html_escape(current_user()->first_name) . '</a>'; ?>
                        <?php echo __('Welcome, %s', $userLink); ?> | <a href="<?php echo html_escape(uri('users/logout')); ?>" id="logout"><?php echo __('Log Out'); ?></a></p>
                    <?php endif; ?>
                    <?php if (has_permission('Settings', 'edit')): ?>
                    <a href="<?php echo html_escape(uri('settings')); ?>" id="settings-link"><?php echo __('Settings'); ?></a>
                <?php endif; ?>
                <div id="languages" style="position: absolute; top: 40px; right: 0px; width: 200px;">
                    <?php echo language_switcher(); ?>
                </div>
                <?php //echo link_to_home_page(__('View Public Site'), array('id'=>'public-link')); ?>
                <?php echo plugin_append_to_admin_site_info(); ?>
            </div>
            <?php echo common('primary-nav'); ?>

        </div>
        <div id="content"<?php echo isset($content_class) ? ' class="' . $content_class . '"' : ''; ?>>

