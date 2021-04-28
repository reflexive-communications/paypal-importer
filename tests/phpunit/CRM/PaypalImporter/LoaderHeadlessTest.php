<?php

use Civi\Api4\Contact;
use Civi\Api4\Contribution;
use Civi\Api4\Email;

/**
 * Loader class headless tests.
 *
 * @group headless
 */
class CRM_PaypalImporter_LoaderHeadlessTest extends CRM_PaypalImporter_HeadlessBase
{
    /**
     * It checks that the contact function works well.
     */
    public function testContactMissingData()
    {
        $contactData = [];
        $contactId = CRM_PaypalImporter_Loader::contact($contactData);
        self::assertIsInt($contactId);
    }
    public function testContactValidData()
    {
        $contactData = [
            'contact_type' => 'Individual',
            'first_name' => '',
            'last_name' => '',
        ];
        $contactId = CRM_PaypalImporter_Loader::contact($contactData);
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
     * It checks that the email function works well.
     */
    public function testEmailMissingContactId()
    {
        $emailData = ['email' => 'testlooser@email.com'];
        self::expectException(TypeError::class);
        self::expectExceptionMessage('Argument 1 passed to CRM_PaypalImporter_Loader::email() must be of the type int, null given');
        $emailId = CRM_PaypalImporter_Loader::email(null, $emailData);
    }
    public function testEmailInvalidContactId()
    {
        $emailData = ['email' => 'testlooser@email.com'];
        self::expectException(CRM_Core_Exception::class);
        self::expectExceptionMessage('Invalid ID');
        $emailId = CRM_PaypalImporter_Loader::email(-1, $emailData);
    }
    public function testEmailMissingEmailData()
    {
        // create contact with the loader
        $contactData = [
            'contact_type' => 'Individual',
            'first_name' => '',
            'last_name' => '',
        ];
        $contactId = CRM_PaypalImporter_Loader::contact($contactData);
        $emailData = [];
        self::expectException(CRM_Core_Exception::class);
        self::expectExceptionMessage('Failed to create Email, reason: Mandatory values missing from Api4 Email::create: email');
        $emailId = CRM_PaypalImporter_Loader::email($contactId, $emailData);
    }
    public function testEmailValidData()
    {
        // create contact with the loader
        $contactData = [
            'contact_type' => 'Individual',
            'first_name' => '',
            'last_name' => '',
        ];
        $contactId = CRM_PaypalImporter_Loader::contact($contactData);
        $emailData = ['email' => 'testlooser@email.com'];
        $emailId = CRM_PaypalImporter_Loader::email($contactId, $emailData);
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
     * It checks that the contribution function works well.
     */
    public function testContributionMissingContactId()
    {
        $contribData = ['trxn_id' => 'a-1', 'total_amount' => 10];
        self::expectException(TypeError::class);
        self::expectExceptionMessage('Argument 1 passed to CRM_PaypalImporter_Loader::contribution() must be of the type int, null given');
        $contributionId = CRM_PaypalImporter_Loader::contribution(null, $contribData);
    }
    public function testContributionInvalidContactId()
    {
        $contribData = ['trxn_id' => 'a-1', 'total_amount' => 10];
        self::expectException(CRM_Core_Exception::class);
        self::expectExceptionMessage('Invalid ID');
        $emailId = CRM_PaypalImporter_Loader::contribution(-1, $contribData);
    }
    public function testContributionValidDataInsert()
    {
        // create contact with the loader
        $contactData = [
            'contact_type' => 'Individual',
            'first_name' => '',
            'last_name' => '',
        ];
        $contactId = CRM_PaypalImporter_Loader::contact($contactData);
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
        $contributionId = CRM_PaypalImporter_Loader::contribution($contactId, $contribData);
        self::assertIsInt($contributionId);
        $contributions = Contribution::get(false)
            ->addSelect('trxn_id')
            ->addWhere('id', '=', $contributionId)
            ->setLimit(1)
            ->execute();
        $contribution = $contributions->first();
        self::assertSame($contribData['trxn_id'], $contribution['trxn_id'], 'Invalid transaction has been returned');
    }
    public function testContributionValidDataUpdate()
    {
        // create contact with the loader
        $contactData = [
            'contact_type' => 'Individual',
            'first_name' => '',
            'last_name' => '',
        ];
        $contactId = CRM_PaypalImporter_Loader::contact($contactData);
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
        $contributionId = CRM_PaypalImporter_Loader::contribution($contactId, $contribData);
        self::assertIsInt($contributionId);
        $contributions = Contribution::get(false)
            ->addSelect('trxn_id')
            ->addWhere('id', '=', $contributionId)
            ->setLimit(1)
            ->execute();
        $contribution = $contributions->first();
        self::assertSame($contribData['trxn_id'], $contribution['trxn_id'], 'Invalid transaction has been returned');
        $contribData['source'] = 'test';
        $newContributionId = CRM_PaypalImporter_Loader::contribution($contactId, $contribData);
        self::assertSame($contributionId, $newContributionId, 'Insertion happened instead of update.');
    }
}
