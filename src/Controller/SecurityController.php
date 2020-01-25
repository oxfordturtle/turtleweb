<?php

namespace App\Controller;

use App\Entity\User\User;
use App\Entity\User\UserManager;
use App\Entity\User\CreateUserType;
use App\Model\EmailCredentials\EmailCredentials;
use App\Model\EmailCredentials\EmailCredentialsType;
use App\Model\ResetPasword\ResetPasword;
use App\Model\ResetPasword\ResetPaswordType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

/**
 * @Route("/security", name="security_")
 */
class SecurityController extends AbstractController
{
    /** --- LOGIN FORM (fragment, post with AJAX)
     * @Route("/login", name="login")
     */
    public function login(Request $request, AuthenticationUtils $authenticationUtils): Response
    {
        $twigs = [];
        $twigs['last_username'] = $authenticationUtils->getLastUsername();
        // $twigs['error'] = $authenticationUtils->getLastAuthenticationError();

        return $this->render('_includes/forms/login.html.twig', $twigs);
    }

    /** --- REGISTRATION FORM (fragment, post with AJAX)
     * @Route("/register", name="register")
     */
    public function register(Request $request, UserManager $userManager)
    {
        $user = new User('student');
        $createUserForm = $this->createForm(CreateUserType::class, $user);

        $createUserForm->handleRequest($request);
        if ($createUserForm->isSubmitted()) {
            if ($createUserForm->isValid()) {
                try {
                    $userManager->create($user);
                    $token = new UsernamePasswordToken($user, null, 'main', $user->getRoles());
                    $this->get('security.token_storage')->setToken($token);
                    return $this->redirectToRoute('student_home');
                } catch (\Exception $exception) {
                    $this->addFlash('error', $exception->getMessage());
                }
            } else {
                $this->addFlash('error', 'Your form has errors. Hover over the exclamation marks for details.');
            }
        }

        $twigs = array();
        $twigs['createUserForm'] = $createUserForm->createView();
        return $this->render('_includes/forms/register.html.twig', $twigs);
    }

    /** --- LOGOUT
     * @Route("/logout", name="logout")
     */
    public function logout()
    {
        // this request is handled automatically by the symfony security service
    }

    /** --- EMAIL USER CREDENTIALS FORM (fragment, post with AJAX)
     * @Route("/email_credentials", name="email_credentials", methods={"GET"})
     */
    public function emailCredentials(Request $request): Response
    {
        $data = new EmailCredentials();
        $emailCredentialsForm = $this->createForm(EmailCredentialsType::class, $data, [
            'action' => $this->generateUrl('security_email_credentials_post'),
            'attr' => array('data-action' => 'email-credentials')
        ]);

        $twigs = [];
        $twigs['form'] = $emailCredentialsForm->createView();
        return $this->render('_includes/forms/email_credentials.html.twig', $twigs);
    }

    /** --- EMAIL USER CREDENTIALS POST ACTION (JSON response)
     * @Route("/email_credentials_post", name="email_credentials_post", methods={"POST"})
     */
    public function emailCredentialsPost(Request $request, UserManager $userManager): JsonResponse
    {
        $data = new EmailCredentials();
        $emailCredentialsForm = $this->createForm(EmailCredentialsType::class, $data, [
            'action' => $this->generateUrl('security_email_credentials_post'),
            'attr' => array('data-action' => 'email-credentials')
        ]);
        $emailCredentialsForm->handleRequest($request);

        if ($emailCredentialsForm->isValid()) {
            $user = $this->getDoctrine()->getRepository(User::class)->findOneByEmail($data->getEmail());
            if ($user) {
                $userManager->emailCredentials($user);
                return $this->json(['success' => true]);
            } else {
                $emailCredentialsForm->get('email')->addError(new FormError('Email address not found'));
                return $this->json([
                    'success' => false,
                    'form' => $this->renderView('_includes/forms/email_credentials.html.twig', [
                        'form' => $emailCredentialsForm->createView()
                    ])
                ]);
            }
        } else {
            return $this->json([
                'success' => false,
                'form' => $this->renderView('_includes/forms/email_credentials.html.twig', [
                    'form' => $emailCredentialsForm->createView()
                ])
            ]);
        }
    }

    /** --- RESET PASSWORD PAGE
     * @Route("/reset/{user}/{resetToken}", requirements={"user": "\d+"}, name="reset_password")
     */
    public function resetPasswordAction(Request $request, User $user, $resetToken)
    {
        if ($resetToken !== $user->getResetToken()) {
            throw new NotFoundHttpException('Page not found.');
        }

        $now = new \DateTime();
        if ($now->diff($user->getResetTokenDate())->d > 1) {
            throw new NotFoundHttpException('This link has expired.');
        }

        $data = new ResetPassword();
        $resetPasswordForm = $this->createForm(ResetPasswordType::class, $data);
        $resetPasswordForm->handleRequest($request);

        if ($resetPasswordForm->isSubmitted() && $resetPasswordForm->isValid()) {
            $userManager->changePassword($data->getPassword(), $user);
        }

        $twigs = [];
        $twigs['resetPasswordForm'] = $resetPasswordForm->createView();
        return $this->render('forum/reset_password.html.twig', $twigs);
    }
}
