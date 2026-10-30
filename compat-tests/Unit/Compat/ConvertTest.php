<?php

/**
 * @license   http://www.opensource.org/licenses/mit-license.html  MIT License
 */

namespace phpseclib3\CompatTests\Unit\Compat;

use phpseclib3\Compat\Convert;
use phpseclib3\Crypt\AES;
use phpseclib3\Crypt\DH;
use phpseclib3\Crypt\EC;
use phpseclib3\Exception as Ex3;
use phpseclib4\Exception as Ex4;
use PHPUnit\Framework\TestCase;

class ConvertTest extends TestCase
{
    /**
     * phpseclib 4.0 exceptions whose phpseclib 3.0 class depends on their message
     *
     * @return array
     */
    public static function exceptionProvider()
    {
        return [
            [Ex4\InvalidArgumentException::class, 'No valid mode has been specified', Ex3\BadModeException::class],
            [Ex4\BadDecryptionException::class, 'The ciphertext length (7) needs to be a multiple of the block size (16)', \LengthException::class],
            [Ex4\LengthException::class, 'The IV length (8) does not match the block size (16)', Ex3\InconsistentSetupException::class],
            [Ex4\LengthException::class, 'Ciphertext representative out of range', \OutOfRangeException::class],
            [Ex4\LengthException::class, 'Resultant string length out of range', \OutOfRangeException::class],
            [Ex4\BadMethodCallException::class, 'DH does not use a hash algorithm', Ex3\UnsupportedOperationException::class],
            [Ex4\BadMethodCallException::class, 'Montgomery Curves cannot be used to create signatures', Ex3\UnsupportedOperationException::class],
            [Ex4\BadMethodCallException::class, 'Ed25519 only supports sha512 as a hash', Ex3\UnsupportedAlgorithmException::class],
            [Ex4\BadMethodCallException::class, 'Only Ed25519 and Ed448 support contexts', Ex3\UnsupportedCurveException::class],
            [Ex4\UnexpectedValueException::class, 'x must be a positive integer less than 256 bytes in length', \RangeException::class],
            [Ex4\UnexpectedValueException::class, 'Private Key should be 32-bytes long', \LengthException::class],
            [Ex4\BadMethodCallException::class, 'createKey() should not be called from final classes (phpseclib4\Crypt\RSA\PrivateKey)', \RuntimeException::class],
            [Ex4\TimeoutException::class, 'Connection closed due to timeout', Ex3\ConnectionClosedException::class],
            [Ex4\UnexpectedValueException::class, 'Invalid HMAC', Ex3\ConnectionClosedException::class],
            [Ex4\UnexpectedValueException::class, 'Data is not available on channel', Ex3\InsufficientSetupException::class],
            [Ex4\UnexpectedValueException::class, 'At least four byte needs to be present for successful N / i / s / L decodes', \LengthException::class],
            [Ex4\UnsupportedValueException::class, 'A string was expected.', \InvalidArgumentException::class],
            // the class decides for everything else
            [Ex4\UnexpectedValueException::class, 'Something else', \UnexpectedValueException::class],
            [Ex4\TimeoutException::class, 'Timed out waiting for server', Ex3\TimeoutException::class],
        ];
    }

    /**
     * Like phpseclib 3.0, some exceptions depend on what went wrong rather than on phpseclib 4.0's class
     *
     * @dataProvider exceptionProvider
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('exceptionProvider')]
    public function testException($class4, $message, $class3)
    {
        $converted = Convert::exception(new $class4($message));

        $this->assertSame($class3, get_class($converted));
        $this->assertSame($message, $converted->getMessage());
    }

    public function testSigningWithAMontgomeryCurve()
    {
        $this->expectException(Ex3\UnsupportedOperationException::class);

        EC::createKey('Curve25519')->sign('message');
    }

    public function testUnsupportedHashForEd25519()
    {
        $this->expectException(Ex3\UnsupportedAlgorithmException::class);

        EC::createKey('Ed25519')->withHash('sha256');
    }

    public function testHashForDH()
    {
        $this->expectException(Ex3\UnsupportedOperationException::class);

        DH::createKey(DH::createParameters('diffie-hellman-group14-sha1'))->withHash('sha256');
    }

    public function testInvalidMode()
    {
        $this->expectException(Ex3\BadModeException::class);

        new AES('invalid');
    }
}
