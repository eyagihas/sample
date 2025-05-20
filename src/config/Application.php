<?php

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Events\Dispatcher;
use Illuminate\Container\Container;
use Carbon\Carbon as Carbon;

class Application
{
    private static $_instance = null;
    //private static $capsule = null;

    /**
     * Slim Application初期化.
     *
     * @return object Slimインスタンス
     */
    public static function getInstance()
    {
        if (is_null(self::$_instance)) {
            self::$_instance =
                new \Slim\App(self::getSetting());

            //依存関係設定
            self::loadDepedencies();

            //ミドルウェア
            self::loadMiddleware();
        }

        return self::$_instance;
    }

    /**
     * Slim Applicationの設定
     *
     * @return array 設定配列
    */
    private static function getSetting()
    {
        /*現在日付*/
        $dt = Carbon::now()->format('Ymd');

        return [
            'settings' => [
                'displayErrorDetails' => true,
                'addContentLengthHeader' => false,
                // Twig
                'renderer' => [
                    'template_path' => __DIR__ . '/../../templates/',
                    'twig' =>[
                        //'cache' => __DIR__ . '/../../templates/cache/',
                        'debug' => true,
                        'auto_reload' => false,
                    ],
                ],
                // Monolog settings
                'logger' => [
                    'name' => 'slim-app',
                    'path' => __DIR__ . '/../../logs/app_'.$dt.'.log',
                    'level' => \Monolog\Logger::DEBUG,
                ],
                //DB
                'DB' => [
                    'host' => $_ENV['MYSQL_HOST'],
                    'database' => $_ENV['MYSQL_NAME'],
                    'username' => $_ENV['MYSQL_USER'],
                    'password' => $_ENV['MYSQL_PASS'],
                    'port' => $_ENV['MYSQL_PORT'],
                ],
                //upload file
                'upload_directory' => __DIR__ . '/../..'. $_ENV['UPLOAD_DIRECTORY'],
                //recommend image file
                'recommend_image_directory' => __DIR__ . '/../..'. $_ENV['RECOMMEND_IMAGE_DIRECTORY'],
                //clinic image file
                'clinic_image_directory' => __DIR__ . '/../..'. $_ENV['CLINIC_IMAGE_DIRECTORY'],
                //include file
                'include_directory' => __DIR__ . '/../..'. $_ENV['INCLUDE_DIRECTORY'],
				//sitemap file
                'sitemap_directory' => __DIR__ . '/../..'. $_ENV['SITEMAP_DIRECTORY'],
                //basic auth
                'basic_auth' => $_ENV['BASIC_AUTH']
            ],
        ];
    }

    /**
     * Slim Applicationの依存関係
     *
    */
    private static function loadDepedencies()
    {
        $container = self::$_instance->getContainer();

        // view renderer
        $container['view'] = function ($c) {
            $settings = $c->get('settings')['renderer'];
            $view = new \Slim\Views\Twig($settings['template_path'], $settings['twig']);
            $view->addExtension(new Twig_Extension_Debug());
            $view->addExtension(new Utils\TwigExtension($c['flash']));
            return $view;
        };

        // monolog
        $container['log'] = function ($c) {
            $settings = $c->get('settings')['logger'];
            $formatter = new Monolog\Formatter\LineFormatter("[%datetime%] %level_name% %file%:%line% %message% %extra%\n", null, true, true);
            $stream = new Monolog\Handler\StreamHandler($settings['path'], $settings['level']);
            $stream->setFormatter($formatter);

            $log = new Monolog\Logger($settings['name']);
            $log->pushHandler($stream);
            $log->pushProcessor(function ($record) {
                $record['file'] = $record['context']['file'];
                $record['line'] = $record['context']['line'];
                return $record;
            });
            return $log;
        };

        //DB
        $container['masterDB'] = function($c) {
            $settings = $c->get('settings')['DB'];
            $capsule = new Capsule();
            $capsule->addConnection([
                'driver' => 'mysql',
                'host' => $settings['host'],
                'port' => $settings['port'],
                'database' => $settings['database'],
                'username' => $settings['username'],
                'password' => $settings['password'],
                'charset' => 'utf8',
                'collation' => 'utf8_unicode_ci',
                'prefix' => '',
              ],'master'
            );
            $capsule->setAsGlobal();
            $capsule->bootEloquent();
            $capsule->setEventDispatcher(new Dispatcher(new Container()));
            $capsule->getDatabaseManager()->connection('master')->enableQueryLog();
            if (is_null($capsule->getDatabaseManager()->connection('master')->getPdo())) {
                return $capsule->getDatabaseManager()->connection('master')->reconnect();
            }else{
                return $capsule->getDatabaseManager()->connection('master');
            }
        };

        $container['flash'] = function () {
            return new \Slim\Flash\Messages();
        };


        // upload directory
        $container['upload_directory'] = function($c) {
            $settings = $c->get('settings')['upload_directory'];
            return $settings;
        };

        // recommend image directory
        $container['recommend_image_directory'] = function($c) {
            $settings = $c->get('settings')['recommend_image_directory'];
            return $settings;
        };

        // clinic image directory
        $container['clinic_image_directory'] = function($c) {
            $settings = $c->get('settings')['clinic_image_directory'];
            return $settings;
        };


		// sitemap directory
        $container['sitemap_directory'] = function($c) {
            $settings = $c->get('settings')['sitemap_directory'];
            return $settings;
        };

        // include directory
        $container['include_directory'] = function($c) {
            $settings = $c->get('settings')['include_directory'];
            return $settings;
        };

        // basic authentication
        $container['basic_auth'] = function($c) {
            $settings = $c->get('settings')['basic_auth'];
            return $settings;
        };

		//NotFound
		$container['notFoundHandler'] = function ($c) {
			$c->request->getUri()->getUserInfo() !== '' ?
			$base_url = str_replace($c->request->getUri()->getUserInfo().'@','',$c->request->getUri()->getBaseUrl()) :
			$base_url = $c->request->getUri()->getBaseUrl();

			$meta['base_url'] = $base_url;
			$meta['common_image_path'] = $base_url.'/image/';

            return function ($request, $response) use ($c, $meta) {
                //return $c['view']->render($response->withStatus(404),  'common/404.twig', ['meta' => $meta]);
                $contents = file_get_contents('https://plus.implant.ac/404.html');
                return $c['view']->render($response->withStatus(404),  'common/404.twig', ['contents' => $contents]);
            };
		};

        return $container;
    }

    /**
     * Slim Applicationのミドルウェア
     *
    */
    private static function loadMiddleware()
    {
        //self::$_instance->add(new \Slim\Csrf\Guard);
    }
}
