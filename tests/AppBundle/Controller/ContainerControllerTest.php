<?php


namespace Tests\AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use AppBundle\Entity\Container;
use AppBundle\DataFixtures\ORM\LoadUserData;

/**
 * ContainerControllerTest short summary.
 *
 * ContainerControllerTest description.
 *
 * @version 1.0
 * @author Team MURR
 */
class ContainerControllerTest extends WebTestCase
{
    private $em;

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

    public function testAddActionSuccess()
    {
        //$container = new Container();
        //$container->setContainerSerial('testSerialController');
        //$repo = $this->em->getRepository(Container::class);
        //$repo->remove($container);

        //Create a client to go through the web page
        $client = static::createClient(array(), array('PHP_AUTH_USER' => 'admin', 'PHP_AUTH_PW'   => 'password'));
        //Reques the contact add page
        $crawler = $client->request('GET','/container/new');
        //select the form and add values to it.
        $form = $crawler->selectButton('Create')->form();
        $form['appbundle_container[containerSerial]'] = 'testSerialController' + time();
        $form['appbundle_container[frequency]'] = 'Weekly';
        $form['appbundle_container[locationDesc]'] = 'Near backdoor';
        $form['appbundle_container[type]'] = 'Bin';
        $form['appbundle_container[size]'] = '6';
        $form['appbundle_container[long]'] = '10';
        $form['appbundle_container[lat]'] = '25';
        $form['appbundle_container[status]'] = 'Active';
        $form['appbundle_container[reasonForStatus]'] = 'Test reason';
        $form['appbundle_container[augmentation]'] = 'Wheels installed';

        //crawler submits the form
        $crawler = $client->submit($form);
        //check for the success message
        //$this->assertGreaterThan(
        //    0,
        //    $crawler->filter('html:contains("Contact has been successfully added")')->count()
        //    );
        $this->assertContains('Redirecting to /container', $client->getResponse()->getContent());
    }

    /**
     * 12c - tests that the user can navigate to the container edit page
     */
    public function testEditRedirection()
    {
        //create a container to insert into the database
        $container = new Container();
        $container->setContainerSerial('123456')
            ->setType("Bin")
            ->setStatus("Active")
            ->setFrequency("Weekly")
            ->setSize("6 yd");

        //get entity manager and repo
        $client = static::createClient(array(), array('PHP_AUTH_USER' => 'admin', 'PHP_AUTH_PW'   => 'password'));
        $em = $client->getContainer()->get('doctrine.orm.entity_manager');
        $repo = $em->getRepository(Container::class);

        //save the container
        $repo->save($container);

        //Create a client to go through the web page
        $client = static::createClient(array(), array('PHP_AUTH_USER' => 'admin', 'PHP_AUTH_PW'   => 'password'));
        //Request the contact edit page
        $crawler = $client->request('GET','/container/');
        // Select the first button on the page that views the details for a contact
        $link = $crawler->filter('a[href="/container/1/edit"]')->eq(0)->link();
        // Go there - should be viewing a specific contact after this
        $crawler = $client->click($link);

        $this->assertContains('Edit Container 123456', $client->getResponse()->getContent());


    }

    /**
     * 12c - test that the page loads the view page after the submit button in clicked
     */
    public function testEditSubmitRedirect()
    {
        //create a container to insert into the database
        $container = new Container();
        $container->setContainerSerial('123456')
            ->setType("Bin")
            ->setStatus("Active")
            ->setFrequency("Weekly")
            ->setSize("6 yd");

        //get entity manager and repo
        $client = static::createClient(array(), array('PHP_AUTH_USER' => 'admin', 'PHP_AUTH_PW'   => 'password'));
        $em = $client->getContainer()->get('doctrine.orm.entity_manager');
        $repo = $em->getRepository(Container::class);

        //save the container
        $repo->save($container);


        //Request the contact edit page
        $crawler = $client->request('GET','/container/1/edit');
        //$link = $crawler->filter('a:contains("Edit")')->eq(0)->link();
        // Go there - should be viewing a specific contact after this
        //$crawler = $client->click($link);

        $form = $crawler->selectButton("Save")->form();

        $crawler = $client->submit($form);
        $crawler = $client->request('GET', '/container/1');

        $this->assertGreaterThan('Container', $client->getResponse()->getContent());
    }

