# phpseclib3_compat

[![CI Status](https://github.com/phpseclib/phpseclib3_compat/actions/workflows/ci.yml/badge.svg?branch=master&event=push "CI Status")](https://github.com/phpseclib/phpseclib3_compat/actions/workflows/ci.yml?query=branch%3Amaster)

phpseclib 3.0 polyfill built with phpseclib 4.0

## Overview

phpseclib 4.0 breaks backwards compatibility with phpseclib 3.0. Its namespace is `\phpseclib4` instead of `\phpseclib3`, SSH2 and SFTP throw exceptions where they used to return false, X.509 certificates work completely differently, etc. So let's say you wanted to use phpseclib 4.0 whilst some of your other dependencies still use phpseclib 3.0. What would you do in that instance?

That's where phpseclib3_compat comes into play. Require phpseclib/phpseclib:~4.0 and phpseclib/phpseclib3_compat:~1.0 and your dependencies will start using phpseclib 4.0 even if they don't know it.

Consider this code sample:

```php
use phpseclib3\Crypt\PublicKeyLoader;
use phpseclib3\Net\SFTP;

$key = PublicKeyLoader::load(file_get_contents('id_ed25519'), false);

$sftp = new SFTP('website.com');
if (!$sftp->login('username', $key)) {
    exit('Login Failed');
}

if (!$sftp->put('filename.remote', 'xxx')) {
    print_r($sftp->getSFTPErrors());
}
```

With phpseclib3_compat that code runs on phpseclib 4.0: the key is loaded and the SFTP session is handled by phpseclib 4.0, and `put()` returns false and logs the error, as it did in phpseclib 3.0, instead of throwing an exception, as phpseclib 4.0 does.

phpseclib 3.0's unit tests and its SSH2 / SFTP functional tests are run against phpseclib3_compat.

## How it works

phpseclib3_compat provides the `\phpseclib3` classes. Most of them use a phpseclib 4.0 object behind the scenes, which `getV4Object()` returns. That's handy when migrating code to phpseclib 4.0 one piece at a time. Arguments, return values and exceptions are converted between the two versions. For example, `RSA::SIGNATURE_PKCS1` has a different value in phpseclib 4.0 and phpseclib 4.0's `BadConfigurationException` becomes phpseclib 3.0's.

Classes whose API didn't change in phpseclib 4.0 (the elliptic curves, the BigInteger engines, the key format plugins, etc.) are aliases of the phpseclib 4.0 classes.

`\phpseclib3\File\ASN1` and `\phpseclib3\File\X509` are the exception. phpseclib 4.0 replaced the array based ASN.1 and X.509 API with an object based one so phpseclib3_compat includes phpseclib 3.0's ASN.1 and X.509 code. The keys, hashes and signatures that it uses still come from phpseclib 4.0.

## Differences from phpseclib 3.0

- PHP 8.1 or newer is required since that's what phpseclib 4.0 requires
- Extending a phpseclib 3.0 class and overriding its protected methods won't change what the class does since the work is done by phpseclib 4.0
- Fractional timeouts are rounded up to whole seconds since phpseclib 4.0 only supports whole seconds
- `getErrors()` and `getSFTPErrors()` may word errors differently
- `\phpseclib3\Math\PrimeField\Integer::squareRoot()` returns null instead of false when there's no square root
- `get_class()` returns the names of phpseclib 4.0 classes for the classes that are aliases

## Installation

With [Composer](https://getcomposer.org/):

```
composer require phpseclib/phpseclib:~4.0 phpseclib/phpseclib3_compat:~1.0
```
