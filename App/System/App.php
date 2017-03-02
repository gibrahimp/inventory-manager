<?php
namespace App\System;

use \App\System\Settings;
use \App\Controllers\Controller;

class App {

    private static $database;
    private static $twig;

    public function __construct() {
        error_reporting(E_ALL);
        ini_set('display_errors', 1);
    }

    public static function getDb() {
        if(self::$database === null) {
            self::$database = new Database(
                Settings::getConfig()['database']['name'],
                Settings::getConfig()['database']['username'],
                Settings::getConfig()['database']['password'],
                Settings::getConfig()['database']['host']
            );
        }

        return self::$database;
    }

    public static function getTwig() {
        if(self::$twig === null) {
            $loader = new \Twig_Loader_Filesystem(dirname(__DIR__) . '/views');

            self::$twig = new \Twig_Environment($loader, [
                'cache' => Settings::getConfig()['twig']['cache']
            ]);

            $asset = new \Twig_Function('asset', function ($path) {
                return Settings::getConfig()['url'] . 'assets/' . $path;
            });

            $excerpt = new \Twig_Function('excerpt', function ($content) {
                return substr($content, 0, 300) . '...';
            });

            $url = new \Twig_Function('url', function ($slug, $id = null, $post_type = null) {
                return Settings::getConfig()['url'] . $slug;
            });

            $title = new \Twig_Function('title', function ($title = null) {
                if($title) return $title . ' - ' . Settings::getConfig()['name'];
                else return Settings::getConfig()['name'];
            });


            self::$twig->addFunction($asset);
            self::$twig->addFunction($excerpt);
            self::$twig->addFunction($url);
            self::$twig->addFunction($title);

            isset($_SESSION['auth']) ? self::$twig->addGlobal('auth', $_SESSION['auth']) : self::$twig->addGlobal('auth', '');
        }

        return self::$twig;
    }

    public static function error() {
        header("HTTP/1.0 404 Not Found");
        $controller = new \App\Controllers\Controller();
        $controller->render('pages/404.twig', []);
    }

    public static function redirect($path = '') {
        $location = 'Location: ' . Settings::getConfig()['url'] . $path;
        header($location);
    }

    public static function secured() {
        if(!isset($_SESSION['auth'])) {
            self::redirect('signin');
            exit;
        }
    }

}
