<?php

namespace Tests\AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use AppBundle\Entity\Communication;
use AppBundle\Entity\Property;
use AppBundle\Entity\Address;
use AppBundle\DataFixtures\ORM\LoadUserData;
use DateTime;
//use Doctrine\Common\Persistence\ObjectRepository;

class CommunicationControllerTest extends WebTestCase
{
    private $em;

    /**
     * (@inheritDoc)
     */
    protected function setUp()
    {
        self::bootKernel();
        $this->em = static::$kernel->getContainer()
            ->get('doctrine')
            ->getManager();

        // Load the admin user into the database so they can log in
        $encoder = static::$kernel->getContainer()->get('security.password_encoder');

        $userLoader = new LoadUserData($encoder);
        $userLoader->load($this->em);
    }

    public function testFormSuccess()
    {
        $client = static::createClient(array(), array('PHP_AUTH_USER' => 'admin', 'PHP_AUTH_PW'   => 'password'));

        $crawler = $client->request('GET', '/communication/new');

        $form = $crawler->selectButton('Add')->form();

        //set form values
        //$form['communication[date][year]'] = "2017";
        //$form['communication[date][month]'] = "10";
        //$form['communication[date][day]'] = "5";
        $form['communication[type]']="Phone";
        $form['communication[medium]']="Incoming";
        //$form['communication[contact]']=1; //contact id
        $form['communication[contactName]'] = "John Smith";
        $form['communication[contactEmail]'] = "email@email.com";
        $form['communication[contactPhone]'] = "123-123-4567";
        //$form['communication[property]']=1; //property id
        $form['communication[category]']="Container";
        $form["communication[description]"]="Container has graffiti and needs to be cleaned. Action request made";

        $crawler = $client->submit($form);

        $this->assertContains("Communication added successfully",$client->getResponse()->getContent());

        //Refresh the form because a new one was created after submission
        $form = $crawler->selectButton('Add')->form();

        //test that all fields are now empty
            //date will not be empty by default
        //$this->assertEmpty($form['communication[date][year]']->getValue());
        //$this->assertEmpty($form['communication[date][month]']->getValue());
        //$this->assertEmpty($form['communication[date][day]']->getValue());
        $this->assertEmpty($form['communication[type]']->getValue());
        $this->assertEmpty($form['communication[medium]']->getValue());
        $this->assertEmpty( $form['communication[contactName]']->getValue());
        $this->assertEmpty( $form['communication[contactEmail]']->getValue());
        $this->assertEmpty( $form['communication[contactPhone]']->getValue());
        $this->assertEmpty($form['communication[property]']->getValue());
        $this->assertEmpty($form['communication[category]']->getValue());
        $this->assertEmpty($form['communication[description]']->getValue());
    }

    //public function testFutureDate()
    //{

    //    $client = static::createClient(array(), array('PHP_AUTH_USER' => 'admin', 'PHP_AUTH_PW'   => 'password'));

    //    $crawler = $client->request('GET', '/communication/new');

    //    $form = $crawler->selectButton('Add')->form();

    //    //set form values
    //    $form['communication[date][year]'] = "2019";
    //    $form['communication[date][month]'] = "10";
    //    $form['communication[date][day]'] = "5";

    //    $crawler = $client->submit($form);

    //    $this->assertContains("Please select a current or past date",$client->getResponse()->getContent());
    //}

    //public function testEmptyDate()
    //{
    //    $client = static::createClient(array(), array('PHP_AUTH_USER' => 'admin', 'PHP_AUTH_PW'   => 'password'));

    //    $crawler = $client->request('GET', '/communication/new');

    //    $form = $crawler->selectButton('Add')->form();

    //    //set form values
    //    //do not set date by removing the form fields
    //    $form->remove('communication[date][year]');
    //    $form->remove('communication[date][month]');
    //    $form->remove('communication[date][day]');


    //    $crawler = $client->submit($form);

    //    $this->assertContains("Please select a date",$client->getResponse()->getContent());
    //}

    //public function testNonExistantDate()
    //{
    //    $client = static::createClient(array(), array('PHP_AUTH_USER' => 'admin', 'PHP_AUTH_PW'   => 'password'));

