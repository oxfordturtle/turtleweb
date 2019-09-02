<?php

namespace App\Model\CloneCourse;

use App\Entity\Course\Course;
use App\Entity\User\User;
use Symfony\Component\Validator\Constraints as Assert;

class CloneCourse
{
    /** --- constructor
     */
    public function __construct()
    {
        $this->setStartDate(new \DateTime('now'));
    }

    /** --- COURSE TO CLONE (original course)
     */
    private $courseToClone;

    public function getCourseToClone()
    {
        return $this->courseToClone;
    }

    public function setCourseToClone(Course $course)
    {
        $this->courseToClone = $course;
        return $this;
    }

    /** --- NAME (of new course)
     * @Assert\NotBlank(message="Course name cannot be blank")
     */
    private $name;

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /** --- START DATE (of new course)
     * @Assert\NotBlank(message="Start date cannot be blank")
     */
    private $startDate;

    public function getStartDate()
    {
        return $this->startDate;
    }

    public function setStartDate($startDate)
    {
        $this->startDate = $startDate;
        return $this;
    }
}
