<?php

namespace Tests\AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class DefaultControllerTest extends WebTestCase
{

    public function testIndex()
    {
        $client = static::createClient();

        /*$crawler = */$client->request('GET', '/');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        //$this->assertContains('Welcome to Symfony', $crawler->filter('#container h1')->text());
    }

    public function testAssociations()
    {
        $client = static::createClient();

        $client->request('GET', '/acteurs');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

    public function testEvents()
    {
        $client = static::createClient();

        $client->request('GET', '/actions');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

    public function testAssociation()
    {
        $client = static::createClient();

        $client->request('GET', '/acteur/1/Association-name');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

    public function testEventIcs()
    {
        $client = static::createClient();

        $client->request('GET', '/action/1/ics');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

    public function testEvent()
    {
        $client = static::createClient();

        $client->request('GET', '/action/1/Event-name');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

    public function testArchives()
    {
        $client = static::createClient();

        $client->request('GET', '/archives');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

    public function testArchivesPage()
    {
        $client = static::createClient();

        $client->request('GET', '/archives/2019-10');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

    public function testSearch()
    {
        $client = static::createClient();

        $client->request('GET', '/recherche');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

    public function testLegalTerms()
    {
        $client = static::createClient();

        $client->request('GET', '/mentions-legales');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

    public function testMap()
    {
        $client = static::createClient();

        $client->request('GET', '/carte');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

    public function testAdminAssociation()
    {
        $client = static::createClient();

        $client->request('GET', '/admin/acteur');

        $this->assertEquals(302, $client->getResponse()->getStatusCode());
    }

    public function testAdminEvents()
    {
        $client = static::createClient();

        $client->request('GET', '/admin/action');

        $this->assertEquals(302, $client->getResponse()->getStatusCode());
    }

    public function testAdminIndex()
    {
        $client = static::createClient();

        $client->request('GET', '/admin');

        $this->assertEquals(302, $client->getResponse()->getStatusCode());
    }

    public function testAdminEvent()
    {
        $client = static::createClient();

        $client->request('GET', '/admin/action/1');

        $this->assertEquals(302, $client->getResponse()->getStatusCode());
    }

    public function testAdminEventDocument()
    {
        $client = static::createClient();

        $client->request('GET', '/admin/action/1/document');

        $this->assertEquals(302, $client->getResponse()->getStatusCode());
    }

    public function testAdminSearchPlace()
    {
        $client = static::createClient();

        $client->request('GET', '/admin/lieux/recherche');

        $this->assertEquals(302, $client->getResponse()->getStatusCode());
    }

    public function testSuperadminUsers()
    {
        $client = static::createClient();

        $client->request('GET', '/admin/superadmin/utilisateur');

        $this->assertEquals(302, $client->getResponse()->getStatusCode());
    }

    public function testSuperadminAssociations()
    {
        $client = static::createClient();

        $client->request('GET', '/admin/superadmin/acteur');

        $this->assertEquals(302, $client->getResponse()->getStatusCode());
    }

    public function testSuperadminEvents()
    {
        $client = static::createClient();

        $client->request('GET', '/admin/superadmin/action');

        $this->assertEquals(302, $client->getResponse()->getStatusCode());
    }

    public function testSuperadminEvent()
    {
        $client = static::createClient();

        $client->request('GET', '/admin/superadmin/action/1');

        $this->assertEquals(302, $client->getResponse()->getStatusCode());
    }

    public function testSuperadminPlaces()
    {
        $client = static::createClient();

        $client->request('GET', '/admin/superadmin/lieu');

        $this->assertEquals(302, $client->getResponse()->getStatusCode());
    }

    public function testSuperadminPlace()
    {
        $client = static::createClient();

        $client->request('GET', '/admin/superadmin/lieu/1');

        $this->assertEquals(302, $client->getResponse()->getStatusCode());
    }

    public function testSuperadminQuizScores()
    {
        $client = static::createClient();

        $client->request('GET', '/admin/superadmin/quizz');

        $this->assertEquals(302, $client->getResponse()->getStatusCode());
    }

    public function testSuperadminAssociationDocument()
    {
        $client = static::createClient();

        $client->request('GET', '/admin/superadmin/acteur/1/document');

        $this->assertEquals(302, $client->getResponse()->getStatusCode());
    }

    public function testSuperadminNewsletters()
    {
        $client = static::createClient();

        $client->request('GET', '/admin/superadmin/lettre');

        $this->assertEquals(302, $client->getResponse()->getStatusCode());
    }

    public function testSuperadminNewslettersHighlight()
    {
        $client = static::createClient();

        $client->request('GET', '/admin/superadmin/lettre/1/actions');

        $this->assertEquals(302, $client->getResponse()->getStatusCode());
    }

    public function testAdminWorkshop()
    {
        $client = static::createClient();

        $client->request('GET', '/admin/atelier');

        $this->assertEquals(302, $client->getResponse()->getStatusCode());
    }

    public function testAdminWorkshops()
    {
        $client = static::createClient();

        $client->request('GET', '/admin/ateliers');

        $this->assertEquals(302, $client->getResponse()->getStatusCode());
    }

    public function testAdminWorkshopEvents()
    {
        $client = static::createClient();

        $client->request('GET', '/admin/reunions');

        $this->assertEquals(302, $client->getResponse()->getStatusCode());
    }

    public function testNewsletter()
    {
        $client = static::createClient();

        $client->request('GET', '/lettre/2020-01-01/2020-01-31');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

    public function testNewsletterId()
    {
        $client = static::createClient();

        $client->request('GET', '/lettre/1');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

    public function testWorkshop()
    {
        $client = static::createClient();

        $client->request('GET', '/atelier/1/Workshop-name');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

    public function testWorkshops()
    {
        $client = static::createClient();

        $client->request('GET', '/ateliers');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }
}
