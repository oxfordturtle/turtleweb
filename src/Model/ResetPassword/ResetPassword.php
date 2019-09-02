<?php

namespace App\Model\ResetPassword;

use Symfony\Component\Validator\Constraints as Assert;

class ResetPassword
{
    /** --- PASSWORD
     * @Assert\NotBlank(message="Password cannot be blank")
     */
    private $password;

    public function getPassword()
    {
        return $this->password;
    }

    public function setPassword($password)
    {
        $this->password = $password;
        return $this;
    }
}
