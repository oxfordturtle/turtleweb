<?php

namespace App\Entity\Course;

use App\Entity\ActivityLog\ActivityLogManager;
use App\Entity\User\User;
use Doctrine\ORM\EntityManagerInterface;

class CourseManager
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

    public function create(Course $course, User $user)
    {
        $this->entityManager->persist($course);
        $this->entityManager->flush();
        $this->activityLogManager->create($user, 'created course', $course->getId());
    }

    public function edit(Course $course, User $user)
    {
        if ($course->getOwner() !== $user) {
            throw new \Exception('You do not have permission to edit this course.');
        }
        $this->entityManager->persist($course);
        $this->entityManager->flush();
        $this->activityLogManager->create($user, 'edited course', $course->getId());
    }

    public function delete(Course $course, User $user)
    {
        if ($course->getOwner() !== $user) {
            throw new \Exception('You do not have permission to delete this course.');
        }
        foreach ($course->getFolders()->toArray() as $folder) {
            $folder->getFiles()->clear();
            $this->entityManager->persist($folder);
            $this->entityManager->remove($folder);
        }
        foreach ($course->getOwner()->getFiles()->toArray() as $file) {
            if ($file->getFolders()->count() === 0) {
                $this->entityManager->remove($file);
            }
        }
        $course->getStudents()->clear();
        $this->entityManager->persist($course);
        $this->entityManager->remove($course);
        $this->entityManager->flush();
        $this->activityLogManager->create($user, 'deleted course', $course->getId());
    }

    public function createClone(Course $courseToClone, $name, $startDate, User $user)
    {
        $courseToClone->setClones($courseToClone->getClones() + 1);
        $this->entityManager->persist($courseToClone);
        $clonedCourse = clone $courseToClone;
        $clonedCourse->setOwner($user);
        $clonedCourse->setName($name);
        $clonedCourse->setStartDate($startDate);
        $clonedCourse->getOwner()->addCourse($clonedCourse);
        $this->entityManager->persist($clonedCourse);
        foreach ($courseToClone->getFolders()->toArray() as $folder) {
            $clonedFolder = clone $folder;
            $clonedFolder->setCourse($clonedCourse);
            $this->entityManager->persist($clonedFolder);
        }
        $this->entityManager->flush();
        $this->activityLogManager->create($user, 'cloned course', $courseToClone->getId(), $clonedCourse->getId());
    }

    public function addStudent(Course $course, User $user)
    {
        if ($course->getStatus() !== 'open') {
            throw new \Exception('This course is closed.');
        }
        if ($course->getStudents()->contains($user)) {
            throw new \Exception('You are already signed up to this course.');
        }
        if ($user->getType() === 'teacher') {
            throw new \Exception('Teachers cannot sign up to courses.');
        }
        $user->addSubscription($course); // ensure immediate update without reloading the user entity
        $course->addStudent($user);
        $this->entityManager->persist($course);
        $this->entityManager->flush();
        $this->activityLogManager->create($user, 'joined course', $course->getId());
    }
}
