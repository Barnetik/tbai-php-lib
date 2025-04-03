# Changelog

All notable changes to this project will be documented in this file. See [commit-and-tag-version](https://github.com/absolute-version/commit-and-tag-version) for commit guidelines.

## [0.6.2](https://github.com/Barnetik/tbai-php-lib/compare/v0.6.1...v0.6.2) (2025-04-03)


### Features

* Added saveFullResponse to response object to save full headers info too.\nAdded toArray method to Responses.\nSaveResponseContent now saves ungzipped content on bizkaia responses too. ([403eee4](https://github.com/Barnetik/tbai-php-lib/commit/403eee433e32993f5ed46f8c1408288fc542a275))


### Bug Fixes

* Allow between 1 an 3 vat regimes as stated on ticketbai spec ([34ada59](https://github.com/Barnetik/tbai-php-lib/commit/34ada59dcdd287cfff77c198774eb3868e163e7e))

## [0.6.1](https://github.com/Barnetik/tbai-php-lib/compare/v0.6.0...v0.6.1) (2025-03-13)


### Bug Fixes

* Ticketbai::batuzIncomeTaxes should be able to return null values ([4eda700](https://github.com/Barnetik/tbai-php-lib/commit/4eda7004b4c8d3700e5c92943da660f6078bc9ad))

## [0.6.0](https://github.com/Barnetik/tbai-php-lib/compare/v0.5.0...v0.6.0) (2024-10-29)


### Features

* Added Expenses without invoice submission ([06f0314](https://github.com/Barnetik/tbai-php-lib/commit/06f0314e6c0443bfdcec03dbaa08105db730ff54))
* Added lastInvoiceNumber attribute to expense invoices Header ([ff3b7d3](https://github.com/Barnetik/tbai-php-lib/commit/ff3b7d3c5542d6af451b7b454aeb94a9d60a4243))
* Juridic and physical person expenses with invoices registration working ([2b15960](https://github.com/Barnetik/tbai-php-lib/commit/2b15960e10b0143775b32f601bbc85e545dcf392))


### Bug Fixes

* Greek VIES code uses ISO 639-1 language code (EL) instead of ISO 3166 country code. ([185ad57](https://github.com/Barnetik/tbai-php-lib/commit/185ad572d9b0f3a3f6bc808d6bb0ff4a84c4ea8e)), closes [#48](https://github.com/Barnetik/tbai-php-lib/issues/48)
* Loading international invoices from XML was not loading proper recipient identifier data ([7b45e6e](https://github.com/Barnetik/tbai-php-lib/commit/7b45e6ecb9ae861063c1f0796b4738199ef15c2e)), closes [#47](https://github.com/Barnetik/tbai-php-lib/issues/47)

## [0.5.0](https://github.com/Barnetik/tbai-php-lib/compare/v0.4.0...v0.5.0) (2024-08-22)


### Features

* Added better curl error control. Now exception is thrown with curl error number and message. ([597305b](https://github.com/Barnetik/tbai-php-lib/commit/597305b955f39a06a0dd7863a09ca67f9c1bc2ab))


### Bug Fixes

* VatRegimes count control was not working. ([d557b04](https://github.com/Barnetik/tbai-php-lib/commit/d557b048e067e21ad797811550e97368e22cf6bb))

## [0.4.0](https://github.com/Barnetik/tbai-php-lib/compare/v0.3.0...v0.4.0) (2024-08-08)


### Features

* Added chainSignatureValue method to Ticketbai for easier chain data retrieving ([0c7ca63](https://github.com/Barnetik/tbai-php-lib/commit/0c7ca635e35489c77b45298442f5f44eaaec9666))

## [0.3.0](https://github.com/Barnetik/tbai-php-lib/compare/v0.2.2...v0.3.0) (2024-06-24)


### Features

* Add headers() function to retrieve all headers from response ([4213202](https://github.com/Barnetik/tbai-php-lib/commit/42132020d21f68f17218763dbafcf87d4884968e))

## [0.2.2](https://github.com/Barnetik/tbai-php-lib/compare/v0.2.1...v0.2.2) (2024-06-24)

## [0.2.1](https://github.com/Barnetik/tbai-php-lib/compare/v0.2.0...v0.2.1) (2024-06-24)



# [0.2.0](https://github.com/Barnetik/tbai-php-lib/compare/v0.1.3...v0.2.0) (2024-06-16)


### Bug Fixes

* Added new not subject tax breadown reasons (VT, IE) ([46db221](https://github.com/Barnetik/tbai-php-lib/commit/46db2213f63c371eaf38aea365016d5c690586c2))
* Added new vat regime codes (17, 19) ([d91ebf3](https://github.com/Barnetik/tbai-php-lib/commit/d91ebf368fcb13c740e5193c2ef566f28e502eac))
* Ensure intracomunitary operations send recipient info inside IDOtro element and with full European VAT ID (country code prefixed) ([58446fd](https://github.com/Barnetik/tbai-php-lib/commit/58446fdc30e47ff572e010d400ba231aa3eebb57))
* Incremented NotSubject Breakdown items limit from 2 to 4 ([ed015cd](https://github.com/Barnetik/tbai-php-lib/commit/ed015cd37bf14aed602d0b8701c3d482c4b46d41))
* Incremented VatDetail Breakdown items limit from 6 to 12 ([f1681dd](https://github.com/Barnetik/tbai-php-lib/commit/f1681ddb612c7df814da5071c3f0aeae74eb5ea9))



## [0.1.3](https://github.com/Barnetik/tbai-php-lib/compare/v0.1.2...v0.1.3) (2023-12-15)


### Bug Fixes

* VatId must allow different Vat formats for NIF/IFZ/EUVAT type as this id type MUST be used for intracomunitary transactions ([bd44583](https://github.com/Barnetik/tbai-php-lib/commit/bd445838ecb74c9aa9d8379d0d1b3339661bf664))



## [0.1.2](https://github.com/Barnetik/tbai-php-lib/compare/v0.1.1...v0.1.2) (2023-11-20)


### Bug Fixes

* password protected PEM keys were not working ([e40c823](https://github.com/Barnetik/tbai-php-lib/commit/e40c8230b4962e20a862225685c8f34ea7f50699))



## [0.1.1](https://github.com/Barnetik/tbai-php-lib/compare/v0.1.0...v0.1.1) (2023-09-29)


### Bug Fixes

* Rectified invoice number was not correctly retrieved when creating a rectifying invoice from xml ([5853abd](https://github.com/Barnetik/tbai-php-lib/commit/5853abd7980766072161d4cfa57e364730c9aa53))



# 0.1.0 (2023-09-05)
