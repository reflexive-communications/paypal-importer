# paypal-importer

This module provides paypal data import functionality. This extension contains an API endpoint for starting the transaction process, a cron job for triggering the endpoint every hour, and an admin form to be able to setup the necessary parameters for the Paypal API and for the import process.

The extension is licensed under [AGPL-3.0](LICENSE.txt).

## Requirements

* PHP v7.3+
* CiviCRM 5.29+ (might work below, not tested)
* [RC-Base](https://github.com/reflexive-communications/rc-base) v0.8.1+

## Installation (CLI, Git)

Sysadmins and developers may clone the [Git](https://en.wikipedia.org/wiki/Git) repo for this extension and
install it with the command-line tool [cv](https://github.com/civicrm/cv).

```bash
git clone git@github.com:reflexive-communications/paypal-importer.git
cv en paypal-importer
```

## Usage

To be able to communicate with the Paypal API, You have to setup a couple parameters in the admin form (Contributions > Paypal Importer).

### Admin form

![settings](./images/paypal-import-form.png)

- The client id and client secret parameters. They are necessary for the [authentication](https://developer.paypal.com/docs/platforms/get-started/#get-api-credentials).
- The host of the Paypal environment. In case of sandbox, it supposed to be `https://api-m.sandbox.paypal.com`, the live supposed to be `https://api-m.paypal.com`.
- The initial start date and limit parameters for the [transaction search API](https://developer.paypal.com/docs/api/transaction-search/v1/).
- The request limit, that is the maximum number of transaction search API calls for one import iteration.
- The payment method that will be used as the payment method of the contribution. The options are the payment methods provided by the current civicrm system.
- The financial type that will be used as the financial type of the contribution. The options are the financial types provided by the current civicrm system.
- The optional tag that will be added to the contributor contact. The options are the tags provided by the current civicrm system.
- The optional group that will receive the contributor contact. The options are the groups provided by the current civicrm system.
- Start, stop or verify checkbox for changing the state of the importer application.

### Dashboard on the admin form

- The current state of the application is always visible on the top box.
- The stats of the last import iteration is also always visible (If we have any stats). 
- The soft issues of the last iteration is also visible if we have any. Soft issues are the ones that doesn't block the import process. Eg: for some reason we can't add the tag to the contact.
- On case of error state, the cause of the error is shown on the state box.

### Importer states.

The importer application is state based. The state manages the behaviour of the import process. The following states are defined:

- `do-nothing` is the initial state. In this state the import process does nothing. On the admin form, You can push back the process to this state from any other states.
- `import-init` state is followed by the `do-nothing` state. In this state the import process pushes the state to `import` and starts the transaction search from the initial start date.
- `import` state is followed by the `impost-init` state. In this state it maintains internal parameters for the transaction search API calls. If the end time of the search is in the future, it pushes the state to `sync`.
- `sync` state is followed by the `import` state. This state behaves exactly the same as the `import` state.
- `error` state could be set if the communication to the Paypal API fails. It means the application could enter this error state from the `import` and the `sync` states. On the admin form, you have to push the state back to `do-nothing` from this state. In this state the import process does nothing.
