=== All Path Messaging ===
Contributors: souptik
Tags: messaging, email, sms, push-notification
Requires at least: 4.4
Tested up to: 6.6.2
Requires PHP: 5.6
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Limitless Communication: All-in-one, super scalable, messaging Solution for WordPress.

== Description ==

[Check out the Github Repository ♥](https://github.com/Souptik2001/all-path-messaging)

**Limitless Communication:** All-in-one, super scalable, messaging Solution for WordPress.

Ok hold on! ✋. So, many words in one line.
Let's understand each one-by-one.

- **All-in-one:** What do you want? - Email, SMS, push-notification? Get all-in-one.
  - But I don't want to use `xyz` provider for SMS, I want to use `pqr`, can I have that? Yes it provides you with lot of pre implemented providers for all email, sms and push-notification.
- **Super Scalable:** But I want to use an email provider named `yxr` you haven't heard the name of. Now what? 🧐
  - No worries! Are you a developer? If yes, just write your own plugin and implement your own adapter and see it nicely hooked-up with "WordPress messaging". Please refer to [this](https://github.com/Souptik2001/wp-messaging/wiki/Create-your-own-Adapter-%F0%9F%9B%A0%EF%B8%8F) section for implementing adapters.

And that's how it provides **Limitless communication**! 🚀

https://www.youtube.com/watch?v=80hWdK8kREM

### Quick Links

[Setup ⚙️](https://github.com/Souptik2001/wp-messaging/wiki/Setup-%E2%9A%99%EF%B8%8F) | [Issues](https://github.com/Souptik2001/wp-messaging/issues) | [Services and functions 🧩](https://github.com/Souptik2001/wp-messaging/wiki/Services-and-functions-%F0%9F%A7%A9) | [Create your own Adapter 🛠️](https://github.com/Souptik2001/wp-messaging/wiki/Create-your-own-Adapter-%F0%9F%9B%A0%EF%B8%8F)

### Coming soon ⏳

- Push notifications
- Email Testing page
- SMS Testing page
- Push notifications Testing page

### Examples

#### Email 📧📨

Send an email through a particular adapter (with headers 😉) -

`
\Souptik\AllPathMessaging\Email\send(
  [ 'dev2@souptik.dev' ],
  'Yay its working!',
  'This is some long mail body.',
  'Souptik',
  'dev1@souptik.dev',
  [
   'cc' => [
    [
     'name'  => 'CC Test',
     'email' => 'cc@souptik.dev',
    ],
   ],
   'attachments' => [
    trailingslashit( WP_CONTENT_DIR ) . '/mu-plugins/test-all-path-messaging.php',
     'SameFileDifferentName.php' => trailingslashit( WP_CONTENT_DIR ) . '/mu-plugins/test-all-path-messaging.php',
   ],
  ],
  'mailgun'
 );
`

Just remove the last parameter! And now it uses the default selected adapter -

`
\Souptik\AllPathMessaging\Email\send(
  [ 'dev2@souptik.dev' ],
  'Yay its working!',
  'This is some long mail body.',
  'Souptik',
  'dev1@souptik.dev',
  [
   'cc' => [
    [
     'name'  => 'CC Test',
     'email' => 'cc@souptik.dev',
    ],
   ],
   'attachments' => [
    trailingslashit( WP_CONTENT_DIR ) . '/mu-plugins/test-all-path-messaging.php',
     'SameFileDifferentName.php' => trailingslashit( WP_CONTENT_DIR ) . '/mu-plugins/test-all-path-messaging.php',
   ],
  ],
 );
`

Checked the override `wp_mail` checkbox? Try a simple `wp_mail`! -

`
wp_mail(
  [ 'dev2@souptik.dev' ],
  'Yay its working!',
  'This is some long mail body - from <strong>wp_mail</strong>.',
  [],
  []
 );
`

#### SMS 📲

Send a SMS through a particular adapter -

`
\Souptik\AllPathMessaging\SMS\send( [ '+xxxxxxxxxxxx' ], 'Yay its working!', 'twilio' );
`

Just remove the last parameter! And now it uses the default selected adapter -

`
\Souptik\AllPathMessaging\SMS\send( [ '+xxxxxxxxxxxx' ], 'Yay its working!' );
`

### Creating your own adapter 🛠️

Here comes the cool part fellow developers! 💻

**Tip:** I have provided a dummy adapter for each service at `inc/<service>/adapters/dummy/`.

Consider that as the starting point and let's understand what each file does.

- Let's start with `namespace.php`. It is the entry point of your adapter.
  - In that you will see a simple `bootstrap` function.
  - In that function we are hooking into `EMAIL_SLUG . '_adapters'` and registering our adapter.
  - We pass the following data -
    - `slug`
    - `name`
    - `adapter` class object.
    - `options` - An array defining the settings required for this adapter, which will be used to automatically display the options on the settings page.
- Next is `class-adapter.php`, which is the Adapter class, which we initialized in the above file and passed it to `adapter`. It contains three simple functions -
  - `get_settings_fields` - This is the function which returns the array of options, which we used in the above file for `options`. Each option, will have -
    - The key as the name of the option.
    - And three values -
      - `label` - Label to display in the settings page beside the input.
      - `type` - Type of the field.
      - `sanitize_callback`
  - `get_settings` - This function returns an associative array, whose keys are the name of the options and the value as the value of the options.
  - `get_adapter` - This function will just return the core provider class, which is responsible for processing the message.
    - First check if `Utopia Messaging` already provides the provider or not [here](https://github.com/utopia-php/messaging?tab=readme-ov-file#adapters), for example `Utopia\Messaging\Adapter\Email\Mailgun`.
    - If it is present just use it. Easy peasy! ✨
    - But if not, let's code it ourself, because `Utopia Messaging` makes it so easy to create a new adapter!
- `class-dummy.php` is for that purpose, assuming you don't get a provider already present in `Utopia Messaging`.
  - It's basically a child class of `EmailAdapter` or `SMSAdapter`, which abstract a lot of stuff for us!
  - Let me explain two main functions, `_construct` and `process`. *Rest of the functions and properties are self-explanatory!* 😉
    - In the `_construct` function just put the arguments which you want to accept. That's it! And now they will be available everywhere else as `$this->param_name`!
    - The `process` function is the place where you have to write the main logic of calling your providers API to send the message.
      - As said above all the credentials/data you accepted through constructor are available as `$this->param_name`.
      - Build the `body` and the `headers`.
      - And then you can use the `$this->request` function as demonstrated in the dummy!
      - Create a response using Utopia's `Response` class.
      - Handle the errors, populate the response, return! Done! 🚀

== External services ==

= Brevo =

This plugin connect's to Brevo's API to send emails through Brevo. This is the [API](https://api.brevo.com/v3/smtp/email) it sends the request to.
The request is send everytime a mail is sent, and Brevo is selected as the default adapter from the plugin settings (or the function to send email through Brevo is directly invoked in the code).
Here is the [Terms of Use](https://www.brevo.com/legal/termsofuse/) and [Privacy Policy](https://www.brevo.com/legal/privacypolicy/) of the service.

= AWS SES =

This plugin connect's to AWS SES's API to send emails through AWS SES.
The request is send everytime a mail is sent, and AWS SES is selected as the default adapter from the plugin settings (or the function to send email through AWS SES is directly invoked in the code).
Here is the [Terms of Use](https://aws.amazon.com/service-terms/) and [Privacy Policy](https://docs.aws.amazon.com/ses/latest/dg/data-protection.html) of the service.

= Mailgun =

This plugin connect's to Mailgun's API to send emails through Mailgun.
The request is send everytime a mail is sent, and Mailgun is selected as the default adapter from the plugin settings (or the function to send email through Mailgun is directly invoked in the code).
Here is the [Terms of Use](https://www.mailgun.com/legal/terms/) and [Privacy Policy](https://www.mailgun.com/legal/privacy-policy/) of the service.

= Telesign =

This plugin connect's to Telesign's API to send SMS through Telesign.
The request is send everytime the function to send SMS is invoked with the Adapter as Telesign or the default adapter is set as Telesign.
Here is the [Terms of Use](hhttps://www.telesign.com/telesign-terms-of-service) and [Privacy Policy](https://www.telesign.com/privacy-policy) of the service.

= Twilio =

This plugin connect's to Twilio's API to send SMS through Twilio.
The request is send everytime the function to send SMS is invoked with the Adapter as Twilio or the default adapter is set as Twilio.
Here is the [Terms of Use](https://www.twilio.com/en-us/legal/tos) and [Privacy Policy](https://www.twilio.com/en-us/legal/privacy) of the service.

== Installation ==

Upload 'all-path-messaging' to the '/wp-content/plugins/' directory.

Activate the plugin through the 'Plugins' menu in WordPress.

== Frequently Asked Questions ==

= I have a codebase, where I have used `wp_mail`. Do I need to make any changes to the codebase after installing this plugin? =

Good news - no!
You just have to check the `Override wp_mail functionality checkbox` in the settings and that's it! All your mails you are triggering through `wp_mail` will be sent through your selected provider!

= I am already using an email marketing plugin. Can I use this plugin to use as the email sending service? =

Absolutely!
The plugin is made for that only! Keep using your existing email marketing plugin and just set the email provider as default (i.e it should use `wp_mail`). And that's it the emails will be send through the desired provider you select in this plugin.

= I need a provider called `xyz`, which is not present currently in this plugin. Do I have to ask you to integrate that? =

If you know coding! - You don't have to wait for it! [Go ahead and create your own adapter in your plugin by extending this plugin.](https://github.com/Souptik2001/wp-messaging/wiki/Create-your-own-Adapter-%F0%9F%9B%A0%EF%B8%8F)
If you are non-tech! - Please create an [issue](https://github.com/Souptik2001/wp-messaging/issues) over here, and I will try to integrate the provider ASAP.

== Screenshots ==

1. WordPress Options (Email)
2. WordPress Options (SMS)

== Changelog ==

= 1.0.0 =
* First stable release.