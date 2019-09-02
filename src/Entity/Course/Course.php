<?php

namespace App\Entity\Course;

use App\Entity\File\File;
use App\Entity\Folder\Folder;
use App\Entity\User\User;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="course")
 * @ORM\Entity
 */
class Course
{
    /** --- constructor and cloner
     */
    public function __construct(User $user)
    {
        $this->setOwner($user);
        $this->setStatus('open');
        $this->setPasscode();
        $this->setStartDate(new \DateTime('now'));
        $this->setClones(0);
        $this->students = new ArrayCollection();
        $this->folders = new ArrayCollection();
    }

    public function __clone()
    {
        $this->setStatus('open');
        $this->setPasscode();
        $this->setStartDate(new \DateTime('now'));
        $this->setClones(0);
        $this->students = new ArrayCollection();
        $this->folders = new ArrayCollection();
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

    /** --- NAME
     * @ORM\Column(name="name", type="string", length=255)
     * @Assert\NotBlank(message="Course name cannot be blank")
     */
    private $name;

    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    public function getName()
    {
        return $this->name;
    }

    /** --- STATUS (open, closed, or archived)
     * @ORM\Column(name="status", type="string", length=255)
     */
    private $status;

    public function setStatus($status)
    {
        $validStatuses = ['open', 'closed', 'archived'];
        if (in_array($status, $validStatuses)) {
            $this->status = $status;
        } else {
            $this->status = 'closed'; // set to closed in case of unexpected input
        }
        return $this;
    }

    public function getStatus()
    {
        return $this->status;
    }

    /** --- PASSCODE (students must enter this to subscribe)
     * @ORM\Column(name="passcode", type="string", length=255)
     */
    private $passcode;

    public function setPasscode()
    {
        // exclude I/l/1 and O/0 as these can be hard to distinguish
        $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz23456789';
        $length = 6;
        $passcode = '';
        for ($i = 0; $i < $length; $i++) {
            $passcode .= $chars[rand(0, strlen($chars)-1)];
        }
        $this->passcode = $passcode;
        return $this;
    }

    public function getPasscode()
    {
        return $this->passcode;
    }

    /** --- START DATE
     * @ORM\Column(name="start_date", type="date")
     * @Assert\NotBlank(message="Course start date cannot be blank")
     */
    private $startDate;

    public function setStartDate(\DateTime $startDate)
    {
        $this->startDate = $startDate;
        return $this;
    }

    public function getStartDate()
    {
        return $this->startDate;
    }

    /** --- PUBLIC (derivative property)
     */
    public function isPublic()
    {
        return $this->getOwner()->getUsername() === 'public';
    }

    /** --- DESCRIPTION
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    private $description;

    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }

    public function getDescription()
    {
        return $this->description;
    }

    /** --- CLONES (keep a count of the number of clones made)
     * @ORM\Column(name="clones", type="integer")
     */
    private $clones;

    public function setClones($count)
    {
        $this->clones = $count;
        return $this;
    }

    public function getClones()
    {
        return $this->clones;
    }

    /** --- OWNER
     * @ORM\ManyToOne(targetEntity="App\Entity\User\User", inversedBy="courses")
     */
    private $owner;

    public function setOwner(User $user)
    {
        $this->owner = $user;
        return $this;
    }

    public function getOwner()
    {
        return $this->owner;
    }

    /** --- STUDENTS (ArrayCollection)
     * @ORM\ManyToMany(targetEntity="App\Entity\User\User", inversedBy="subscriptions")
     * @ORM\OrderBy({"surname"="ASC", "firstname"="ASC"})
     */
    private $students;

    public function addStudent(User $user)
    {
        $this->students->add($user);
        return $this;
    }

    public function removeStudent(User $user)
    {
        $this->students->removeElement($user);
        return $this;
    }

    public function getStudents()
    {
        return $this->students;
    }

    /** --- FOLDERS
     * @ORM\OneToMany(targetEntity="App\Entity\Folder\Folder", mappedBy="course")
     * @ORM\OrderBy({"type"="DESC", "number"="ASC"})
     */
    private $folders;

    public function addFolder(Folder $folder)
    {
        $this->folders->add($folder);
        return $this;
    }

    public function removeFolder(Folder $folder)
    {
        $this->folders->removeElement($folder);
        return $this;
    }

    public function getFolders()
    {
        return $this->folders;
    }

    /** --- LESSONS AND ASSIGNMENTS (derivative properties, subsets of folders)
     */
    public function getLessons()
    {
        return $this->getFolders()->filter(function (Folder $folder) {
            return $folder->getType() === 'lesson';
        });
    }

    public function getAssignments()
    {
        return $this->getFolders()->filter(function (Folder $folder) {
            return $folder->getType() === 'assignment';
        });
    }

    public function getFoldersByType($type)
    {
        if ($type === 'lesson') {
            return $this->getLessons();
        }
        if ($type === 'assignment') {
            return $this->getAssignments();
        }
    }

    /** --- STUDENT SUBMISSIONS TOTAL (derivative property, submissions total from a given student)
     */
    public function getStudentSubmissionsTotal(User $student)
    {
        $total = 0;
        foreach ($this->getAssignments()->toArray() as $assignment) {
            if ($assignment->getStudentSubmission($student)) {
                $total += 1;
            }
        }
        return $total;
    }

    /** --- FILES ARRAY (associative array of folders with their files, for tables and dropdown menus)
     */
    public function getFilesArray()
    {
        $files = [];
        foreach ($this->getLessons() as $lesson) {
            if (count($lesson->getTeacherFiles()) > 0) {
                $key = ucfirst($lesson->getType()) . ' ' . $lesson->getNumber() . ': ';
                $key .= $lesson->getName();
                $files[$key] = $lesson->getFiles();
            }
        }
        foreach ($this->getAssignments() as $assignment) {
            if (count($assignment->getTeacherFiles()) > 0) {
                $key = ucfirst($assignment->getType()) . ' ' . $assignment->getNumber() . ': ';
                $key .= $assignment->getName();
                $files[$key] = $assignment->getFiles();
            }
        }
        return $files;
    }

    /** --- USER CAN VIEW (permissions check)
     */
    public function userCanView(User $user)
    {
        if ($this->getOwner() === $user) {
            return true; // teachers can view their own courses
        }
        if ($this->getOwner()->getUsername() === 'public') {
            return true; // everyone can view public courses
        }
        if (in_array($this, $user->getSubscriptions())) {
            return true; // students can view courses they're subscribed to
        }
        return false;
    }
}
