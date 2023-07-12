<?php

use Monolog\Formatter\LineFormatter;
use Monolog\Handler\ErrorLogHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

/**
 * install/dist.loghandler.php
 *
 ****************************
 ** DO NOT EDIT THIS FILE! **
 ****************************
 *
 * You are free to copy this file as "loghandler.php" and make any
 * modification you need.  This allows you to make customization that will not
 * be overwritten during an update.
 *
 * WHMCS will attempt to load your custom "loghandler.php" instead of this
 * file ("dist.loghandler.php").
 *
 ****************************
 ** DO NOT EDIT THIS FILE! **
 ****************************
 *
 * WHMCS initializes a Monolog logger, exposing the handler for customization.
 *
 * By default, WHMCS will log all messages to the configured PHP error log
 * (i.e., the Apache webserver error log).
 *
 * NOTE:
 * * The installer will attempt to write to install/log/installer.log.  If this
 *   is not possible due to permissions, the default PHP error log will be used.
 * * The installer's handler by default, as defined here, will log at the
 *   'debug' level, the most verbose level possible.
 * * The 'loghandler.php' file in the root directory will be loaded first, and
 *   then this file, creating a standard handler & then an installer specific
 *   handler. This may have relevance if you decide to alter this handler and
 *   "bubble" log messages through the handler stack.
 * * The installer's handler by default will prevent "bubbling" of messages,
 *   namely to the standard handler will never be used.  To change this behavior,
 *   pass the boolean "true" value as the last argument of the log handler's
 *   constructor.
 *
 * Please see Monolog documentation for usage of handlers and log levels
 * @link https://github.com/Seldaek/monolog
 *
 * @copyright Copyright (c) WHMCS Limited 2005-2018
 * @license https://www.whmcs.com/license/ WHMCS Eula
 */

if (!defined("ROOTDIR")) {
    die("This file cannot be accessed directly");
}

// Handler for individual installer log, if possible.
// Otherwise fall back to configured PHP error log
$logDirectory = INSTALLER_DIR . DIRECTORY_SEPARATOR . 'log';
$logFile = $logDirectory . DIRECTORY_SEPARATOR . 'installer.log';
if (is_writable($logDirectory)
    && (is_writable($logFile) || !file_exists($logFile))
) {
    $handleInstallLog = new StreamHandler(
        $logFile,
        Logger::DEBUG,
        false
    );

    $format = "[%datetime%][%channel%] %level_name%: %message% %extra%\n";
} else {
    $handleInstallLog = new ErrorLogHandler(
        ErrorLogHandler::OPERATING_SYSTEM,
        Logger::DEBUG,
        false
    );

    /**
     * Auto append runtime detail of the outermost file responsible for the log
     * entry.  This is needed by control panels that parse a single error log
     * and expose those entries based on the presence of the respective user's
     * home directory in the message body.
     */
    $handleInstallLog->pushProcessor(function ($record) {
        $script = (isset($_SERVER['SCRIPT_NAME'])) ? $_SERVER['SCRIPT_NAME'] : '';
        $record['context'] = ['in ' . ROOTDIR . $script];

        return $record;
    });

    $format = '[%channel%] %level_name%: %message% %context% %extra%';
}

$handleInstallLog->setFormatter(new LineFormatter($format));
Log::pushHandler($handleInstallLog);
