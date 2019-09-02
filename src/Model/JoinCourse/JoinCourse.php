<?php

namespace App\Model\JoinCourse;

use Symfony\Component\Validator\Constraints as Assert;

class JoinCourse
{
    /** --- COURSE PASSCODE (actually passcode with course id at the end, to ensure uniqueness)
     * @Assert\NotBlank(message="Passcode cannot be blank")
     */
    private $passcode;

    public function getPasscode()
    {
        return $this->passcode;
    }

    public function setPasscode($passcode)
    {
        $this->passcode = $passcode;
        return $this;
    }
}
