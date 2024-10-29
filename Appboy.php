<?php

/**
* Appboy Web SDK
* License available at https://github.com/Appboy/appboy-web-sdk-wordpress/blob/master/LICENSE
*
* @author      Appboy, Inc.
* @copyright   2016 Appboy, Inc.
*
* @wordpress-plugin
* Plugin Name: Appboy Web SDK
* Description: Appboy empowers you to build better customer relationships through a multi-channel approach.
* Version:     1.0.0
* Author:      Appboy, Inc.
* Author URI:  https://appboy.com
*/

namespace Appboy\Plugin;

class Appboy {
  private $options;
  private $id;
  private $sdk_version;

  /**
   * Constructor
   */
  function __construct() {
    $this->options = ['api_key', 'push_enabled', 'gcm_sender_id', 'safari_web_push_id', 'request_push_permission_selector', 'request_push_permission_event', 'custom_event_names', 'custom_event_selectors', 'custom_event_events', 'logging_enabled', 'show_feed'];
    $this->id = 'appboywebsdk';
    $this->sdk_version = '2.0';
  }

  /**
   * Adds handlers to activation and deactivation hooks
   */
  private function add_hooks_appboy() {
    register_activation_hook(__FILE__, array($this, 'on_activate_appboy'));
    register_deactivation_hook(__FILE__, array($this, 'on_deactivate_appboy'));
  }

  /**
   * Adds handlers for admin actions
   */
  private function add_admin_actions_appboy() {
    if (is_admin()) {
      add_action('admin_init', array($this, 'admin_init_appboy'));
      add_action('admin_menu', array($this, 'add_link_to_settings_menu_appboy'));
    } else {
      add_action('wp_head', array($this, 'generate_sdk_implementation_appboy'));
    }
  }

  /**
   * Since the API key is required, notify the user if they don't have it set
   */
  private function print_warnings_appboy() {
    if (empty(get_option('api_key'))) {
      _e('<div class="notice-error notice is-dismissible"><p><strong>WARNING:</strong> you must specify a valid API key for the SDK to be initialized properly.</p><button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button></div>');
    }
  }

  /**
   * Registers all the plugin options as settings
   */
  public function admin_init_appboy() {
    foreach ($this->options as $opt) {
      register_setting($this->id, $opt);
    }
  }

  /**
   * When the plugin is deactivated, delete all the options that were registered as settings
   */
  public function on_deactivate_appboy() {
    foreach ($this->options as $opt) {
      delete_option($opt);
    }
  }

  /**
   * Adds the link to the options form under the settings section on the admin panel
   */
  public function add_link_to_settings_menu_appboy() {
    add_options_page('Appboy Web SDK Settings', 'Appboy Web SDK', 'manage_options', 'appboywebsdk', array($this, 'render_options_form_appboy'));
  }

  /**
   * Generates a friendly hash to render the custom events
   */
  public function save_custom_events_hash_appboy($event_names, $event_selectors, $event_events) {
    $hash = [];
    if (intval(count($event_names)) > 0) {
      foreach($event_names as $i => $item) {
        $new_custom_event = [
          'name' => $event_names[$i],
          'selector' => $event_selectors[$i],
          'event' => $event_events[$i]
        ];
        array_push($hash, $new_custom_event);
      }
    }
    update_option(custom_events, $hash);
    return $hash;
  }

  /**
   * Generates the javascript for logging the saved custom events
   */
  public function generate_custom_events_js_appboy($custom_events) {
    $js = '';
    foreach($custom_events as $custom_event) {
      $js .= 'document.querySelector(' . json_encode($custom_event['selector']) . ').addEventListener(' . json_encode($custom_event['event']) . ', function() { appboy.logCustomEvent(' . json_encode($custom_event['name']) . '); }, false);';
      $js .= "\r\n";
    }
    return $js;
  }

  /**
   * Generates the javascript for enabling push
   */
  public function generate_push_permission_appboy($dom_selector, $dom_event, $safari_web_push_id) {
    $js = '';
    $safariJs = '';

    if ($safari_web_push_id) {
      $safariJs .= 'null, null, ' . json_encode($safari_web_push_id) . '';
    }

    if ($dom_selector && $dom_event) {
      $js .= 'document.querySelector(' . json_encode($dom_selector) . ').addEventListener(' . json_encode($dom_event) . ', function() { appboy.registerAppboyPushMessages(' . $safariJs . '); }, false);';
    } else {
      $js .= 'appboy.registerAppboyPushMessages(' . $safariJs . ');';
    }
    $js .= "\r\n";
    return $js;
  }

