<?php

/**
 * Conversions between phpseclib 3.0 and phpseclib 4.0 values
 *
 * @license   http://www.opensource.org/licenses/mit-license.html  MIT License
 */

namespace phpseclib3\Compat;

use phpseclib3\Exception as Ex3;
use phpseclib3\Math\BigInteger;
use phpseclib4\Exception as Ex4;
use phpseclib4\File\ASN1\Types;

/**
 * Conversions between phpseclib 3.0 and phpseclib 4.0 values
 *
 * @internal
 */
abstract class Convert
{
    /**
     * The phpseclib 3.0 exception thrown in place of each phpseclib 4.0 exception
     *
     * phpseclib 4.0 exceptions are matched by their class and then by their parents' classes
     */
    const EXCEPTIONS = [
        Ex4\BadConfigurationException::class => Ex3\BadConfigurationException::class,
        Ex4\BadDecryptionException::class => Ex3\BadDecryptionException::class,
        Ex4\BadMethodCallException::class => \BadMethodCallException::class,
        Ex4\ConnectionClosedException::class => Ex3\ConnectionClosedException::class,
        Ex4\FileSystemException::class => Ex3\FileNotFoundException::class,
        Ex4\InvalidArgumentException::class => \InvalidArgumentException::class,
        Ex4\InvalidModeException::class => Ex3\BadModeException::class,
        Ex4\InvalidPacketLengthException::class => Ex3\InvalidPacketLengthException::class,
        Ex4\InvalidStateException::class => Ex3\InsufficientSetupException::class,
        Ex4\KeyConstraintException::class => \LengthException::class,
        Ex4\LengthException::class => \LengthException::class,
        Ex4\NoKeyLoadedException::class => Ex3\NoKeyLoadedException::class,
        Ex4\NoSupportedAlgorithmsException::class => Ex3\NoSupportedAlgorithmsException::class,
        Ex4\ResourceLimitException::class => \OutOfBoundsException::class,
        Ex4\TimeoutException::class => Ex3\TimeoutException::class,
        Ex4\UnexpectedValueException::class => \UnexpectedValueException::class,
        Ex4\UnsupportedAlgorithmException::class => Ex3\UnsupportedAlgorithmException::class,
        Ex4\UnsupportedCurveException::class => Ex3\UnsupportedCurveException::class,
        Ex4\UnsupportedValueException::class => \RuntimeException::class,
    ];

