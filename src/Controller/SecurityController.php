<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\EmailCredentials\EmailCredentialsType;
use App\Form\ResetPaswordType;
use App\Model\ResetPasword;
use App\Model\EmailCredentials;
use App\Service\UserManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

/**
 * Controller for security related routes (login, logout, register, forgot credentials).
 *
 * @Route("/")
 */
class SecurityController extends AbstractController
{
  /**
   * Route for signing in.
   *
   * @Route("/login", name="login")
   * @param Request $request
   * @param AuthenticationUtils $authenticationUtils
   * @return Response
   */
  public function login(Request $request, AuthenticationUtils $authenticationUtils): Response
  {
    // set the twig variables
    $twigs = [
      'last_username' => $authenticationUtils->getLastUsername(),
      'error' => $authenticationUtils->getLastAuthenticationError()
    ];

    // render and return the page
    return $this->render('pages/login.html.twig', $twigs);
  }

  /**
   * Route for signing out.
   *
   * @Route("/logout", name="logout")
   */
  public function logout()
  {
    // this request is handled automatically by the symfony security service
  }

  /**
   * Route for registering on the site.
   *
   * @Route("/register", name="register")
   * @param Request $request
   * @param UserManager $userManager
   * @return Response
   */
  public function register(Request $request, UserManager $userManager): Response
  {
    // set the twig variables
    $twigs = [];

    // render and return the page
    return $this->render('register.html.twig', $twigs);
  }

  /**
   * Route for requesting forgotten credentials.
   *
   * @Route("/forgot", name="forgot")
   * @param Request $request
   * @param UserManager $userManager
   * @return Response
   */
  public function forgot(Request $request, UserManager $userManager): Response
  {
    // create the email credentials form
    $emailCredentials = new EmailCredentials();
    $emailCredentialsForm = $this->createForm(EmailCredentialsType::class, $emailCredentials);

    // handle the email credentials form
    $emailCredentialsForm->handleRequest($request);
    if ($emailCredentialsForm->isSubmitted() && $emailCredentialsForm->isValid()) {
      $user = $this->userManager->getUserByEmail($emailCredentials->getEmail());
      if ($user) {
        $userManager->emailCredentials($user);
        $this->addFlash('success', 'An email has been sent to your address with further instructions.');
      } else {
        $emailCredentialsForm->get('email')->addError(new FormError('Email address not found'));
      }
    }

    // set the twig variables
    $twigs = [
      'emailCredentialsForm' => $emailCredentialsForm->createView()
    ];

    // render and return the page
    return $this->render('forgot.html.twig', $twigs);
  }

  /**
   * Route for resetting a password.
   *
   * @Route("/reset/{user}/{resetToken}", name="reset")
   * @param Request $request
   * @param UserManager $userManager
   * @param User $user
   * @param string $resetToken
   * @return Response
   */
  public function reset(
    Request $request,
    UserManager $userManager,
    User $user,
    string $resetToken
  ): Response {
    // check the reset token matches the user account
    if ($resetToken !== $user->getResetToken()) {
      throw new NotFoundHttpException('Page not found.');
    }

    // check the reset token isn't more than a day old
    $now = new \DateTime();
    if ($now->diff($user->getResetTokenDate())->d > 1) {
      throw new NotFoundHttpException('This link has expired.');
    }

    // create the reset password form
    $resetPassword = new ResetPassword();
    $resetPasswordForm = $this->createForm(ResetPasswordType::class, $resetPassword);

    // handle the reset password form
    $resetPasswordForm->handleRequest($request);
    if ($resetPasswordForm->isSubmitted() && $resetPasswordForm->isValid()) {
      $userManager->changePassword($user, $resetPassword->getPassword());
    }

    // set the twig variables
    $twigs = [
      'resetPasswordForm' => $resetPasswordForm->createView()
    ];

    // render and return the page
    return $this->render('forum/reset_password.html.twig', $twigs);
  }
}
