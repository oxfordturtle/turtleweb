<?php

namespace App\Service;

use App\Entity\Folder;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class FileManager
{
    private $entityManager;
    private $activityLogManager;

    public function __construct(
        EntityManagerInterface $entityManager,
        ActivityLogManager $activityLogManager
    ) {
        $this->entityManager = $entityManager;
        $this->activityLogManager = $activityLogManager;
    }

    public function create(File $file, User $user)
    {
        if ($user->getType() === 'teacher') {
            throw new \Exception('Files must be created within folders (lessons or assignments).');
        }
        if ($user->getType() === 'student' && !$file->isProgram()) {
            throw new \Exception('This is not an valid Turtle file.');
        }
        $this->entityManager->persist($file);
        $this->entityManager->flush();
        $this->activityLogManager->create($user, 'uploaded file', $file->getId());
    }

    public function createInFolder(File $file, Folder $folder, User $user)
    {
        switch ($user->getType()) {
            case 'teacher':
                if ($folder->getCourse()->getOwner() !== $user) {
                    throw new \Exception('You do not have permission to upload a file to this '.$folder->getType().'.');
                }
                break;
            case 'student':
                if (!$file->isProgram()) {
                    throw new \Exception('This is not an valid Turtle file.');
                }
                if (!$user->getSubscriptions()->contains($folder->getCourse())) {
                    throw new \Exception('You do not have permission to submit a program to this assignment.');
                }
                break;
        }
        $this->entityManager->persist($file);
        $folder->addFile($file);
        $this->entityManager->persist($folder);
        $this->entityManager->flush();
        $this->activityLogManager->create($user, 'uploaded file to folder', $file->getId(), $folder->getId());
    }

    public function edit(File $file, User $user)
    {
        if ($file->getOwner() !== $user) {
            throw new \Exception('You do not have permission to edit this file.');
        }
        $this->entityManager->persist($file);
        $this->entityManager->flush();
        $this->activityLogManager->create($user, 'edited file', $file->getId());
    }

    public function delete(File $file, User $user)
    {
        if ($file->getOwner() !== $user) {
            throw new \Exception('You do not have permission to delete this file.');
        }
        if (($user->getType() === 'student') and !$file->getFolders()->isEmpty()) {
            throw new \Exception('This program is submitted to an assignment and cannot be deleted.');
        }
        $this->entityManager->remove($file);
        $this->entityManager->flush();
        $this->activityLogManager->create($user, 'deleted file', $file->getId());
    }
}
