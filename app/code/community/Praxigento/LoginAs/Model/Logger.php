<?php
/**
 * Copyright (c) 2013, Praxigento
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without modification, are permitted provided that the
 * following conditions are met:
 *  - Redistributions of source code must retain the above copyright notice, this list of conditions and the following
 *      disclaimer.
 *  - Redistributions in binary form must reproduce the above copyright notice, this list of conditions and the
 *      following disclaimer in the documentation and/or other materials provided with the distribution.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES,
 * INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
 * SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY,
 * WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 */
/**
 * Wrapper for loggers (Mage default or Log4php from Nmmlm_log module)
 * User: Alex Gusev <flancer64@gmail.com>
 * Date: 2/20/13
 * Time: 9:38 AM
 */
class Praxigento_LoginAs_Model_Logger
{
    /** @var bool 'true' - Log4php logging framework is used. */
    private static $isLog4phpUsed = null;
    /** @var string name for the current logger */
    private $name;
    /** @var Logger */
    private $loggerLog4php;


    function __construct($name)
    {
        self::$isLog4phpUsed = class_exists('Nmmlm_Log_Logger', false);
        if (self::$isLog4phpUsed) {
            $this->loggerLog4php = Nmmlm_Log_Logger::getLogger($name);
        } else {
            $this->name = is_object($name) ? get_class($name) : (string)$name;
        }
    }

    /**
     * Override getter to use '$log = Nmmlm_Log_Logger::getLogger($this)' form in Mage classes.
     * @static
     *
     * @param string $name
     *
     * @return Praxigento_LoginAs_Model_Logger
     */
    public static function getLogger($name)
    {
        return new Praxigento_LoginAs_Model_Logger($name);
    }

    /**
     * Internal dispatcher for the called log method.
     * @param $message
     * @param $throwable
     * @param $log4phpMethod
     * @param $zendLevel
     */
    private function doLog($message, $throwable, $log4phpMethod, $zendLevel)
    {
        if (Praxigento_LoginAs_Config::cfgGeneralLogEvents()) {
            if (self::$isLog4phpUsed) {
                $this->loggerLog4php->$log4phpMethod($message, $throwable);
            } else {
                Mage::log($this->name . ': ' . $message, $zendLevel);
                if ($throwable instanceof Exception) {
                    Mage::logException($throwable);
                }
            }
        }
    }

    public function trace($message, $throwable = null)
    {
        $this->doLog($message, $throwable, 'trace', Zend_Log::DEBUG);
    }

    public function debug($message, $throwable = null)
    {
        $this->doLog($message, $throwable, 'debug', Zend_Log::INFO);
    }

    public function info($message, $throwable = null)
    {
        $this->doLog($message, $throwable, 'info', Zend_Log::NOTICE);
    }

    public function warn($message, $throwable = null)
    {
        $this->doLog($message, $throwable, 'warn', Zend_Log::WARN);
    }

    public function error($message, $throwable = null)
    {
        $this->doLog($message, $throwable, 'error', Zend_Log::ERR);
    }

    public function fatal($message, $throwable = null)
    {
        $this->doLog($message, $throwable, 'fatal', Zend_Log::CRIT);
    }
}
