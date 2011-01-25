<?php
/*
 * Plugin Name:   cbnet Multi Author Comment Notification
 * Plugin URI:    http://www.chipbennett.net/wordpress/plugins/cbnet-multi-author-comment-notification/
 * Description:   Get email notifications for comments made on other authors' posts . (Note: this plugin is a fork of the MaxBlogPress Multi Author Comment Notification plugin, with registration/activiation functionality removed.) Adjust settings <a href="options-general.php?page=cbnet-multi-author-comment-notification/cbnet-multi-author-comment-notification.php">here</a>.
 * Version:       1.1.1
 * Author:        chipbennett
 * Author URI:    http://www.chipbennett.net/
 *
 * License:       GNU General Public License, v2 (or newer)
 * License URI:  http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * This program was modified from MaxBlogPress Multi Author Comment Notification plugin, version 1.0.5, 
 * Copyright (C) 2007 www.maxblogpress.com, released under the GNU General Public License.
 */
 
define('cbnetmcn_NAME', 'cbnet Multi Author Comment Notification');
define('cbnetmcn_VERSION', '1.1.1');

/**
 * MultiCommentNotifications - Multi Author Comment Notification Class
 * Holds all the necessary functions and variables
 */
class MultiCommentNotifications 
{
	var $cbnetmcn_path = "";
	var $cbnetmcn_options = array();
	var	$default_options = array('emails' => '', 'additional_emails' => '', 'not_to_logged_users' => 0, 'disabled' => 0);

	/**
	 * Constructor. Adds the plugin's actions/filters.
	 * @access public
	 */
	function MultiCommentNotifications() { 
		$this->cbnetmcn_path     = preg_replace('/^.*wp-content[\\\\\/]plugins[\\\\\/]/', '', __FILE__);
		$this->cbnetmcn_path     = str_replace('\\','/',$this->cbnetmcn_path);
		$this->cbnetmcn_siteurl  = get_bloginfo('wpurl');
		$this->cbnetmcn_siteurl  = (strpos($this->cbnetmcn_siteurl,'http://') === false) ? get_bloginfo('siteurl') : $this->cbnetmcn_siteurl;
		$this->site_email   = ( is_email($this->settings['email']) && $this->settings['email'] != 'email@example.com' ) ? $this->settings['email'] : get_bloginfo('admin_email');
		$this->site_name    = ( $this->settings['name'] != 'YOUR NAME' && !empty($this->settings['name']) ) ? stripslashes($this->settings['name']) : get_bloginfo('name');
		$this->cbnetmcn_fullpath = $this->cbnetmcn_siteurl.'/wp-content/plugins/'.substr($this->cbnetmcn_path,0,strrpos($this->cbnetmcn_path,'/')).'/';
		$this->cbnetmcn_abspath  = str_replace("\\","/",ABSPATH); 
		$this->img_how      = '<img src="'.$this->cbnetmcn_fullpath.'images/how.gif" border="0" align="absmiddle">';
		$this->img_comment  = '<img src="'.$this->cbnetmcn_fullpath.'images/comment.gif" border="0" align="absmiddle">';
	  
	    add_action('activate_'.$this->cbnetmcn_path, array(&$this, 'cbnetmcnActivate'));
		add_action('admin_menu', array(&$this, 'cbnetmcnAddMenu'));
		
		if( !$this->cbnetmcn_options = get_option('multi_comment_notifications') ) {
			$this->cbnetmcn_options = array();
		}
		add_action('comment_post', array(&$this, 'cbnetmcnCommentNotification'));
	}
	
	/**
	 * Called when plugin is activated. Adds option_value to the options table.
	 * @access public
	 */
	function cbnetmcnActivate() {
		add_option('multi_comment_notifications', $this->default_options, 'Multi Author Comment Notification plugin option', 'no');
		return true;
	}
	
	/**
	 * Sends email
	 * @param string $to
	 * @param string $subject
	 * @param string $message
	 * @access public
	 */
	function cbnetmcnSendMail($to, $subject, $message) {
		$site_name  = str_replace('"', "'", $this->site_name);
		$site_email = str_replace(array('<', '>'), array('', ''), $this->site_email);
		$charset    = get_settings('blog_charset');
		$headers    = "From: \"{$site_name}\" <{$site_email}>\n";
		$headers   .= "MIME-Version: 1.0\n";
		$headers   .= "Content-Type: text/plain; charset=\"{$charset}\"\n";
		$subject    = '['.get_bloginfo('name').'] '.$subject;
		return wp_mail($to, $subject, $message, $headers);
	}
	
