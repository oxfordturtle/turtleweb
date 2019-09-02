<?php

namespace App\Entity\User;

use App\Entity\ActivityLog\ActivityLogManager;
use Doctrine\ORM\EntityManagerInterface;

class UserManager
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

    public function create(User $user)
    {
        if ($user->getType() === 'teacher') {
            $dummyStudent = clone $user;
            $dummyStudent->setType('student');
            $dummyStudent->setUsername($user->getUsername() . '.student');
            $dummyStudent->setEmail('teacher:' . $user->getEmail());
            $dummyStudent->setParentUser($user);
            $user->setDummyStudent($dummyStudent);
            $this->entityManager->persist($user);
            $this->entityManager->persist($dummyStudent);
        } else {
            $this->entityManager->persist($user);
        }
        $this->entityManager->flush();
        $this->activityLogManager->create($user, 'signed up');
    }

    public function edit(User $user)
    {
        $this->entityManager->persist($user);
        if ($user->getDummyStudent()) {
            $dummyStudent = $user->getDummyStudent();
            $dummyStudent->setUsername($user->getUsername() . '.student');
            $dummyStudent->setEmail('teacher:' . $user->getEmail());
            $dummyStudent->setFirstname($user->getFirstname());
            $dummyStudent->setSurname($user->getSurname());
            $dummyStudent->setSchoolUrn($user->getSchoolUrn());
            $dummyStudent->setSchoolName($user->getSchoolName());
            $dummyStudent->setSchoolPostcode($user->getSchoolPostcode());
            $this->entityManager->persist($dummyStudent);
        }
        $this->entityManager->flush();
        $this->activityLogManager->create($user, 'edited account details');
    }

    public function delete(User $user)
    {
        foreach ($user->getLogs()->toArray() as $log) {
            $this->entityManager->remove($log);
        }
        foreach ($user->getCourses()->toArray() as $course) {
            foreach ($course->getFolders()->toArray() as $folder) {
                $folder->getFiles()->clear();
                $this->entityManager->persist($folder);
                $this->entityManager->remove($folder);
            }
            $course->getStudents()->clear();
            $this->entityManager->persist($course);
            $this->entityManager->remove($course);
        }
        foreach ($user->getSubscriptions()->toArray() as $course) {
            foreach ($course->getAssignments() as $assignment) {
                $submission = $assignment->getStudentSubmission($user);
                if ($submission) {
                    $assignment->removeFile($submission);
                    $this->entityManager->persist($assignment);
                }
            }
            $course->removeStudent($user);
            $this->entityManager->persist($course);
        }
        foreach ($user->getFiles() as $file) {
            $this->entityManager->remove($file);
        }
        $this->entityManager->remove($user);
        $this->entityManager->flush();
        if ($user->getDummyStudent()) {
            $this->delete($user->getDummyStudent());
        }
    }

    public function changePassword($newPassword, User $user)
    {
        $user->setPassword($newPassword);
        $this->entityManager->persist($user);
        $dummyStudent = $user->getDummyStudent();
        if ($dummyStudent) {
            $dummyStudent->setPassword($newPassword);
            $this->entityManager->persist($dummyStudent);
        }
        $this->entityManager->flush();
        $this->activityLogManager->create($user, 'changed password');
    }

    public function emailCredentials(User $user)
    {
        $user->setResetToken();
        $this->entityManager->persist($user);
        $this->entityManager->flush();
        $to = $user->getEmail();
        $subject = 'Turtle Forum: Reset Password';
        $message = '<html><head><title>Turtle Forum: Reset Password</title></head><body>';
        $message .= '<p>'.$user->getFirstname().' '.$user->getSurname().',</p>';
        $message .= '<p>Your username for the Turtle Forum at <a href="http://www.turtle.ox.ac.uk/forum/">www.turtle.ox.ac.uk/forum/</a> is <b>'.$user->getUsername().'</b>. To reset your password, click on the link below. This link will be valid for 24 hours.</p>';
        $message .= '<p><a href="http://www.turtle.ox.ac.uk/reset/'.$user->getId().'/'.$user->getResetToken().'">Reset Password</a></p>';
        $message .= '<p>This is an automatically generated email. Please do not reply to this message. If you did not expect to receive it, then it seems that someone else entered your email address into the "Forgot Credentials" form on our web site. In this case, you can safely ignore this message; without the link above, no one else will be able to reset your password.</p>';
        $message .= '</body></html>';
        $headers = 'Content-Type: text/html; charset=UTF-8
From: Turtle Forum <no-reply@turtle.ox.ac.uk>';
        mail($to, $subject, $message, $headers);
        $this->activityLogManager->create($user, 'emailed credentials');
    }
}
