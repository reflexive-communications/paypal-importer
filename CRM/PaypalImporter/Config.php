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
            "api-key" => "",
            "import-limit" => 1,
        ];
    }
}