    //    $crawler = $client->request('GET', '/communication/new');

    //    $form = $crawler->selectButton('Add')->form();

    //    //set form values
    //    $form['communication[date][year]'] = "2017";
    //    $form['communication[date][month]'] = "2";
    //    $form['communication[date][day]'] = "30";

    //    $crawler = $client->submit($form);

    //    $this->assertContains("Please select a valid date",$client->getResponse()->getContent());
    //}

    public function testNoType()
    {
        $client = static::createClient(array(), array('PHP_AUTH_USER' => 'admin', 'PHP_AUTH_PW'   => 'password'));

        $crawler = $client->request('GET', '/communication/new');

        $form = $crawler->selectButton('Add')->form();

        //set form values
        $form['communication[type]']=0;

        $crawler = $client->submit($form);

        $this->assertContains("Please select a type of communication",$client->getResponse()->getContent());
    }

    public function testNoMedium()
    {
        $client = static::createClient(array(), array('PHP_AUTH_USER' => 'admin', 'PHP_AUTH_PW'   => 'password'));

        $crawler = $client->request('GET', '/communication/new');

        $form = $crawler->selectButton('Add')->form();

        //set form values
        //do not set medium
        //$form['communication[medium]']=0;

        $crawler = $client->submit($form);

        $this->assertContains("Please select a direction",$client->getResponse()->getContent());
    }

    //public function testBlankContact()
    //{
    //    $client = static::createClient(array(), array('PHP_AUTH_USER' => 'admin', 'PHP_AUTH_PW'   => 'password'));

    //    $crawler = $client->request('GET', '/communication/new');

    //    $form = $crawler->selectButton('Add')->form();

    //    //set form values
    //    $form['communication[contact]']=0; //blank contact ID

    //    $crawler = $client->submit($form);

    //    $this->assertContains("Please enter a contact",$client->getResponse()->getContent());
    //}

    //public function testResidentContact()
    //{
    //    $client = static::createClient(array(), array('PHP_AUTH_USER' => 'admin', 'PHP_AUTH_PW'   => 'password'));

    //    $crawler = $client->request('GET', '/communication/new');

    //    $form = $crawler->selectButton('Add')->form();

    //    //set form values
    //    $form['communication[date][year]'] = "2017";
    //    $form['communication[date][month]'] = "10";
    //    $form['communication[date][day]'] = "5";
    //    $form['communication[type]']="phone";
    //    $form['communication[medium]']="incoming";
    //    $form['communication[contact]']=-1; //identifier for a resident, will not be stored
    //    $form['communication[property]']=1;
    //    $form['communication[category]']="container";
    //    $form["communication[description]"]="Container has graffiti and needs to be cleaned. Action request made";

    //    $crawler = $client->submit($form);

    //    $this->assertContains("Communication added successfully",$client->getResponse()->getContent());
    //}

    //public function testBlankProperty()
    //{
    //    $client = static::createClient(array(), array('PHP_AUTH_USER' => 'admin', 'PHP_AUTH_PW'   => 'password'));

    //    $crawler = $client->request('GET', '/communication/new');

    //    $form = $crawler->selectButton('Add')->form();

    //    //set form values
    //    $form['communication[property]']=0; //blank property ID

    //    $crawler = $client->submit($form);

    //    $this->assertContains("Please select a property",$client->getResponse()->getContent());
    //}

    //public function testMultiOrNAProperty()
    //{
    //    $client = static::createClient(array(), array('PHP_AUTH_USER' => 'admin', 'PHP_AUTH_PW'   => 'password'));

    //    $crawler = $client->request('GET', '/communication/new');

    //    $form = $crawler->selectButton('Add')->form();

    //    //set form values
    //    //set form values
    //    $form['communication[date][year]'] = "2017";
    //    $form['communication[date][month]'] = "10";
    //    $form['communication[date][day]'] = "5";
    //    $form['communication[type]']="Phone";
    //    $form['communication[medium]']="Incoming";
    //    $form['communication[contact]']=1; //contact id
    //    $form['communication[property]']=-1; //multi-property or N/A property identifier
    //    $form['communication[category]']="container";
    //    $form["communication[description]"]="Container has graffiti and needs to be cleaned. Action request made";

