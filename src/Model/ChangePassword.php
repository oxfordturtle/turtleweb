<?php

namespace App\Model;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Security\Core\Validator\Constraints as SecurityAssert;

/**
 * Model for the change password form type.
 */
class ChangePassword
{
  /**
   * The old (current) password.
   *
   * @var string
   * @SecurityAssert\UserPassword(message="Wrong value for your current password.")
   */
  private $oldPassword;

  /**
   * The new password.
   *
   * @var string
   * @Assert\NotBlank(message="Password cannot be blank.")
   */
  private $newPassword;

  /**
   * Get the old password.
   *
   * @return string|null
   */
  public function getOldPassword(): ?string
  {
    return $this->oldPassword;
  }

  /**
   * Set the old password.
   *
   * @param string
   * @return self
   */
  public function setOldPassword(string $password): self
  {
    $this->oldPassword = $password;
    return $this;
  }

  /**
   * Get the new password.
   *
   * @return string|null
   */
  public function getNewPassword(): ?string
  {
    return $this->newPassword;
  }

  /**
   * Set the new password.
   *
   * @param string
   * @return self
   */
  public function setNewPassword(string $password): self
  {
    $this->newPassword = $password;
    return $this;
  }
}
