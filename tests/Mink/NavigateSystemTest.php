<?php


use DMore\ChromeDriver\ChromeDriver;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Tests\AppBundle\DatabasePrimer;
use Behat\Mink\Session;

use AppBundle\DataFixtures\ORM\LoadUserData;
use AppBundle\DataFixtures\ORM\LoadCommunicationData;
use AppBundle\DataFixtures\ORM\LoadContactData;
use AppBundle\DataFixtures\ORM\LoadContainerData;
use AppBundle\DataFixtures\ORM\LoadPropertyData;
use AppBundle\DataFixtures\ORM\LoadRouteData;
use AppBundle\DataFixtures\ORM\LoadTruckData;
/**
 * NavigateSystemTest short summary.
 *
 * NavigateSystemTest description.
 *
 * @version 1.0
 * @author cst244
 */
class NavigateSystemTest extends WebTestCase
{
    private $driver;
    private $session;
    private $em;

    public static function setUpBeforeClass()
    {
        self::bootKernel();
        DatabasePrimer::prime(self::$kernel);

        $encoder = static::$kernel->getContainer()->get('security.password_encoder');

        //Load ALL fixtures (so that there is stuff to browse to)
        $userLoader = new LoadUserData($encoder);
        $userLoader->load(DatabasePrimer::$entityManager);

        $communicationLoader = new LoadCommunicationData();
        $communicationLoader->load(DatabasePrimer::$entityManager);

        $contactLoader = new LoadContactData();
        $contactLoader->load(DatabasePrimer::$entityManager);

        $containerLoader = new LoadContainerData();
        $containerLoader->load(DatabasePrimer::$entityManager);

        $propertyLoader = new LoadPropertyData();
        $propertyLoader->load(DatabasePrimer::$entityManager);

        $routeLoader = new LoadRouteData();
        $routeLoader->load(DatabasePrimer::$entityManager);

        $truckLoader = new LoadTruckData();
        $truckLoader->load(DatabasePrimer::$entityManager);
    }

    protected function setUp()
    {
        // get the entity manager
        self::bootKernel();
        $this->em = static::$kernel->getContainer()
            ->get('doctrine')
            ->getManager();

        // Create a driver
        $this->driver = new ChromeDriver("http://localhost:9222",null, "localhost:8000");
        // Create a session and pass it the driver
        $this->session = new Session($this->driver);

        //Log the user in
        // Start the session
        $this->session->start();

        // go to the login page
        $this->session->visit('http://localhost:8000/app_test.php/login');
        // Get the current page
        $page = $this->session->getPage();
        // Fill out the login form
        $page->findById("username")->setValue("admin");
        $page->findById("password")->setValue("password");
        // Submit the form
        $page->find('named', array('id_or_name', "login"))->submit();
        // Wait for the page to load before trying to browse elsewhere
        $this->session->wait(10000, "document.readyState === 'complete'");
    }

    protected function tearDown()
    {
        // After the test has been run, make sure to stop the session so you don't run into problems
        $this->session->stop();
    }


    //NAVIGATION TESTS BELOW

    /**
     * Story 23a
     * Tests that you can browse to the various communications pages (minus view, tested in search frontend)
     */
    public function testBrowseCommunications()
    {
        //start up a new session, starting at the home page
        $this->session->visit('http://localhost:8000/app_test.php/');
        // Get the page
        $page = $this->session->getPage();

        //click on the communications button
        $page->find("css","#communications")->click();

        //check that the header is the communication search page
        assertContains("Communication Search", $page->find("css","h2:first-child")->getHtml());
        assertContains("/communication/search", $this->session->getCurrentUrl());

        //Click on the add communication button
        $page->find("css","#newCommunication")->click();

        //check that the header is there and we're on the new communication page
        assertContains("Add Communication" , $page->find("css","h2:first-child")->getHtml());
        assertContains("/communication/new", $this->session->getCurrentUrl());

        //Attempt to click on the cancel button
        $page->find("css","#cancelButton")->click();

        //check that we're back on the search page
        assertContains("Communication Search", $page->find("css","h2:first-child")->getHtml());
        assertContains("/communication/search", $this->session->getCurrentUrl());
    }

    /**
     * Story 23a
     * Tests that when you are on a communication view page that you can go back to the communication search page
     */
    public function testCommunicationsBack()
    {
        //start up a new session, go to the communication view page
        $this->session->visit('http://localhost:8000/app_test.php/communication/7');
        // Get the page
        $page = $this->session->getPage();

        //click on the back button
        $page->find("css","#backButton")->click();

        //check that we're back on the search page
        assertContains("Communication Search", $page->find("css","h2:first-child")->getHtml());
        assertContains("/communication/search", $this->session->getCurrentUrl());
    }

