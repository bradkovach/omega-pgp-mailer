<?php
/**
* Plugin Name: Omega PGP Mailer
* Plugin URI: https://bradkovach.com/wordpress/pgp-mailer
* Description: This allows a visitor to send a secure email to the owner of the site.
* Version: 0.1.0
* Author: Brad Kovach
* Author URI: https://bradkovach.com/
* License: GPL2
*/ 

class Omega_PGP_Mailer
{
	
	var $send_errors = array();
	var $sending = false;
	var $sent = false;
	
	public function __construct()
	{
		// register hooks
		add_action('wp_head', array($this, 'omega_pgp_mailer_wp_head') );
		add_shortcode('omega_pgp_mailer', array($this, 'omega_pgp_mailer_pgpmail_shortcode') );
		add_action('init', array($this, 'omega_pgp_mailer_send') );
			
		add_action( 'admin_menu', array($this, 'omega_pgp_mailer_add_admin_menu') );
		add_action( 'admin_init', array($this, 'omega_pgp_mailer_settings_init') );

	}
	
	public function omega_pgp_mailer_wp_head()
	{
		
	}
	
	public function omega_pgp_mailer_pgpmail_shortcode()
	{
		require 'form.php';
	}
	
	public function omega_pgp_mailer_send()
	{
		if( !empty($_POST['pgp']) )
		if( !empty($_POST['pgp']['message']) )
		if( check_admin_referer('omega_pgp_send', 'omega_nonce') )
		{
			$this->sending = true;
			
			$pgp = $_POST['pgp'];
			require 'libs/php-gpg/libs/GPG.php';
			
			$options = get_option( 'omega_pgp_mailer_settings' );
			
			$valid_public_key = false;
			if( isset($pgp['public_key']) )
			{
				try
				{
					$sender_public_key = new GPG_Public_key( trim($pgp['public_key']) );
					$valid_public_key = true;
				}
				catch(Exception $e)
				{
					$valid_public_key = false;
				}
			}
			
			$find_replace_map = array(
				'name' => (isset($pgp['name']) )? trim($pgp['name']) : "<not provided>",
				'email' => (isset($pgp['email']) )? trim($pgp['email']) : "<not provided>",
				'subject' => (isset($pgp['subject']) )? trim($pgp['subject']) : "<not provided>",
				'message' => (isset($pgp['message']) )? trim($pgp['message']) : "<not provided>",
				'public_key' => ( $valid_public_key )? trim($pgp['public_key']) : "<no valid key provided>"
			);
			
			$message = $options['omega_pgp_mailer_textarea_message_template'];
			foreach( $find_replace_map as $key => $value )
			{
				$message = str_replace(
					sprintf("{%s}", $key),
					$value,
					$message
				);
			}
			
			$pgp = new GPG();
			try
			{
				$public_key = new GPG_Public_key( $options['omega_pgp_mailer_textarea_public_key'] );
				$encrypted_message = $pgp->encrypt($public_key, $message);
				
				$this->sent = wp_mail(
					get_bloginfo('admin_email'),
					__("Encrypted message from " . get_bloginfo('name'), 'omega_pgp_mailer'),
					$encrypted_message
				);
			}
			catch(Exception $e)
			{
				$this->send_errors[] = $e->getMessage();
			}
			
			
			return;
		}
		
		
	}

	function omega_pgp_mailer_add_admin_menu()
	{
		add_options_page(
			'Omega PGP Mailer', 
			'Omega PGP Mailer', 
			'manage_options', 
			'omega_pgp_mailer',
			array($this, 'omega_pgp_mailer_options_page')
		);
	}
	
	public function omega_pgp_mailer_settings_init(  ) { 
	
		register_setting( 'omega_settings_page', 'omega_pgp_mailer_settings' );
	
		add_settings_section(
			'omega_pgp_mailer_omega_settings_page_section', 
			__( 'Configure Omega PGP Mailer', 'omega_pgp_mailer' ), 
			array($this, 'omega_pgp_mailer_settings_section_callback'), 
			'omega_settings_page'
		);
	
		add_settings_field( // require SSL
			'omega_pgp_mailer_checkbox_ssl_render', 
			__( 'Require HTTPS?', 'omega_pgp_mailer' ), 
			array($this, 'omega_pgp_mailer_checkbox_require_ssl_render'), 
			'omega_settings_page', 
			'omega_pgp_mailer_omega_settings_page_section' 
		);
	
		add_settings_field( 
			'omega_pgp_mailer_textarea_public_key', 
			__( 'PGP Public Key', 'omega_pgp_mailer' ), 
			array($this, 'omega_pgp_mailer_textarea_public_key_render'), 
			'omega_settings_page', 
			'omega_pgp_mailer_omega_settings_page_section' 
		);
		
		add_settings_field(
			'omega_pgp_mailer_textarea_message_template',
			__('Message Template', 'omega_pgp_mailer'),
			array($this, 'omega_pgp_mailer_textarea_message_template_render'),
			'omega_settings_page',
			'omega_pgp_mailer_omega_settings_page_section'
		);
	
	
	}
	
	
	public function omega_pgp_mailer_checkbox_require_ssl_render()
	{ 
	
		$options = get_option( 'omega_pgp_mailer_settings' );
		?>
		<input type='checkbox' name='omega_pgp_mailer_settings[omega_pgp_mailer_checkbox_require_ssl]' <?php checked( $options['omega_pgp_mailer_checkbox_require_ssl'], 1 ); ?> value='1'>
		<p class="description">Check this box to force the form and message to submit over HTTPS.</p>
		<?php
	
	}
	
	
	public function omega_pgp_mailer_textarea_public_key_render()
	{ 
	
		$options = get_option( 'omega_pgp_mailer_settings' );
		?>
		<textarea cols='50' rows='20' name='omega_pgp_mailer_settings[omega_pgp_mailer_textarea_public_key]'><?php echo $options['omega_pgp_mailer_textarea_public_key']; ?></textarea>
		<p class="description">Paste your entire public key here including headers, versions, and comments.</p>
		<?php
	
	}
	
	public function omega_pgp_mailer_textarea_message_template_render()
	{ 
	
		$options = get_option( 'omega_pgp_mailer_settings' );
		?>
		<textarea cols='50' rows='20' name='omega_pgp_mailer_settings[omega_pgp_mailer_textarea_message_template]'><?php echo $options['omega_pgp_mailer_textarea_message_template']; ?></textarea>
		<p class="description">Define the format of your message before it is encrypted.</p>
		<h3>Format your message with the following tokens</h3>
		<dl>
			<dt>{name}</dt>
				<dd>The name from the form</dd>
			<dt>{email}</dt>
				<dd>The sender's email address</dd>
			<dt>{subject}</dt>
				<dd>The subject specified by the sender</dd>
			<dt>{message}</dt>
				<dd>The message from the sender</dd>
			<dt>{public_key}</dt>
				<dd>The sender's public key.  The public key will be validated.</dd>
		</dl>
		<?php
	
	}
	
	
	public function omega_pgp_mailer_settings_section_callback()
	{ 
	
		echo __( 'Here are the settings for the Omega PGP Mailer. ', 'omega_pgp_mailer' );
	
	}
	
	
	public function omega_pgp_mailer_options_page()
	{ 
	
		?>
		<form action='options.php' method='post'>
			
			<h2>Omega PGP Mailer</h2>
			
			<?php
			settings_fields( 'omega_settings_page' );
			do_settings_sections( 'omega_settings_page' );
			submit_button();
			?>
			
		</form>
		<?php
	
	}
}

$omega_pgp_mailer = new Omega_PGP_Mailer();
