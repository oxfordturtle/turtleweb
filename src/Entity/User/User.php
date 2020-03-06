<?php

namespace App\Entity\User;

use App\Entity\ActivityLog\ActivityLog;
use App\Entity\Course\Course;
use App\Entity\File\File;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Table(name="user")
 * @ORM\Entity
 * @UniqueEntity(
 *     fields="email",
 *     message="This email address is already taken",
 *     groups={"student", "teacher", "registration"}
 * )
 * @UniqueEntity(
 *     fields="username",
 *     message="This username is already taken",
 *     groups={"student", "teacher", "registration"}
 * )
 */
class User implements UserInterface
{
    /** --- constructor
     */
    public function __construct($type = 'student')
    {
        $this->setType($type);
        $this->logs = new ArrayCollection();
        $this->files = new ArrayCollection();
        $this->courses = new ArrayCollection();
        $this->subscriptions = new ArrayCollection();
    }

    /** --- ID
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    public function getId()
    {
        return $this->id;
    }

    /** --- USERNAME
     * @ORM\Column(name="username", type="string", length=50, unique=true)
     * @Assert\NotBlank(
     *     message="Username cannot be blank",
     *     groups={"student", "teacher", "registration"}
     * )
     * @Assert\NotIdenticalTo(
     *     value="admin",
     *     message="This username is already taken",
     *     groups={"student", "teacher", "registration"}
     * )
     */
    private $username;

    public function setUsername($username)
    {
        $this->username = $username;
        return $this;
    }

    public function getUsername()
    {
        return $this->username;
    }

    /** --- EMAIL ADDRESS
     * @ORM\Column(name="email", type="string", length=100, unique=true)
     * @Assert\NotBlank(
     *     message="Email address cannot be blank",
     *     groups={"student", "teacher", "registration"}
     * )
     * @Assert\Email(
     *     message="This is not a valid email address",
     *     groups={"student", "teacher", "registration"}
     * )
     */
    private $email;

    public function setEmail($email)
    {
        $this->email = $email;
        return $this;
    }

    public function getEmail()
    {
        return $this->email;
    }

    /** --- PASSWORD
     * @ORM\Column(name="password", type="string", length=100)
     * @Assert\NotBlank(
     *     message="Password cannot be blank",
     *     groups={"registration"}
     * )
     */
    private $password;

    public function setPassword($password)
    {
        $this->password = password_hash($password, PASSWORD_BCRYPT, array('cost' => 12));
        return $this;
    }

    public function getPassword()
    {
        return $this->password;
    }

    /** --- RESET_TOKEN_DATE (date the current reset token was created)
     * @ORM\Column(name="reset_token_date", type="date", nullable=true)
     */
    private $resetTokenDate;

    public function setResetTokenDate(\DateTime $resetTokenDate)
    {
        $this->resetTokenDate = $resetTokenDate;
        return $this;
    }

    public function getResetTokenDate()
    {
        return $this->resetTokenDate;
    }

    /** --- RESET_TOKEN (randomly generated on request to reset password)
     * @ORM\Column(name="reset_token", type="string", length=20, nullable=true)
     */
    private $resetToken;

    public function setResetToken()
    {
        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
        $length = 20;
        $resetToken = '';
        for ($i = 0; $i < $length; $i++) {
            $resetToken .= $chars[rand(0, strlen($chars) - 1)];
        }
        $this->resetToken = $resetToken;
        $this->setResetTokenDate(new \DateTime('now'));
        return $this;
    }

    public function getResetToken()
    {
        return $this->resetToken;
    }

    /** --- DUMMY_STUDENT (teachers only - points to their dummy student account)
     * @ORM\OneToOne(targetEntity="App\Entity\User\User", inversedBy="parentUser")
     */
    private $dummyStudent;

    public function setDummyStudent(User $dummyStudent)
    {
        $this->dummyStudent = $dummyStudent;
        return $this;
    }

    public function getDummyStudent()
    {
        return $this->dummyStudent;
    }

    /** --- PARENT_USER (students only - points to their parent teacher account, if any)
     * @ORM\OneToOne(targetEntity="App\Entity\User\User", mappedBy="dummyStudent")
     */
    private $parentUser;

    public function setParentUser(User $parentUser)
    {
        $this->parentUser = $parentUser;
        return $this;
    }

