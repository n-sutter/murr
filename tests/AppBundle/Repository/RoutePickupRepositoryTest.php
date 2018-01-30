<?php
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use AppBundle\Entity\RoutePickup;
use AppBundle\Entity\Route;
use AppBundle\Entity\Container;

/**
 * RoutePickupRepositoryTest short summary.
 *
 * RoutePickupRepositoryTest description.
 *
 * @version 1.0
 * @author cst244
 */
class RoutePickupRepositoryTest extends KernelTestCase
{
    /**
     * The entity manager
     * @var \Doctrine\ORM\EntityManager
     */
    private $em;

    /**
     * Just some setup stuff required by symfony for testing Repositories
     * {@inheritDoc}
     */
    protected function setUp()
    {
        self::bootKernel();

        $this->em = static::$kernel->getContainer()
            ->get('doctrine')
            ->getManager();
    }

    /**
     * Story 22b
     * Tests that a routePickup is able to be stored in the database
     */
    public function testSave(){
        //create a route to add the pickup to
        $route = new Route();
        $route->setRouteId(1001);

        //Get the repository for the route
        $repository = $this->em->getRepository(Route::class);
        //Call insert on the repository for the route
        $repository->save($route);

        //specify a container for this routePickup
        $container = new Container();
        $container->setContainerSerial("testSerialRepo");
        $container->setType("Bin");
        $container->setSize("6");
        $container->setStatus("Active");

        //save the container
        $repo = $this->em->getRepository(Container::class);
        $repo->save($container);

        //Specify a route pickup
        $routePickup = new RoutePickup();
        $routePickup->setPickupOrder(1);
        $routePickup->setContainer($container);
        $routePickup->setRoute($route);

        //Get the repository for testing
        $repository = $this->em->getRepository(RoutePickup::class);
        //Call insert on the repository and record the id of the new object
        $id = $repository->save($routePickup);
        //Assert that the id was returned
        $this->assertNotNull($id);
        //check the Route Pickup id is the same as the returned id
        $this->assertEquals($routePickup->getId(), $id);
    }
}