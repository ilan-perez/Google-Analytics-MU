<?php
/*
Plugin Name:    Google Analytics MU
Plugin URI:     https://github.com/foe-services/google-analytics-mu
Description:    Collect network-wide Google Analytics statistics and allow site admins to use their own tracking codes
Version:        2.4
Author:         Foe Services Labs and Ilan Perez
Author URI:     http://labs.foe-services.de and http://ilanperez.com
License:        GPL2
Text Domain:    google-analytics-mu
Domain Path:    /languages/
*/

/*  
	Copyright 2013  Ilan Perez (email : ilan.perez@gmail.com)
	Copyright 2012  Foe Services Labs (http://labs.foe-services.de)
    Copyright 2011-12  Niklas Jonsson  (email : niklas@darturonline.se)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

add_action('network_admin_menu', 'ga_mu_plugin_network_menu');
add_action('admin_menu', 'ga_mu_plugin_menu');
add_action('wp_head', 'ga_mu_plugin_add_script_to_head');

define('UAID_OPTION','ga_mu_uaid');
define('TRACKING_TYPE','ga_mu_tracking_code_type');
define('REMARKETING_SWITCH','ga_mu_remarketing_switch');
define('MAINDOMAIN_OPTION', 'ga_mu_maindomain');
define('SITE_SPECIFIC_ALLOWED_OPTION','ga_mu_site_specific_allowed');
define('ANONYMIZEIP_ACTIVATED_OPTION','ga_mu_anonymizeip_activated');
define('PAGESPEED_ACTIVATED_OPTION','ga_mu_pagespeed_activated');
define('MAIN_BLOG_ID',1);



if ( !function_exists('ga_mu_plugin_network_menu') ) :
	function ga_mu_plugin_network_menu() {
		add_submenu_page( 'settings.php', __('Google Analytics', 'google-analytics-mu'), __('Google Analytics', 'google-analytics-mu'), 'manage_network', 'google-analytics-mu-network', 'google_analytics_mu_network_options');
	}
endif;

if ( !function_exists('ga_mu_plugin_menu') ) :
	function ga_mu_plugin_menu() {
		switch_to_blog(MAIN_BLOG_ID);
		$trackingCodeType = get_option(TRACKING_TYPE);
		$RemarketingSwitch = get_option(REMARKETING_SWITCH);
		$siteSpecificAllowed = get_option(SITE_SPECIFIC_ALLOWED_OPTION);
		restore_current_blog();
		if (isset($siteSpecificAllowed) && $siteSpecificAllowed != '' && $siteSpecificAllowed != '0') {
			add_options_page( __('Google Analytics', 'google-analytics-mu'), __('Google Analytics', 'google-analytics-mu'), 'manage_options', 'google-analytics-mu', 'google_analytics_mu_options');
		}
	}
endif;

if ( !function_exists('google_analytics_mu_options') ) :
	function google_analytics_mu_options() {
		
		global $blog_id;
                
                $plugin_dir = basename(dirname(__FILE__));
                load_plugin_textdomain( 'google-analytics-mu', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
		
		if (!current_user_can('manage_options'))  {
			wp_die( __('You do not have sufficient permissions to access this page.', 'google-analytics-mu') );
		}
		
		if (isset($_POST['UAID'])) {
			update_option(UAID_OPTION, preg_replace('/[^a-zA-Z\d\-]/','',$_POST['UAID']));
                        update_option(ANONYMIZEIP_ACTIVATED_OPTION, preg_replace('/[^a-zA-Z\d\-]/','',$_POST['AnonymizeIpActivated']));
                        update_option(PAGESPEED_ACTIVATED_OPTION, preg_replace('/[^a-zA-Z\d\-]/','',$_POST['PageSpeedActivated']));
			?>
			<div id="message" class="updated fade"><p><?php _e('Analytics ID saved.', 'google-analytics-mu') ?></p></div>
        <?php }	?>
			    	
		<div class="wrap">
                    <style>
                        .indent {padding-left: 2em}
                        h4 {margin-bottom: 0;}
                    </style>
                        <?php screen_icon( 'plugins' ); ?>
			<h2><?php _e('Google Analytics Settings', 'google-analytics-mu') ?></h2>
			<form name="form" action="" method="post">
                            <ul>
                                <li>
                                    <h4><?php _e('Google Analytics Property-ID', 'google-analytics-mu') ?>:</h4>
                                    <p class="indent">
                                        <input type="text" id="UAID" name="UAID"
					<?php 
					if ($blog_id == MAIN_BLOG_ID) {
						echo 'disabled="disabled"';
					} ?>
					value="<?php echo get_option(UAID_OPTION); ?>" placeholder="UA-01234567-8"/>
                                        <?php _e('Format: UA-01234567-8', 'google-analytics-mu')?>
                                    </p>
                                    <p class="indent"><?php printf( __('Go to your %s to find your Property-ID', 'google-analytics-mu'), '<a href="https://www.google.com/analytics/web/" target="_blank">' . __('Google Analytics Dashboard', 'google-analytics-mu') . '</a>') ?></p>
                                </li>
                                <li>
                                    <h4><?php _e('Anonymize IPs', 'google-analytics-mu') ?>:</h4>
                                    <p class="indent">
                                        <input type="checkbox" id="AnonymizeIpActivated" name="AnonymizeIpActivated" value="Activated"
                                        <?php
                                        $anonymizeIp = get_option(ANONYMIZEIP_ACTIVATED_OPTION);
                                        restore_current_blog();
                                        if (isset($anonymizeIp) && $anonymizeIp != '' && $anonymizeIp != '0') {
                                                echo 'checked="checked"';
                                        }
                                        ?>
                                         /> <?php _e('Activated', 'google-analytics-mu') ?>
                                    </p>
                                    <p class="indent">
                                        <?php _e('If AnonymizeIP is activated all tracked IPs will be saved in shortened form.', 'google-analytics-mu')?>
                                    </p>
                                </li>
                                <li>
                                    <h4><?php _e('Google PageSpeed', 'google-analytics-mu') ?>:</h4>
                                    <p class="indent">
                                        <input type="checkbox" id="PageSpeedActivated" name="PageSpeedActivated" value="Activated"
                                        <?php
                                        $PageSpeed = get_option(PAGESPEED_ACTIVATED_OPTION);
                                        restore_current_blog();
                                        if (isset($PageSpeed) && $PageSpeed != '' && $PageSpeed != '0') {
                                                echo 'checked="checked"';
                                        }
                                        ?>
                                         /> <?php _e('Activated', 'google-analytics-mu') ?>
                                    </p>
                                    <p class="indent">
                                        <?php _e('Activate to track performance via Google PageSpeed.', 'google-analytics-mu')?>
                                    </p>
                                </li>
                                <p>
                                    <input type="submit" id="submit" name="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
                                </p>
                                <p>
                                    <?php
					if ($blog_id == MAIN_BLOG_ID) {
						_e('As this is the main blog it uses the same ID as the network do. Changing this would change the networkwide ID; that is why it is disabled here.', 'google-analytics-mu');
                                    } ?>
                                </p>
                            </ul>
			</form>
		</div>
		<?php }
endif;


if ( !function_exists('google_analytics_mu_network_options') ) :
	function google_analytics_mu_network_options( $active_tab = '' ) {
	
		load_plugin_textdomain('google-analytics-mu', null, '/google-analytics-mu/languages/');
		
		if (!current_user_can('manage_options'))  {
			wp_die( __('You do not have sufficient permissions to access this page.', 'google-analytics-mu') );
		}
				
		if (current_user_can('manage_network'))  {
			
			if (isset($_POST['UAIDsuper'])) {
				if (isset($_POST['AllowSiteSpecificAccounts'])) {
					$allowSiteSpecificAccounts = 1;
				}
				else {
					$allowSiteSpecificAccounts = 0;
				}
                                if (isset($_POST['AnonymizeIpActivated'])) {
					$AnonymizeIpActivated = 1;
				}
				else {
					$AnonymizeIpActivated = 0;
				}
                                if (isset($_POST['PageSpeedActivated'])) {
					$AnonymizeIpActivated = 1;
				}
				else {
					$AnonymizeIpActivated = 0;
				}
				switch_to_blog(MAIN_BLOG_ID);
				update_option(UAID_OPTION, preg_replace('/[^a-zA-Z\d\-]/','',$_POST['UAIDsuper']));
                                update_option(MAINDOMAIN_OPTION, preg_replace('/[^a-zA-Z\d\-\.]/','',$_POST['MainDomain']));
                                update_option(TRACKING_TYPE, preg_replace('/[^a-zA-Z\d\-]/','',$_POST['TrackingCodeType']));
								update_option(REMARKETING_SWITCH, preg_replace('/[^a-zA-Z\d\-]/','',$_POST['RemarketingSwitch']));
                                update_option(SITE_SPECIFIC_ALLOWED_OPTION, preg_replace('/[^a-zA-Z\d\-]/','',$_POST['AllowSiteSpecificAccounts']));
                                update_option(ANONYMIZEIP_ACTIVATED_OPTION, preg_replace('/[^a-zA-Z\d\-]/','',$_POST['AnonymizeIpActivated']));
                                update_option(PAGESPEED_ACTIVATED_OPTION, preg_replace('/[^a-zA-Z\d\-]/','',$_POST['PageSpeedActivated']));
				restore_current_blog();
			?>
			<div id="message" class="updated fade"><p><?php _e('Network settings saved.', 'google-analytics-mu') ?></p></div>
			<?php }	} ?>
		
		<div class="wrap">
                        <style>
                            .indent {padding-left: 2em}
                            h4 {margin-bottom: 0;}
                        </style>
                        <?php screen_icon( 'plugins' ); ?>
			<h2><?php _e('Google Analytics Network Settings', 'google-analytics-mu') ?></h2>
                        
                        <?php
                        if (isset($_GET['tab'])) {
                            $active_tab = $_GET['tab'];
                        } else if ($active_tab == 'about') {
                            $active_tab = 'about';
                        } else {
                            $active_tab = 'network-settings';
                        } // end if/else 
                        ?>
                        
                        <h2 class="nav-tab-wrapper">
                            <a href="?page=google-analytics-mu-network&tab=network-settings" class="nav-tab <?php echo $active_tab == 'network-settings' ? 'nav-tab-active' : ''; ?>"><?php _e('Network-Settings', 'google-analytics-mu'); ?></a>
                            <a href="?page=google-analytics-mu-network&tab=about" class="nav-tab <?php echo $active_tab == 'about' ? 'nav-tab-active' : ''; ?>"><?php _e('About', 'google-analytics-mu'); ?></a>
                        </h2>

			<?php if (current_user_can('manage_network') && $active_tab == 'about') { ?>
                            <div class="wrap indent">

                                <h1>Google Analytics MU - WordPress Plugin</h1>
                                <p>
                                    <a href="https://wordpress.org/extend/plugins/google-analytics-mu/" target="_blank">WordPress.org</a> | 
                                    <a href="https://github.com/Foe-Services-Labs/Google-Analytics-MU/" target="_blank">GitHub Repository</a> | 
                                    <a href="https://github.com/Foe-Services-Labs/Google-Analytics-MU/issues" target="_blank">Issue Tracker</a>
                                </p>

                                <h3><?php _e('About', 'google-analytics-mu'); ?></h3>
                                <ul>
                                    <li>Fork of <a href="https://wordpress.org/extend/plugins/google-analytics-multisite-async/" target="_blank">Google Analytics Multisite Async</a> by <a href="http://www.darturonline.se/ga-mu-async.html" target="_blank">Niklas Jonsson</a></li>
                                    <li>Upstream: <a href="https://github.com/Foe-Services-Mirrors/google-analytics-multisite-async" target="_blank">Foe-Services-Mirrors / google-analytics-multisite-async</a> - Synced Git-Mirror of the official SVN-repo</li>
                                </ul>

                                <h3><?php _e('Development', 'google-analytics-mu'); ?></h3>
                                <ul>
                                    <li><?php _e('Main Developer', 'google-analytics-mu'); ?>: <a href="http://labs.foe-services.com/" target="_blank">Foe Services Labs</a> | <a href="https://github.com/Foe-Services-Labs/" target="_blank">Foe Services Labs@GitHub</a> | <a href="http://profiles.wordpress.org/foe-services-labs" target="_blank">Foe Services Labs@WP.org</a></li>
                                </ul>

                                <h3>WordPress</h3>
                                <ul>
                                    <li><?php printf( __('Requires at least: %s', 'google-analytics-mu'), '3.0.1'); ?></li>
                                    <li><?php printf( __('Tested up to: %s', 'google-analytics-mu'), '3.5.1'); ?></li>
                                </ul>

                                <h3><?php _e('Languages', 'google-analytics-mu'); ?></h3>
                                <ul>
                                    <li>English</li>
                                    <li>German</li>
                                </ul>
                                <p><?php printf( __('Help to translate at %s', 'google-analytics-mu'), '<a href="https://translate.foe-services.de/projects/google-analytics-mu" target="_blank">https://translate.foe-services.de/projects/google-analytics-mu</a>'); ?></p>

                                <h3><?php _e('License', 'google-analytics-mu'); ?></h3> <a href="http://www.gnu.org/licenses/gpl-2.0.html" target="_blank">GPLv2</a>

                            </div><!-- /.wrap --> 
					
                        <?php } elseif (current_user_can('manage_network')) { ?>
                            <div class="wrap">
                            <form name="form" action="" method="post">
                                <ul>
                                    <li>
                                        <h4><?php _e('Network Google Analytics Property-ID', 'google-analytics-mu') ?>:</h4>
                                        <p class="indent">
                                            <input type="text" id="UAIDsuper" name="UAIDsuper" value="<?php 
                                            switch_to_blog(MAIN_BLOG_ID);
                                            echo get_option(UAID_OPTION);
                                            restore_current_blog();
                                            ?>" placeholder="UA-01234567-8"/>
                                            <?php _e('Format: UA-01234567-8', 'google-analytics-mu')?>
                                        </p>
                                        <p class="indent"><?php printf( __('Go to your %s to find your Property-ID', 'google-analytics-mu'), '<a href="https://www.google.com/analytics/web/" target="_blank">' . __('Google Analytics Dashboard', 'google-analytics-mu') . '</a>') ?></p>
                                    </li>
								    <li>
                                        <h4><?php _e('Tracking Code Type', 'google-analytics-mu') ?>:</h4>
                                        <p class="indent">
                                            <input type="checkbox" id="TrackingCodeType" name="TrackingCodeType" value="universal"
                                            <?php
                                            switch_to_blog(MAIN_BLOG_ID);
                                            $trackingCodeType = get_option(TRACKING_TYPE);
                                            restore_current_blog();
                                            if (isset($trackingCodeType) && $trackingCodeType != '' && $trackingCodeType != '0') {
                                                    echo 'checked="checked"';
											  $hideDemoInt = true;
											}else{
											 $hideDemoInt = false;
											}
                                            ?>
                                             /> <?php _e('Universal', 'google-analytics-mu') ?>
                                        </p>
                                        <p class="indent">
                                            <?php _e('If you have migrated your Google Analytics account to the new universal code, check this box.', 'google-analytics-mu')?>
                                        </p>
                                    </li>
								  <li style="<?php echo (!$hideDemoInt ? "" : "display:none;" ); ?>">
                                        <h4><?php _e('Demographics and Interest Reports', 'google-analytics-mu') ?>:</h4>
                                        <p class="indent">
                                            <input type="checkbox" id="RemarketingSwitch" name="RemarketingSwitch" value="on"
                                            <?php
                                            switch_to_blog(MAIN_BLOG_ID);
                                            $RemarketingSwitch = get_option(REMARKETING_SWITCH);
                                            restore_current_blog();
                                            if (isset($RemarketingSwitch) && $RemarketingSwitch != '' && $RemarketingSwitch != '0') {
                                                    echo 'checked="checked"';
                                            }
                                            ?>
                                             /> <?php _e('On', 'google-analytics-mu') ?>
                                        </p>
                                        <p class="indent">
                                            <?php _e('If you have enabled Demographics and Interest Reports in Google Analytics account, check this box. Note: This is not supported by GA Universal Code <a href="https://developers.google.com/analytics/devguides/collection/upgrade/">More Info</a>', 'google-analytics-mu')?>
                                        </p>
                                    </li>
                                    <li>
                                        <h4><?php _e('Network domain', 'google-analytics-mu') ?>:</h4>
                                        <p class="indent">
                                            <input type="text" id="MainDomain" name="MainDomain" value="<?php 
                                            switch_to_blog(MAIN_BLOG_ID);
                                            echo get_option(MAINDOMAIN_OPTION);
                                            restore_current_blog();
                                            ?>" /> 
                                            <?php _e('Format: ".mydomain.com"', 'google-analytics-mu')?>
                                        </p>
                                        <p class="indent">
                                            <?php printf( __('Start with a dot! This value goes into %s', 'google-analytics-mu'), "<i>_gaq.push(['_setDomainName', 'NETWORK_DOMAIN'])</i>"); ?> 
                                        </p>
                                    </li>
                                    <li>
                                        <h4><?php _e('Site specific accounts', 'google-analytics-mu') ?>:</h4>
                                        <p class="indent">
                                            <input type="checkbox" id="AllowSiteSpecificAccounts" name="AllowSiteSpecificAccounts" value="Allowed"
                                            <?php
                                            switch_to_blog(MAIN_BLOG_ID);
                                            $siteSpecificAllowed = get_option(SITE_SPECIFIC_ALLOWED_OPTION);
                                            restore_current_blog();
                                            if (isset($siteSpecificAllowed) && $siteSpecificAllowed != '' && $siteSpecificAllowed != '0') {
                                                    echo 'checked="checked"';
                                            }
                                            ?>
                                             /> <?php _e('Allowed', 'google-analytics-mu') ?>
                                        </p>
                                        <p class="indent">
                                            <?php _e('If this is disallowed the Google Analytics settings page will not be visible to site admins.', 'google-analytics-mu')?><br>
                                            <?php _e('That means they will not be able to use their own Google Analytics accounts to track statistics.', 'google-analytics-mu')?>
                                        </p>
                                    </li>
                                    <li>
                                        <h4><?php _e('Anonymize IPs for Network-Tracking', 'google-analytics-mu') ?>:</h4>
                                        <p class="indent">
                                            <input type="checkbox" id="AnonymizeIpActivated" name="AnonymizeIpActivated" value="Activated"
                                            <?php
                                            switch_to_blog(MAIN_BLOG_ID);
                                            $anonymizeIp = get_option(ANONYMIZEIP_ACTIVATED_OPTION);
                                            restore_current_blog();
                                            if (isset($anonymizeIp) && $anonymizeIp != '' && $anonymizeIp != '0') {
                                                    echo 'checked="checked"';
                                            }
                                            ?>
                                             /> <?php _e('Activated', 'google-analytics-mu') ?>
                                        </p>
                                        <p class="indent">
                                            <?php _e('This option activates IP-Anonymization for the network domain on the main site and all subsites.', 'google-analytics-mu')?><br>
                                            <?php _e('If AnonymizeIP is activated all tracked IPs will be saved in shortened form.', 'google-analytics-mu')?>
                                        </p>
                                    </li>
                                    <li>
                                        <h4><?php _e('Google PageSpeed', 'google-analytics-mu') ?>:</h4>
                                        <p class="indent">
                                            <input type="checkbox" id="PageSpeedActivated" name="PageSpeedActivated" value="Activated"
                                            <?php
                                            switch_to_blog(MAIN_BLOG_ID);
                                            $PageSpeed = get_option(PAGESPEED_ACTIVATED_OPTION);
                                            restore_current_blog();
                                            if (isset($PageSpeed) && $PageSpeed != '' && $PageSpeed != '0') {
                                                    echo 'checked="checked"';
                                            }
                                            ?>
                                             /> <?php _e('Activated', 'google-analytics-mu') ?>
                                        </p>
                                        <p class="indent"><?php _e('Activate to track network-wide performance via Google PageSpeed.', 'google-analytics-mu')?></p>
                                    </li>
                                </ul>
                                <p><input type="submit" id="submit" name="submit" class="button-primary" value="<?php _e( 'Save Changes') ?>" /></p>
                            </form>
                            </div><!-- /.wrap --> 
                        <?php } ?>
		</div>		
		<?php }
endif;

if ( !function_exists('ga_mu_plugin_add_script_to_head') ) :
	function ga_mu_plugin_add_script_to_head() {
	
		switch_to_blog(MAIN_BLOG_ID);
		$uaidsuper = get_option(UAID_OPTION);
		$maindomain = get_option(MAINDOMAIN_OPTION);
                $anonymizeIpNetwork = get_option(ANONYMIZEIP_ACTIVATED_OPTION);
                $PageSpeedNetwork = get_option(PAGESPEED_ACTIVATED_OPTION);
		$trackingCodeType = get_option(TRACKING_TYPE);
				$RemarketingSwitch = get_option(REMARKETING_SWITCH);
		$siteSpecificAllowed = get_option(SITE_SPECIFIC_ALLOWED_OPTION);
		restore_current_blog();

		$uaid = get_option(UAID_OPTION);
		$anonymizeIp = get_option(ANONYMIZEIP_ACTIVATED_OPTION);
                $PageSpeed = get_option(PAGESPEED_ACTIVATED_OPTION);
                
		$super = false;
		$user = false;
		
		if (isset($uaidsuper) && $uaidsuper != '' && $uaidsuper != '0') {
			$super = true;
		}
		if (isset($uaid) && $uaid != '' && $uaid != '0') {
			$user = true;
		}
		
		if ($super && $user) {
			if ($uaidsuper == $uaid) {
				$user = false;
			}
		}
		
		if ($user == true && (!isset($siteSpecificAllowed) || $siteSpecificAllowed == '' || $siteSpecificAllowed == '0')) {
			$user = false;
		}
		
		if ($super || $user)
		{
                $prefix = ''
                ?>
                <script type="text/javascript">
               
                <?php
                if ($super) {
				  if($trackingCodeType == "universal"){
					  
                        ?>
					
					  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
					  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
					  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
					  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');
					
					  ga('create', '<?php echo $uaidsuper ?>', '<?php echo $maindomain ?>');
					  ga('send', 'pageview');

						
						
						  <?php }else{ ?>
						   var _gaq = _gaq || [];
                        _gaq.push(['_setAccount', '<?php echo $uaidsuper ?>']);
                        <?php
                        if ($maindomain)
                        { ?>
                        _gaq.push(['_setDomainName', '<?php echo $maindomain ?>']);
                        <?php
                        } ?>
                        _gaq.push(['_trackPageview']);
                        <?php 
                        if (isset($PageSpeedNetwork) && $PageSpeedNetwork != '' && $PageSpeedNetwork != '0')
                        { ?>
                        _gaq.push(['_trackPageLoadTime']);
                        <?php }
                        if (isset($anonymizeIpNetwork) && $anonymizeIpNetwork != '' && $anonymizeIpNetwork != '0')
                        { ?>
                        _gaq.push(['_gat._anonymizeIp']);
                        <?php
                        }                                        
                        $prefix = 'b.'; ?>
						    (function() {
                var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
				<?php 
				if($RemarketingSwitch == "on"){
					  
                        ?>
				
              
             ga.src = ('https:' == document.location.protocol ? 'https://' : 'http://') + 'stats.g.doubleclick.net/dc.js';
				<?php }else{ ?>
					  ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
				<?php	} ?>
                    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
				  })();
				  
						<?php
                	}
				}
                if ($user) {
                        if($trackingCodeType == "universal"){
					  
                        ?>
					
					  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
					  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
					  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
					  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');
					
					  ga('create', '<?php echo $uaid ?>', '<?php echo $maindomain ?>');
					  ga('send', 'pageview');

						
						
						  <?php }else{ ?>
						 var _gaq = _gaq || [];
                        _gaq.push(['<?php echo $prefix ?>_setAccount', '<?php echo $uaid ?>']);
                        _gaq.push(['<?php echo $prefix ?>_trackPageview']);
                        <?php
                        if (isset($PageSpeed) && $PageSpeed != '' && $PageSpeed != '0')
                        { ?>
                            _gaq.push(['<?php echo $prefix ?>_trackPageLoadTime']);
                        <?php }
                        if (isset($anonymizeIp) && $anonymizeIp != '' && $anonymizeIp != '0')
                        { ?>
                        _gaq.push(['<?php echo $prefix ?>_gat._anonymizeIp']);
                        <?php
                        } ?>
						  (function() {
                var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
          <?php 
				if($RemarketingSwitch == "on"){
					  
                        ?>
				
              
             ga.src = ('https:' == document.location.protocol ? 'https://' : 'http://') + 'stats.g.doubleclick.net/dc.js';
				<?php }else{ ?>
					  ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
				<?php	} ?>
                var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
                  })();
				  <?php
                }
				}
                ?>
            
                </script>			
                <?php }
	}
endif;
?>