    //    $crawler = $client->submit($form);

    //    $this->assertContains("Communication added successfully",$client->getResponse()->getContent());
    //}

    public function testBlankCategory()
    {
        $client = static::createClient(array(), array('PHP_AUTH_USER' => 'admin', 'PHP_AUTH_PW'   => 'password'));

        $crawler = $client->request('GET', '/communication/new');

        $form = $crawler->selectButton('Add')->form();

        //set form values
        $form['communication[category]']=0; //blank category value


        $crawler = $client->submit($form);

        $this->assertContains("Please select a category",$client->getResponse()->getContent());
    }

    public function testBlankDescription()
    {
        $client = static::createClient(array(), array('PHP_AUTH_USER' => 'admin', 'PHP_AUTH_PW'   => 'password'));

        $crawler = $client->request('GET', '/communication/new');

        $form = $crawler->selectButton('Add')->form();

        //set form values
        $form['communication[description]']=""; //blank description


        $crawler = $client->submit($form);

        $this->assertContains("Please provide a brief description of the communication",$client->getResponse()->getContent());
    }

    //public function testShortDescription()
    //{
    //    $client = static::createClient(array(), array('PHP_AUTH_USER' => 'admin', 'PHP_AUTH_PW'   => 'password'));

    //    $crawler = $client->request('GET', '/communication/new');

    //    $form = $crawler->selectButton('Add')->form();

    //    //set form values
    //    $form['communication[description]']="Talked"; //description too short


    //    $crawler = $client->submit($form);

    //    $this->assertContains("Please provide a description of 50 characters or more",$client->getResponse()->getContent());
    //}

    public function testLongDescription()
    {
        $client = static::createClient(array(), array('PHP_AUTH_USER' => 'admin', 'PHP_AUTH_PW'   => 'password'));

        $crawler = $client->request('GET', '/communication/new');

        $form = $crawler->selectButton('Add')->form();


        //set form values
        $form['communication[description]']=str_repeat('a',501);//generate a string that is too long


        $crawler = $client->submit($form);

        $this->assertContains("Description must be 500 characters or less",$client->getResponse()->getContent());
    }



    /**
     * Story 11b
     * Tests that you can view a communication entry with the proper information
     */
    public function testViewActionSuccess(){

        //create a property for the communication
        $property = new Property();
        $property->setSiteId(1593846);
        $property->setPropertyName("Charlton Arms");
        $property->setPropertyType("Townhouse Condo");
        $property->setPropertyStatus("Active");
        $property->setNumUnits(5);
        $property->setNeighbourhoodName("Sutherland");
        $property->setNeighbourhoodId("O48");

        //create an address for the property
        $address = new Address();
        $address->setStreetAddress("12 15th st east");
        $address->setPostalCode("S0E 1A0");
        $address->setCity("Saskatoon");
        $address->setProvince("Saskatchewan");
        $address->setCountry("Canada");
        $property->setAddress($address);

        //create a communication
        $comm = new Communication();
        $comm->setType("In Person");
        $comm->setMedium("Incoming");
        $comm->setContactName("John Smith");
        $comm->setContactEmail("email@email.com");
        $comm->setContactPhone("306-123-4567");
        $comm->setProperty($property);
        $comm->setCategory("Container");
        $comm->setDescription("Bin will be moved to the eastern side of the building");

        //create the client
        $client = static::createClient(array(), array('PHP_AUTH_USER' => 'admin', 'PHP_AUTH_PW'   => 'password'));

        //get the entity manager and make sure the communication exists
        $em = $client->getContainer()->get('doctrine.orm.entity_manager');
        $repo = $em->getRepository(Communication::class);

        //insert the communication
        $commId = $repo->insert($comm);

        $crawler = $client->request("GET","/communication/view/$commId");

        $response = $client->getResponse()->getContent();

        //check that the page contains all the information from the object
        $this->assertContains("In Person",$response);
        $this->assertContains("Incoming",$response);
        $this->assertContains("John Smith",$response);
        $this->assertContains("email@email.com",$response);
        $this->assertContains("306-123-4567",$response);
        $this->assertContains("Container",$response);
        $this->assertContains("Bin will be moved to the eastern side of the building",$response);
        $this->assertContains("12 15th st east",$response);
    }

