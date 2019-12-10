<?php

namespace App\Model;

use Symfony\Component\Validator\Constraints as Assert;

class EmailCredentials
{
    /** --- EMAIL
     * @Assert\NotBlank(message="Email address cannot be blank")
     * @Assert\Email(message="This is not a valid email address")
     */
    private $email;

    public function getEmail()
    {
        return $this->email;
    }

    public function setEmail($email)
    {
        $this->email = $email;
        return $this;
    }
}
