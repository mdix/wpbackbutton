<?php
/*
Plugin Name: wpbackbutton
Plugin URI: http://no-plugin-site-available
Description: Enables a back button that reverse engineers the users navigation to enable a simple "back" button on every page.
Version: 0.2
Author: Marc Dix
Author URI: http://www.dixpix.de
Author Email: marcdix@marcdix.de
*/
class WP_back_button {

    /*--------------------------------------------*
     * Constants
     *--------------------------------------------*/

    const name = 'wpbackbutton';

    const slug = 'wpbackbutton';

    private $savedURLs = array();
    /*--------------------------------------------*
     * Constructor
     *--------------------------------------------*/

    /**
     * Initializes the plugin by setting localization, filters, and administration functions.
     */
    function __construct() {

        // Define constants used throughout the plugin
        $this->init_plugin_constants();
        $this->init_shortcodes();
        load_plugin_textdomain( PLUGIN_LOCALE, false, dirname( plugin_basename( __FILE__ ) ) . '/lang' );

        // Load JavaScript and stylesheets
        $this->register_scripts_and_styles();

        // Init
        add_action( 'init', array( $this, 'addAndRemoveURLs') );

    } // end constructor

    /*--------------------------------------------*
     * Core Functions
     *---------------------------------------------*/
    function addAndRemoveURLs() {
        $homeURL = home_url() . '/';
        $currentURL = $this->getCurrentURL();

        if ($this->cookieDoesntExist()) {
            if ($homeURL == $currentURL) {
                $this->addURLToStack($homeURL);
                $this->setCookie();
                return;
            }

            $this->addURLToStack($homeURL);
            $this->addURLToStack($this->getCurrentURL());
            $this->setCookie();
            return;
        }

        // navigated to the start - remove all urls: "restart" - no back button
        if ($homeURL == $currentURL) {
            $this->addURLToStack($homeURL);
            $this->setCookie();
            return;
        }

        $this->savedURLs = $this->getUnserializedStackFromCookie();
        // back button has been used
        if ($this->getSecondToTheLastSavedURL() == $this->getCurrentURL()) {
            $this->removeLastURLFromStack();
            $this->setCookie();
            return;
        }

        // new page has been entered
        if ($this->getLastURL() != $this->getCurrentURL()) {
            $this->addURLToStack($this->getCurrentURL());
            $this->setCookie();
            return;
        }
    }

    private function setCookie() {
        setcookie('wpbackbutton', serialize($this->savedURLs));
    }

    private function addURLToStack($elem) {
        array_push($this->savedURLs, $elem);
    }

    private function removeLastURLFromStack() {
        array_pop($this->savedURLs);
    }

    private function getUnserializedStackFromCookie() {
        return unserialize(stripslashes(urldecode($_COOKIE['wpbackbutton'])));
    }

    private function getCurrentURL() {
        if (!isset($_SERVER['REQUEST_URI'])){
            $serverrequri = $_SERVER['PHP_SELF'];
        } else {
            $serverrequri = $_SERVER['REQUEST_URI'];
        }
        $s = empty($_SERVER["HTTPS"]) ? '' : ($_SERVER["HTTPS"] == "on") ? "s" : "";
        $protocol = $this->strleft(strtolower($_SERVER["SERVER_PROTOCOL"]), "/") . $s;
        $port = ($_SERVER["SERVER_PORT"] == "80") ? "" : (":" . $_SERVER["SERVER_PORT"]);
        return $protocol . "://" . $_SERVER['SERVER_NAME'] . $port . $serverrequri;
    }

    private function strleft($s1, $s2) {
        return substr($s1, 0, strpos($s1, $s2));
    }

    private function cookieDoesntExist() {
        return !isset($_COOKIE['wpbackbutton']);
    }

    private function getLastURL() {
        return end($this->savedURLs);
    }

    private function getSecondToTheLastSavedURL() {
        end($this->savedURLs);
        return prev($this->savedURLs);
    }

    /**
     * Note:  Filters are points of execution in which WordPress modifies data
     *        before saving it or sending it to the browser.
     *
     *          WordPress Filters: http://codex.wordpress.org/Plugin_API#Filters
     *          Filter Reference:  http://codex.wordpress.org/Plugin_API/Filter_Reference
     *
     */
    function filter_method_name() {
        // TODO define your filter method here
    } // end filter_method_name

    /*--------------------------------------------*
     * Private Functions
     *---------------------------------------------*/

    /**
     * Initializes constants used for convenience throughout
     * the plugin.
     */
    private function init_plugin_constants() {

        /*
         * This is what shows in the Widgets area of WordPress.
         */
        define( 'PLUGIN_NAME', self::name );

        /*
         * this is the slug of your plugin used in initializing it with
         * the WordPress API.

         * This should also be the
         * directory in which your plugin resides. Use hyphens.
         *
         */
        define( 'PLUGIN_SLUG', self::slug );


    } // end init_plugin_constants

    /**
     * Add a shortcode to be able to get button source via do_shortcode in the template
     */
    function init_shortcodes() {
        add_shortcode('renderBackButton', array($this, 'wpbackbutton_shortcode'));
    }

    /**
     * Returns nothing if no backbutton needed, else the backbutton
     */
    function wpbackbutton_shortcode() {
        if ($this->getSecondToTheLastSavedURL()) {
            return '<a class="wpbackbutton" href="' . $this->getSecondToTheLastSavedURL() . '">BACK</a>';
        }
        return;
    }

    /**
     * Registers and enqueues stylesheets for the administration panel and the
     * public facing site.
     */
    private function register_scripts_and_styles() {
        if ( is_admin() ) {
            $this->load_file( self::slug . '-admin-script', '/js/admin.js', true );
            $this->load_file( self::slug . '-admin-style', '/css/admin.css' );
        } else {
            $this->load_file( self::slug . '-script', '/js/wpbackbutton.js', true);
            $this->load_file( self::slug . '-script', '/js/widget.js', true );
            $this->load_file( self::slug . '-style', '/css/widget.css' );
        } // end if/else
    } // end register_scripts_and_styles

    /**
     * Helper function for registering and enqueueing scripts and styles.
     *
     * @name      The ID to register with WordPress
     * @file_path The path to the actual file
     * @is_script Optional argument for if the incoming file_path is a JavaScript source file.
     */
    private function load_file( $name, $file_path, $is_script = false ) {

        $url = plugins_url($file_path, __FILE__);
        $file = plugin_dir_path(__FILE__) . $file_path;

        if( file_exists( $file ) ) {
            if( $is_script ) {
                wp_register_script( $name, $url, array('jquery') );
                wp_enqueue_script( $name );
            } else {
                wp_register_style( $name, $url );
                wp_enqueue_style( $name );
            } // end if
        } // end if

    } // end load_file

} // end class

new WP_back_button();
?>