<?php
/*
Plugin Name: WP Spam Blacklister
Plugin URI: http://www.blueliquiddesigns.com.au/index.php/wp-automatic-spam-blacklister
Description: Automatically places IP address from marked spam comment in the comments blacklist (Settings -> Discussion).
Version: 1.0.0
Author: Blue Liquid Designs
Author URI: http://www.blueliquiddesigns.com.au

------------------------------------------------------------------------

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA
*/

/*error_reporting(E_ALL ^ E_NOTICE);
ini_set('error_reporting', E_ALL ^ E_NOTICE);
ini_set('display_errors',1);*/

add_action('spammed_comment',  array('BLDSpamBlacklister', 'add_blacklist_ip'), 10, 1);
add_action('unspammed_comment',  array('BLDSpamBlacklister', 'remove_blacklist_ip'), 10, 1);
register_activation_hook( __FILE__, array('BLDSpamBlacklister', 'install') );

class BLDSpamBlacklister 
{
	public static function install() {
	 	$installation = get_option( 'bldSpamBlacklister_install' );
		
		if(strlen($installation) == 0)
		{
			/* get all comments */
			$comments = get_comments(array('status' => 'spam'));
			foreach($comments as $comment)
			{
				$ip = $comment->comment_author_IP;	
				self::add_to_blacklist($ip);
			}
			add_option( 'bldSpamBlacklister_install', 'true');
		}
		
	}
	
	public static function add_blacklist_ip($comment_id) {
		/*global $comment_id;*/
		self::do_spam_comment($comment_id);
	}
	
	public static function do_spam_comment($comment_id) {
		
		/* get the spam comment ID */
		$comment = get_comment($comment_id);
		$author_ip = $comment->comment_author_IP;
		
		self::add_to_blacklist($author_ip);
		
	}
	
	public static function add_to_blacklist($ip_address)
	{
		/* pull the comments blacklist from the database */
		$comments_blacklist = get_option( 'blacklist_keys' );
		
		/* split string into array */
		$blacklist_array = explode("\n", $comments_blacklist);
		
		/* if IP not found in array then insert */
		if(!in_array($ip_address, $blacklist_array) && strlen($ip_address) > 0)
		{
			$blacklist_array[] = $ip_address;
			$new_blacklist = implode("\n", $blacklist_array);
			update_option('blacklist_keys', $new_blacklist);
			return true;
		}
		 
		return false;	
	}
	
	public static function remove_blacklist_ip($comment_id) 
	{
		/* get the spam comment ID */
		$comment = get_comment($comment_id);
		$author_ip = $comment->comment_author_IP;
		
		self::remove_from_blacklist($author_ip);
	}
	
	public static function remove_from_blacklist($ip_address)
	{
		/* pull the comments blacklist from the database */
		$comments_blacklist = get_option( 'blacklist_keys' );
		
		if(strlen($ip_address) > 0)
		{
			$new_blacklist = str_replace("\n".$ip_address, '', $comments_blacklist); 
			update_option('blacklist_keys', $new_blacklist);
		}
		
		return true;
		 		
	}	
}

?>