    /**
     * phpseclib 3.0 exceptions that depend on the message of the phpseclib 4.0 exception
     *
     * These take precedence over self::EXCEPTIONS
     */
    const MESSAGES = [
        // symmetric keys
        '~^Key length has already been set to~' => Ex3\InconsistentSetupException::class,
        '~^Key has already been set and is not~' => Ex3\InconsistentSetupException::class,
        '~^The IV length \(\d+\) does not match the block size~' => Ex3\InconsistentSetupException::class,
        '~^No valid mode has been specified$~' => Ex3\BadModeException::class,
        '~^The ciphertext length \(\d+\) needs to be a multiple of the block size~' => \LengthException::class,
        '~^A salt must be provided for bcrypt to work$~' => \RuntimeException::class,
        // public keys
        '~^Multiple (encryption|signature) padding modes have been selected~' => Ex3\InconsistentSetupException::class,
        '~ private keys do not support encryption$~' => Ex3\UnsupportedFormatException::class,
        '~^(Ciphertext|Message|Signature) representative out of range$~' => \OutOfRangeException::class,
        '~^Resultant string length out of range$~' => \OutOfRangeException::class,
        '~^The PSS format can only be used when the signature method has been explicitly set to PSS$~' =>
            Ex3\UnsupportedFormatException::class,
        '~^Public key components not found$~' => \RuntimeException::class,
        '~^(DH does not use a hash algorithm|Montgomery Curves cannot be used to create signatures)$~' =>
            Ex3\UnsupportedOperationException::class,
        '~^(Ed25519 only supports sha512 as a hash|Ed448 only supports shake256 with a length of 114 bytes)$~' =>
            Ex3\UnsupportedAlgorithmException::class,
        '~^Only Ed25519 and Ed448 support contexts$~' => Ex3\UnsupportedCurveException::class,
        '~^x must be between 1 and the order of the curve~' => \RangeException::class,
        '~^x must be a positive integer less than \d+ bytes in length$~' => \RangeException::class,
        '~^Private Key should be \d+-bytes long$~' => \LengthException::class,
        '~^The only supported lengths are 32 and 56$~' => \LengthException::class,
        '~^(Private Key secret is not of the correct length|Point compression on binary finite field elliptic curves is not supported)$~' =>
            \RuntimeException::class,
        '~^Invalid named prime provided$~' => \InvalidArgumentException::class,
        '~^Valid parameters are either two integers \(L and N\), a single DSA object or no parameters at all\.$~' =>
            Ex3\InsufficientSetupException::class,
        '~^SSH only supports keys with an N \(length of Group Order q\) of 160$~' => \InvalidArgumentException::class,
        '~^(createKey|createParameters)\(\) should not be called from final classes ~' => \RuntimeException::class,
        '~^Valid engines are null, PHP, OpenSSL or libsodium$~' => \InvalidArgumentException::class,
        '~^Unsupported block cipher mode of operation$~' => \UnexpectedValueException::class,
        '~^ssh-agent does not provide a mechanism to get the private key$~' => \RuntimeException::class,
        // SSH
        '~^Cannot connect to ~' => Ex3\UnableToConnectException::class,
        '~^(Connection closed due to timeout|Invalid [HU]MAC|Plaintext is too short)$~' =>
            Ex3\ConnectionClosedException::class,
        '~^Data is not available on channel$~' => Ex3\InsufficientSetupException::class,
        '~^\$data should be a Closure if you specify SOURCE_CALLBACK flag$~' => \BadFunctionCallException::class,
        // decoding of SSH packets and keys
        '~^(A boolean parameter|A string|An array|An integer) was expected\.$~' => \InvalidArgumentException::class,
        '~^Bytes must be represented as an integer between 0 and 255, inclusive\.$~' => \InvalidArgumentException::class,
        '~^At least (one|four|eight) byte needs to be present for successful ~' => \LengthException::class,
    ];

    /**
     * Converts a phpseclib 4.0 exception to the exception phpseclib 3.0 would have thrown
     *
     * Exceptions that don't come from phpseclib 4.0 are returned as-is. $map takes precedence over
     * self::EXCEPTIONS.
     *
     * @param array<class-string, class-string> $map
     */
    public static function exception(\Throwable $e, array $map = []): \Throwable
    {
        if (!$e instanceof Ex4\BaseException) {
            return $e;
        }
        foreach (self::MESSAGES as $pattern => $new) {
            if (preg_match($pattern, $e->getMessage())) {
                return new $new($e->getMessage(), $e->getCode(), $e);
            }
        }
        $map += self::EXCEPTIONS;
        for ($class = get_class($e); $class !== false; $class = get_parent_class($class)) {
            if (isset($map[$class])) {
                $new = $map[$class];
                return new $new($e->getMessage(), $e->getCode(), $e);
            }
        }
        return new \RuntimeException($e->getMessage(), $e->getCode(), $e);
    }

    /**
     * Returns the phpseclib 4.0 class that corresponds to a phpseclib 3.0 class
     *
     * For classes that extend phpseclib 3.0 classes, that's the class corresponding to the nearest phpseclib 3.0
     * parent class
     *
     * @param string $class
     * @return string
     */
    public static function v4Class($class)
    {
        while (strpos($class, 'phpseclib3\\') !== 0) {
            $class = get_parent_class($class);
        }
        return 'phpseclib4' . substr($class, 10);
    }

    /**
     * Converts phpseclib 4.0 values to phpseclib 3.0 values
     *
     * Arrays are converted recursively
     *
     * @param mixed $value
     * @return mixed
     */
    public static function toV3($value)
    {
        if (is_array($value)) {
            foreach ($value as &$v) {
                $v = self::toV3($v);
            }
            return $value;
        }
        if ($value instanceof \phpseclib4\Math\BigInteger && !$value instanceof BigInteger) {
            $result = new BigInteger($value->toHex(true), -16);
            if ($value->getPrecision() > 0) {
                $result->setPrecision($value->getPrecision());
            }
            return $result;
        }
        if (is_object($value) && strpos(get_class($value), 'phpseclib4\\') === 0) {
            return self::wrap($value);
        }
        return $value;
    }

