<?php 
//phpinfo();exit();
// Указание пути к директории приложения
define('PUBLIC_PATH',realpath(dirname(__FILE__)));
defined('APPLICATION_PATH')
    || define('APPLICATION_PATH',
              PUBLIC_PATH . '/application');
// Определение текущего режима работы приложения
defined('APPLICATION_ENV')
    || define('APPLICATION_ENV',
              (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV')
                                         : 'production'));
define('LIBRARY_PATH',realpath(PUBLIC_PATH . '/../library'));
define('PLUGIN_PATH',realpath(APPLICATION_PATH . '/plugins'));
//var_dump(LIBRARY_PATH);exit();

set_include_path(implode(PATH_SEPARATOR, array(
    LIBRARY_PATH,
    PLUGIN_PATH,
  //  get_include_path()
)));

/** Zend_Application */
require_once 'Zend/Application.php';
// Создание объекта приложения, начальная загрузка, запуск

$application = new Zend_Application(
    APPLICATION_ENV,
    APPLICATION_PATH . '/configs/application.ini'
);

$application->bootstrap()
            ->run();
            	