    /**
     * Story 11b
     * Tests that if an invalid ID is put in the request that it will fail
     */
    public function testViewBadId(){
        //create a client to get to the page
        $client = static::createClient(array(), array('PHP_AUTH_USER' => 'admin', 'PHP_AUTH_PW'   => 'password'));

        //request the communication view page for a communication that does not exist
        $crawler = $client->request("GET","communication/view/-5");

        //assert that the correct error message appeared
        $this->assertContains("The specified communication ID could not be found", $client->getResponse()->getContent());
    }

    /**
     * Story 11b
     * Tests that if no ID is put in the request there will be an error message
     */
    public function testViewNoID(){
        //create a client to get to the page
        $client = static::createClient(array(), array('PHP_AUTH_USER' => 'admin', 'PHP_AUTH_PW'   => 'password'));

        //request the communication view page for a communication that does not exist
        $crawler = $client->request("GET","communication/view/");

        //assert that the correct error message appeared
        $this->assertContains("No communication ID specified", $client->getResponse()->getContent());
    }

    /**
     * Story 11c
     * Test that special characters can be entered into the database
     */
    public function testSearchSpecialCharactersSuccess()
    {
        // get a repository so we can query for data
        $repository = $this->em->getRepository(Communication::class);

        // create a client so we can view the page
        $client = static::createClient();

        // create a communication to search for in the test
        $communication = new Communication();
        $communication->setDate(new DateTime("2018-01-01"));
        $communication->setType("Phone");
        $communication->setMedium("Incoming");
        $communication->setCategory("Multi-purpose");
        $communication->setDescription("Its a bin");

        $repository->insert($communication);

        // go to the page and search for 'Jim'
        $client->request('GET', '/communication/jsonsearch/Multi-purpose');

        // create an array so we can call the search
        $queryStrings = array();
        $queryStrings[] = 'Multi-purpose';

        // query the database
        $repository->communicationSearch($queryStrings);

        // assert that what we expect is actually returned
        $this->assertContains('[{"id":1,"date":{"timezone":{"name":"America\/Regina","transitions":[{"ts":-2147483648,"time":"1901-12-13T20:45:52+0000","offset":-25116,"isdst":false,"abbr":"LMT"},{"ts":-2147483648,"time":"1901-12-13T20:45:52+0000","offset":-25116,"isdst":false,"abbr":"LMT"},{"ts":-2030202084,"time":"1905-09-01T06:58:36+0000","offset":-25200,"isdst":false,"abbr":"MST"},{"ts":-1632063600,"time":"1918-04-14T09:00:00+0000","offset":-21600,"isdst":true,"abbr":"MDT"},{"ts":-1615132800,"time":"1918-10-27T08:00:00+0000","offset":-25200,"isdst":false,"abbr":"MST"},{"ts":-1251651600,"time":"1930-05-04T07:00:00+0000","offset":-21600,"isdst":true,"abbr":"MDT"},{"ts":-1238349600,"time":"1930-10-05T06:00:00+0000","offset":-25200,"isdst":false,"abbr":"MST"},{"ts":-1220202000,"time":"1931-05-03T07:00:00+0000","offset":-21600,"isdst":true,"abbr":"MDT"},{"ts":-1206900000,"time":"1931-10-04T06:00:00+0000","offset":-25200,"isdst":false,"abbr":"MST"},{"ts":-1188752400,"time":"1932-05-01T07:00:00+0000","offset":-21600,"isdst":true,"abbr":"MDT"},{"ts":-1175450400,"time":"1932-10-02T06:00:00+0000","offset":-25200,"isdst":false,"abbr":"MST"},{"ts":-1156698000,"time":"1933-05-07T07:00:00+0000","offset":-21600,"isdst":true,"abbr":"MDT"},{"ts":-1144000800,"time":"1933-10-01T06:00:00+0000","offset":-25200,"isdst":false,"abbr":"MST"},{"ts":-1125248400,"time":"1934-05-06T07:00:00+0000","offset":-21600,"isdst":true,"abbr":"MDT"},{"ts":-1111946400,"time":"1934-10-07T06:00:00+0000","offset":-25200,"isdst":false,"abbr":"MST"},{"ts":-1032714000,"time":"1937-04-11T07:00:00+0000","offset":-21600,"isdst":true,"abbr":"MDT"},{"ts":-1016992800,"time":"1937-10-10T06:00:00+0000","offset":-25200,"isdst":false,"abbr":"MST"},{"ts":-1001264400,"time":"1938-04-10T07:00:00+0000","offset":-21600,"isdst":true,"abbr":"MDT"},{"ts":-986148000,"time":"1938-10-02T06:00:00+0000","offset":-25200,"isdst":false,"abbr":"MST"},{"ts":-969814800,"time":"1939-04-09T07:00:00+0000","offset":-21600,"isdst":true,"abbr":"MDT"},{"ts":-954093600,"time":"1939-10-08T06:00:00+0000","offset":-25200,"isdst":false,"abbr":"MST"},{"ts":-937760400,"time":"1940-04-14T07:00:00+0000","offset":-21600,"isdst":true,"abbr":"MDT"},{"ts":-922039200,"time":"1940-10-13T06:00:00+0000","offset":-25200,"isdst":false,"abbr":"MST"},{"ts":-906310800,"time":"1941-04-13T07:00:00+0000","offset":-21600,"isdst":true,"abbr":"MDT"},{"ts":-890589600,"time":"1941-10-12T06:00:00+0000","offset":-25200,"isdst":false,"abbr":"MST"},{"ts":-880210800,"time":"1942-02-09T09:00:00+0000","offset":-21600,"isdst":true,"abbr":"MWT"},{"ts":-769395600,"time":"1945-08-14T23:00:00+0000","offset":-21600,"isdst":true,"abbr":"MPT"},{"ts":-765388800,"time":"1945-09-30T08:00:00+0000","offset":-25200,"isdst":false,"abbr":"MST"},{"ts":-748450800,"time":"1946-04-14T09:00:00+0000","offset":-21600,"isdst":true,"abbr":"MDT"},{"ts":-732729600,"time":"1946-10-13T08:00:00+0000","offset":-25200,"isdst":false,"abbr":"MST"},{"ts":-715791600,"time":"1947-04-27T09:00:00+0000","offset":-21600,"isdst":true,"abbr":"MDT"},{"ts":-702489600,"time":"1947-09-28T08:00:00+0000","offset":-25200,"isdst":false,"abbr":"MST"},{"ts":-684342000,"time":"1948-04-25T09:00:00+0000","offset":-21600,"isdst":true,"abbr":"MDT"},{"ts":-671040000,"time":"1948-09-26T08:00:00+0000","offset":-25200,"isdst":false,"abbr":"MST"},{"ts":-652892400,"time":"1949-04-24T09:00:00+0000","offset":-21600,"isdst":true,"abbr":"MDT"},{"ts":-639590400,"time":"1949-09-25T08:00:00+0000","offset":-25200,"isdst":false,"abbr":"MST"},{"ts":-620838000,"time":"1950-04-30T09:00:00+0000","offset":-21600,"isdst":true,"abbr":"MDT"},{"ts":-608140800,"time":"1950-09-24T08:00:00+0000","offset":-25200,"isdst":false,"abbr":"MST"},{"ts":-589388400,"time":"1951-04-29T09:00:00+0000","offset":-21600,"isdst":true,"abbr":"MDT"},{"ts":-576086400,"time":"1951-09-30T08:00:00+0000","offset":-25200,"isdst":false,"abbr":"MST"},{"ts":-557938800,"time":"1952-04-27T09:00:00+0000","offset":-21600,"isdst":true,"abbr":"MDT"},{"ts":-544636800,"time":"1952-09-28T08:00:00+0000","offset":-25200,"isdst":false,"abbr":"MST"},{"ts":-526489200,"time":"1953-04-26T09:00:00+0000","offset":-21600,"isdst":true,"abbr":"MDT"},{"ts":-513187200,"time":"1953-09-27T08:00:00+0000","offset":-25200,"isdst":false,"abbr":"MST"},{"ts":-495039600,"time":"1954-04-25T09:00:00+0000","offset":-21600,"isdst":true,"abbr":"MDT"},{"ts":-481737600,"time":"1954-09-26T08:00:00+0000","offset":-25200,"isdst":false,"abbr":"MST"},{"ts":-463590000,"time":"1955-04-24T09:00:00+0000","offset":-21600,"isdst":true,"abbr":"MDT"},{"ts":-450288000,"time":"1955-09-25T08:00:00+0000","offset":-25200,"isdst":false,"abbr":"MST"},{"ts":-431535600,"time":"1956-04-29T09:00:00+0000","offset":-21600,"isdst":true,"abbr":"MDT"},{"ts":-418233600,"time":"1956-09-30T08:00:00+0000","offset":-25200,"isdst":false,"abbr":"MST"},{"ts":-400086000,"time":"1957-04-28T09:00:00+0000","offset":-21600,"isdst":true,"abbr":"MDT"},{"ts":-386784000,"time":"1957-09-29T08:00:00+0000","offset":-25200,"isdst":false,"abbr":"MST"},{"ts":-337186800,"time":"1959-04-26T09:00:00+0000","offset":-21600,"isdst":true,"abbr":"MDT"},{"ts":-321465600,"time":"1959-10-25T08:00:00+0000","offset":-25200,"isdst":false,"abbr":"MST"},{"ts":-305737200,"time":"1960-04-24T09:00:00+0000","offset":-21600,"isdst":false,"abbr":"CST"}],"location":{"country_code":"CA","latitude":50.4,"longitude":-104.65001,"comments":"CST - SK (most areas)"}},"offset":-21600,"timestamp":1514786400},"type":"Phone","medium":"Incoming","contactName":"","contactEmail":"","contactPhone":"","property":null,"category":"Multi-purpose","description":"Its a bin"}]', $client->getResponse()->getContent());
    }

