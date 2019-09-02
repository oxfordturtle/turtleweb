<?php

/** STUDENT CONTROLLER
 *  the forum (red) section when logged in as a student
 */
namespace App\Controller\Student;

use App\Entity\User\EditTeacherDetailsType;
use App\Entity\User\EditStudentDetailsType;
use App\Entity\User\UserManager;
use App\Model\ChangePassword\ChangePassword;
use App\Model\ChangePassword\ChangePasswordType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/student", name="student_")
 */
class StudentController extends AbstractController
{
    /** --- STUDENT AREA HOME PAGE
     * @Route("/{tab}", requirements={"tab": "about"}, name="home")
     */
    public function home(Request $request, $tab = 'about'): Response
    {
        $twigs = array();
        $twigs['tab'] = $tab;
        return $this->render('student/home.html.twig', $twigs);
    }

    /** --- DELETE ACCOUNT (pseudo-form, called from account page)
     * @Route("/delete", name="delete_account", methods={"POST"})
     */
    public function deleteAccount(Request $request, UserManager $userManager): Response
    {
        if ($this->getUser()->getParentUser()) {
            $user = $this->getUser()->getParentUser();
        } else {
            $user = $this->getUser();
        }

        try {
            $userManager->delete($user);
            $this->addFlash('notice', 'Your account has been deleted. You can create a new account again at any time. If you have any suggestions for how this web forum could be improved, please get in touch.');
            $this->get('security.token_storage')->setToken(null);
            return $this->redirectToRoute('forum_home');
        } catch (\Exception $exception) {
            $this->addFlash('error', $exception->getMessage());
            return $this->redirectToRoute('student_account', array('tab' => 'delete'));
        }
    }

    /** --- ACCOUNT PAGE
     * @Route("/account/{tab}", requirements={"tab": "details|edit|password|delete"}, name="account")
     */
    public function accountAction(
        Request $request,
        UserManager $userManager,
        $tab = 'details'
    ): Response {
        if ($this->getUser()->getParentUser()) {
            $user = $this->getUser()->getParentUser();
        } else {
            $user = $this->getUser();
        }

        $type = $user->getType();
        $options = array('urn_path' => $this->generateUrl('forum_school_from_urn'));
        switch ($type) {
            case 'teacher':
                $editDetailsForm = $this->createForm(EditTeacherDetailsType::class, $user, $options);
                break;
            case 'student':
                $editDetailsForm = $this->createForm(EditStudentDetailsType::class, $user, $options);
                break;
        }
        $editDetailsForm->handleRequest($request);

        if ($editDetailsForm->isSubmitted()) {
            $tab = 'edit';
            if ($editDetailsForm->isValid()) {
                try {
                    $userManager->edit($user);
                    $this->addFlash('notice', 'Your details have been updated.');
                } catch (\Exception $exception) {
                    $this->addFlash('error', $exception->getMessage());
                }
            } else {
                $this->addFlash('error', 'Your form has errors. Hover over the exclamation marks for details.');
            }
        }

        $changePassword = new ChangePassword();
        $changePasswordForm = $this->createForm(ChangePasswordType::class, $changePassword);
        $changePasswordForm->handleRequest($request);

        if ($changePasswordForm->isSubmitted()) {
            $tab = 'password';
            if ($changePasswordForm->isValid()) {
                try {
                    $userManager->changePassword($changePassword->getNewPassword(), $user);
                    $this->addFlash('notice', 'Your password has been changed.');
                } catch (\Exception $exception) {
                    $this->addFlash('error', $exception->getMessage());
                }
            } else {
                $this->addFlash('error', 'Your form has errors. Hover over the exclamation marks for details.');
            }
        }

        $twigs = [];
        $twigs['tab'] = $tab;
        $twigs['user'] = $user;
        $twigs['editDetailsForm'] = $editDetailsForm->createView();
        $twigs['changePasswordForm'] = $changePasswordForm->createView();
        return $this->render('student/account.html.twig', $twigs);
    }
}
