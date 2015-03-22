# omega-pgp-mailer
WordPress plugin that allows a user to send the website owner a PGP encrypted message.

## Installation
1. Clone into your `wp-content/plugins` directory with `git clone https://github.com/bradkovach/omega-pgp-mailer.git omega-pgp-mailer`
2. Activate "Omega PGP Mailer" from the WordPress plugins screen
3. Visit Options > Omega PGP Mailer to configure the plugin.  The plugin does *not* need your private key.  You can force the form to be rendered on HTTPS pages only.
4. Use the `[omega_pgp_mailer]` shortcode whereever you want the PGP mail form to display.
