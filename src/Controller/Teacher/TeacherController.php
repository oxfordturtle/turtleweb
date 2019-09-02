<?php

/** TEACHER CONTROLLER
 *  the forum (red) section when logged in as a teacher
 */
namespace App\Controller\Teacher;

use App\Entity\User\EditTeacherDetailsType;
use App\Entity\User\UserManager;
use App\Model\ChangePassword\ChangePassword;
use App\Model\ChangePassword\ChangePasswordType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/teacher", name="teacher_")
 */
class TeacherController extends AbstractController
{
    /** --- HOME PAGE
     * @Route("/{tab}", requirements={"tab": "help|about"}, name="home")
     */
    public function home(Request $request, $tab = 'about'): Response
    {
        $twigs = array();
        $twigs['tab'] = $tab;
        return $this->render('teacher/home.html.twig', $twigs);
    }

    /** --- DELETE ACCOUNT (pseudo-form, called from account page)
     * @Route("/delete", name="delete_account", methods={"POST"})
     */
    public function deleteAccount(Request $request, UserManager $userManager): Response
    {
        $teacher = $this->getUser();

        try {
            $userManager->delete($teacher);
            $this->addFlash('notice', 'Your account has been deleted. You can create a new account again at any time. If you have any suggestions for how this web forum could be improved, please get in touch.');
            $this->get('security.token_storage')->setToken(null);
            return $this->redirectToRoute('forum_home');
        } catch (\Exception $exception) {
            $this->addFlash('error', $exception->getMessage());
            return $this->redirectToRoute('teacher_account', array('tab' => 'delete'));
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
        $options = array('urn_path' => $this->generateUrl('forum_school_from_urn'));
        $editDetailsForm = $this->createForm(EditTeacherDetailsType::class, $this->getUser(), $options);
        $editDetailsForm->handleRequest($request);

        if ($editDetailsForm->isSubmitted()) {
            $tab = 'edit';
            if ($editDetailsForm->isValid()) {
                try {
                    $userManager->edit($this->getUser());
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
                    $userManager->changePassword($changePassword->getNewPassword(), $this->getUser());
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
        $twigs['editDetailsForm'] = $editDetailsForm->createView();
        $twigs['changePasswordForm'] = $changePasswordForm->createView();
        return $this->render('teacher/account.html.twig', $twigs);
    }
}
