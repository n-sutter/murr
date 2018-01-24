<?php


namespace Tests\AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use AppBundle\Entity\Container;

/**
 * ContainerControllerTest short summary.
 *
 * ContainerControllerTest description.
 *
 * @version 1.0
 * @author cst201
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

    }

    public function testAddActionSuccess()
    {
        //$container = new Container();
        //$container->setContainerSerial('testSerialController');
        //$repo = $this->em->getRepository(Container::class);
        //$repo->remove($container);

        //Create a client to go through the web page
        $client = static::createClient();
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
        $client = static::createClient();
        $em = $client->getContainer()->get('doctrine.orm.entity_manager');
        $repo = $em->getRepository(Container::class);

        //save the container
        $repo->save($container);

        //Create a client to go through the web page
        $client = static::createClient();
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
        $client = static::createClient();
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
        $client = static::createClient();

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

    protected function tearDown()
    {
        parent::tearDown();

        // Delete all the things that were just inserted. Or literally everything.
        $client = static::createClient();
        $em = $client->getContainer()->get('doctrine.orm.entity_manager');
        $stmt = $em->getConnection()->prepare('DELETE FROM Container');
        $stmt->execute();
        $em->close();

    }

}