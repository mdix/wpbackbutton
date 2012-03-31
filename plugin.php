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
    const name         = 'wpbackbutton';
    const slug         = 'wpbackbutton';
    private $savedURLs = array();

    function __construct() {
        $this->init_plugin_constants();
        $this->init_shortcodes();
        add_action( 'init', array( $this, 'addAndRemoveURLs') );
    }

    function addAndRemoveURLs() {
        $mainpageURL = home_url() . '/';
        $currentURL  = $this->getCurrentURL();

        if ($this->cookieDoesntExist()) {
            if ($mainpageURL == $currentURL) {
                $this->addURLToStack($mainpageURL);
                $this->writeCookie();
                return;
            }

            $this->addURLToStack($mainpageURL);
            $this->addURLToStack($currentURL);
            $this->writeCookie();
            return;
        }

        // navigated to the start - remove all urls: "restart" - no back button
        if ($mainpageURL == $currentURL) {
            $this->addURLToStack($mainpageURL);
            $this->writeCookie();
            return;
        }

        $this->savedURLs = $this->getUnserializedStackFromCookie();
        // back button has been used
        if ($this->getSecondToTheLastSavedURL() == $currentURL) {
            $this->removeLastURLFromStack();
            $this->writeCookie();
            return;
        }

        // new page has been entered
        if ($this->getLastURL() != $this->getCurrentURL()) {
            $this->addURLToStack($this->getCurrentURL());
            $this->writeCookie();
            return;
        }
    }

    private function writeCookie() {
        setcookie('wpbackbutton', serialize($this->savedURLs));
    }

    private function addURLToStack($URL) {
        if (!is_array($this->savedURLs)) {
            return;
        }
        array_push($this->savedURLs, $URL);
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
        return $protocol . "://" . $_SERVER['SERVER_NAME'] . $serverrequri;
    }

    private function strleft($s1, $s2) {
        return substr($s1, 0, strpos($s1, $s2));
    }

    private function cookieDoesntExist() {
        return !isset($_COOKIE['wpbackbutton']);
    }

    private function getLastURL() {
        if (!is_array($this->savedURLs)) {
            return;
        }
        return end($this->savedURLs);
    }

    private function getSecondToTheLastSavedURL() {
        if (!is_array($this->savedURLs)) {
            return;
        }
        end($this->savedURLs);
        return prev($this->savedURLs);
    }

    private function init_plugin_constants() {
        // This is what shows in the Widgets area of WordPress
        define( 'PLUGIN_NAME', self::name );
        // WP initializing slug directory in which the plugin resides
        define( 'PLUGIN_SLUG', self::slug );
    }

    // Add a shortcode to be able to get button source via do_shortcode in the template
    function init_shortcodes() {
        add_shortcode('renderBackButton', array($this, 'wpbackbutton_shortcode'));
    }

    function wpbackbutton_shortcode() {
        if ($this->getSecondToTheLastSavedURL()) {
            return '<a class="wpbackbutton" href="' . $this->getSecondToTheLastSavedURL() . '">BACK</a>';
        }
        return;
    }
}
new WP_back_button();
?>