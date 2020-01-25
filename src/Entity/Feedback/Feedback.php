<?php

namespace App\Entity\Feedback;

use App\Entity\User\User;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table()
 * @ORM\Entity
 */
class Feedback
{
    /** --- constructor
     */
    public function __construct(User $user, string $type)
    {
        $this->user = $user;
        $this->type = $type;
        $this->date = new \DateTime('now');
    }

    /** --- ID
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    public function getId()
    {
        return $this->id;
    }

    /** --- USER
     * @ORM\ManyToOne(targetEntity="App\Entity\User\User", inversedBy="feedback")
     */
    private $user;

    public function getUser()
    {
        return $this->user;
    }

    /** --- TYPE
     * @ORM\Column(type="string", length=255)
     */
    private $type;

    public function getType()
    {
        return $this->type;
    }

    /** --- DATE
     * @ORM\Column(type="datetime")
     */
    private $date;

    public function getDate()
    {
        return $this->date;
    }

    /** --- SUBJECT
     * @ORM\Column(type="string", length=255)
     */
    private $subject;

    public function getSubject()
    {
        return $this->subject;
    }

    public function setSubject($subject)
    {
        $this->subject = $subject;
        return $this;
    }

    /** --- COMMENTS
     * @ORM\Column(type="text")
     */
    private $comments;

    public function getComments()
    {
        return $this->comments;
    }

    public function setComments($comments)
    {
        $this->comments = $comments;
        return $this;
    }
}
