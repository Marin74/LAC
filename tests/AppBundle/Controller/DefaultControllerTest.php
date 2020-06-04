<?php

namespace Tests\AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class DefaultControllerTest extends WebTestCase
{

    public function testIndexIsUp()
    {
        $client = static::createClient();

        /*$crawler = */$client->request('GET', '/');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        //$this->assertContains('Welcome to Symfony', $crawler->filter('#container h1')->text());
    }

    public function testAssociationsIsUp()
    {
        $client = static::createClient();

        $client->request('GET', '/acteurs');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

    public function testEventsIsUp()
    {
        $client = static::createClient();

        $client->request('GET', '/actions');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

    public function testAssociationIsUp()
    {
        $client = static::createClient();

        $client->request('GET', '/acteur/1/Association-name');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

    public function testEventIcsIsUp()
    {
        $client = static::createClient();

        $client->request('GET', '/action/1/ics');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

    public function testEventIsUp()
    {
        $client = static::createClient();

        $client->request('GET', '/action/1/Event-name');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

    public function testArchivesIsUp()
    {
        $client = static::createClient();

        $client->request('GET', '/archives');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

    public function testArchivesPageIsUp()
    {
        $client = static::createClient();

        $client->request('GET', '/archives/2019-10');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

    public function testSearchIsUp()
    {
        $client = static::createClient();

        $client->request('GET', '/recherche');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

    public function testLegalTermsIsUp()
    {
        $client = static::createClient();

        $client->request('GET', '/mentions-legales');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

    public function testMapIsUp()
    {
        $client = static::createClient();

        $client->request('GET', '/carte');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

    public function testAdminAssociationIsUp()
    {
        $client = static::createClient();

        $client->request('GET', '/admin/acteur');

        $this->assertEquals(302, $client->getResponse()->getStatusCode());
    }

    public function testAdminEventsIsUp()
    {
        $client = static::createClient();

        $client->request('GET', '/admin/action');

        $this->assertEquals(302, $client->getResponse()->getStatusCode());
    }

    public function testAdminIndexIsUp()
    {
        $client = static::createClient();

        $client->request('GET', '/admin');

        $this->assertEquals(302, $client->getResponse()->getStatusCode());
    }

    public function testAdminEventIsUp()
    {
        $client = static::createClient();

        $client->request('GET', '/admin/action/1');

        $this->assertEquals(302, $client->getResponse()->getStatusCode());
    }

    public function testAdminEventDocumentIsUp()
    {
        $client = static::createClient();

        $client->request('GET', '/admin/action/1/document');

        $this->assertEquals(302, $client->getResponse()->getStatusCode());
    }

    public function testAdminSearchPlaceIsUp()
    {
        $client = static::createClient();

        $client->request('GET', '/admin/lieux/recherche');

        $this->assertEquals(302, $client->getResponse()->getStatusCode());
    }

    public function testSuperadminUsersIsUp()
    {
        $client = static::createClient();

        $client->request('GET', '/admin/superadmin/utilisateur');

        $this->assertEquals(302, $client->getResponse()->getStatusCode());
    }

    public function testSuperadminAssociationsIsUp()
    {
        $client = static::createClient();

        $client->request('GET', '/admin/superadmin/acteur');

        $this->assertEquals(302, $client->getResponse()->getStatusCode());
    }

    public function testSuperadminEventsIsUp()
    {
        $client = static::createClient();

        $client->request('GET', '/admin/superadmin/action');

        $this->assertEquals(302, $client->getResponse()->getStatusCode());
    }

    public function testSuperadminEventIsUp()
    {
        $client = static::createClient();

        $client->request('GET', '/admin/superadmin/action/1');

        $this->assertEquals(302, $client->getResponse()->getStatusCode());
    }

    public function testSuperadminPlacesIsUp()
    {
        $client = static::createClient();

        $client->request('GET', '/admin/superadmin/lieu');

        $this->assertEquals(302, $client->getResponse()->getStatusCode());
    }

    public function testSuperadminPlaceIsUp()
    {
        $client = static::createClient();

        $client->request('GET', '/admin/superadmin/lieu/1');

        $this->assertEquals(302, $client->getResponse()->getStatusCode());
    }

    public function testSuperadminQuizScoresIsUp()
    {
        $client = static::createClient();

        $client->request('GET', '/admin/superadmin/quizz');

        $this->assertEquals(302, $client->getResponse()->getStatusCode());
    }

    public function testSuperadminAssociationDocumentIsUp()
    {
        $client = static::createClient();

        $client->request('GET', '/admin/superadmin/acteur/1/document');

        $this->assertEquals(302, $client->getResponse()->getStatusCode());
    }

    public function testSuperadminNewslettersIsUp()
    {
        $client = static::createClient();

        $client->request('GET', '/admin/superadmin/lettre');

        $this->assertEquals(302, $client->getResponse()->getStatusCode());
    }

    public function testSuperadminNewslettersHighlightIsUp()
    {
        $client = static::createClient();

        $client->request('GET', '/admin/superadmin/lettre/1/actions');

        $this->assertEquals(302, $client->getResponse()->getStatusCode());
    }

    public function testAdminWorkshopIsUp()
    {
        $client = static::createClient();

        $client->request('GET', '/admin/atelier');

        $this->assertEquals(302, $client->getResponse()->getStatusCode());
    }

    public function testAdminWorkshopsIsUp()
    {
        $client = static::createClient();

        $client->request('GET', '/admin/ateliers');

        $this->assertEquals(302, $client->getResponse()->getStatusCode());
    }

    public function testAdminWorkshopEventsIsUp()
    {
        $client = static::createClient();

        $client->request('GET', '/admin/reunions');

        $this->assertEquals(302, $client->getResponse()->getStatusCode());
    }

    public function testNewsletterIsUp()
    {
        $client = static::createClient();

        $client->request('GET', '/lettre/2020-01-01/2020-01-31');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

    public function testNewsletterIdIsUp()
    {
        $client = static::createClient();

        $client->request('GET', '/lettre/1');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

    public function testWorkshopIsUp()
    {
        $client = static::createClient();

        $client->request('GET', '/atelier/1/Workshop-name');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

    public function testWorkshopsIsUp()
    {
        $client = static::createClient();

        $client->request('GET', '/ateliers');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }
}
