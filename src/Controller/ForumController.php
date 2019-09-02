<?php

/** FORUM CONTROLLER
 *  pages for the forum (red) section when not logged in
 */
namespace App\Controller;

use App\Entity\File\File;
use App\Entity\User\CreateTeacherType;
use App\Entity\User\CreateStudentType;
use App\Entity\User\User;
use App\Entity\User\UserManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

// use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

/**
 * @Route("/forum", name="forum_")
 */
class ForumController extends AbstractController
{
    /** --- FORUM HOME PAGE
     * @Route("/", name="home")
     */
    public function home(Request $request): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute($this->getUser()->getType() . '_home');
        }

        return $this->render('forum/home.html.twig');
    }

    /** --- FETCH SCHOOL DATA FROM DfE USING URN (JSON response)
     * @Route(
     *     "/school/{urn}",
     *     requirements={"urn": "\d+"},
     *     name="school_from_urn")
     */
    public function school(Request $request, $urn = '000000'): JsonResponse
    {
        if ($urn === '000000') {
            $name = 'none';
            $postcode = 'none';
        } else {
            $name = 'not found';
            $postcode = 'not found';
        }

        $url = 'http://www.education.gov.uk/edubase/establishment/communications.xhtml?urn='.$urn;
        $page = file_get_contents($url);

        preg_match('/<title>(.*?)<\/title>/', $page, $matches);
        if ($matches) {
            $titleBits = explode(' - ', $matches[1]);
            $name = str_replace('&#039;', '\'', $titleBits[1]);
            $pattern = '/<th>Postcode<\/th><td class="underline"><div title="(.*?)"/';
            preg_match($pattern, $page, $matches);
            $postcode = $matches[1];
        }

        return $this->json(['name' => $name, 'postcode' => $postcode]);
    }

    /** --- TEACHER REGISTRATION PAGE
     * @Route("/teachers", name="teachers")
     */
    public function teachers(Request $request, UserManager $userManager): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute($this->getUser()->getType().'_home');
        }

        $teacher = new User('teacher');
        $options = array('urn_path' => $this->generateUrl('forum_school_from_urn'));
        $createTeacherForm = $this->createForm(CreateTeacherType::class, $teacher, $options);
        $createTeacherForm->handleRequest($request);

        if ($createTeacherForm->isSubmitted()) {
            if ($createTeacherForm->isValid()) {
                try {
                    $userManager->create($teacher);
                    $token = new UsernamePasswordToken($teacher, null, 'main', $teacher->getRoles());
                    $this->get('security.token_storage')->setToken($token);
                    return $this->redirectToRoute('teacher_home');
                } catch (\Exception $exception) {
                    $this->addFlash('error', $exception->getMessage());
                }
            } else {
                $this->addFlash('error', 'Your form has errors. Hover over the exclamation marks for details.');
            }
        }

        $twigs = array();
        $twigs['createTeacherForm'] = $createTeacherForm->createView();
        return $this->render('forum/teachers.html.twig', $twigs);
    }

    /** --- STUDENT REGISTRATION PAGE
     * @Route("/students", name="students")
     */
    public function students(Request $request, UserManager $userManager)
    {
        if ($this->getUser()) {
            return $this->redirectToRoute($this->getUser()->getType().'_home');
        }

        $student = new User('student');
        $options = array('urn_path' => $this->generateUrl('forum_school_from_urn'));
        $createStudentForm = $this->createForm(CreateStudentType::class, $student, $options);
        $createStudentForm->handleRequest($request);

        if ($createStudentForm->isSubmitted()) {
            if ($createStudentForm->isValid()) {
                try {
                    $userManager->create($student);
                    $token = new UsernamePasswordToken($student, null, 'main', $student->getRoles());
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
        $twigs['createStudentForm'] = $createStudentForm->createView();
        return $this->render('forum/students.html.twig', $twigs);
    }

    /** --- DOWNLOAD A FILE UPLOADED TO THE FORUM
     * @Route(
     *     "/download/{file}/{filename}",
     *     requirements={"file": "\d+"},
     *     name="download_file")
     */
    public function downloadFile(
        Request $request,
        File $file,
        ?string $filename = null
    ): BinaryFileResponse {
        if (!$file->userCanView($this->getUser())) {
            throw new AccessDeniedHttpException('You are not allowed to download this file.');
        }

        $response = new BinaryFileResponse($file->getPath());
        $contdisp = 'filename="'.$file->getName().'.'.$file->getExt().'"';
        $contdisp = 'attachment;'.$contdisp;
        $response->headers->set('Content-Disposition', $contdisp);
        return $response;
    }

    /** --- VIEW A FILE (IN THE BROWSER) UPLOADED TO THE FORUM
     * @Route(
     *     "/view/{file}/{filename}",
     *     requirements={"file": "\d+"},
     *     name="view_file")
     */
    public function viewFile(
        Request $request,
        File $file,
        ?string $filename = null
    ): BinaryFileResponse {
        if (!$file->userCanView($this->getUser())) {
            throw new AccessDeniedHttpException('You are not allowed to download this file.');
        }

        $response = new BinaryFileResponse($file->getPath());
        $contdisp = 'filename="'.$file->getName().'.'.$file->getExt().'"';
        $response->headers->set('Content-Disposition', $contdisp);
        return $response;
    }
}