	/**
	 * Sends notification emails when new comment is added to the post
	 * @param integer $comment_id
	 * @access public
	 */
	function cbnetmcnCommentNotification($comment_id = 0) {
		global $wpdb;
		if ( is_user_logged_in() && $this->cbnetmcn_options['not_to_logged_users'] == 1 ) {
			return $comment_id;
		}
		if ( intval($comment_id) > 0 ) {
			$query = "SELECT t1.comment_post_ID,t1.comment_date,t1.comment_author,t1.comment_author_email,t1.comment_author_url,t1.comment_content,t1.comment_approved,
					  t2.ID,t2.post_title,t2.post_author FROM $wpdb->comments t1 INNER JOIN $wpdb->posts t2 ON t1.comment_post_ID=t2.ID 
					  WHERE comment_ID=$comment_id";
			$row = $wpdb->get_row($query, ARRAY_A);
			$pauth_fname = get_usermeta($row['post_author'],'first_name');
			$pauth_lname = get_usermeta($row['post_author'],'last_name');
			$pauth_name  = $pauth_fname.' '.$pauth_lname;
			$auth        = get_userdata($row['post_author']);
			$pauth_email = $auth->user_email;
			if ( trim($pauth_name) == '' ) {
				$pauth_name = $auth->user_nicename;
			}
			
			$subject  = "New Comment On: ".$row['post_title'];
			$message  = '';
			if ( $row['comment_approved'] == 'spam' ) {
				return $comment_id;
			} else if ( $row['comment_approved'] == 0 ) {
				$message .= "Note: This comment is under moderation\n\n";
			}
			$message .= "New comment on: \"".$row['post_title']."\"\n".get_permalink($row['comment_post_ID'])."\n";
			$message .= "(Post Author: ".$pauth_name.")\n\n";
			$message .= "Commenter's Name: ".$row['comment_author']."\n";
			$message .= "Email: ".$row['comment_author_email']."\n";
			$message .= "URL: ".$row['comment_author_url']."\n\n";
			$message .= "Comment:\n\n".stripslashes($row['comment_content'])."\n\n\n";
			if ( $row['comment_approved'] != 1 ) {
				$message .= "Approve it: ".$this->cbnetmcn_siteurl."/wp-admin/comment.php?action=mac&c=".$comment_id."\n";
			}
			$message .= "Delete it: ".$this->cbnetmcn_siteurl."/wp-admin/comment.php?action=cdc&c=".$comment_id."\n";
			$message .= "Spam it: ".$this->cbnetmcn_siteurl."/wp-admin/comment.php?action=cdc&dt=spam&c=".$comment_id."\n";
			
			$user_emails = str_replace($pauth_email,'',$this->cbnetmcn_options['emails']); // remove post author email
			$user_emails = str_replace(',,',',',$user_emails);
			$user_emails = str_replace($row['comment_author_email'],'',$user_emails); // remove comment author email
			$user_emails = str_replace(',,',',',$user_emails);
			$cbnetmcn_emails  = $user_emails.','.$this->cbnetmcn_options['additional_emails'];
			$cbnetmcn_emails  = trim($cbnetmcn_emails,',');
			if ( trim($cbnetmcn_emails) != '' ) {
				$cbnetmcn_email_arr = explode(',', $cbnetmcn_emails);
				if ( count($cbnetmcn_email_arr) > 0 ) {
					foreach ( $cbnetmcn_email_arr as $cbnetmcn_email ) {
						$this->cbnetmcnSendMail(trim($cbnetmcn_email), $subject, $message);
					}
				}
			}
		}
	}

	/**
	 * Adds "Multi Author Comment Notification" link to admin Options menu
	 * @access public 
	 */
	function cbnetmcnAddMenu() {
		add_options_page('Multi Author Comment Notification', 'Multi Author Comment Notification', 'manage_options', $this->cbnetmcn_path, array(&$this, 'cbnetmcnOptionsPg'));
	}
	
	/**
	 * Page Header
	 */
	function cbnetmcnHeader() {
		echo '<h2>'.cbnetmcn_NAME.' '.cbnetmcn_VERSION.'</h2>';
	}
	
	/**
	 * Page Footer
	 */
	function cbnetmcnFooter() {
		echo '<p style="text-align:center;margin-top:3em;"><strong>'.cbnetmcn_NAME.' '.cbnetmcn_VERSION.' by <a href="http://www.chipbennett.net/" target="_blank" >Chip Bennett</a></strong></p>';
	}
	
