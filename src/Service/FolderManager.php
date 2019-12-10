<?php

namespace App\Service;

use App\Entity\ActivityLog\ActivityLogManager;
use App\Entity\File\File;
use App\Entity\User\User;
use Doctrine\ORM\EntityManagerInterface;

class FolderManager
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

    public function create(Folder $folder, User $user)
    {
        if ($folder->getCourse()->getOwner() !== $user) {
            throw new \Exception('You do not have permission to create a ' . $folder->getType() . ' for this course.');
        }
        $folder->getCourse()->addFolder($folder); // ensure immediate update without reloading the course entity
        $this->entityManager->persist($folder);
        $this->entityManager->flush();
        $this->activityLogManager->create($user, 'created folder', $folder->getId());
    }

    public function edit(Folder $folder, User $user)
    {
        if ($folder->getCourse()->getOwner() !== $user) {
            throw new \Exception('You do not have permission to edit this ' . $folder->getType() . '.');
        }
        $this->entityManager->persist($folder);
        $this->entityManager->flush();
        $this->activityLogManager->create($user, 'edited folder', $folder->getId());
    }

    public function delete(Folder $folder, User $user)
    {
        if ($folder->getCourse()->getOwner() !== $user) {
            throw new \Exception('You do not have permission to delete this folder.');
        }
        $this->entityManager->remove($folder);
        foreach ($folder->getCourse()->getOwner()->getFiles()->toArray() as $file) {
            if ($file->getFolders()->isEmpty()) {
                $this->entityManager->remove($file);
            }
        }
        foreach ($folder->getCourse()->getFoldersByType($folder->getType())->toArray() as $sibling) {
            if ($sibling->getNumber() > $folder->getNumber()) {
                $sibling->setNumber($sibling->getNumber() - 1);
                $this->entityManager->persist($sibling);
            }
        }
        $this->entityManager->flush();
        $this->activityLogManager->create($user, 'deleted folder', $folder->getId());
    }

    public function move(Folder $folder, $difference, User $user)
    {
        if ($folder->getCourse()->getOwner() !== $user) {
            throw new \Exception('You do not have permission to move this ' . $folder->getType() . '.');
        }
        $number = $folder->getNumber();
        $newNumber = $number + $difference;
        $course = $folder->getCourse();
        $siblings = $course->getFoldersByType($folder->getType());
        if ($newNumber === 0 || $newNumber === $siblings->count() + 1) {
            return;
        }
        $folder->setNumber($newNumber);
        $this->entityManager->persist($folder);
        foreach ($siblings->toArray() as $sibling) {
            if ($sibling->getNumber() === $newNumber && $sibling !== $folder) {
                $sibling->setNumber($number);
                $this->entityManager->persist($sibling);
            }
        }
        $this->entityManager->flush();
        $this->activityLogManager->create($user, 'moved folder', $folder->getId());
    }

    public function addFile(Folder $folder, File $file, User $user)
    {
        switch ($user->getType()) {
            case 'teacher':
                if ($folder->getCourse()->getOwner() !== $user) {
                    throw new \Exception('You do not have permission to add a file to this '.$folder->getType().'.');
                }
                if ($folder->getFiles()->contains($file)) {
                    throw new \Exception('File "'.$file->getName().'" is already associated with this '.$folder->getType().'.');
                }
                break;
            case 'student':
                if ($folder->getType() === 'lesson') {
                    throw new \Exception('You do not have permission to add a file to this lesson.');
                    return;
                }
                if (!$user->getSubscriptions()->contains($folder->getCourse())) {
                    throw new \Exception('You do not have permission to submit a program to this assignment.');
                }
                if ($folder->getStudentSubmission($user)) {
                    throw new \Exception('You have already submitted a program to this assignment.');
                }
                if ($file->getExt() !== 'tgx') {
                    throw new \Exception('This is not an valid Turtle Export/Upload file. Open up your program in the Turtle System, and click \'File > Save Export/Upload file ...\' to export in the required format.');
                }
                break;
        }
        $folder->addFile($file);
        $this->entityManager->persist($folder);
        $this->entityManager->flush();
        $this->activityLogManager->create($user, 'added file to folder', $folder->getId(), $file->getId());
    }

    public function removeFile(Folder $folder, File $file, User $user)
    {
        switch ($user->getType()) {
            case 'teacher':
                if ($folder->getCourse()->getOwner() !== $user) {
                    throw new \Exception('You do not have permission to remove files from this ' . $folder->getType() . '.');
                }
                if ($file->getFolders()->count() === 1) {
                    throw new \Exception('Every file must be associated with at least one lesson or assignment. To remove this file altogether, you must delete it.');
                }
                break;
            case 'student':
                if ($file->getOwner() !== $user) {
                    throw new \Exception('You do not have permission to remove this file from this ' . $folder->getType());
                }
                break;
        }
        $folder->removeFile($file);
        $this->entityManager->persist($folder);
        $this->entityManager->flush();
        $this->activityLogManager->create($user, 'removed file from folder', $folder->getId(), $file->getId());
    }
}
