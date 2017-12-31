<?php

/**
 * @copyright 2017-2018 Vladimir Jimenez
 * @license   https://github.com/allejo/roconut/blob/master/LICENSE.md MIT
 */

namespace AppBundle\Repository;

use AppBundle\Entity\PasteStatus;
use AppBundle\Entity\User;
use Doctrine\ORM\EntityRepository;

/**
 * PasteRepository.
 */
class PasteRepository extends EntityRepository
{
    /**
     * Find the Pastes that were created by a given user.
     *
     * Note, these are
     */
    public function findPublicPartialPastesBy(User $user): array
    {
        $qb = $this->createQueryBuilder('p');
        $qb
            ->select(['p.id', 'p.title', 'p.encryption_key AS encryptionKey', 'p.created'])
            ->andWhere('p.encryption_key IS NOT NULL')
            ->andWhere('p.status = :status')->setParameter('status', PasteStatus::ACTIVE)
            ->andWhere('p.user = :user_id')->setParameter('user_id', $user)
        ;

        return $qb->getQuery()->getResult();
    }
}
