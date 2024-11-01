<?php
/*
	Plugin Name: S2MadMimi
	Plugin URI: http://leumund.ch
	Description: Add a Subscribe to MadMimi Checkbox after the comment form
	Author: Christian Leu
	Author URI: http://leumund.ch
	Version: 0.1
	
	Powered by the MadMailer class for PHP (http://nicholaswyoung.com/software/madmailer).
	
	Released under the MIT License

	Copyright (c) 2009 Nicholas Young

	Permission is hereby granted, free of charge, to any person obtaining a copy
	of this software and associated documentation files (the "Software"), to deal
	in the Software without restriction, including without limitation the rights
	to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
	copies of the Software, and to permit persons to whom the Software is
	furnished to do so, subject to the following conditions:

	The above copyright notice and this permission notice shall be included in
	all copies or substantial portions of the Software.

	THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
	IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
	FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
	AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
	LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
	OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
	THE SOFTWARE.
*/

	require('MadMailer.class.php');
	




function show_s2MadMimi_subscribe_option(){

if (!current_user_can('level_10') AND check_subscribe_2_madmimi_options()) { 
 if (!empty($_COOKIE['comment_author_email_'.COOKIEHASH]) AND $_COOKIE['mailchimp_checkbox_'.COOKIEHASH]!="subscriber") { 
// dann ist e_mail Adresse vorhanden die wir im mailchimp api pruefen ob schon abonnent ist
$email_adress = $_COOKIE['comment_author_email_'.COOKIEHASH];


$list_name = get_option('s2madmimi-list-name');
$user = get_option('s2madmimi-email-address');
$apikey = get_option('s2madmimi-api-key');

//echo get_option('s2madmimi-api-key');


$mailer = new MadMailer($user,$apikey, false, false);
$xml = $mailer->Memberships($email_adress);
//echo $xml->asXML();
							$check = $xml->xpath("/lists/list[@name='$list_name']");
//print_r($check);
//print_r($check);
//echo $xml->list->attributes()->name;
	 if (!empty($check)) {
	//	echo "ok";	
		$status = "subscribed"; 
		}
		else { 
		// echo "nok";
		//echo get_option('s2madmimi-api-key');
}

if ($status=="subscribed") { setcookie('mailchimp_checkbox_'. COOKIEHASH, 'subscriber', time() +60*60*5, COOKIEPATH); 
}
//$code =  $json->code;
//echo $status;
}
/*
else { $status = "subscribed"; }
if ($json->code=="215") { 
echo "<p>Vielen Dank. Du hast linkRiss abonniert. Bitte folge den Anweisungen in deiner Mailbox.</p>";
}

*/
if ($status!="subscribed" OR !isset($_COOKIE['comment_author_email_'.COOKIEHASH])) {
?><p <?php if ($sg_subscribe->clear_both) echo 'style="clear: both;" '; ?>class="subscribe-to-mailchimp">
	<input type="checkbox" name="s2madmimi" id="s2madmimi" value="s2madmimi" style="width: auto;" <? if (isset($_COOKIE['mailchimp_checkbox_'.COOKIEHASH])) { ?>checked="checked" <? } ?>  />
	<label for="s2madmimi"><?php echo get_option('s2madmimi-text'); ?></label>
	</p> <?
}
elseif ($status=="subscribed") { //setcookie('mailchimp_checkbox_'. COOKIEHASH, 'subscribe', time() +60*60, COOKIEPATH); 
}

}

}



add_action('thesis_hook_comment_form', 'show_s2madmimi_subscribe_option');


function check_subscribe_2_madmimi_options() {

$form_id =get_option('s2madmimi-form-id');
$list_name = get_option('s2madmimi-list-name');
$user = get_option('s2madmimi-email-address');
$apikey = get_option('s2madmimi-api-key');
$s2madmimitext = get_option('s2madmimi-text');

if ($form_id AND $list_name AND $user AND $apikey AND $s2madmimitext) { return true; }

}



