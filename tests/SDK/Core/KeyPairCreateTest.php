<?php
/**
 * Part of the evias/nem-php package.
 *
 * NOTICE OF LICENSE
 *
 * Licensed under MIT License.
 *
 * This source file is subject to the MIT License that is
 * bundled with this package in the LICENSE file.
 *
 * @package    evias/nem-php
 * @version    1.0.0
 * @author     Grégory Saive <greg@evias.be>
 * @license    MIT License
 * @copyright  (c) 2017, Grégory Saive <greg@evias.be>
 * @link       http://github.com/evias/nem-php
 */
namespace NEM\Tests\SDK\Core;

use NEM\Tests\TestCase;
use NEM\Core\KeyPair;
use NEM\Contracts\KeyPair as KeyPairContract;
use NEM\Core\Buffer;

class KeyPairCreateTest
    extends TestCase
{
    /**
     * Unit test for *KeyPair Cloning*.
     *
     * @return void
     */
    public function testCreateValidKeyPair()
    {
        $kp1 = KeyPair::create("e77c84331edbfa3d209c4e68809c98a634ad6e8891e4174455c33be9dd25fce5");
        $kp2 = KeyPair::create("e77c84331edbfa3d209c4e68809c98a634ad6e8891e4174455c33be9dd25fce5");

        // should always create the same KeyPair content !
        $this->assertEquals($kp1->getPrivateKey("hex"), $kp2->getPrivateKey("hex"));
        $this->assertEquals($kp1->getSecretKey("hex"), $kp2->getSecretKey("hex"));
        $this->assertEquals($kp1->getPublicKey("hex"), $kp2->getPublicKey("hex"));

        $publicShouldBe = "d90c08cfbbf918d9304ddd45f6432564c390a5facff3df17ed5c096c4ccf0d04";
        $this->assertEquals($publicShouldBe, $kp1->getPublicKey("hex"));
    }

    /**
     * Unit test for *Random KeyPair creation*.
     *
     * This should produce a randomly generated KeyPair.
     *
     * @depends testCreateValidKeyPair
     * @return void
     */
    public function testCreateRandomKeyPair()
    {
        $kp = KeyPair::create();

        // check contract and class..
        $this->assertInstanceOf(KeyPair::class, $kp);
        $this->assertInstanceOf(KeyPairContract::class, $kp);

        // check KeyPair content
        $this->assertEquals(64, strlen($kp->getPrivateKey("hex")));
        $this->assertEquals(64, strlen($kp->getSecretKey("hex")));
        $this->assertEquals(64, strlen($kp->getPublicKey("hex")));
        $this->assertTrue(ctype_xdigit($kp->getPrivateKey("hex")));
        $this->assertTrue(ctype_xdigit($kp->getSecretKey("hex")));
        $this->assertTrue(ctype_xdigit($kp->getPublicKey("hex")));

        // validate SECRET KEY creation. The secret key contains
        // the *reversed hexadecimal representation* of the private key.
        $buf = Buffer::fromHex($kp->getPrivateKey("hex"));
        $flipped = $buf->flip();

        $this->assertEquals($flipped->getHex(), $kp->getSecretKey("hex"));

        // should *deterministically* create keys.
        $priv = $kp->getPrivateKey("hex");
        $newKp = KeyPair::create($priv); // create from private key hex

        $this->assertEquals($kp->getPrivateKey("hex"), $newKp->getPrivateKey("hex"));
        $this->assertEquals($kp->getSecretKey("hex"), $newKp->getSecretKey("hex"));
        $this->assertEquals($kp->getPublicKey("hex"), $newKp->getPublicKey("hex"));
    }

    /**
     * Unit test for *KeyPair Cloning*.
     *
     * @depends testCreateRandomKeyPair
     * @return void
     */
    public function testKeyPairCloning()
    {
        $kp = KeyPair::create();
        $clone = KeyPair::create($kp);

        // validate internal KeyPair content cloning
        $this->assertEquals($kp->getPrivateKey("hex"), $clone->getPrivateKey("hex"));
        $this->assertEquals($kp->getSecretKey("hex"), $clone->getSecretKey("hex"));
        $this->assertEquals($kp->getPublicKey("hex"), $clone->getPublicKey("hex"));
    }

    /**
     * Unit test for *Private Key Buffer Cloning*.
     *
     * @depends testCreateRandomKeyPair
     * @return void
     */
    public function testPrivateKeyBufferCloning()
    {
        $kp = KeyPair::create("0123456789abcdef0123456789abcdef0123456789abcdef0123456789abcdef");
        $privateBuffer = Buffer::fromHex("0123456789abcdef0123456789abcdef0123456789abcdef0123456789abcdef", 32);
        $clone = KeyPair::create($privateBuffer);

        // validate internal buffer content cloning
        $this->assertEquals($privateBuffer->getHex(), $clone->getPrivateKey("hex"));
        $this->assertEquals($kp->getPrivateKey("hex"), $clone->getPrivateKey("hex"));
        $this->assertEquals($kp->getSecretKey("hex"), $clone->getSecretKey("hex"));
        $this->assertEquals($kp->getPublicKey("hex"), $clone->getPublicKey("hex"));
    }

    /**
     * Data provider for the testKeyPairVectors unit test.
     *
     * @return array
     */
    public function keypairVectorsProvider()
    {
        /**
         * Some private keys are 64 bytes and some are 66 bytes. The 66 bytes private keys
         * correspond to *padded private keys* generate by NIS for negatives.
         *
         * Samples taken from https://github.com/NemProject/nem-test-vectors
         * @link https://github.com/NemProject/nem-test-vectors
         */

        return [
            ["e77c84331edbfa3d209c4e68809c98a634ad6e8891e4174455c33be9dd25fce5", "d90c08cfbbf918d9304ddd45f6432564c390a5facff3df17ed5c096c4ccf0d04"],
            ["00f8cd8e07478559a64b7d2be6b92a55d9b63ec0f0bd3d4b7686ee6f524113c490", "a9092549008e7f4965ce140004e736378e91d2f7e67e5fb8729a14b1bf764780"],
            ["575dbb3062267eff57c970a336ebbc8fbcfe12c5bd3ed7bc11eb0481d7704ced", "c5f54ba980fcbb657dbaaa42700539b207873e134d2375efeab5f1ab52f87844"],
            ["5b0e3fa5d3b49a79022d7c1e121ba1cbbf4db5821f47ab8c708ef88defc29bfe", "96eb2a145211b1b7ab5f0d4b14f8abc8d695c7aee31a3cfc2d4881313c68eea3"],
            ["738ba9bb9110aea8f15caa353aca5653b4bdfca1db9f34d0efed2ce1325aeeda", "2d8425e4ca2d8926346c7a7ca39826acd881a8639e81bd68820409c6e30d142a"],
            ["e8bf9bc0f35c12d8c8bf94dd3a8b5b4034f1063948e3cc5304e55e31aa4b95a6", "4feed486777ed38e44c489c7c4e93a830e4c4a907fa19a174e630ef0f6ed0409"],
            ["00e8bf9bc0f35c12d8c8bf94dd3a8b5b4034f1063948e3cc5304e55e31aa4b95a6", "4feed486777ed38e44c489c7c4e93a830e4c4a907fa19a174e630ef0f6ed0409"],
            ["c325ea529674396db5675939e7988883d59a5fc17a28ca977e3ba85370232a83", "83ee32e4e145024d29bca54f71fa335a98b3e68283f1a3099c4d4ae113b53e54"],
            ["00c325ea529674396db5675939e7988883d59a5fc17a28ca977e3ba85370232a83", "83ee32e4e145024d29bca54f71fa335a98b3e68283f1a3099c4d4ae113b53e54"],
            ["a811cb7a80a7227ae61f6da536534ee3c2744e3c7e4b85f3e0df3c6a9c5613df", "6d34c04f3a0e42f0c3c6f50e475ae018cfa2f56df58c481ad4300424a6270cbb"],
            ["00a811cb7a80a7227ae61f6da536534ee3c2744e3c7e4b85f3e0df3c6a9c5613df", "6d34c04f3a0e42f0c3c6f50e475ae018cfa2f56df58c481ad4300424a6270cbb"],
            ["143a815e92e43f3ed1a921ee48cd143931b88b7c3d8e1e981f743c2a5be3c5ba", "419ed11d48730e4ae2c93f0ea4df853b8d578713a36dab227517cf965861af4e"],
            ["bc1a082f5ac6fdd3a83ade211e5986ac0551bad6c7da96727ec744e5df963e2a", "a160e6f9112233a7ce94202ed7a4443e1dac444b5095f9fecbb965fba3f92cac"],
            ["00bc1a082f5ac6fdd3a83ade211e5986ac0551bad6c7da96727ec744e5df963e2a", "a160e6f9112233a7ce94202ed7a4443e1dac444b5095f9fecbb965fba3f92cac"],
            ["4e47b4c6f4c7886e49ec109c61f4af5cfbb1637283218941d55a7f9fe1053f72", "fbb91b16df828e21a9802980a44fc757c588bc1382a4cea429d6fa2ae0333f56"],
            ["032d5a1558209ef0462fba5e2126451e693c11b81c25006cf2fe57bad4097fa7", "7faa6f65050c5ac97f6fd907555764232cf69307c123c0a197a8b8a2715fe8a9"],
            ["1aa7172480361b6dc9c918b61d71d78e656d36c18e8a1d4548bae8d9df990ed3", "d556956e4ae43d4146335820819018c2f0723e4d5c03b18ffffa8fc1096b832d"],
        ];
    }

    /**
     * Test content initialization for KeyPair class.
     *
     * @depends testCreateValidKeyPair
     * @dataProvider keypairVectorsProvider
     *
     * @param   string  $privateKey
     * @param   string  $expectedPublicKey
     * @return void
     */
    public function testKeyPairVectors($privateKey, $expectedPublicKey)
    {
        $kp = KeyPair::create($privateKey);
        $this->assertEquals($expectedPublicKey, $kp->getPublicKey("hex"));
    }
}
