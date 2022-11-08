<?php

use Civi\Test\HeadlessInterface;
use Civi\Test\TransactionalInterface;
use PHPUnit\Framework\TestCase;

/**
 * Base class for headless tests.
 * It implements the before and teardown functions
 *
 * @group headless
 */
class CRM_PaypalImporter_HeadlessBase extends TestCase implements HeadlessInterface, TransactionalInterface
{
    public function setUpHeadless()
    {
    }

    /**
     * Apply a forced rebuild of DB, thus
     * create a clean DB before running tests
     *
     * @throws \CRM_Extension_Exception_ParseException
     */
    public static function setUpBeforeClass(): void
    {
        // Resets DB and install depended extension
        \Civi\Test::headless()
            ->install('rc-base')
            ->installMe(__DIR__)
            ->apply(true);
    }
}
