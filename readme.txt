=== Appboy Web SDK ===
Contributors: appboydev
Tags: marketing, analytics, segmentation, sdk, crm,
Requires at least: 2.0.0
Tested up to: 2.0.0
Stable tag: 2.0.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Braze empowers you to build better customer relationships through a multi-channel approach.

== Description ==

Effective marketing automation is an essential part of successfully scaling and managing your business. Braze empowers you to build better customer relationships through a seamless, multi-channel approach that addresses all aspects of the user life cycle. Braze helps you engage your users on an ongoing basis.

== Configuring The Plugin ==

Once you have the Braze Web SDK plugin installed and activated, navigate to `Settings` in your admin panel and click on `Braze Web SDK`. You will be directed to the settings page for the plugin. You will will see a list of options to configure your plugin. You **must** enter a valid `API Key` in order for the plugin to be initialized properly on your site. Once you have configured the plugin using the options form, click `Save Changes`. Navigate to your site and view the source of your HTML page. You should see something very similar to the following in the `<head>` section of your site:

`
<script type="text/javascript">
  +function(a,p,P,b,y) {
    (y = a.createElement(p)).type = 'text/javascript';
    y.src = 'https://js.appboycdn.com/web-sdk/2.0>/appboy.min.js';
    (c = a.getElementsByTagName(p)[0]).parentNode.insertBefore(y, c);
    if (y.addEventListener) {
      y.addEventListener('load', b, false);
    } else if (y.readyState) {
      y.onreadystatechange = b;
    }
  }(document, 'script', 'link', function() {
    appboy.initialize({{ YOUR API_KEY HERE }});
    appboy.display.automaticallyShowNewInAppMessages();
    appboy.openSession();
  });
</script>
`

== Configuring your site for Web Push ==
In order to send web push notifications on your WordPress site, please make sure your domain is [configured for push](https://www.braze.com/documentation/Web/#push-notifications).

== Testing Your Configuration ==

The easiest way to test if your plugin is configured properly is to open the JavaScript console on your site and enter `appboy`. If you see `Object {ab: Object, display: Object, sharedLib: Object}`, the web SDK is on your site and you now have access to the global `appboy` object. If you see `Uncaught ReferenceError: appboy is not defined`, you do not have the plugin configured properly.

Another way to make sure the plugin is configured correctly is to go back to the settings page for the plugin and select `Enable Logging`. Go back to a page on your site and view the JavaScript console. If you see messages from `appboy`, your plugin has been configured successfully and you now have access to the global `appboy` object. **NOTE:** while it is useful to enable logging for testing and debugging, it is recommended that you unselect `Enable Logging` in production.

== Troubleshooting ==

If you see errors in your JavaScript console from `appboy`, here are some resources that can help you troubleshoot:

* [Documentation](https://www.braze.com/documentation/Web/)
* [Public Repository](https://github.com/Appboy/appboy-web-sdk)
* [Full Technical Documentation](https://js.appboycdn.com/web-sdk/2.0/doc/module-appboy.html)
