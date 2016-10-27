<?php

namespace AppBundle\Repository;

use AppBundle\Entity\Media;
use AppBundle\Entity\User;
use Doctrine\ORM\Tools\Pagination\Paginator;

/**
 * MediaRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class MediaRepository extends \Doctrine\ORM\EntityRepository
{
    /**
     * Get a random media
     *
     * @return Media
     */
    public function getRandomMedia()
    {
        $ids = $this
            ->createQueryBuilder('m')
            ->select('m.id')
            ->getQuery()
            ->getResult();

        if (!count($ids)) {
            return null;
        }

        // get a random Id from the list
        $index = rand(0, count($ids) - 1);
        $randomId = $ids[$index]['id'];

        // get the media corresponding to this id
        $media = $this
            ->createQueryBuilder('m')
            ->where('m.id = :randomId')
            ->setParameter('randomId', $randomId)
            ->getQuery()
            ->getOneOrNullResult();

        return $media;
    }

    /**
     * Get 5 top medias
     * @return Paginator
     */
    public function getTopsMedia()
    {
        $query = $this->createQueryBuilder('m')
            ->select('m, v')
            ->leftJoin('m.votes', 'v')
            ->where('m.average IS NOT NULL')
            ->orderBy('m.average', 'DESC')
            ->setFirstResult(0)
            ->setMaxResults(5)
            ->getQuery();

        return new Paginator($query, true);
    }

    /**
     * Get 5 flop medias
     * @return Paginator
     */
    public function getFlopsMedia()
    {
        $query = $this->createQueryBuilder('m')
            ->select('m, v')
            ->leftJoin('m.votes', 'v')
            ->where('m.average IS NOT NULL')
            ->orderBy('m.average', 'ASC')
            ->setFirstResult(0)
            ->setMaxResults(5)
            ->getQuery();

        return new Paginator($query, true);
    }


    /**
     * Get a media from an id, with votes hydrated
     *
     * @param integer $id
     * @return Media
     */
    public function getHydratedMediaById($id)
    {
        return $this
            ->createQueryBuilder('m')
            ->select('m, v')
            ->leftJoin('m.votes', 'v')
            ->where('m.id = :randomId')
            ->setParameter('randomId', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Get a media User hasn't voted for yet
     *
     * @param User $user
     * @return Media|null
     */
    public function getNewMediaForUser(User $user)
    {
        $repoString = Media::class;

        $dql = sprintf(
            'SELECT m.id FROM %s m
             WHERE m.id NOT IN (
             SELECT m2.id FROM %s m2
             INNER JOIN m2.votes v2 WITH v2.user = %s
                        )',
            $repoString,
            $repoString,
            $user->getId()
        );

        $results = $this->createQueryBuilder('m')
            ->getQuery()
            ->setDQL($dql)
            ->getResult();

        // return one random media
        if ($count = count($results)) {
            $index = rand(0, $count - 1);
            $id = $results[$index]['id'];

            return $this->getHydratedMediaById($id);
        }

        // or null if none are found
        return null;
    }

    /**
     * @param $vote
     */
    public function save($vote)
    {
        $this->_em->persist($vote);
        $this->_em->flush();
    }
}
