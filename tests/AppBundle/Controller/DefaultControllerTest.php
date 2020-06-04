<?php

namespace Tests\AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class DefaultControllerTest extends WebTestCase
{
    public function testIndex()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        //$this->assertContains('Welcome to Symfony', $crawler->filter('#container h1')->text());
    }

    public function testAssociations()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/acteurs');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

    public function testEvents()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/actions');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

    public function testAssociation()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/acteur/1/Association-name');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

    public function testEventIcs()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/action/1/ics');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

    public function testEvent()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/action/1/Event-name');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

    public function testArchives()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/archives');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

    public function testArchivesPage()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/archives/2019-10');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

    public function testSearch()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/recherche');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

    public function testLegalTerms()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/mentions-legales');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }
}
