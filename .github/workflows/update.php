<?php

/**
 * Adapts phpseclib 3.0's unit tests to phpseclib3_compat
 *
 * Tests of phpseclib 3.0's internals (protected methods and properties) are skipped since phpseclib3_compat
 * doesn't have those internals, as are tests of the few differences that are documented in README.md
 */

function replaceFileContents($fileName, $search, $replace)
{
    $fileContents = file_get_contents($fileName);
    $updatedFileContents = preg_replace($search, $replace, $fileContents, -1, $count);
    if (!$count) {
        throw new RuntimeException("$search not found in $fileName");
    }
    file_put_contents($fileName, $updatedFileContents);
}

function skipTest($fileName, $test)
{
    replaceFileContents($fileName, "~ public function $test\(~", " private function skip$test(");
}

// the library lives in src/ instead of phpseclib/
replaceFileContents('tests/Unit/Crypt/EC/CurveTest.php', '~/\.\./\.\./\.\./\.\./phpseclib/~', '/../../../../src/');
replaceFileContents('tests/Unit/File/ASN1Test.php', '~\'phpseclib/File/ASN1/Maps\'~', '\'src/File/ASN1/Maps\'');

// these use reflection on protected methods or properties
skipTest('tests/Unit/Crypt/ChaCha20Test.php', 'test252');
skipTest('tests/Unit/Crypt/ChaCha20Test.php', 'test262');
skipTest('tests/Unit/Crypt/EC/CurveTest.php', 'testCurveExistance');
// this overrides protected methods to fake an SFTP server
unlink('tests/Unit/Net/SFTPUnitTest.php');

// PrimeField\Integer::squareRoot() returns null instead of false
skipTest('tests/Unit/Math/PrimeField/TestCase.php', 'testPrimeFieldWithPrimeNumbers');

// make_compatible_with_phpunit12.php deletes SSH2Test.php because of a mock that newer PHPUnit versions
// don't support. SSH2 is too important not to test so the CI workflow puts the file back, which leaves it
// for this script to make compatible
$file = 'tests/Functional/Net/SSH2Test.php';
if (file_exists($file)) {
    replaceFileContents($file, '~ /\*\* @dataProvider ([a-zA-Z0-9]+) \*/~', ' #[\PHPUnit\Framework\Attributes\DataProvider("$1")]');
    replaceFileContents($file, '~ /\*\* @depends ([a-zA-Z0-9]+) \*/~', ' #[\PHPUnit\Framework\Attributes\Depends("$1")]');
    replaceFileContents(
        $file,
        '~\$callbackObject = \$this->getMockBuilder\(\'stdClass\'\).*?->will\(\$this->returnValue\(true\)\);~s',
        '$callbackObject = new class {
            public $callCount = 0;
            public function callbackMethod($data)
            {
                $this->callCount++;
                return true;
            }
        };'
    );
    replaceFileContents(
        $file,
        '~(\$ssh->exec\(\'pwd\', \[\$callbackObject, \'callbackMethod\'\]\);)~',
        '$1
        $this->assertGreaterThanOrEqual(1, $callbackObject->callCount, \'callbackMethod was not called\');'
    );
    // testCryptoAlgorithms() reflects on phpseclib 3.0's private encryption_algorithm_to_crypt_instance(),
    // which phpseclib3_compat doesn't have, so it uses phpseclib 4.0's
    replaceFileContents(
        $file,
        '~self::callFunc\(\$ssh, \'encryption_algorithm_to_crypt_instance\', \[\$algorithm\]\)~',
        '(new \ReflectionMethod(\phpseclib4\Net\SSH2::class, \'encryption_algorithm_to_crypt_instance\'))->invoke(null, $algorithm)'
    );
}
