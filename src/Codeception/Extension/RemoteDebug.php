<?php

namespace Codeception\Extension;

use Codeception\Platform\Extension;
use Codeception\SuiteManager;
use Codeception\Util\RemoteInterface;

class RemoteDebug extends Extension
{
    static $events = [
        'suite.before' => 'beforeSuite'
    ];

    protected $module;

    /**
     * @return RemoteInterface|null
     */
    protected function getRemoteConnectionModule()
    {
        foreach (SuiteManager::$modules as $module) {
            if ($module instanceof RemoteInterface) {
                return $module;
            }
        }
        return null;
    }

    public function beforeSuite()
    {
        $this->module = $this->getRemoteConnectionModule();
        if (!$this->module) {
            return;
        }

        if (function_exists('xdebug_is_enabled')
            && xdebug_is_enabled()
            && ini_get('xdebug.remote_enable')
        ) {
            $this->module->setCookie('XDEBUG_SESSION', $this->config['sessionName']);

            if (method_exists($this->module, 'getClient')) {
                $clientConfig                 = $this->module->getClient()->getConfig();
                $curlOptions                  = $clientConfig->get('curl.options');
                $curlOptions[CURLOPT_TIMEOUT] = 0;
                $clientConfig->set('curl.options', $curlOptions);
            }
        }
    }
}
