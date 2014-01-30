<?php

namespace Codeception\Extension;

use Codeception\SuiteManager;
use Codeception\Module\PhpBrowser;
use Codeception\Platform\Extension;
use Codeception\Util\RemoteInterface;

class RemoteDebug extends Extension
{
    static $events = array(
        'suite.before' => 'beforeSuite'
    );

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

    /**
     * @return \Guzzle\Http\Client
     */
    protected function getGuzzleHttpClient()
    {
        $client = null;
        if (method_exists($this->module, 'getClient')) {
            $client = $this->module->getClient();
        } elseif ($this->module instanceof PhpBrowser) {
            $driver = $this->module->session->getDriver();
            if ($driver instanceof \Behat\Mink\Driver\GoutteDriver) {
                $client = $driver->getClient();
                if (method_exists($client, 'getClient')) {
                    $client = $client->getClient();
                }
            }
        }

        return ($client instanceof \Guzzle\Http\Client ? $client : null);
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

            $client = $this->getGuzzleHttpClient();
            if ($client !== null) {
                $clientConfig                 = $client->getConfig();
                $curlOptions                  = $clientConfig->get('curl.options');
                $curlOptions[CURLOPT_TIMEOUT] = 0;
                $clientConfig->set('curl.options', $curlOptions);
            }
        }
    }
}
