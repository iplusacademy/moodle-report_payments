# moodle-report_payments

This report helps admins and managers to get a better view who payed for which course.

## Installation

* Unzip code into the report/payments directory
* Log into Moodle as administrator.
* Visit Site admin => Notifications.

## Use

This is a report generating an overview of payments done on different levels:

* User
* Course
* Course Category
* Global

But it also adds the possibility to add payments to custom reports so (daily - weekly - monthly) reports can be generated and
send automatically to the finance department.

## Supported databases

This plugin has been tested on

* MYSQL
* MariaDB
* PostgreSQL

## THANKS

This plugin was developed for [Medical Access Uganda](https://medical-access.org/).

## Theme support

This plugin is developed and tested on Moodle Core's Boost theme and Boost child themes, including Moodle Core's Classic theme.

## Moodle release support

This plugin is maintained for the latest major releases of Moodle.

## Bug Reports / Support

We try our best to deliver bug-free plugins, but we can not test the plugin for every platform, database, PHP and
Moodle version. If you find any bug please report it on GitHub: https://github.com/iplusacademy/report_payments/issues.
Please provide a detailed bug description, including the plugin and Moodle version and, if applicable, a screenshot.

You may also file a request for enhancement on GitHub https://github.com/iplusacademy/report_payments/pulls.
If we consider the request generally useful and if it can be implemented with reasonable effort we might implement it in a future version.

You may also post general questions on the plugin on GitHub, but note that we do not have the resources to provide
detailed support.

## Maturity: Release candidate

[![Build Status](https://github.com/iplusacademy/report_payments/actions/workflows/main.yml/badge.svg)](https://github.com/iplusacademy/report_payments/actions/workflows/main.yml)
[![Coverage Status](https://coveralls.io/repos/github/iplusacademy/report_payments/badge.svg?branch=main)](https://coveralls.io/github/iplusacademy/report_payments?branch=main)

## Todo

* payment totals (See MDL-80153)

## Done

* global level
* course level
* privacy
* cost alignment
* download reports
* category level
* datasource
* scheduled monthly report (via report builder)
* user level

## Copyright

Medical Access Uganda Limited

This program is free software: you can redistribute it and/or modify it under
the terms of the GNU General Public License as published by the Free Software
Foundation, either version 3 of the License, or (at your option) any later
version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY
WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A
PARTICULAR PURPOSE.  See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with
this program.  If not, see <https://www.gnu.org/licenses/>.
