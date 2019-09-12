# Clover Tg

[![Build Status](https://travis-ci.org/clover/clover-tg.svg?branch=master)](https://travis-ci.org/clover/clover-tg)
[![styleci](https://styleci.io/repos/CHANGEME/shield)](https://styleci.io/repos/CHANGEME)
[![Coverage Status](https://coveralls.io/repos/github/clover/clover-tg/badge.svg?branch=master)](https://coveralls.io/github/clover/clover-tg?branch=master)

[![Packagist](https://img.shields.io/packagist/v/clover/clover-tg.svg)](https://packagist.org/packages/clover/clover-tg)
[![Packagist](https://poser.pugx.org/clover/clover-tg/d/total.svg)](https://packagist.org/packages/clover/clover-tg)
[![Packagist](https://img.shields.io/packagist/l/clover/clover-tg.svg)](https://packagist.org/packages/clover/clover-tg)

Package description: CHANGE ME

## Installation

Install via composer

composer.json
```json
  //...
  "repositories": [
      {
          "type": "vcs",
          "url":  "git@github.com:skyhilam/clover-tg.git"
      }
  ],
  //...
```

```bash
composer require clover/clover-tg
```

### Publish Configuration File

```bash
php artisan vendor:publish --provider="Clover\CloverTg\ServiceProvider" --tag="config"
```

## Usage

first get start [CLOVER https://t.me/clover_computer_ltd_bot](https://t.me/clover_computer_ltd_bot) bot get your token
add your token to .env file

.env
```
TELEGRAM_TOKEN=token
```

```php
  CloverTg::send($message, $token)
  // order
  CloverTg::sendWithCallback($message, $callbackurl, $ex_time = 60, $token = null)
```


## Security

If you discover any security related issues, please email
instead of using the issue tracker.

## Credits

- [](https://github.com/clover/clover-tg)
- [All contributors](https://github.com/clover/clover-tg/graphs/contributors)

This package is bootstrapped with the help of
[melihovv/laravel-package-generator](https://github.com/melihovv/laravel-package-generator).