function subscribe_to_madmimi($cid) { 


	if ( isset($_POST['s2madmimi']) AND check_subscribe_2_madmimi_options()) {
global $wpdb;
		$cid = (int) $cid;
		$id = (int) $id;
		$comment = $wpdb->get_row("SELECT * FROM $wpdb->comments WHERE comment_ID='$cid' LIMIT 1");


//create array of data to be posted
	$post_data['signup[name]'] = $comment->comment_author;
	$post_data['signup[email]'] = $comment->comment_author_email;
	
	//traverse array and prepare data for posting (key1=value1)
	foreach ( $post_data as $key => $value) {
		$post_items[] = $key . '=' . $value;
	}
	
	//create the final string to be posted using implode()
	$post_string = implode ('&', $post_items);
	
	//create cURL connection
	$curl_connection = 
	  curl_init(get_option('s2madmimi-form-id'));
	
	//set options
	curl_setopt($curl_connection, CURLOPT_CONNECTTIMEOUT, 30);
	curl_setopt($curl_connection, CURLOPT_USERAGENT, 
	  "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1)");
	curl_setopt($curl_connection, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($curl_connection, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($curl_connection, CURLOPT_FOLLOWLOCATION, 1);
	
	//set data to be posted
	curl_setopt($curl_connection, CURLOPT_POSTFIELDS, $post_string);
	
	//perform our request
	$result = curl_exec($curl_connection);
	//show information regarding the request
//	print_r(curl_getinfo($curl_connection));
	$ok = curl_getinfo($curl_connection);
//	echo curl_errno($curl_connection) . '-' . 
					curl_error($curl_connection);
//	echo $ok[url];
	//close the connection
	curl_close($curl_connection);



if ($ok[http_code]=="200") { setcookie('mailchimp_checkbox_'. COOKIEHASH, 'subscribe', time() +60*60*24, COOKIEPATH); }
		
}	

} 
add_action('comment_post', 'subscribe_to_madmimi');


function s2madmimi_options() { ?>
	<div class="wrap">
		<h2>Subscribe 2 MadMimi - Add a Subscribe to MadMimi Checkbox!</h2>
		<form method="post" action="options.php">
			<?php wp_nonce_field('update-options'); ?>
			<table class="form-table">
				<tr valign="top">
					<th scope="row">Mad Mimi List Name</th>
					<td><input type="text" name="s2madmimi-list-name" value="<?php echo get_option('s2madmimi-list-name'); ?>" /></td>
				</tr>
				<tr valign="top">
					<th scope="row">Mad Mimi Login E-Mail Address</th>
					<td><input type="text" name="s2madmimi-email-address" value="<?php echo get_option('s2madmimi-email-address'); ?>" /></td>
				</tr>
				<tr valign="top">
					<th scope="row">Mad Mimi API Key</th>
					<td><input type="text" name="s2madmimi-api-key" value="<?php echo get_option('s2madmimi-api-key'); ?>" /></td>
				</tr>
				<tr valign="top">
					<th scope="row">Mad Mimi Signup Form URL</th>
					<td><input type="text" name="s2madmimi-form-id" value="<?php echo get_option('s2madmimi-form-id'); ?>" /></td>
				</tr>
				<tr valign="top">
					<th scope="row">Your Text</th>
					<td><textarea rows="5" cols="25" name="s2madmimi-text"><?php if (get_option('s2madmimi-text')) { echo get_option('s2madmimi-text'); } else { echo "subscribe now to my newsletter"; } ?></textarea></td>
				</tr>

				
			</table>
			<input type="hidden" name="action" value="update" />
			<input type="hidden" name="page_options" value="s2madmimi-list-name,s2madmimi-form-id,s2madmimi-email-address,s2madmimi-api-key,s2madmimi-text" />
			<p class="submit">
				<input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
			</p>
		</form>
		<b>Instructions for use:</b><br />
		1. Insert your List Name<br />
		2. Enter the e-mail address you use to login at Mad Mimi, and your mailer API key above.<br />
		4. Enter the complete URL of your SignUp Form.<br /><br />
	</div>
<?php
}
function s2madmimi_menu() {
  add_options_page('s2madmimi Options', 's2madmimi', 1, 's2madmimi', 's2madmimi_options');
}
add_action('admin_menu', 's2madmimi_menu');
?>