<?php

/**
 * @license   http://www.opensource.org/licenses/mit-license.html  MIT License
 */

namespace phpseclib3\CompatTests\Unit\File;

use phpseclib3\Crypt\RSA;
use phpseclib3\File\ASN1;
use phpseclib3\File\ASN1\Element;
use phpseclib3\File\ASN1\Maps\RSASSA_PSS_params;
use phpseclib3\File\X509;
use PHPUnit\Framework\TestCase;

class X509Test extends TestCase
{
    /**
     * The RSASSA-PSS parameters must be encoded as RSASSA-PSS-params, not as a string
     */
    public function testPSSSignatureParameters()
    {
        $key = RSA::createKey(1024)
            ->withPadding(RSA::SIGNATURE_PSS)
            ->withHash('sha256')
            ->withMGFHash('sha256');

        $subject = new X509();
        $subject->setDN('CN=phpseclib');
        $subject->setPublicKey($key->getPublicKey());
        $issuer = new X509();
        $issuer->setPrivateKey($key);
        $issuer->setDN('CN=phpseclib');
        $x509 = new X509();
        $cert = $x509->saveX509($x509->sign($issuer, $subject));

        $x509 = new X509();
        $cert = $x509->loadX509($cert);
        $this->assertSame('id-RSASSA-PSS', $cert['signatureAlgorithm']['algorithm']);
        $parameters = $cert['signatureAlgorithm']['parameters'];
        $this->assertInstanceOf(Element::class, $parameters);
        $decoded = ASN1::decodeBER($parameters->element);
        $parameters = ASN1::asn1map($decoded[0], RSASSA_PSS_params::MAP);
        $this->assertSame('id-sha256', $parameters['hashAlgorithm']['algorithm']);
        $this->assertTrue($x509->validateSignature(false));
    }
}
