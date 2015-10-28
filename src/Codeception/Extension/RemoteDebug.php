<?php

namespace Codeception\Extension;

use Codeception\Module\PhpBrowser;
use Codeception\Platform\Extension;
use Codeception\Lib\InnerBrowser;

class RemoteDebug extends Extension
{
    static $events = array(
        'suite.before' => 'beforeSuite'
    );

    protected $module;

    /**
     * @return InnerBrowser|null
     */
    protected function getRemoteConnectionModule()
    {
        foreach ($this->getCurrentModuleNames() as $moduleName) {
            $module = $this->getModule($moduleName);
            if ($module instanceof InnerBrowser) {
                return $module;
            }
        }

        return null;
    }

    /**
     * @return \GuzzleHttp\Client
     */
    protected function getGuzzleHttpClient()
    {
        $client = null;
        if (method_exists($this->module, 'getClient')) {
            $client = $this->module->getClient();
        } elseif ($this->module instanceof PhpBrowser) {
            $client = $this->module->guzzle;
        }

        return ($client instanceof \GuzzleHttp\Client ? $client : null);
    }

    public function beforeSuite()
    {
        if (!(function_exists('xdebug_is_enabled') && xdebug_is_enabled() && ini_get('xdebug.remote_enable'))) {
            return;
        }

        $this->module = $this->getRemoteConnectionModule();
        if (!$this->module) {
            return;
        }

        $this->module->setCookie('XDEBUG_SESSION', $this->config['sessionName']);

        if ($this->module instanceof PhpBrowser) {
            $this->module->_setConfig(['timeout' => 0]);
        }

        //$client = $this->getGuzzleHttpClient();
//        if ($client !== null) {
//            $clientConfig            = $client->getConfig();
//            $clientConfig['timeout'] = 0;
//
//            $clientConfigRefl = new \ReflectionProperty('GuzzleHttp\Client', 'config');
//            $clientConfigRefl->setAccessible(true);
//            $clientConfigRefl->setValue($client, $clientConfig);
//        }
    }
}
