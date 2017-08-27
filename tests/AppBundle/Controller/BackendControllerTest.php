<?php

namespace tests\AppBundle\Controller;

// http://symfony.com/doc/current/book/testing.html for further details
use AppBundle\Test\WebTestCase;
use AppBundle\Entity\Media;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class BackendControllerTest extends WebTestCase
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $em;

    /**
     * {@inheritDoc}
     */
    protected function setUp()
    {
        self::bootKernel();

        $this->em = static::$kernel->getContainer()
            ->get('doctrine')
            ->getManager();
    }

    public function testNewMediaForm()
    {
        $this->loadFixtures(self::$kernel);

        $client = static::createClient([], [
            'PHP_AUTH_USER' => 'admin',
            'PHP_AUTH_PW'   => 'password',
        ]);

        $client->followRedirects();
        $client->enableProfiler();

        $client2 = static::createClient();
        $client2->insulate();

        $crawler = $client->request('GET', '/admin');

        $this->assertEquals(
            Response::HTTP_OK,
            $client->getResponse()->getStatusCode()
        );

        $form = $crawler->selectButton('Valider')->form();
        $form->setValues([
            'media[title]' => 'trop trognon ce chaton',
            'media[url]'    => 'http://exh5266.cias.rit.edu/256/homework3/images/kitten.jpg',
        ]);

        $crawler = $client->submit($form);

        $this->assertEquals(
            Response::HTTP_OK,
            $client->getResponse()->getStatusCode()
        );

        $this->assertEquals(1, $crawler->filter('html:contains("Votre media est enregistré")')->count());

        $media = $this->em->getRepository(Media::class)->findOneBy(['url' => 'http://exh5266.cias.rit.edu/256/homework3/images/kitten.jpg']);
        $this->assertNotNull($media);
        $this->assertNull($media->getAverage());

        // Check that the profiler is enabled
        if ($profile = $client->getProfile()) {
            // check the time spent in the framework
            $this->assertLessThan(
                600,
                $profile->getCollector('time')->getDuration()
            );
        }

        $client2->request('GET', '/show/11');

        $this->assertEquals(
            Response::HTTP_OK,
            $client2->getResponse()->getStatusCode()
        );
    }

    public function testNewMediaFormWithFollowRedirect()
    {
        $this->loadFixtures(self::$kernel);

        $client = static::createClient();
        $session = $client->getContainer()->get('session');

        // the firewall context defaults to the firewall name
        $firewallContext = 'backend';

        $token = new UsernamePasswordToken('admin', 'password', $firewallContext, ['ROLE_ADMIN']);
        $session->set('_security_'.$firewallContext, serialize($token));
        $session->save();

        $cookie = new Cookie($session->getName(), $session->getId());
        $client->getCookieJar()->set($cookie);

        $client->enableProfiler();

        $client2 = static::createClient();
        $client2->insulate();

        $client->request('GET', '/admin');
        $crawler = $client->followRedirect();

        $this->assertEquals(
            Response::HTTP_OK,
            $client->getResponse()->getStatusCode()
        );

        $form = $crawler->selectButton('Valider')->form();
        $form->setValues([
            'media[title]' => 'trop trognon ce chaton',
            'media[url]'    => 'http://exh5266.cias.rit.edu/256/homework3/images/kitten.jpg',
        ]);

        $client->submit($form);

        if ($profile = $client->getProfile()) {
            // check the number of requests
            $this->assertEquals(
                3,
                $profile->getCollector('db')->getQueryCount()
            );
        }

        $this->assertEquals($client->getContainer()->get('session')->getFlashBag()->get('success')[0], 'Votre media est enregistré');

        $client->followRedirect();

        $this->assertEquals(
            Response::HTTP_OK,
            $client->getResponse()->getStatusCode()
        );

        $media = $this->em->getRepository(Media::class)->findOneBy(['url' => 'http://exh5266.cias.rit.edu/256/homework3/images/kitten.jpg']);
        $this->assertNotNull($media);
        $this->assertNull($media->getAverage());

        // Check that the profiler is enabled
        if ($profile = $client->getProfile()) {
            // check the time spent in the framework
            $this->assertLessThan(
                600,
                $profile->getCollector('time')->getDuration()
            );
        }

        $client2->request('GET', '/show/11');

        $this->assertEquals(
            Response::HTTP_OK,
            $client2->getResponse()->getStatusCode()
        );
    }

    /**
     * {@inheritDoc}
     */
    protected function tearDown()
    {
        parent::tearDown();

        $this->em->close();
        $this->em = null; // avoid memory leaks
    }
}