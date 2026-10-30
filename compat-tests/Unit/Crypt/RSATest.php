<?php

/**
 * @license   http://www.opensource.org/licenses/mit-license.html  MIT License
 */

namespace phpseclib3\CompatTests\Unit\Crypt;

use phpseclib3\Crypt\RSA;
use phpseclib3\Exception\UnsupportedFormatException;
use phpseclib3\Math\BigInteger;
use PHPUnit\Framework\TestCase;

class RSATest extends TestCase
{
    public function testRawExportOfPrivateKey()
    {
        $raw = RSA::createKey(512)->toString('Raw');

        $this->assertSame(['e', 'n', 'd', 'primes', 'exponents', 'coefficients'], array_keys($raw));
        $this->assertInstanceOf(BigInteger::class, $raw['d']);
    }

    /**
     * Keys without primes are saved as public keys, as phpseclib 3.0 did
     */
    public function testRawExportOfKeyWithoutPrimes()
    {
        $public = RSA::createKey(512)->getPublicKey();
        $raw = $public->asPrivateKey()->toString('Raw');

        $this->assertSame(['e', 'n'], array_keys($raw));
        $this->assertTrue($raw['n']->equals($public->toString('Raw')['n']));
    }

    public function testRawExportOfPasswordProtectedKey()
    {
        $this->expectException(UnsupportedFormatException::class);

        RSA::createKey(512)->withPassword('password')->toString('Raw');
    }

    public function testSupportedKeyFormatsAreTheClassesOfThisPackage()
    {
        $formats = RSA::getSupportedKeyFormats();

        $this->assertSame('phpseclib3\Crypt\RSA\Formats\Keys\PKCS8', $formats['pkcs8']);
        foreach ($formats as $class) {
            $this->assertTrue(class_exists($class), "$class doesn't exist");
        }
    }
}