  /**
   * Renders the options form
   */
  public function render_options_form_appboy() {
    ?>
    <div class="wrap">
      <h2>Appboy Web SDK Settings</h2>
      <hr>
      <form method="post" action="options.php" enctype="multipart/form-data">
        <?php wp_nonce_field('update-options'); ?>
        <?php settings_fields('appboywebsdk'); ?>
        <table class="form-table">
          <tr>
            <th>API Key</th>
            <td>
              <input required type="text" name="api_key" value="<?php _e(esc_html(get_option('api_key'))); ?>" />
              <p class="description">Be sure to add your API key here. This will provide a global variable named appboy that you can use to send data to the Appboy API.</p>
            </td>
          </tr>
          <tr>
            <th>Custom Events</th>
            <td>
              <button style="margin-bottom: 10px;" data-action="add-custom-event" type="button" class="button-secondary">Add Custom Event</button>
              <?
              $this->save_custom_events_hash_appboy(get_option('custom_event_names'), get_option('custom_event_selectors'), get_option('custom_event_events'));
              $custom_events = get_option('custom_events');
              if (intval(count($custom_events)) > 0) {
                foreach($custom_events as $custom_event) {
                  _e('<div>');
                  _e('<input required name="custom_event_names[]" type="text" placeholder="Custom event name" value="' . htmlspecialchars($custom_event[name]) . '">');
                  _e('<input required name="custom_event_selectors[]" type="text" placeholder="DOM selector" value="' . htmlspecialchars($custom_event[selector]) . '">');
                  _e('<input required name="custom_event_events[]" type="text" placeholder="DOM event" value="' . htmlspecialchars($custom_event[event]) . '">');
                  _e('<button type="button" data-action="remove-setting" class="button-secondary">&times;</button>');
                  _e('</div>');
                }
              }
              ?>
              <p class="description">Enable <a target="_blank" href="https://www.appboy.com/documentation/Web/#tracking-custom-events">custom events</a> on your site.</p>
            </td>
          </tr>
          <tr>
            <th>Enable Push?</th>
            <td>
              <input style="margin-bottom: 10px;" type="checkbox" name="push_enabled" <?php if (get_option('push_enabled')) { _e("checked"); } ?>>
              <?
              if (get_option('push_enabled')) {
                _e('<div class="web-push-config"><input required value="' . htmlspecialchars(get_option('gcm_sender_id')) . '" type="text" name="gcm_sender_id" placeholder="Your GCM project #"><div><input value="' . htmlspecialchars(get_option('safari_web_push_id')) . '" type="text" name="safari_web_push_id" placeholder="Safari website push ID"></div><br><br><div><strong><small>If you want a custom UI event to trigger push permission add the selector and DOM event here:</small></strong><br><input name="request_push_permission_selector" type="text" placeholder="DOM selector" value="' . htmlspecialchars(get_option('request_push_permission_selector')) . '"><input name="request_push_permission_event" type="text" placeholder="DOM event" value="' . htmlspecialchars(get_option('request_push_permission_event')) . '"></div></div>');
              }
              ?>
              <p class="description">Enable <a target="_blank" href="https://www.appboy.com/documentation/Web/#integration">web push</a> on your site.</p>
            </td>
          </tr>
          <tr>
            <th>Show Feed?</th>
            <td>
              <input type="checkbox" name="show_feed" <?php if (get_option('show_feed')) { _e("checked"); } ?>>
              <p class="description">Display the user's news feed.</p>
            </td>
          </tr>
          <tr>
            <th>Enable Logging?</th>
            <td>
              <input type="checkbox" name="logging_enabled" <?php if (get_option('logging_enabled')) { _e("checked"); } ?>>
              <p class="description">This will cause Appboy to log to the JavaScript console. This is valuable for development but is visible to all users so it is not recommended to have this on in production.</p>
            </td>
          </tr>
        </table>
        <input type="hidden" name="action" value="update">
        <p class="submit">
          <input type="submit" class="button-primary" value="Save Changes">
        </p>
      </form>
    </div>
    <script src="<?= plugins_url('/js/ui.js', __FILE__ ) ?>"></script>
    <?php
  }