    /**
     * Story 11c
     * test that the query to search on is too long
     */
    public function testQueryTooLong()
    {
        // create a client so we can view the page
        $client = static::createClient();

        // go to the page and search for a string that is 501 characters long
        $client->request('GET', '/Communication/jsonsearch/BobJonesBobJonesBobJonesBobJonesBobJonesBobJonesBobJonesBobJonesBobJonesBobJonesBobJonesBobJonesBobJBobJonesBobJonesBobJonesBobJonesBobJonesBobJonesBobJonesBobJonesBobJonesBobJonesBobJonesBobJonesBobJBobJonesBobJonesBobJonesBobJonesBobJonesBobJonesBobJonesBobJonesBobJonesBobJonesBobJonesBobJonesBobJBobJonesBobJonesBobJonesBobJonesBobJonesBobJonesBobJonesBobJonesBobJonesBobJonesBobJonesBobJonesBobJBobJonesBobJonesBobJonesBobJonesBobJonesBobJonesBobJonesBobJonesBobJonesBobJonesBobJonesBobJonesBobJo');

        // assert that what we expect is actually returned
        $this->assertContains('[]', $client->getResponse()->getContent());
    }

    /**
     * Story 11c
     * test that the query to search on is empty
     */
    public function testQueryEmpty()
    {
        // create a client so we can view the page
        $client = static::createClient();

        // go to the page and search for a string that is empty
        $client->request('GET', '/Communication/jsonsearch/');

        // assert that what we expect is actually returned
        $this->assertContains('[]', $client->getResponse()->getContent());
    }

    protected function tearDown()
    {
        parent::tearDown();

        // Delete all the things that were just inserted. Or literally everything.
        $client = static::createClient(array(), array('PHP_AUTH_USER' => 'admin', 'PHP_AUTH_PW'   => 'password'));
        $em = $client->getContainer()->get('doctrine.orm.entity_manager');
        $stmt = $em->getConnection()->prepare('DELETE FROM Communication');
        $stmt->execute();
        $stmt = $em->getConnection()->prepare('DELETE FROM Property');
        $stmt->execute();
        $stmt = $em->getConnection()->prepare('DELETE FROM Address');
        $stmt->execute();
        $stmt = $em->getConnection()->prepare('DELETE FROM User');
        $stmt->execute();
        $em->close();

    }
}