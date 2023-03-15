<?php

use Civi\PaypalImporter\HeadlessTestCase;

/**
 * Transformer class headless tests.
 *
 * @group headless
 */
class CRM_PaypalImporter_TransformerTest extends HeadlessTestCase
{
    const PAYPAL_SAMPLE_DATA = [
        "transaction_info" => [
            "paypal_account_id" => "6STWC2LSUYYYE",
            "transaction_id" => "5TY05013RG002845M",
            "transaction_event_code" => "T0006",
            "transaction_initiation_date" => "2014-07-11T04:03:52+0000",
            "transaction_updated_date" => "2014-07-11T04:03:52+0000",
            "transaction_amount" => [
                "currency_code" => "USD",
                "value" => "465.00",
            ],
            "fee_amount" => [
                "currency_code" => "USD",
                "value" => "-13.79",
            ],
            "insurance_amount" => [
                "currency_code" => "USD",
                "value" => "15.00",
            ],
            "shipping_amount" => [
                "currency_code" => "USD",
                "value" => "30.00",
            ],
            "shipping_discount_amount" => [
                "currency_code" => "USD",
                "value" => "10.00",
            ],
            "transaction_status" => "S",
            "transaction_subject" => "Bill for your purchase",
            "transaction_note" => "Check out the latest sales",
            "invoice_id" => "Invoice-005",
            "custom_field" => "Thank you for your business",
            "protection_eligibility" => "01",
        ],
        "payer_info" => [
            "account_id" => "6STWC2LSUYYYE",
            "email_address" => "consumer@example.com",
            "address_status" => "Y",
            "payer_status" => "Y",
            "payer_name" => [
                "given_name" => "test",
                "surname" => "consumer",
                "alternate_full_name" => "test consumer",
            ],
            "country_code" => "US",
        ],
        "shipping_info" => [
            "name" => "Sowmith",
            "address" => [
                "line1" => "Eco Space, bellandur",
                "line2" => "OuterRingRoad",
                "city" => "Bangalore",
                "country_code" => "IN",
                "postal_code" => "560103",
            ],
        ],
        "cart_info" => [
            "item_details" => [
                [
                    "item_code" => "ItemCode-1",
                    "item_name" => "Item1 - radio",
                    "item_description" => "Radio",
                    "item_quantity" => "2",
                    "item_unit_price" => [
                        "currency_code" => "USD",
                        "value" => "50.00",
                    ],
                    "item_amount" => [
                        "currency_code" => "USD",
                        "value" => "100.00",
                    ],
                    "tax_amounts" => [
                        [
                            "tax_amount" => [
                                "currency_code" => "USD",
                                "value" => "20.00",
                            ],
                        ],
                    ],
                    "total_item_amount" => [
                        "currency_code" => "USD",
                        "value" => "120.00",
                    ],
                    "invoice_number" => "Invoice-005",
                ],
                [
                    "item_code" => "ItemCode-2",
                    "item_name" => "Item2 - Headset",
                    "item_description" => "Headset",
                    "item_quantity" => "3",
                    "item_unit_price" => [
                        "currency_code" => "USD",
                        "value" => "100.00",
                    ],
                    "item_amount" => [
                        "currency_code" => "USD",
                        "value" => "300.00",
                    ],
                    "tax_amounts" => [
                        [
                            "tax_amount" => [
                                "currency_code" => "USD",
                                "value" => "60.00",
                            ],
                        ],
                    ],
                    "total_item_amount" => [
                        "currency_code" => "USD",
                        "value" => "360.00",
                    ],
                    "invoice_number" => "Invoice-005",
                ],
                [
                    "item_name" => "3",
                    "item_quantity" => "1",
                    "item_unit_price" => [
                        "currency_code" => "USD",
                        "value" => "-50.00",
                    ],
                    "item_amount" => [
                        "currency_code" => "USD",
                        "value" => "-50.00",
                    ],
                    "total_item_amount" => [
                        "currency_code" => "USD",
                        "value" => "-50.00",
                    ],
                    "invoice_number" => "Invoice-005",
                ],
            ],
        ],
        "store_info" => [],
        "auction_info" => [],
        "incentive_info" => [],
    ];

    /**
     * It checks that the paypalTransactionToContact function works well.
     */
    public function testPaypalTransactionToContact()
    {
        $expectedContactData = [
            'contact_type' => 'Individual',
            'first_name' => self::PAYPAL_SAMPLE_DATA['payer_info']['payer_name']['given_name'],
            'last_name' => self::PAYPAL_SAMPLE_DATA['payer_info']['payer_name']['surname'],
        ];
        $transformedContact = CRM_PaypalImporter_Transformer::paypalTransactionToContact(self::PAYPAL_SAMPLE_DATA);
        self::assertSame($expectedContactData, $transformedContact, 'Invalid transformed data.');
    }

