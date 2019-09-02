<?php

namespace App\Model\ChangePassword;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Security\Core\Validator\Constraints as SecurityAssert;

class ChangePassword
{
    /** --- OLD PASSWORD
     * @SecurityAssert\UserPassword(message="Wrong value for your current password")
    */
    private $oldPassword;

    public function getOldPassword()
    {
        return $this->oldPassword;
    }

    public function setOldPassword($password)
    {
        $this->oldPassword = $password;
        return $this;
    }

    /** --- NEW PASSWORD
     * @Assert\NotBlank(message="Password cannot be blank")
     */
    private $newPassword;

    public function getNewPassword()
    {
        return $this->newPassword;
    }

    public function setNewPassword($password)
    {
        $this->newPassword = $password;
        return $this;
    }
}