    /**
     * Wraps a phpseclib 4.0 object in the phpseclib 3.0 class of the same name
     *
     * Objects of phpseclib 4.0 classes that phpseclib3 doesn't have a wrapper for (eg. because the
     * phpseclib 3.0 class is an alias of the phpseclib 4.0 class) are returned as-is
     *
     * @param object $value
     * @return object
     */
    public static function wrap($value)
    {
        $class = 'phpseclib3' . substr(get_class($value), 10);
        if (!is_subclass_of($class, Wrapper::class)) {
            return $value;
        }
        $wrapper = (new \ReflectionClass($class))->newInstanceWithoutConstructor();
        (function () use ($value) {
            $this->v4 = $value;
        })->call($wrapper);
        return $wrapper;
    }

    /**
     * Converts phpseclib 3.0 RSA padding flags to phpseclib 4.0 ones
     *
     * phpseclib 4.0 gave SIGNATURE_PKCS1 the value that SIGNATURE_RELAXED_PKCS1 had and always accepts the
     * signatures that SIGNATURE_RELAXED_PKCS1 accepted
     *
     * @param int $padding
     * @return int
     */
    public static function rsaPaddingToV4($padding)
    {
        $pkcs1 = \phpseclib3\Crypt\RSA::SIGNATURE_PKCS1 | \phpseclib3\Crypt\RSA::SIGNATURE_RELAXED_PKCS1;
        $result = $padding & ~$pkcs1;
        if ($padding & $pkcs1) {
            $result |= \phpseclib4\Crypt\RSA::SIGNATURE_PKCS1;
        }
        return $result;
    }

    /**
     * Converts phpseclib 4.0 RSA padding flags to phpseclib 3.0 ones
     *
     * @param int $padding
     * @return int
     */
    public static function rsaPaddingToV3($padding)
    {
        $result = $padding & ~\phpseclib4\Crypt\RSA::SIGNATURE_PKCS1;
        if ($padding & \phpseclib4\Crypt\RSA::SIGNATURE_PKCS1) {
            $result |= \phpseclib3\Crypt\RSA::SIGNATURE_PKCS1;
        }
        return $result;
    }

    /**
     * Converts decoded phpseclib 4.0 ASN.1 values to decoded phpseclib 3.0 ASN.1 values
     *
     * Arrays and constructed types are converted recursively
     *
     * @param mixed $value
     * @return mixed
     */
    public static function asn1ToV3($value)
    {
        switch (true) {
            case $value instanceof Types\Choice:
                return [$value->index => self::asn1ToV3($value->value)];
            case $value instanceof \phpseclib4\File\ASN1\Constructed:
                return self::asn1ToV3($value->toArray());
            case is_array($value):
                foreach ($value as &$v) {
                    $v = self::asn1ToV3($v);
                }
                return $value;
            case $value instanceof Types\OID:
                return $value->name ?? (string) $value->value;
            case $value instanceof Types\Integer:
                return isset($value->mappedValue) ? $value->mappedValue : self::toV3($value);
            case $value instanceof Types\BitString:
                return isset($value->mappedValue) ? $value->mappedValue : $value->value;
            case $value instanceof Types\BaseString:
                return $value->value;
            case $value instanceof Types\Boolean:
                return $value->value;
            case $value instanceof Types\ExplicitNull:
                return '';
            case $value instanceof Types\UTCTime:
            case $value instanceof Types\GeneralizedTime:
                return $value->format('D, d M Y H:i:s O');
            case $value instanceof \phpseclib4\File\ASN1\Element:
                return new \phpseclib3\File\ASN1\Element($value->value);
        }
        return self::toV3($value);
    }

    /**
     * Converts phpseclib 3.0 values to phpseclib 4.0 values
     *
     * Arrays are converted recursively
     *
     * @param mixed $value
     * @return mixed
     */
    public static function toV4($value)
    {
        if (is_array($value)) {
            foreach ($value as &$v) {
                $v = self::toV4($v);
            }
            return $value;
        }
        if ($value instanceof Wrapper) {
            return $value->getV4Object();
        }
        return $value;
    }
}
