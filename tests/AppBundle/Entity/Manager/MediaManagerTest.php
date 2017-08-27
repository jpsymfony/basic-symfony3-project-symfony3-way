<?php

namespace tests\AppBundle\Entity\Manager;

use AppBundle\Entity\Media;
use AppBundle\Entity\User;
use AppBundle\Repository\MediaRepository;
use AppBundle\Entity\Manager\MediaManager;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class MediaManagerTest extends TestCase
{
    protected $mediaRepository;
    protected $token;
    protected $tokenStorage;
    protected $mediaManager;

    public function setUp()
    {
        $this->token = $this->createMock(TokenInterface::class);
        $this->tokenStorage = $this->createMock(TokenStorageInterface::class);
//        $this->getMock(
//            $originalClassName,
//            $methods = array(),
//            array $arguments = array(),
//                $mockClassName = '',
//                $callOriginalConstructor = TRUE,
//                $callOriginalClone = TRUE,
//                $callAutoload = TRUE
//            );

        $this->mediaRepository = $this->getMockBuilder(MediaRepository::class)
                                 ->setMethods(['getNewMediaForUser', 'getRandomMedia', 'getHydratedMediaById', 'save'])
                                 ->disableOriginalConstructor()
                                 ->getMock();

        $this->mediaManager = new MediaManager($this->mediaRepository, $this->tokenStorage);
    }

    public function testGetNextMedia()
    {
        $user = new User();
        $media = new Media();

        $this->token
            ->expects($this->once())
            ->method('getUser')
            ->willReturn($user);

        $this->tokenStorage
            ->expects($this->once())
            ->method('getToken')
            ->willReturn($this->token);

        $this->mediaRepository
            ->expects($this->once())
            ->method('getNewMediaForUser')
            ->willReturn($media);

        $this->mediaRepository
            ->expects($this->never())
            ->method('getRandomMedia');

        $this->assertEquals($media, $this->mediaManager->getNextMedia());
    }

    /*public function testGetNextMediaWillThrowExceptionIfObjectNotInstanceOfMedia()
    {
        $user = new User();
        $media = new Media();

        $this->token
            ->expects($this->once())
            ->method('getUser')
            ->willReturn($user);

        $this->tokenStorage
            ->expects($this->once())
            ->method('getToken')
            ->willReturn($this->token);

        $this->mediaRepository
            ->expects($this->once())
            ->method('getNewMediaForUser')
            ->willReturn($user);

        $this->mediaRepository
            ->expects($this->never())
            ->method('getRandomMedia');

        $this->expectException('\Exception');
        $this->expectExceptionMessage('L\'objet n\'est pas de type Media');

        $this->assertEquals($media, $this->mediaManager->getNextMedia());
    }*/

    public function testGetNextMediaWillReturnGetRandomMediaRepositoryMethodIfNoMediaReturnedByGetNewMediaForUserMethod()
    {
        $user = new User();
        $media = new Media();

        $this->token
            ->expects($this->once())
            ->method('getUser')
            ->willReturn($user);

        $this->tokenStorage
            ->expects($this->once())
            ->method('getToken')
            ->willReturn($this->token);

        $this->mediaRepository
            ->expects($this->once())
            ->method('getNewMediaForUser')
            ->willReturn(null);

        $this->mediaRepository
            ->expects($this->once())
            ->method('getRandomMedia')
            ->willReturn($media);

        $this->assertEquals($media, $this->mediaManager->getNextMedia());
    }

    public function testGetNextMediaWillReturnGetRandomMediaRepositoryMethodIfNoConnectedUser()
    {
        $media = new Media();

        $this->token
            ->expects($this->once())
            ->method('getUser')
            ->willReturn(null);

        $this->tokenStorage
            ->expects($this->once())
            ->method('getToken')
            ->willReturn($this->token);

        $this->mediaRepository
            ->expects($this->never())
            ->method('getNewMediaForUser');

        $this->mediaRepository
            ->expects($this->once())
            ->method('getRandomMedia')
            ->willReturn($media);

        $this->assertEquals($media, $this->mediaManager->getNextMedia());
    }

    public function testGetMedia()
    {
        $media = new Media();

        $this->mediaRepository
            ->expects($this->once())
            ->method('getHydratedMediaById')
            ->with(123456)
            ->willReturn($media);

        $this->assertEquals($media, $this->mediaManager->getMedia(123456));
    }

    public function testGetMediaWillReturnNull()
    {
        $this->mediaRepository
            ->expects($this->once())
            ->method('getHydratedMediaById')
            ->with(123456)
            ->willReturn(null);

        $this->assertNull($this->mediaManager->getMedia(123456));
    }

    public function testSaveMedia()
    {
        $media = new Media();

        $this->mediaRepository
            ->expects($this->once())
            ->method('save')
            ->with($media);

        $this->mediaManager->saveMedia($media);
    }
}