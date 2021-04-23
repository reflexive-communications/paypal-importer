<?php

class CRM_PaypalImporter_Config extends CRM_RcBase_Config
{
    /**
     * Provides a default configuration object.
     *
     * @return array the default configuration object.
     */
    public function defaultConfiguration(): array
    {
        return [
            "client-id" => "",
            "client-secret" => "",
            "paypal-host" => "",
            "start-date" => "",
            "import-limit" => 1,
        ];
    }
}
