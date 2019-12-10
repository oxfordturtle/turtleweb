<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;

/**
 * The user manager.
 *
 * This service handles the main business logic related to users.
 */
class UserManager
{
  /**
   * @var EntityManagerInterface
   */
  private $entityManager;

  /**
   * @var ActivityLogManager
   */
  private $activityLogManager;

  /**
   * Constructor function.
   *
   * @param EntityManagerInterface $entityManager
   * @param ActivityLogManager $activityLogManager
   */
  public function __construct(
    EntityManagerInterface $entityManager,
    ActivityLogManager $activityLogManager
  ) {
    $this->entityManager = $entityManager;
    $this->activityLogManager = $activityLogManager;
  }

  /**
   * Get a user by email.
   *
   * @param string $email
   * @return User|null
   */
   public function getUserByEmail(string $email): ?User
   {
     return $this->entityManager->getRepository(User::class)->findOneByEmail($email);
   }

  /**
   * Update a user.
   *
   * @param User $user
   */
  public function updateUser(User $user)
  {
    $this->entityManager->persist($user);
    $this->entityManager->flush();
  }

  /**
   * Create a user.
   *
   * @param User $user
   */
  public function createUser(User $user)
  {
    // save the user
    $this->updateUser($user);

    // if it's a teacher, create and save the corresponding dummy student account
    if ($user->getType() === 'teacher') {
      $dummyStudent = clone $user;
      $dummyStudent->setType('student');
      $dummyStudent->setUsername($user->getUsername() . '.student');
      $dummyStudent->setEmail('teacher:' . $user->getEmail());
      $dummyStudent->setParentUser($user);
      $user->setDummyStudent($dummyStudent);
      $this->updateUser($user);
      $this->updateUser($dummyStudent);
    }

    // log the activity
    $this->activityLogManager->create($user, 'signed up');
  }

  /**
   * Edit a user.
   *
   * @param User $user
   */
  public function editUser(User $user)
  {
    // save the user
    $this->updateUser($user);

    // if it's a teacher, save the corresponding dummy student details
    $dummyStudent = $user->getDummyStudent();
    if ($dummyStudent) {
      $dummyStudent->setUsername($user->getUsername() . '.student');
      $dummyStudent->setEmail('teacher:' . $user->getEmail());
      $dummyStudent->setFirstname($user->getFirstname());
      $dummyStudent->setSurname($user->getSurname());
      $dummyStudent->setSchoolUrn($user->getSchoolUrn());
      $dummyStudent->setSchoolName($user->getSchoolName());
      $dummyStudent->setSchoolPostcode($user->getSchoolPostcode());
      $this->updateUser($dummyStudent);
    }

    // log the activity
    $this->activityLogManager->create($user, 'edited account details');
  }

  /**
   * Delete a user.
   *
   * @param User $user
   */
  public function deleteUser(User $user)
  {
    // delete all the user's activity logs
    foreach ($user->getLogs()->toArray() as $log) {
      $this->entityManager->remove($log);
    }

    // delete all the user's courses
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

    // delete all the user's subscriptions
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

    // delete all the user's files
    foreach ($user->getFiles() as $file) {
      $this->entityManager->remove($file);
    }

    // delete the user
    $this->entityManager->remove($user);

    // flush the changes
    $this->entityManager->flush();

    // if the user has a dummy student account, delete that too
    if ($user->getDummyStudent()) {
      $this->deleteUser($user->getDummyStudent());
    }
  }

  /**
   * Change a user's password.
   *
   * @param User $user
   * @param string $newPassword
   */
  public function changePassword(User $user, string $newPassword)
  {
    // change the user's password
    $user->setPassword($newPassword);
    $this->updateUser($user);

    // if the user has a dummy student account, change that password too
    $dummyStudent = $user->getDummyStudent();
    if ($dummyStudent) {
      $dummyStudent->setPassword($newPassword);
      $this->updateUser($dummyStudent);
    }

    // log the activity
    $this->activityLogManager->create($user, 'changed password');
  }

  /**
   * Email credentials to the given user.
   *
   * @param User $user
   */
  public function emailCredentials(User $user)
  {
    // create a new random reset token and save the user
    $user->setResetToken();
    $this->saveUser($user);

    // create the email
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

    // send the email
    mail($to, $subject, $message, $headers);

    // log the activity
    $this->activityLogManager->create($user, 'emailed credentials');
  }
}