	/**
	 * Plugin's Options page
	 * Carries out all the operations in Options page
	 * @access public 
	 */
	function cbnetmcnOptionsPg() {
		global $wpdb;
		$msg = '';

			$this->cbnetmcn_request = $_REQUEST['cbnetmcn'];
			if ( $this->cbnetmcn_request['save'] ) {
				$this->cbnetmcn_options['not_to_logged_users'] = $this->cbnetmcn_request['not_to_logged_users'];
				$this->cbnetmcn_options['disabled'] = $this->cbnetmcn_request['disabled'];
				foreach ( (array)$this->cbnetmcn_request['emails'] as $email ) {
					$cbnetmcn_emails .= ','.$email;
				}
				$cbnetmcn_emails = trim($cbnetmcn_emails,',');
				$this->cbnetmcn_options['emails'] = $cbnetmcn_emails;
				$this->cbnetmcn_options['additional_emails'] = trim($this->cbnetmcn_request['additional_emails'],',');
				update_option("multi_comment_notifications", $this->cbnetmcn_options);
				$msg = 'Options saved';
			}
			if ( trim($msg) !== '' ) {
				echo '<div id="message" class="updated fade"><p><strong>'.$msg.'</strong></p></div>';
			}
			$logged_users_chk = '';
			$disable_chk      = '';
			if ( $this->cbnetmcn_options['not_to_logged_users'] == 1 ) {
				$logged_users_chk = ' checked ';
			}
			if ( $this->cbnetmcn_options['disabled'] == 1 ) {
				$disable_chk = ' checked ';
			}
			$user_array = array();
			$query  = "SELECT ID FROM $wpdb->users";
			$result = $wpdb->get_results($query, ARRAY_A);
			foreach ( (array)$result as $key=>$row ) {
				$data = get_userdata($row['ID']);
				$data->wp_user_level = intval($data->wp_user_level);
				$userid   = $data->ID;
				$username = $data->user_login;
				$fullname = $data->first_name.' '.$data->last_name;
				$email    = $data->user_email;
				$level    = @key($data->wp_capabilities);
				$user_array[$data->wp_user_level][] = array($userid, $username, $fullname, $email, $level);
			}
			ksort($user_array, SORT_DESC);
			reset($user_array);
			$user_array = array_reverse($user_array, TRUE);
			?>
			<div class="wrap">
			 <?php $this->cbnetmcnHeader(); ?>
			 <form method="post">
			 <p>
			 <h3><?php _e('Send new comment notification to the following users', 'cbnetmcn'); ?>:</h3>
			 <table border="0" width="80%" cellpadding="3" cellspacing="1">
			 <?php 
			 $last_user_level = '';
			 foreach ( (array)$user_array as $user_level=>$user_arr ) { 
			 ?>
				<tr><td colspan="4"><strong><?php echo ucfirst($user_arr[0][4]);?></strong></td></tr>
				<tr bgcolor="#dddddd">
				 <td width="3%"></td>
				 <td><strong><?php _e('Username', 'cbnetmcn'); ?></strong></td>
				 <td><strong><?php _e('Name', 'cbnetmcn'); ?></strong></td>
				 <td><strong><?php _e('E-mail', 'cbnetmcn'); ?></strong></td>
				</tr>
			 <?php
				foreach ( (array)$user_arr as $user_detail ) { 
					$user_chk = '';
					if ( strpos($this->cbnetmcn_options['emails'],$user_detail[3]) !== false ) {
						$user_chk = ' checked ';
					}
			 ?>
					<tr class="alternate">
					 <td><input type="checkbox" name="cbnetmcn[emails][]" value="<?php echo $user_detail[3];?>" <? echo $user_chk;?> /></td>
					 <td><?php echo $user_detail[1];?></td>
					 <td><?php echo $user_detail[2];?></td>
					 <td><?php echo $user_detail[3];?></td>
					</tr>
			 <?php	
				}
			 } 
			 ?>
			 </table>
			 </p>
			 <p><br /><?php _e('Send new comment notification to the following E-mails as well: (separate multiple E-mails with comma)', 'cbnetmcn'); ?><br />
			 <input type="text" name="cbnetmcn[additional_emails]" value="<?php echo $this->cbnetmcn_options['additional_emails'];?>" size="85"></p>
			 <p><input type="checkbox" name="cbnetmcn[not_to_logged_users]" value="1" <?php echo $logged_users_chk;?> /> <?php _e('Don\'t send comment notification if registered user (admin, author, editor etc...) posts a comment', 'cbnetmcn'); ?></p>
			 <p><input type="checkbox" name="cbnetmcn[disabled]" value="1" <?php echo $disable_chk;?> /> <?php _e('Disable comment notification', 'cbnetmcn'); ?></p>
			 <p><input type="submit" name="cbnetmcn[save]" value="<?php _e('Save', 'cbnetmcn'); ?>" class="button" /></p>
			 </form>
			 <?php $this->cbnetmcnFooter(); ?>
			</div>
			<?php
		}
	

	
} // Eof Class

$MultiCommentNotifications = new MultiCommentNotifications();
?>