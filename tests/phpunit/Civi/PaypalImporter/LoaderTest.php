<?php

namespace Civi\PaypalImporter;

use Civi\Api4\Contact;
use Civi\Api4\Contribution;
use Civi\Api4\Email;
use CRM_Core_Exception;

/**
 * @group headless
 */
class LoaderTest extends HeadlessTestCase
{
    /**
     * @return void
     * @throws \CRM_Core_Exception
     */
    public function testContactMissingData()
    {
        $contactData = [];
        $contactId = Loader::contact($contactData);
        self::assertIsInt($contactId);
    }

    /**
     * @return void
     * @throws \API_Exception
     * @throws \CRM_Core_Exception
     * @throws \Civi\API\Exception\UnauthorizedException
     */
    public function testContactValidData()
    {
        $contactData = [
            'contact_type' => 'Individual',
            'first_name' => '',
            'last_name' => '',
        ];
        $contactId = Loader::contact($contactData);
        self::assertIsInt($contactId);
        // The contact is create well.
        $contacts = Contact::get(false)
            ->addSelect('contact_type')
            ->addWhere('id', '=', $contactId)
            ->setLimit(1)
            ->execute();
        $contact = $contacts->first();
        self::assertSame($contactData['contact_type'], $contact['contact_type'], 'Invalid contact_type has been returned');
    }

    /**
     * @return void
     * @throws \CRM_Core_Exception
     */
    public function testEmailInvalidContactId()
    {
        $emailData = ['email' => 'testlooser@email.com'];
        self::expectException(CRM_Core_Exception::class);
        self::expectExceptionMessage('Invalid ID');
        $emailId = Loader::email(-1, $emailData);
    }

    /**
     * @return void
     * @throws \CRM_Core_Exception
     */
    public function testEmailMissingEmailData()
    {
        // create contact with the loader
        $contactData = [
            'contact_type' => 'Individual',
            'first_name' => '',
            'last_name' => '',
        ];
        $contactId = Loader::contact($contactData);
        $emailData = [];
        self::expectException(CRM_Core_Exception::class);
        self::expectExceptionMessage('Failed to create Email, reason: Mandatory values missing from Api4 Email::create: email');
        $emailId = Loader::email($contactId, $emailData);
    }

    /**
     * @return void
     * @throws \API_Exception
     * @throws \CRM_Core_Exception
     * @throws \Civi\API\Exception\UnauthorizedException
     */
    public function testEmailValidData()
    {
        // create contact with the loader
        $contactData = [
            'contact_type' => 'Individual',
            'first_name' => '',
            'last_name' => '',
        ];
        $contactId = Loader::contact($contactData);
        $emailData = ['email' => 'testlooser@email.com'];
        $emailId = Loader::email($contactId, $emailData);
        self::assertIsInt($emailId);
        $emails = Email::get(false)
            ->addSelect('email')
            ->addWhere('id', '=', $emailId)
            ->setLimit(1)
            ->execute();
        $email = $emails->first();
        self::assertSame($emailData['email'], $email['email'], 'Invalid email address has been returned');
    }

    /**
     * @return void
     * @throws \CRM_Core_Exception
     */
    public function testContributionInvalidContactId()
    {
        $contribData = ['trxn_id' => 'a-1', 'total_amount' => 10];
        self::expectException(CRM_Core_Exception::class);
        self::expectExceptionMessage('Invalid ID');
        $emailId = Loader::contribution(-1, $contribData);
    }

    /**
     * @return void
     * @throws \API_Exception
     * @throws \CRM_Core_Exception
     * @throws \Civi\API\Exception\UnauthorizedException
     */
    public function testContributionValidDataInsert()
    {
        // create contact with the loader
        $contactData = [
            'contact_type' => 'Individual',
            'first_name' => '',
            'last_name' => '',
        ];
        $contactId = Loader::contact($contactData);
        $contribData = ['trxn_id' => 'a-1', 'total_amount' => 10, 'financial_type_id' => 1];
        // The contribution shouldn't exists.
        $contributions = Contribution::get(false)
            ->addSelect('contribution_id')
            ->addWhere('trxn_id', '=', $contribData['trxn_id'])
            ->setLimit(1)
            ->execute();
        $contribution = $contributions->first();
        if (is_array($contribution)) {
            self::fail('Contribution already exists.');
        }
        $contributionId = Loader::contribution($contactId, $contribData);
        self::assertIsInt($contributionId);
        $contributions = Contribution::get(false)
            ->addSelect('trxn_id')
            ->addWhere('id', '=', $contributionId)
            ->setLimit(1)
            ->execute();
        $contribution = $contributions->first();
        self::assertSame($contribData['trxn_id'], $contribution['trxn_id'], 'Invalid transaction has been returned');
    }

    /**
     * @return void
     * @throws \API_Exception
     * @throws \CRM_Core_Exception
     * @throws \Civi\API\Exception\UnauthorizedException
     */
    public function testContributionValidDataUpdate()
    {
        // create contact with the loader
        $contactData = [
            'contact_type' => 'Individual',
            'first_name' => '',
            'last_name' => '',
        ];
        $contactId = Loader::contact($contactData);
        $contribData = ['trxn_id' => 'a-2', 'total_amount' => 10, 'financial_type_id' => 1];
        // The contribution shouldn't exists.
        $contributions = Contribution::get(false)
            ->addSelect('contribution_id')
            ->addWhere('trxn_id', '=', $contribData['trxn_id'])
            ->setLimit(1)
            ->execute();
        $contribution = $contributions->first();
        if (is_array($contribution)) {
            self::fail('Contribution already exists.');
        }
        $contributionId = Loader::contribution($contactId, $contribData);
        self::assertIsInt($contributionId);
        $contributions = Contribution::get(false)
            ->addSelect('trxn_id')
            ->addWhere('id', '=', $contributionId)
            ->setLimit(1)
            ->execute();
        $contribution = $contributions->first();
        self::assertSame($contribData['trxn_id'], $contribution['trxn_id'], 'Invalid transaction has been returned');
        $contribData['source'] = 'test';
        $newContributionId = Loader::contribution($contactId, $contribData);
        self::assertSame($contributionId, $newContributionId, 'Insertion happened instead of update.');
    }
}
