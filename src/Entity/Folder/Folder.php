<?php

namespace App\Entity\Folder;

use App\Entity\Course\Course;
use App\Entity\File\File;
use App\Entity\User\User;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="folder")
 * @ORM\Entity
 */
class Folder
{
    /** --- constructor
     */
    public function __construct(Course $course, $type)
    {
        $this->setCourse($course);
        $this->setType($type);
        $this->setNumber($course->getFoldersByType($type)->count() + 1);
        $this->files = new ArrayCollection();
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

    /** --- TYPE (lesson or assignment)
     * @ORM\Column(name="type", type="string", length=255)
     */
    private $type;

    public function setType($type)
    {
        $validTypes = ['lesson', 'assignment'];
        if (in_array($type, $validTypes)) {
            $this->type = $type;
        } else {
            $this->type = 'lesson'; // set to lesson in case of unexpected input
        }
        return $this;
    }

    public function getType()
    {
        return $this->type;
    }

    /** --- NUMBER (for custom ordering)
     * @ORM\Column(name="number", type="integer")
     */
    private $number;

    public function setNumber($number)
    {
        $this->number = $number;
        return $this;
    }

    public function getNumber()
    {
        return $this->number;
    }

    /** --- NAME
     * @ORM\Column(name="name", type="string", length=255)
     * @Assert\NotBlank(message="Folder name cannot be blank")
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

    /** --- LONG NAME (derivative property)
     */
    private $longName;

    public function getLongName()
    {
        return ucfirst($this->getType()) . ' ' . $this->getNumber() . ': ' . $this->getName();
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

    /** --- COURSE
     * @ORM\ManyToOne(targetEntity="App\Entity\Course\Course", inversedBy="folders")
     */
    private $course;

    public function setCourse(Course $course)
    {
        $this->course = $course;
        return $this;
    }

    public function getCourse()
    {
        return $this->course;
    }

    /** --- FILES (ArrayCollection)
     * @ORM\ManyToMany(targetEntity="App\Entity\File\File", inversedBy="folders")
     * @ORM\OrderBy({"name"="ASC"})
     */
    private $files;

    public function addFile(File $file)
    {
        $this->files->add($file);
    }

    public function removeFile(File $file)
    {
        $this->files->removeElement($file);
    }

    public function getFiles()
    {
        return $this->files;
    }

    /** --- TEACHER FILES (derivative property, subset of files)
     */
    public function getTeacherFiles()
    {
        return $this->getFiles()->filter(function (File $file) {
            return $file->getOwner()->getType() === 'teacher';
        });
    }

    /** --- SUBMISSIONS (derivative property, subset of files)
     */
    public function getSubmissions()
    {
        return $this->getFiles()->filter(function (File $file) {
            return $file->getOwner()->getType() === 'student';
        });
    }

    /** --- STUDENT SUBMISSION (submission from a given student)
     */
    public function getStudentSubmission(User $user)
    {
        foreach ($this->getSubmissions() as $submission) {
            if ($submission->getOwner() === $user) {
                return $submission;
            }
        }
    }

    /** --- USER CAN VIEW (permissions check)
     */
    public function userCanView(User $user)
    {
        if ($this->getCourse()->getOwner() === $user) {
            return true; // teachers can view folders from their own courses
        }
        if ($this->getCourse()->getOwner()->getUsername() === 'public') {
            return true; // everyone can view folders from public courses
        }
        if ($user->getSubscriptions()->contains($this->getCourse())) {
            return true; // students can view folders of courses they are subscribed to
        }
        return false;
    }
}
