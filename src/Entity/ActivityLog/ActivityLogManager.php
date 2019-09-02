<?php

namespace App\Entity\ActivityLog;

use App\Entity\User\User;
use Doctrine\ORM\EntityManagerInterface;

class ActivityLogManager
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function create(User $user, $action, $entity1 = null, $entity2 = null)
    {
        $activityLog = new ActivityLog($user, $action, $entity1, $entity2);
        $this->entityManager->persist($activityLog);
        $this->entityManager->flush();
    }
}
