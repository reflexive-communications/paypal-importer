# paypal-importer

[![CI](https://github.com/reflexive-communications/paypal-importer/actions/workflows/main.yml/badge.svg)](https://github.com/reflexive-communications/paypal-importer/actions/workflows/main.yml)

This extension provides a PayPal transaction importer.
It contains an API endpoint for starting the process, a scheduled job for triggering the endpoint every hour, and an admin form to setup the necessary parameters.

The extension is licensed under [AGPL-3.0](LICENSE.txt).

## Requirements

-   PHP v7.3+
-   CiviCRM v5.38+
-   rc-base

## Installation

Sysadmins and developers may clone the [Git](https://en.wikipedia.org/wiki/Git) repo for this extension and install it with the command-line tool [cv](https://github.com/civicrm/cv).

```bash
git clone git@github.com:reflexive-communications/paypal-importer.git
cv en paypal-importer
```

## Getting Started

To be able to communicate with Paypal API you have to config the importer on the admin form **Contributions > Paypal Importer**.

## Known Issues

Currently the contribution status ID mapping is based on hardcoded IDs.
For details check the [Developer Notes](DEVELOPER.md).