    /**
     * Story 23a
     * Tests that you can browse to the contact search and add pages (but not view, because that is tested in the frontend search)
     */
    public function testBrowseContacts()
    {
        //start up a new session, starting at the home page
        $this->session->visit('http://localhost:8000/app_test.php/');
        // Get the page
        $page = $this->session->getPage();

        //click on the contacts button
        $page->find("css","#contacts")->click();

        //check that the header is the contact search page
        assertContains("Contact Search", $page->find("css","h2:first-child")->getHtml());
        assertContains("/contact/search", $this->session->getCurrentUrl());

        //Click on the add contact button
        $page->find("css","#newContact")->click();

        //check that the header is there and we're on the new contact page
        assertContains("Add Contact" , $page->find("css","h2:first-child")->getHtml());
        assertContains("/contact/new", $this->session->getCurrentUrl());

        //Attempt to click on the cancel button
        $page->find("css","#cancelButton")->click();

        //check that we're back on the search page
        assertContains("Contact Search", $page->find("css","h2:first-child")->getHtml());
        assertContains("/contact/search", $this->session->getCurrentUrl());
    }

    /**
     * Story 23a
     * Tests that when you are on a contact view page that you can go back to the contact search page
     */
    public function testContactsBack()
    {
        //start up a new session, go to the communication view page
        $this->session->visit('http://localhost:8000/app_test.php/contact/1');
        // Get the page
        $page = $this->session->getPage();

        //click on the back button
        $page->find("css","#backButton")->click();

        //check that we're back on the search page
        assertContains("Contact Search", $page->find("css","h2:first-child")->getHtml());
        assertContains("/contact/search", $this->session->getCurrentUrl());
    }

    /**
     * Story 23a
     * Tests that you can browse the container search and add pages (but not view, it is tested in the container search story)
     */
    public function testBrowseContainers()
    {
        //start up a new session, starting at the home page
        $this->session->visit('http://localhost:8000/app_test.php/');
        // Get the page
        $page = $this->session->getPage();

        //click on the containers button
        $page->find("css","#containers")->click();

        //check that the header is the container search page
        assertContains("Container Search", $page->find("css","h2:first-child")->getHtml());
        assertContains("/container/search", $this->session->getCurrentUrl());

        //Click on the add container button
        $page->find("css","#newContainer")->click();

        //check that the header is there and we're on the new container page
        assertContains("Add Container" , $page->find("css","h2:first-child")->getHtml());
        assertContains("/container/new", $this->session->getCurrentUrl());

        //Attempt to click on the cancel button
        $page->find("css","#cancelButton")->click();

        //check that we're back on the search page
        assertContains("Container Search", $page->find("css","h2:first-child")->getHtml());
        assertContains("/container/search", $this->session->getCurrentUrl());
    }

    /**
     * Story 23a
     * Tests that when you're on the container view page you can get back to the container search page
     */
    public function testContainersBack()
    {
        //start up a new session, go to the communication view page
        $this->session->visit('http://localhost:8000/app_test.php/container/1');
        // Get the page
        $page = $this->session->getPage();

        //click on the back button
        $page->find("css","#backButton")->click();

        //check that we're back on the search page
        assertContains("Container Search", $page->find("css","h2:first-child")->getHtml());
        assertContains("/container/search", $this->session->getCurrentUrl());
    }

    /**
     * Story 23a
     * Tests that you can browse to the property search and add pages (Not view, tested in the frontend search)
     */
    public function testBrowseProperties()
    {
        //start up a new session, starting at the home page
        $this->session->visit('http://localhost:8000/app_test.php/');
        // Get the page
        $page = $this->session->getPage();

        //click on the containers button
        $page->find("css","#properties")->click();

        //check that the header is the property search page
        assertContains("Property Search", $page->find("css","h2:first-child")->getHtml());
        assertContains("/property/search", $this->session->getCurrentUrl());

        //Click on the add property button
        $page->find("css","#newProperty")->click();

        //check that the header is there and we're on the new property page
        assertContains("Add Property" , $page->find("css","h2:first-child")->getHtml());
        assertContains("/property/new", $this->session->getCurrentUrl());

        //Attempt to click on the cancel button
        $page->find("css","#cancelButton")->click();

        //check that we're back on the search page
        assertContains("Property Search", $page->find("css","h2:first-child")->getHtml());
        assertContains("/property/search", $this->session->getCurrentUrl());
    }

    /**
     * Story 23a
     * Tests that when you're on the container view page you can get back to the container search page
     */
    public function testPropertiesBack()
    {
        //start up a new session, go to the communication view page
        $this->session->visit('http://localhost:8000/app_test.php/property/1');
        // Get the page
        $page = $this->session->getPage();

        //click on the back button
        $page->find("css","#backButton")->click();

        //check that we're back on the search page
        assertContains("Property Search", $page->find("css","h2:first-child")->getHtml());
        assertContains("/property/search", $this->session->getCurrentUrl());
    }
}