<?php
/*
Plugin Name: Ical Feed Parser
Description: Shortcode [icalfeed url="http://VCAL-FEED-URL"] permettant la récupération et l'insertion d'une liste de h-Event
Author: lolalalol
Version: 1
Author URI: https://github.com/lolalalol/
License : http://www.gnu.org/licenses/gpl-3.0.html
*/
 
require_once dirname(__FILE__).'/iCalReader.class.php';

if (!function_exists('shortcode_atts')) {
  function shortcode_atts($default = array(), $atts = array()) {
    return array_merge($default, $atts);
  }
}

class Lolal_IcalFeedParser {
  
  public $EXTENSIONS_URL = null;
  public $EXTENSIONS_DIR = null;
  
  protected static $instance = NULL;

  /**
   * Handler for the action 'init'. Instantiates this class.
   *
   * @since   0.0.1
   * @access  public
   * @return  $classobj
   */
  public static function get_instance() {
    NULL === self::$instance and self::$instance = new self();
    return self::$instance;
  }
  
  public function __construct() {   
    if (function_exists('plugin_dir_url')) {
      $this->EXTENSIONS_URL = plugin_dir_url(__FILE__);
      $this->EXTENSIONS_DIR = plugin_dir_path(__FILE__);
    } else {
      $this->EXTENSIONS_URL = '/wp-content/plugins/lolal-icalfeedparser/';
      $this->EXTENSIONS_DIR = dirname(__FILE__);
    }
  }  
  
  public function scripts() {    
    wp_register_style( 'lolal-icalfeedparser-css', plugin_dir_url(__FILE__).'def.css', false, false );    
    wp_enqueue_style( 'lolal-icalfeedparser-css' );
	wp_register_script( 'lolal-icalfeedparser-js', plugin_dir_url(__FILE__).'ical.js', false, false, false );
	wp_enqueue_script( 'lolal-icalfeedparser-js' );
  }  
  
  /**
   * Insert a rendered h-Event list from a VCal feed
   * @param array $atts 
   * @param string $content
   * @return string
   */
  public static function shortcode($atts, $content = "") {
	$atts = shortcode_atts( array(
		'url' => '',
		'paginated' => '10'
	), $atts);
    
    $body = $content;
        
    $vcal = new ICal();
    // Request VCalendar and cache the response for 7 days
    $vcal->setCache(dirname(__FILE__).'/cache', 7 * 24 * 3600)
            ->initURL($atts['url']);
    
    if ($vcal->hasEvents()) {
      ob_start();
    ?>
<div class="icalfeed">
<ul>
<?php  
      foreach($vcal->events() as $event) {
        $event['DTSTART'] = new \DateTime($event['DTSTART']);
        $event['DTEND'] = new \DateTime($event['DTEND']);
        // Render an event
?>  
        <li class="hevent">
          <p><strong class="p-name"><?php echo $event['SUMMARY'] ?></strong><br/>
            Du <time class="dt-start" datetime="<?php echo $event['DTSTART']->format(\DateTime::ISO8601) ?>"><?php echo $event['DTSTART']->format('d/m') ?></time>
            au <time class="dt-end" datetime="<?php echo $event['DTEND']->format(\DateTime::ISO8601) ?>"><?php echo $event['DTEND']->format('d/m') ?></time><br/>
            <!--span class="p-location">Some bar in SF</span-->
            <?php if (strpos('://', $event['DESCRIPTION'])): ?>
            <span class="p-summary"><?php echo preg_replace('#.*(https?://.+)$#is', '<a href="$1" target="icalfeedNewWindow">Voir le site</a>', $event['DESCRIPTION']) ?></span>
            <?php endif; ?></p>
        </li>
<?php
      }
?>
</ul>
</div>
<?php
      $body = ob_get_contents();
      ob_clean();
    } else {
      $body = '<p>Aucun événement...</p>';
    }
    return $body;
  }
  
}


if (function_exists('shortcode_atts')) {
  add_action( 'plugins_loaded', array( 'Lolal_IcalFeedParser', 'get_instance' ) );
  add_action( 'wp_enqueue_scripts', array( 'Lolal_IcalFeedParser', 'scripts' ) );
  add_shortcode( 'icalfeed', array( 'Lolal_IcalFeedParser', 'shortcode' ) );
} else {
?>
<link rel="stylesheet" href="def.css" type="text/css" media="screen" />
<script type='text/javascript' src='/wp-includes/js/jquery/jquery.js'></script>
<script type='text/javascript' src='ical.js'></script>
<?php
  $test = new Lolal_IcalFeedParser();
  $test->shortcode(array(
    'url' => '<YOUR-FEED-URL-SAMPLE-TEST-HERE>'
  ));  
}
