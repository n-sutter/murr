<?php

namespace Tests\AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use AppBundle\Entity\Communication;
//use Doctrine\Common\Persistence\ObjectRepository;

class CommunicationControllerTest extends WebTestCase
{
    public function testFormSuccess()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/communication/new');

        $form = $crawler->selectButton('Add')->form();

        //set form values
        $form['communication[date][year]'] = "2017";
        $form['communication[date][month]'] = "10";
        $form['communication[date][day]'] = "5";
        $form['communication[type]']="phone";
        $form['communication[medium]']="incoming";
        $form['communication[contact]']=1; //contact id
        $form['communication[property]']=1; //property id
        $form['communication[category]']="container";
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
        $this->assertEmpty($form['communication[contact]']->getValue());
        $this->assertEmpty($form['communication[property]']->getValue());
        $this->assertEmpty($form['communication[category]']->getValue());
        $this->assertEmpty($form['communication[description]']->getValue());
    }

    public function testFutureDate()
    {

        $client = static::createClient();

        $crawler = $client->request('GET', '/communication/new');

        $form = $crawler->selectButton('Add')->form();

        //set form values
        $form['communication[date][year]'] = "2019";
        $form['communication[date][month]'] = "10";
        $form['communication[date][day]'] = "5";

        $crawler = $client->submit($form);

        $this->assertContains("Please select a current or past date",$client->getResponse()->getContent());
    }

    public function testEmptyDate()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/communication/new');

        $form = $crawler->selectButton('Add')->form();

        //set form values
        //do not set date by removing the form fields
        $form->remove('communication[date][year]');
        $form->remove('communication[date][month]');
        $form->remove('communication[date][day]');


        $crawler = $client->submit($form);

        $this->assertContains("Please select a date",$client->getResponse()->getContent());
    }

    public function testNonExistantDate()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/communication/new');

        $form = $crawler->selectButton('Add')->form();

        //set form values
        $form['communication[date][year]'] = "2017";
        $form['communication[date][month]'] = "2";
        $form['communication[date][day]'] = "30";

        $crawler = $client->submit($form);

        $this->assertContains("Please select a valid date",$client->getResponse()->getContent());
    }

    public function testNoType()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/communication/new');

        $form = $crawler->selectButton('Add')->form();

        //set form values
        $form['communication[type]']=0;

        $crawler = $client->submit($form);

        $this->assertContains("Please select a type of communication",$client->getResponse()->getContent());
    }

    public function testNoMedium()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/communication/new');

        $form = $crawler->selectButton('Add')->form();

        //set form values
        //do not set medium
        //$form['communication[medium]']=0;

        $crawler = $client->submit($form);

        $this->assertContains("Please select incoming or outgoing",$client->getResponse()->getContent());
    }

    public function testBlankContact()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/communication/new');

        $form = $crawler->selectButton('Add')->form();

        //set form values
        $form['communication[contact]']=0; //blank contact ID

        $crawler = $client->submit($form);

        $this->assertContains("Please enter a contact",$client->getResponse()->getContent());
    }

    public function testResidentContact()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/communication/new');

        $form = $crawler->selectButton('Add')->form();

        //set form values
        $form['communication[date][year]'] = "2017";
        $form['communication[date][month]'] = "10";
        $form['communication[date][day]'] = "5";
        $form['communication[type]']="phone";
        $form['communication[medium]']="incoming";
        $form['communication[contact]']=-1; //identifier for a resident, will not be stored
        $form['communication[property]']=1;
        $form['communication[category]']="container";
        $form["communication[description]"]="Container has graffiti and needs to be cleaned. Action request made";

        $crawler = $client->submit($form);

        $this->assertContains("Communication added successfully",$client->getResponse()->getContent());
    }

    public function testBlankProperty()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/communication/new');

        $form = $crawler->selectButton('Add')->form();

        //set form values
        $form['communication[property]']=0; //blank property ID

        $crawler = $client->submit($form);

        $this->assertContains("Please select a property",$client->getResponse()->getContent());
    }

    public function testMultiOrNAProperty()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/communication/new');

        $form = $crawler->selectButton('Add')->form();

        //set form values
        //set form values
        $form['communication[date][year]'] = "2017";
        $form['communication[date][month]'] = "10";
        $form['communication[date][day]'] = "5";
        $form['communication[type]']="phone";
        $form['communication[medium]']="incoming";
        $form['communication[contact]']=1; //contact id
        $form['communication[property]']=-1; //multi-property or N/A property identifier
        $form['communication[category]']="container";
        $form["communication[description]"]="Container has graffiti and needs to be cleaned. Action request made";

        $crawler = $client->submit($form);

        $this->assertContains("Communication added successfully",$client->getResponse()->getContent());
    }

    public function testBlankCategory()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/communication/new');

        $form = $crawler->selectButton('Add')->form();

        //set form values
        $form['communication[category]']=0; //blank category value


        $crawler = $client->submit($form);

        $this->assertContains("Please select a category",$client->getResponse()->getContent());
    }

    public function testBlankDescription()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/communication/new');

        $form = $crawler->selectButton('Add')->form();

        //set form values
        $form['communication[description]']=""; //blank description


        $crawler = $client->submit($form);

        $this->assertContains("Please provide a brief description of the communication",$client->getResponse()->getContent());
    }

    public function testShortDescription()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/communication/new');

        $form = $crawler->selectButton('Add')->form();

        //set form values
        $form['communication[description]']="Talked"; //description too short


        $crawler = $client->submit($form);

        $this->assertContains("Please provide a description of 50 characters or more",$client->getResponse()->getContent());
    }

    public function testLongDescription()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/communication/new');

        $form = $crawler->selectButton('Add')->form();


        //set form values
        $form['communication[description]']=str_repeat('a',2001);//generate a string that is too long


        $crawler = $client->submit($form);

        $this->assertContains("Please keep the description under 2000 characters",$client->getResponse()->getContent());
    }
}