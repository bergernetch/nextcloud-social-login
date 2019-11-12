# Social login with Patch for PlanningCenter

For the original code see https://github.com/zorn-v/nextcloud-social-login

## Installation

Just install the sociallogin App from Nextcloud App Store, then replace the file nextcloud-social-login/lib/Provider/CustomOAuth2.php with the one from this repo.

After an update, very likely this must be done again, not tested yet.

## Custom OAuth2/OIDC groups

The custom OAuth2 code has been changed to allow a OAuth2 with PlanningCenter. 
Groups are pulled from PlanningCenter from a custom tab.

## Config

You can use `'social_login_auto_redirect' => true` setting in `config.php` for auto redirect unauthorized users to social login if only one provider is configured.

## Hint

### About Callback(Reply) Url
You can copy link from specific login button on login page and paste it on provider's website as callback url!
Some users may get strange reply(Callback) url error from provider even if you pasted the right url, that's because your nextcloud server may generate http urls when you are actually using https.
Please set 'overwriteprotocol' => 'https', in your config.php file.