    public function getParentUser()
    {
        return $this->parentUser;
    }

    /** --- TITLE (teachers only)
     * @ORM\Column(name="title", type="string", length=40, nullable=true)
     * @Assert\NotBlank(
     *     message="Title cannot be blank - this is how you will be referred to in your students' pages",
     *     groups={"teacher"}
     * )
     */
    private $title;

    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }

    public function getTitle()
    {
        return $this->title;
    }

    /** --- FIRSTNAME
     * @ORM\Column(name="firstname", type="string", length=30)
     * @Assert\NotBlank(
     *     message="First name cannot be blank",
     *     groups={"student", "teacher", "registration"}
     * )
     */
    private $firstname;

    public function setFirstname($firstname)
    {
        $this->firstname = $firstname;
        return $this;
    }

    public function getFirstname()
    {
        return $this->firstname;
    }

    /** --- SURNAME
     * @ORM\Column(name="surname", type="string", length=30)
     * @Assert\NotBlank(
     *     message="Surname cannot be blank",
     *     groups={"student", "teacher", "registration"}
     * )
     */
    private $surname;

    public function setSurname($surname)
    {
        $this->surname = $surname;
        return $this;
    }

    public function getSurname()
    {
        return $this->surname;
    }

    /** --- TYPE (teacher or student)
     * @ORM\Column(name="type", type="string", length=20)
     */
    private $type;

    public function setType($type)
    {
        $validTypes = ['teacher', 'student'];
        if (in_array($type, $validTypes)) {
            $this->type = $type;
        } else {
            $this->type = 'student'; // set to student in case of unexpected input
        }
        return $this;
    }

    public function getType()
    {
        return $this->type;
    }

    /** --- PARENT'S FIRSTNAME (if under thirteen)
     * @ORM\Column(name="parent_firstname", type="string", length=30, nullable=true)
     */
    private $parentFirstname;

    public function setParentFirstname($parentFirstname)
    {
        $this->parentFirstname = $parentFirstname;
        return $this;
    }

    public function getParentFirstname()
    {
        return $this->parentFirstname;
    }

    /** --- PARENT'S SURNAME (if under thirteen)
     * @ORM\Column(name="parent_surname", type="string", length=30, nullable=true)
     */
    private $parentSurname;

    public function setParentSurname($parentSurname)
    {
        $this->parentSurname = $parentSurname;
        return $this;
    }

    public function getParentSurname()
    {
        return $this->parentSurname;
    }

    /** --- SCHOOL_URN
     * @ORM\Column(name="school_urn", type="string", length=30, nullable=true)
     */
    private $schoolUrn;

    public function setSchoolUrn($schoolUrn)
    {
        $this->schoolUrn = $schoolUrn;
        return $this;
    }

    public function getSchoolUrn()
    {
        return $this->schoolUrn;
    }

    /** --- SCHOOL_NAME (teachers only)
     * @ORM\Column(name="school_name", type="string", length=60, nullable=true)
     * @Assert\NotBlank(
     *     message="School name cannot be blank",
     *     groups={"teacher"}
     * )
     */
    private $schoolName;

    public function setSchoolName($schoolName)
    {
        $this->schoolName = $schoolName;
        return $this;
    }

    public function getSchoolName()
    {
        return $this->schoolName;
    }

    /** --- SCHOOL_POSTCODE (teachers only)
     * @ORM\Column(name="school_postcode", type="string", length=20, nullable=true)
     * @Assert\NotBlank(
     *     message="School postcode cannot be blank",
     *     groups={"teacher"}
     * )
     */
    private $schoolPostcode;

    public function setSchoolPostcode($schoolPostcode)
    {
        $this->schoolPostcode = $schoolPostcode;
        return $this;
    }

    public function getSchoolPostcode()
    {
        return $this->schoolPostcode;
    }

    /** --- DATE_OF_BIRTH (students only)
     * @ORM\Column(name="date_of_birth", type="date", nullable=true)
     */
    private $dateOfBirth;

    public function setDateOfBirth(\DateTime $dateOfBirth)
    {
        $this->dateOfBirth = $dateOfBirth;
        return $this;
    }

    public function getDateOfBirth()
    {
        return $this->dateOfBirth;
    }

    /** --- HOME_POSTCODE (students only)
     * @ORM\Column(name="home_postcode", type="string", length=20, nullable=true)
     */
    private $homePostcode;

    public function setHomePostcode($homePostcode)
    {
        $this->homePostcode = $homePostcode;
        return $this;
    }

    public function getHomePostcode()
    {
        return $this->homePostcode;
    }

    /** --- LOGS
     * @ORM\OneToMany(targetEntity="App\Entity\ActivityLog\ActivityLog", mappedBy="user")
     * @ORM\OrderBy({"date"="ASC"})
     */
    private $logs;

    public function addLog(ActivityLog $log)
    {
        $this->logs->add($log);
        return $this;
    }

    public function removeLog(ActivityLog $log)
    {
        $this->logs->removeElement($log);
        return $this;
    }

    public function getLogs()
    {
        return $this->logs;
    }

    /** --- FILES
     * @ORM\OneToMany(targetEntity="App\Entity\File\File", mappedBy="owner")
     * @ORM\OrderBy({"name"="ASC"})
     */
    private $files;

    public function addFile(File $file)
    {
        $this->files->add($file);
        return $this;
    }

    public function removeFile(File $file)
    {
        $this->files->removeElement($file);
        return $this;
    }

    public function getFiles()
    {
        return $this->files;
    }

    /** --- PROGRAMS (derivative property, subset of files)
     */
    public function getPrograms()
    {
        return $this->getFiles()->filter(function ($file) {
            return $file->isProgram();
        });
    }

    /** --- COURSES (teachers only, courses OWNED by this user)
     * @ORM\OneToMany(targetEntity="App\Entity\Course\Course", mappedBy="owner")
     * @ORM\OrderBy({"startDate"="DESC", "name"="ASC"})
     */
    private $courses;

    public function addCourse(Course $course)
    {
        $this->courses->add($course);
        return $this;
    }

    public function removeCourse(Course $course)
    {
        $this->courses->removeElement($course);
        return $this;
    }

    public function getCourses()
    {
        return $this->courses;
    }

    /* --- OPEN COURSES (derivative property, subset of courses)
     */
    public function getOpenCourses()
    {
        return $this->getCourses()->filter(function ($course) {
            return $course->getStatus() === 'open';
        });
    }

    /* --- CURRENT COURSES (derivative property, subset of courses)
     */
    public function getCurrentCourses()
    {
        return $this->getCourses()->filter(function ($course) {
            return $course->getStatus() !== 'archived';
        });
    }

    /* --- ARCHIVED COURSES (derivative property, subset of courses)
     */
    public function getArchivedCourses()
    {
        return $this->getCourses()->filter(function ($course) {
            return $course->getStatus() === 'archived';
        });
    }

    /* --- COURSES BY TYPE (derivative property, subset of courses)
     */
    public function getCoursesByType($courseType)
    {
        switch ($courseType) {
            case 'open':
                return $this->getOpnCourses();
            case 'current':
                return $this->getCurrentCourses();
            case 'archived':
                return $this->getArchivedCourses();
        }
    }

    /** --- SUBSCRIPTIONS (students only, courses this user is SUBSCRIBED to)
     * @ORM\ManyToMany(targetEntity="App\Entity\Course\Course", mappedBy="students")
     * @ORM\OrderBy({"startDate"="DESC", "name"="ASC"})
     */
    private $subscriptions;

    public function addSubscription(Course $course)
    {
        $this->subscriptions->add($course);
        return $this;
    }

    public function removeSubscription(Course $course)
    {
        $this->subscriptions->removeElement($course);
        return $this;
    }

    public function getSubscriptions($teacher = null)
    {
        return $this->subscriptions;
    }

    /* --- CURRENT SUBSCRIPTIONS (derivative property, subset of subscriptions)
     */
    public function getCurrentSubscriptions($teacher = null)
    {
        $currentSubscriptions = $this->getSubscriptions()->filter(function ($course) {
            return $course->getStatus() !== 'archived';
        });
        if ($teacher === null) {
            return $currentSubscriptions;
        }
        $filteredSubscriptions = array();
        foreach ($currentSubscriptions as $course) {
            if ($course->getOwner() === $teacher) {
                $filteredSubscriptions[] = $course;
            }
        }
        return $filteredSubscriptions;
    }

    /* --- ARCHIVED SUBSCRIPTIONS (derivative property, subset of subscriptions)
     */
    public function getArchivedSubscriptions($teacher = null)
    {
        $archivedSubscriptions = $this->getSubscriptions()->filter(function ($course) {
            return $course->getStatus() === 'archived';
        });
        if ($teacher === null) {
            return $archivedSubscriptions;
        }
        $filteredSubscriptions = array();
        foreach ($archivedSubscriptions as $course) {
            if ($course->getOwner() === $teacher) {
                $filteredSubscriptions[] = $course;
            }
        }
        return $filteredSubscriptions;
    }

    /* --- SUBSCRIPTIONS BY TYPE (derivative property, subset of subscriptions)
     */
    public function getSubscriptionsByType($subscriptionType, $teacher = null)
    {
        switch ($subscriptionType) {
            case 'current':
                return $this->getCurrentSubscriptions($teacher);
            case 'archived':
                return $this->getArchivedSubscriptions($teacher);
        }
    }

    /** --- FEEDBACK
     * @ORM\OneToMany(targetEntity="App\Entity\Feedback\Feedback", mappedBy="user")
     * @ORM\OrderBy({"date"="ASC"})
     */
    private $feedback;

    public function getFeedback()
    {
        return $this->feedback;
    }

    /* --- DISK USAGE (derivative property)
     */
    public function getDiskUsage($formatted = false, $precision = 2)
    {
        $bytes = 0;
        foreach ($this->getFiles() as $file) {
            $bytes += $file->getSize();
        }
        if ($formatted) {
            $kilobyte = 1024;
            $megabyte = $kilobyte * 1024;
            $gigabyte = $megabyte * 1024;
            $terabyte = $gigabyte * 1024;
            if (($bytes >= 0) && ($bytes < $kilobyte)) {
                $bytes = $bytes . ' B';
            } elseif (($bytes >= $kilobyte) && ($bytes < $megabyte)) {
                $bytes = round($bytes / $kilobyte, $precision) . ' KB';
            } elseif (($bytes >= $megabyte) && ($bytes < $gigabyte)) {
                $bytes = round($bytes / $megabyte, $precision) . ' MB';
            } elseif (($bytes >= $gigabyte) && ($bytes < $terabyte)) {
                $bytes = round($bytes / $gigabyte, $precision) . ' GB';
            } elseif ($bytes >= $terabyte) {
                $bytes = round($bytes / $terabyte, $precision) . ' TB';
            } else {
                $bytes = $bytes . ' B';
            }
        }
        return $bytes;
    }

    /* USER CAN VIEW (permissions check)
     */
    public function userCanView(User $user)
    {
        if ($this === $user) {
            return true; // everyone can view their own details
        }
        if ($this->getParentUser() === $user) {
            //return true; // teachers can view their dummy student accounts
        }
        foreach ($this->getSubscriptions() as $course) {
            if ($course->getOwner() === $user) {
                return true; // teachers can view students on their courses
            }
        }
        return false;
    }

    public function getFilesArray($status, $match = true)
    {
        $files = [];
        foreach ($this->getCourses() as $course) {
            $add = false;
            if ($match && $status == $course->getStatus()) {
                $add = true;
            }
            if (!$match && $status != $course->getStatus()) {
                $add = true;
            }
            if ($add) {
                foreach ($course->getLessons() as $lesson) {
                    foreach ($lesson->getFiles() as $file) {
                        $key = $course->getName() . ' > Lessons';
                        if (!array_key_exists($key, $files) or !in_array($file, $files[$key])) {
                            $files[$key][] = $file;
                        }
                    }
                }
                foreach ($course->getAssignments() as $assignment) {
                    foreach ($assignment->getTeacherFiles() as $file) {
                        $key = $course->getName() . ' > Assignments';
                        if (!array_key_exists($key, $files) or !in_array($file, $files[$key])) {
                            $files[$key][] = $file;
                        }
                    }
                }
            }
        }
        return $files;
    }

    public function getCurrentFilesArray()
    {
        return $this->getFilesArray('archived', false);
    }

    public function getArchivedFilesArray()
    {
        return $this->getFilesArray('archived', true);
    }

    /** --- METHODS NEEDED BY SYMFONY SECURITY SERVICE
     */
    public function getSalt()
    {
    }

    public function getRoles()
    {
        return array('ROLE_USER', 'ROLE_' . strtoupper($this->type));
    }

    public function eraseCredentials()
    {
    }
}
