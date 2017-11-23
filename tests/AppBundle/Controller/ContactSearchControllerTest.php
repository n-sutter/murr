<?php
namespace Tests\AppBundle\Controller;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * a test class for the search controller
 *
 * @version 1.0
 * @author cst206 cst225
 */
class ContactSearchControllerTest extends WebTestCase
{
    private $client;

    protected function setUp()
    {
        $this->client = static::createClient();
    }

    public function testSuccessfullyReceiveSearch()
    {
        $this->assertContains('', $this->client->getResponse()->getContent());
    }

    //public function testSuccessfullyReceivedSearchWithSpecialCharacter()
    //{

    //}

    //public function testNoSearchOnOnlySpaces()
    //{

    //}

    //public function testRemoveTrailingSpaces()
    //{

    //}

    //public function testRemoveLeadingSpaces()
    //{

    //}

    //public function testRemoveSandwichSpaces()
    //{

    //}

    //public function testRemoveUnneededSpaces()
    //{

    //}
}