    /**
     * 12c - tests that the fields to add property and structure appear in the edit page and not
     *  the add page
     */
    public function testPropertyAndStructureAreInEditAndNotAdd()
    {
        //create client
        $client = static::createClient(array(), array('PHP_AUTH_USER' => 'admin', 'PHP_AUTH_PW'   => 'password'));

        //request add page first
        $crawler = $client->request('GET','/container/new');
        //test that property and structure do not appear on page
        $this->assertEquals(0, $crawler->filter('html:contains("Property")')->count());
        $this->assertEquals(0, $crawler->filter('html:contains("Structure")')->count());

        //create a container to insert into the database
        $container = new Container();
        $container->setContainerSerial('123456')
            ->setType("Bin")
            ->setStatus("Active")
            ->setFrequency("Weekly")
            ->setSize("6 yd");

        //get entity manager and repo
        $em = $client->getContainer()->get('doctrine.orm.entity_manager');
        $repo = $em->getRepository(Container::class);
        $repo->save($container);

        //request edit page
        $crawler = $client->request('GET','/container/1/edit');
        //ensure property and structure do appear on page
        $this->assertGreaterThan(0, $crawler->filter('html:contains("Property")')->count());
        $this->assertGreaterThan(0, $crawler->filter('html:contains("Structure")')->count());
    }

    /**
     * Story 12b
     * Tests that you are able to view a container successfully with the proper information
     */
    public function testViewContainerSuccess(){
        //create a container to be inserted into the database
        $contanier = new Container();
        $contanier->setFrequency('Weekly')
            ->setContainerSerial('123456')
            ->setLocationDesc("South side of building")
            ->setLong(87)
            ->setLat(88)
            ->setType("Cart")
            ->setSize("6 yd")
            ->setAugmentation("Wheels")
            ->setStatus("Active")
            ->setReasonForStatus("Everything normal");

        //create client
        $client = static::createClient(array(), array('PHP_AUTH_USER' => 'admin', 'PHP_AUTH_PW'   => 'password'));

        //Get the entity manager and repo for containers
        $em = $client->getContainer()->get('doctrine.orm.entity_manager');
        $repo = $em->getRepository(Container::class);

        //save the container
        $id = $repo->save($contanier);

        //Go to the containers page
        $crawler = $client->request('GET',"/container/$id");

        //get the content to check
        $content = $client->getResponse()->getContent();

        //assert that all the assigned values are on the page
        $this->assertContains('123456',$content);
        $this->assertContains('Weekly',$content);
        $this->assertContains('South side of building',$content);
        $this->assertContains('87',$content);
        $this->assertContains('88',$content);
        $this->assertContains('Cart',$content);
        $this->assertContains('6 yd',$content);
        $this->assertContains('Wheels',$content);
        $this->assertContains('Active',$content);
        $this->assertContains('Everything normal',$content);

        //Check that the valid labels are on the page
        //check that the field labels are not on the page
        $this->assertContains('Id:',$content);
        $this->assertContains('Frequency:',$content);
        $this->assertContains('Container Serial:',$content);
        $this->assertContains('Location Description:',$content);
        $this->assertContains('Longitude:',$content);
        $this->assertContains('Latitude:',$content);
        $this->assertContains('Type:',$content);
        $this->assertContains('Size:',$content);
        $this->assertContains('Augmentation:',$content);
        $this->assertContains('Status:',$content);
        $this->assertContains('Reason for status:',$content);
        $this->assertContains('Property:',$content);
        $this->assertContains('Structure:',$content);
    }

    /**
     * Story 12b
     * Tests that if you go to an invalid container that an error message appears
     */
    public function testViewContainerFailure(){
        //create client
        $client = static::createClient(array(), array('PHP_AUTH_USER' => 'admin', 'PHP_AUTH_PW'   => 'password'));
        $client->followRedirects(true);

        $crawler = $client->request("GET","/container/NotAnId");

        //get the content to check
        $content = $client->getResponse()->getContent();

        //check that the error message is on the page
        $this->assertContains("Container does not exist",$content);

        //check that the field labels are not on the page
        $this->assertNotContains('Id:',$content);
        $this->assertNotContains('Frequency:',$content);
        $this->assertNotContains('Container Serial:',$content);
        $this->assertNotContains('Location Description:',$content);
        $this->assertNotContains('Longitude:',$content);
        $this->assertNotContains('Latitude:',$content);
        $this->assertNotContains('Type:',$content);
        $this->assertNotContains('Size:',$content);
        $this->assertNotContains('Augmentation:',$content);
        $this->assertNotContains('Status:',$content);
        $this->assertNotContains('Reason for status:',$content);
        $this->assertNotContains('Property:',$content);
        $this->assertNotContains('Structure:',$content);

    }

    protected function tearDown()
    {
        parent::tearDown();

        // Delete all the things that were just inserted. Or literally everything.
        $client = static::createClient(array(), array('PHP_AUTH_USER' => 'admin', 'PHP_AUTH_PW'   => 'password'));
        $em = $client->getContainer()->get('doctrine.orm.entity_manager');
        $stmt = $em->getConnection()->prepare('DELETE FROM Container');
        $stmt->execute();
        $stmt = $em->getConnection()->prepare('DELETE FROM Address');
        $stmt->execute();
        $stmt = $em->getConnection()->prepare('DELETE FROM User');
        $stmt->execute();
        $em->close();

    }

}