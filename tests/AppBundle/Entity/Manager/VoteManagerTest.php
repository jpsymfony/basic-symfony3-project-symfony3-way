<?php

namespace tests\AppBundle\Entity\Manager;

use AppBundle\Entity\Media;
use AppBundle\Entity\User;
use AppBundle\Entity\Vote;
use AppBundle\Repository\VoteRepository;
use AppBundle\Entity\Manager\VoteManager;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class VoteManagerTest extends TestCase
{
    protected $repository;
    protected $token;
    protected $tokenStorage;
    protected $voteManager;

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

        $this->repository = $this->getMockBuilder(VoteRepository::class)
                                      ->setMethods(['save'])
                                      ->disableOriginalConstructor()
                                      ->getMock();

        $this->voteManager = new VoteManager($this->repository, $this->tokenStorage);
    }

    public function testGetNewVote()
    {
        $user = new User();
        $media = new Media();
        $vote = new Vote();
        $vote->setUser($user);
        $vote->setMedia($media);

        $this->token
            ->expects($this->once())
            ->method('getUser')
            ->willReturn($user);

        $this->tokenStorage
            ->expects($this->once())
            ->method('getToken')
            ->willReturn($this->token);

        $result = $this->voteManager->getNewVote($media);

        $this->assertEquals($vote, $this->voteManager->getNewVote($media));
    }

    public function testGetNewVoteReturnsNullIfNoConnectedUser()
    {
        $user = new User();
        $media = new Media();
        $vote = new Vote();
        $vote->setUser($user);
        $vote->setMedia($media);

        $this->token
            ->expects($this->once())
            ->method('getUser')
            ->willReturn(null);

        $this->tokenStorage
            ->expects($this->once())
            ->method('getToken')
            ->willReturn($this->token);

        $this->assertNull($this->voteManager->getNewVote($media));
    }

    public function testSaveVote()
    {
        $media = $this->createMock(Media::class);
        $vote = new Vote();
        $vote->setMedia($media);

        $media
            ->expects($this->once())
            ->method('addVote')
            ->with($vote);

        $this->repository
            ->expects($this->once())
            ->method('save')
            ->with($vote);

        $this->voteManager->saveVote($vote);
    }
}