<?php
namespace AppBundle\DataFixtures\ORM;

use AppBundle\Entity\Container;
use AppBundle\Entity\Property;
use AppBundle\Entity\Address;
use AppBundle\Entity\Structure;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class LoadContainerData implements FixtureInterface
{
    // private attribute that is the container to add
    private $container;

    /**
     * A constructor that sets the private attribute the container passed in
     * @param mixed $container the container entity passed in
     */
    public function __construct($container = null)
    {
        // set the container attribute
        $this->container = $container;
    }

    /**
     * A fixture method to create Containers in the database for testing
     * @param ObjectManager $obMan the object manager
     */
    public function load(ObjectManager $obMan)
    {
        if(is_null($this->container))
        {
            //Address data
            $address = (new Address())
                ->setStreetAddress("Test ST")
                ->setPostalCode('T3S 3TS')
                ->setCity('Saskatoon')
                ->setProvince('Saskatchetest')
                ->setCountry('Testnada');

            $addressFixtureLoader = new LoadAddressData($address);

            $addressFixtureLoader->load($obMan);

            // Property data
            $property = (new Property())
                ->setSiteId((2363566))
                ->setPropertyName("Cosmo")
                ->setPropertyType("Townhouse Condo")
                ->setPropertyStatus("Active")
                ->setStructureId(54586)
                ->setNumUnits(5)
                ->setNeighbourhoodName("Sutherland")
                ->setNeighbourhoodId("O48")
                ->setAddress($address);

            $PropertyFixtureLoader = new LoadPropertyData($property);

            $PropertyFixtureLoader->load($obMan);

            // Structure data
            $structure = (new Structure())
                ->setProperty(143546)
                ->setDescription("Hello World");

            $structureFixtureLoader = new LoadStructureData($structure);

            $structureFixtureLoader->load($obMan);

            //custom, independant autoloaded fixtures
            $this->container = (new Container())
                ->setFrequency("weekly")
                ->setContainerSerial("123457")
                ->setLocationDesc("South-west side")
                ->setLon("87")
                ->setLat("88")
                ->setType("Cart")
                ->setSize("6 yd")
                ->setAugmentation("Wheels")
                ->setStatus("Active")
                ->setReasonForStatus("Everything normal")
                ->setProperty($property)
                ->setStructure($structure);

            $obMan->persist($this->container);
            $obMan->flush();
        }
        else
        {
            // persist the container object set in the constructor to the database
            $obMan->persist($this->container);
            // flush the database connection
            $obMan->flush();
        }
    }
}