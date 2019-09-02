<?php

namespace App\Entity\ActivityLog;

use App\Entity\User\User;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="activity_log")
 * @ORM\Entity
 */
class ActivityLog
{

    /** --- constructor
     */
    public function __construct(User $user, $action, $entity1 = null, $entity2 = null)
    {
        $this->setUser($user);
        $this->setAction($action);
        if (isset($entity1)) {
            $this->setEntity1($entity1);
        }
        if (isset($entity2)) {
            $this->setEntity2($entity2);
        }
        $this->setDate();
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

    /** --- USER
     * @ORM\ManyToOne(targetEntity="App\Entity\User\User", inversedBy="logs")
     */
    private $user;

    public function getUser()
    {
        return $this->user;
    }

    public function setUser(User $user)
    {
        $this->user = $user;
        return $this;
    }

    /** --- ACTION
     * @ORM\Column(name="action", type="string", length=255)
     */
    private $action;

    public function getAction()
    {
        return $this->action;
    }

    public function setAction($action)
    {
        $this->action = $action;
        return $this;
    }

    /** --- ENTITY1 (id of some related entity, depending on action)
     * @ORM\Column(name="entity1", type="integer", nullable=true)
     */
    private $entity1;

    public function getEntity1()
    {
        return $this->entity1;
    }

    public function setEntity1($entity1)
    {
        $this->entity1 = $entity1;
        return $this;
    }

    /** --- ENTITY2 (id of some other related entity, depending on action)
     * @ORM\Column(name="entity2", type="integer", nullable=true)
     */
    private $entity2;

    public function getEntity2()
    {
        return $this->entity2;
    }

    public function setEntity2($entity2)
    {
        $this->entity2 = $entity2;
        return $this;
    }

    /** --- DATE
     * @ORM\Column(name="date", type="datetime")
     */
    private $date;

    public function getDate()
    {
        return $this->date;
    }

    public function setDate()
    {
        $this->date = new \DateTime('now');
        return $this;
    }
}
