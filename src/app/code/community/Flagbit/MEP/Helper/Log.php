<?php
class Flagbit_MEP_Helper_Log extends Mage_Core_Helper_Abstract {

    const FILE = 'mep.log';
    const START_TIME_KEY = 'MEP_START_TIME';

    /**
     * log warn message
     *
     * @param $message
     * @param null $caller
     */
    public function warn($message, $caller = null)
    {
        $this->_log($message, Zend_Log::WARN, $caller);
    }

    /**
     * log notice message
     *
     * @param $message
     * @param null $caller
     */
    public function notice($message, $caller = null)
    {
        $this->_log($message, Zend_Log::NOTICE, $caller);
    }

    /**
     * log info message
     *
     * @param $message
     * @param null $caller
     */
    public function info($message, $caller = null)
    {
        $this->_log($message, Zend_Log::INFO, $caller);
    }

    /**
     * log err message
     *
     * @param $message
     * @param null $caller
     */
    public function err($message, $caller = null)
    {
        $this->_log($message, Zend_Log::ERR, $caller);
    }

    /**
     * log debug message
     *
     * @param $message
     * @param null $caller
     */
    public function debug($message, $caller = null)
    {
        $this->_log($message, Zend_Log::DEBUG, $caller);
    }

    /**
     * log Message
     *
     * @param $message
     * @param $level
     * @param null $caller
     */
    protected function _log($message, $level, $caller = null)
    {
        $_logMessage = array();

        // add caller class name in log4php style
        if(is_object($caller)){
            $_logMessage[] = strtolower(str_replace('_', '.', substr(get_class($caller), 12)));
        }

        // add current memory usage
        $_logMessage[] = $this->_getMemoryUsage();

        // add runtime
        $_logMessage[] = $this->_getRelativeTime();

        // add message
        if($message instanceof Exception){
            $_logMessage[] = (string) $message; //->getMessage();
        }elseif (is_array($message) || is_object($message)) {
            $_logMessage[] = print_r($message, true);
        }else{
            $_logMessage[] = $message;
        }

        Mage::log(implode(' ', $_logMessage), $level, self::FILE, true);
    }

    /**
     * get runtime
     *
     * @param int $decimals
     * @return string
     */
    protected function _getRelativeTime($decimals = 4)
    {
        if(!Mage::registry(self::START_TIME_KEY)){
            Mage::register(self::START_TIME_KEY, microtime(true));
        }
        return sprintf('%.' . $decimals . 'f ms', microtime(true) - Mage::registry(self::START_TIME_KEY));
    }

    /**
     * get current memory usage
     *
     * @return string
     */
    protected function _getMemoryUsage()
    {
        return $this->_byteFormat(memory_get_usage(true));
    }

    /**
     * Byte formatting
     *
     * @param $bytes
     * @param string $unit
     * @param int $decimals
     * @return string
     */
    private function _byteFormat($bytes, $unit = "", $decimals = 2) {
        $units = array('B' => 0, 'KB' => 1, 'MB' => 2, 'GB' => 3, 'TB' => 4,
                'PB' => 5, 'EB' => 6, 'ZB' => 7, 'YB' => 8);

        $value = 0;
        if ($bytes > 0) {
            // Generate automatic prefix by bytes
            // If wrong prefix given
            if (!array_key_exists($unit, $units)) {
                $pow = floor(log($bytes)/log(1024));
                $unit = array_search($pow, $units);
            }

            // Calculate byte value by prefix
            $value = ($bytes/pow(1024,floor($units[$unit])));
        }

        // If decimals is not numeric or decimals is less than 0
        // then set default value
        if (!is_numeric($decimals) || $decimals < 0) {
            $decimals = 2;
        }

        // Format output
        return sprintf('%.' . $decimals . 'f '.$unit, $value);
    }

}