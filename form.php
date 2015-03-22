<?php
	$options = get_option( 'omega_pgp_mailer_settings' );
	
	$render_form = true;
	if( $options['omega_pgp_mailer_checkbox_require_ssl'] == 1)
	{
		$render_form = is_ssl();
	}
	
?>

<?php if( $render_form ): ?>

<?php if( $this->sending ): ?>
	<?php if( $this->sent ): ?>
		<p>Your message was sent successfully.</p>
	<?php else: ?>
		<p>The message did not send successfully.</p>
		
		<?php if( !empty($this->send_errors) ): ?>
			<p>The following diagnostic information might be of use.</p>
			<ul>
			<?php foreach($this->send_errors as $error): ?>
				<?php echo sprintf('<li>%s</li>', $error); ?>
			<?php endforeach; ?>
			</ul>
		<?php endif; ?>
		
	<?php endif; ?>
<?php endif; ?>

<form action="<?php echo esc_attr(get_permalink()); ?>" method="POST">

	<?php wp_nonce_field('omega_pgp_send', 'omega_nonce'); ?>
	
	<?php if( is_ssl() ) : ?>
		<p>Good news!  Your message will be sent to the server using HTTPS.</p>
	<?php else: ?>
		<p><strong>Warning!</strong> This message is not being sent over HTTPS.  
		The unencrypted contents of your message might be visible to an adversary. 
		Proceed with caution.</p>
	<?php endif; ?>
	
	<p>All of the information entered here will be encrypted before transit.  The message is the only required field.</p>
	

	<h3>Required Information</h3>
	
	<p>
	<label>
		Message (required)
		<textarea name="pgp[message]" rows="10"></textarea>
	</label>
	</p>
	
	<h3>Optional Information</h3>
	
	<p>
	<label>
		Subject
		<input type="text" name="pgp[subject]">
	</label>
	</p>
	
	<p>
	<label>
		Name
		<input type="text" name="pgp[name]">
	</label>
	</p>
	
	<p>
	<label>
		Email
		<input type="text" name="pgp[email]">
	</label>
	</p>
	
	<p>
	<label>
		Your Public Key
		<textarea name="pgp[public_key]"></textarea>
	</label>
	</p>
	<p>By providing your public key, I will be able to send you a secure reply.</p>
	
	<p><button type="submit">Send Secure Message</button></p>
</form>
<?php else: ?>
	<p>This form is required to be sent over SSL.  Try changing the page&apos;s scheme to HTTPS.</p>

<?php endif; ?>