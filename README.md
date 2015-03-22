# omega-pgp-mailer
WordPress plugin that allows a user to send the website owner a PGP encrypted message.

## Installation
1. Clone into your `wp-content/plugins` directory with `git clone --recursive https://github.com/bradkovach/omega-pgp-mailer.git omega-pgp-mailer`
 1. Make sure to use the `--recursive` option or else required dependencies will not be downloaded.
2. Activate "Omega PGP Mailer" from the WordPress plugins screen
3. Visit Options > Omega PGP Mailer to configure the plugin.  The plugin does *not* need your private key.  You can force the form to be rendered on HTTPS pages only.
 1. Check "Require HTTPS?" to force the form to render over HTTPS.
 2. Paste your entire *public key* into the PGP Public Key box.  Include headers, versions, comments and footers.  **DO NOT use your private key.**
 3. Modify the message template.  This will affect how the message is formatted after it is decrypted.  An example message template is provided below.
4. Use the `[omega_pgp_mailer]` shortcode whereever you want the PGP mail form to display.

## Example Message Template

```text
Name: {name}
Email: {email}
Subject: {subject}

{message}

Sender's Public Key:
{public_key}
```
