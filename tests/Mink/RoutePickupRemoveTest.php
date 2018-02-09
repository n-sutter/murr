<?php

require_once 'vendor/autoload.php';
use DMore\ChromeDriver\ChromeDriver;
use Behat\Mink\Session;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use AppBundle\Entity\Route;
use AppBundle\Entity\Container;
use AppBundle\Entity\RoutePickup;
use AppBundle\DataFixtures\ORM\LoadUserData;
/**
 * This test uses mink for browser based front-end testing of the javascript used in story 22c
 */
class RoutePickupRemoveTest extends WebTestCase
{
    private $driver;
    private $session;
    private $em;

    protected function setUp()
    {

        self::bootKernel();
        //get a reference to the entity manager
        $this->em = static::$kernel->getContainer()
            ->get('doctrine')
            ->getManager();

        //Wipe database before beginning because tests seem to run into errors
        $stmt = $this->em->getConnection()->prepare('DELETE FROM RoutePickup');
        $stmt->execute();
        $stmt = $this->em->getConnection()->prepare('DELETE FROM Route');
        $stmt->execute();
        $stmt = $this->em->getConnection()->prepare('DELETE FROM Container');
        $stmt->execute();
        $stmt = $this->em->getConnection()->prepare('DELETE FROM User');
        $stmt->execute();


        // Load the admin user into the database so they can log in
        $encoder = static::$kernel->getContainer()->get('security.password_encoder');

        $userLoader = new LoadUserData($encoder);
        $userLoader->load($this->em);

        // Create a driver
        $this->driver = new ChromeDriver("http://localhost:9222",null, "localhost:8000");
        // Create a session and pass it the driver
        $this->session = new Session($this->driver);

        //Log the user in
        // Start the session
        $this->session->start();

        // go to the login page
        $this->session->visit('http://localhost:8000/login');
        // Get the current page
        $page = $this->session->getPage();
        // Fill out the login form
        $page->findById("username")->setValue("admin");
        $page->findById("password")->setValue("password");
        // Submit the form
        $page->find('named', array('id_or_name', "login"))->submit();


    }

    /**
     * Story 22c
     * Tests that when the delete button is clicked on a routePickup, it changes to prompt if the user is sure they want to delete
     */
    public function testDeleteRoutePickupButtonAccept(){
        //create a route to add the pickup to
        $route = new Route();
        $route->setRouteId(1001);

        //Get the repository for the route
        $repository = $this->em->getRepository(Route::class);
        //Call insert on the repository for the route
        $repository->save($route);

        //specify a container for routePickup
        $container = new Container();
        $container->setContainerSerial("X11111");
        $container->setType("Bin");
        $container->setSize("6");
        $container->setStatus("Active");

        //save the container
        $repo = $this->em->getRepository(Container::class);
        $repo->save($container);

        //Create a route pickup for this container
        $rp = new RoutePickup();
        $rp->setPickupOrder(1);
        $rp->setRoute($route);
        $rp->setContainer($container);

        //save the route pickup
        $repo = $this->em->getRepository(RoutePickup::class);
        $repo->save($rp);


        //Now that data exists, go to the page
        //start up a new session
        $this->session->visit('http://localhost:8000/route/1');
        // Get the page
        $page = $this->session->getPage();

        //Find the button with the ID of rmb1 (remove button 1)
        $rmButton = $page->find("css","#rmb1");
        $rmButton->click();

        //Find the form with the ID of rmf1 (remove form 1)
        $rmForm = $page->find("css","#rmf1");

        $this->assertContains("Are you sure?", $rmForm->getHtml()); //check that the form says "Are you sure?"

        //find the button with the ID of rmba1 (Remove button accept 1)
        $acceptBtn = $page->find('css','#rmba1');
        $acceptBtn->click();

        //get the list of containers
        $list = $page->find("css","table");

        //check that the list is now missing the container
        $this->assertNotContains("X11111", $list->getHtml());
    }

    /**
     * Story 22c
     * Tests that when the delete button is clicked on a routePickup, it changes to prompt if the user is sure they want to delete
     */
    public function testDeleteRoutePickupButtonDecline(){
        //create a route to add the pickup to
        $route = new Route();
        $route->setRouteId(1001);

        //Get the repository for the route
        $repository = $this->em->getRepository(Route::class);
        //Call insert on the repository for the route
        $repository->save($route);

        //specify a container for routePickup
        $container = new Container();
        $container->setContainerSerial("X11111");
        $container->setType("Bin");
        $container->setSize("6");
        $container->setStatus("Active");

        //save the container
        $repo = $this->em->getRepository(Container::class);
        $repo->save($container);

        //Create a route pickup for this container
        $rp = new RoutePickup();
        $rp->setPickupOrder(1);
        $rp->setRoute($route);
        $rp->setContainer($container);

        //save the route pickup
        $repo = $this->em->getRepository(RoutePickup::class);
        $repo->save($rp);


        //Now that data exists, go to the page
        //start up a new session
        $this->session->visit('http://localhost:8000/route/1');
        // Get the page
        $page = $this->session->getPage();

        //Find the button with the ID of rmb1 (remove button 1)
        $rmButton = $page->find("css","#rmb1");
        $rmButton->click();

        //Find the form with the ID of rmf1 (remove form 1)
        $rmForm = $page->find("css","#rmf1");

        $this->assertContains("Are you sure?", $rmForm->getHtml()); //check that the form says "Are you sure?"

        //find the button with the ID of rmba1 (Remove button cancel 1)
        $acceptBtn = $page->find('css','#rmbc1');
        $acceptBtn->click();

        //check that the message is gone
        $this->assertNotContains("Are you sure?", $rmForm->getHtml());

        //get the list of containers
        $list = $page->find("css","table");
        //check that the list still has the container
        $this->assertContains("X11111", $list->getHtml());
    }

    protected function tearDown()
    {
        parent::tearDown();
        // After the test has been run, make sure to restart the session so you don't run into problems
        $this->session->stop();

        //Now wipe the database
        $em = $this->em;
        $stmt = $em->getConnection()->prepare('DELETE FROM RoutePickup');
        $stmt->execute();
        $stmt = $em->getConnection()->prepare('DELETE FROM Route');
        $stmt->execute();
        $stmt = $em->getConnection()->prepare('DELETE FROM Container');
        $stmt->execute();
        $stmt = $em->getConnection()->prepare('DELETE FROM User');
        $stmt->execute();
    }
}