    /**
     * It checks that the paypalTransactionToEmail function works well.
     */
    public function testPaypalTransactionToEmail()
    {
        $expectedEmailData = [
            'location_type_id' => 1,
            'email' => self::PAYPAL_SAMPLE_DATA['payer_info']['email_address'],
        ];
        $transformedEmail = CRM_PaypalImporter_Transformer::paypalTransactionToEmail(self::PAYPAL_SAMPLE_DATA);
        self::assertSame($expectedEmailData, $transformedEmail, 'Invalid transformed data.');
    }

    /**
     * It checks that the paypalTransactionToContribution function works well.
     */
    public function testPaypalTransactionToCivicrmContributionIncomindMoney()
    {
        $expectedContributionData = [
            'total_amount' => self::PAYPAL_SAMPLE_DATA['transaction_info']['transaction_amount']['value'],
            'fee_amount' => intval(self::PAYPAL_SAMPLE_DATA['transaction_info']['fee_amount']['value'], 10) * -1,
            'non_deductible_amount' => self::PAYPAL_SAMPLE_DATA['transaction_info']['transaction_amount']['value'],
            'trxn_id' => self::PAYPAL_SAMPLE_DATA['transaction_info']['transaction_id'],
            'receive_date' => self::PAYPAL_SAMPLE_DATA['transaction_info']['transaction_initiation_date'],
            'invoice_number' => self::PAYPAL_SAMPLE_DATA['transaction_info']['invoice_id'],
            'source' => self::PAYPAL_SAMPLE_DATA['cart_info']['item_details'][0]['item_name'] ?: '',
            'contribution_status_id' => 1,
        ];
        $transformedContribution = CRM_PaypalImporter_Transformer::paypalTransactionToContribution(self::PAYPAL_SAMPLE_DATA);
        self::assertSame($expectedContributionData, $transformedContribution, 'Invalid transformed data.');
    }

    public function testPaypalTransactionToCivicrmContributionRefund()
    {
        $expectedContributionData = [
            'total_amount' => self::PAYPAL_SAMPLE_DATA['transaction_info']['transaction_amount']['value'],
            'fee_amount' => intval(self::PAYPAL_SAMPLE_DATA['transaction_info']['fee_amount']['value'], 10) * -1,
            'non_deductible_amount' => self::PAYPAL_SAMPLE_DATA['transaction_info']['transaction_amount']['value'],
            'trxn_id' => self::PAYPAL_SAMPLE_DATA['transaction_info']['transaction_id'],
            'receive_date' => self::PAYPAL_SAMPLE_DATA['transaction_info']['transaction_initiation_date'],
            'invoice_number' => self::PAYPAL_SAMPLE_DATA['transaction_info']['invoice_id'],
            'source' => self::PAYPAL_SAMPLE_DATA['cart_info']['item_details'][0]['item_name'] ?: '',
            'contribution_status_id' => 7,
            'contribution_cancel_date' => self::PAYPAL_SAMPLE_DATA['transaction_info']['transaction_updated_date'],
        ];
        $refundTransaction = self::PAYPAL_SAMPLE_DATA;
        $refundTransaction['transaction_info']['transaction_status'] = 'V';
        $transformedContribution = CRM_PaypalImporter_Transformer::paypalTransactionToContribution($refundTransaction);
        self::assertSame($expectedContributionData, $transformedContribution, 'Invalid transformed data.');
    }

    public function testPaypalTransactionToCivicrmContributionNotMappedStatus()
    {
        $expectedContributionData = [
            'total_amount' => self::PAYPAL_SAMPLE_DATA['transaction_info']['transaction_amount']['value'],
            'fee_amount' => intval(self::PAYPAL_SAMPLE_DATA['transaction_info']['fee_amount']['value'], 10) * -1,
            'non_deductible_amount' => self::PAYPAL_SAMPLE_DATA['transaction_info']['transaction_amount']['value'],
            'trxn_id' => self::PAYPAL_SAMPLE_DATA['transaction_info']['transaction_id'],
            'receive_date' => self::PAYPAL_SAMPLE_DATA['transaction_info']['transaction_initiation_date'],
            'invoice_number' => self::PAYPAL_SAMPLE_DATA['transaction_info']['invoice_id'],
            'source' => self::PAYPAL_SAMPLE_DATA['cart_info']['item_details'][0]['item_name'] ?: '',
            'contribution_status_id' => 0,
        ];
        $refundTransaction = self::PAYPAL_SAMPLE_DATA;
        $refundTransaction['transaction_info']['transaction_status'] = 'XX';
        $transformedContribution = CRM_PaypalImporter_Transformer::paypalTransactionToContribution($refundTransaction);
        self::assertSame($expectedContributionData, $transformedContribution, 'Invalid transformed data.');
    }
}