  /**
   * Generates the web SDK implementation based on option values
   * It's only added to the page if the admin has set the API key
   */
  public function generate_sdk_implementation_appboy() {
    if (!empty(get_option('api_key'))) {
      if (get_option('push_enabled')) { _e('<link rel="manifest" href="' . plugins_url('/sdk-files/manifest.json.php?gcm_sender_id=' . get_option("gcm_sender_id") . '', __FILE__ ) . '">'); }
      ?>
      <script type="text/javascript">
        +function(a,p,P,b,y){
          appboy={};appboyQueue=[];for(var s="initialize destroy getDeviceId toggleAppboyLogging setLogger openSession changeUser requestImmediateDataFlush requestFeedRefresh subscribeToFeedUpdates logCardImpressions logCardClick logFeedDisplayed requestInAppMessageRefresh logInAppMessageImpression logInAppMessageClick logInAppMessageButtonClick logInAppMessageHtmlClick subscribeToNewInAppMessages removeSubscription removeAllSubscriptions logCustomEvent logPurchase isPushSupported isPushBlocked isPushGranted isPushPermissionGranted registerAppboyPushMessages unregisterAppboyPushMessages submitFeedback ab ab.User ab.User.Genders ab.User.NotificationSubscriptionTypes ab.User.prototype.getUserId ab.User.prototype.setFirstName ab.User.prototype.setLastName ab.User.prototype.setEmail ab.User.prototype.setGender ab.User.prototype.setDateOfBirth ab.User.prototype.setCountry ab.User.prototype.setHomeCity ab.User.prototype.setLanguage ab.User.prototype.setEmailNotificationSubscriptionType ab.User.prototype.setPushNotificationSubscriptionType ab.User.prototype.setPhoneNumber ab.User.prototype.setAvatarImageUrl ab.User.prototype.setLastKnownLocation ab.User.prototype.setUserAttribute ab.User.prototype.setCustomUserAttribute ab.User.prototype.addToCustomAttributeArray ab.User.prototype.removeFromCustomAttributeArray ab.User.prototype.incrementCustomUserAttribute ab.User.prototype.addAlias ab.InAppMessage ab.InAppMessage.SlideFrom ab.InAppMessage.ClickAction ab.InAppMessage.DismissType ab.InAppMessage.OpenTarget ab.InAppMessage.ImageStyle ab.InAppMessage.Orientation ab.InAppMessage.CropType ab.InAppMessage.prototype.subscribeToClickedEvent ab.InAppMessage.prototype.subscribeToDismissedEvent ab.InAppMessage.prototype.removeSubscription ab.InAppMessage.prototype.removeAllSubscriptions ab.InAppMessage.Button ab.InAppMessage.Button.prototype.subscribeToClickedEvent ab.InAppMessage.Button.prototype.removeSubscription ab.InAppMessage.Button.prototype.removeAllSubscriptions ab.SlideUpMessage ab.ModalMessage ab.FullScreenMessage ab.HtmlMessage ab.ControlMessage ab.Feed ab.Feed.prototype.getUnreadCardCount ab.Card ab.ClassicCard ab.CaptionedImage ab.Banner ab.WindowUtils display display.automaticallyShowNewInAppMessages display.showInAppMessage display.showFeed display.destroyFeed display.toggleFeed sharedLib".split(" "),i=0;i<s.length;i++){for(var m=s[i],k=appboy,l=m.split("."),j=0;j<l.length-1;j++)k=k[l[j]];k[l[j]]=(new Function("return function "+m.replace(/\./g,"_")+"(){appboyQueue.push(arguments)}"))()}appboy.getUser=function(){return new appboy.ab.User};appboy.getCachedFeed=function(){return new appboy.ab.Feed};(y=p.createElement(P)).type='text/javascript';y.src='https://js.appboycdn.com/web-sdk/<?php _e($this->sdk_version); ?>/appboy.min.js';y.async=1;(b=p.getElementsByTagName(P)[0]).parentNode.insertBefore(y,b)}(window,document,'script');      
          
          appboy.initialize('<? _e(esc_html(get_option('api_key'))); ?>');
          <?php if (get_option('logging_enabled')) { _e('appboy.toggleAppboyLogging();'); } ?>
          <?php if (get_option('show_feed')) { _e('appboy.display.showFeed();'); } ?>
          appboy.display.automaticallyShowNewInAppMessages();
          appboy.openSession();
          <?php _e($this->generate_custom_events_js_appboy(get_option('custom_events'))); ?>
          <?
          if (get_option('push_enabled')) {
            _e($this->generate_push_permission_appboy(get_option('request_push_permission_selector'), get_option('request_push_permission_event'), get_option('safari_web_push_id')));
          }
          ?>
      </script>
      <?php
    }
  }

  /**
   * Inits
   */
  public function init() {
    $this->add_hooks_appboy();
    $this->add_admin_actions_appboy();
  }
}

/**
 * Only initalize if within a wordpress plugin
 */
if (defined('WP_CONTENT_URL')) {
  $ab = new \Appboy\Plugin\Appboy();
  $ab->init();
}
