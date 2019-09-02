<?php

/** TEACHER CONTROLLER
 *  the forum (red) section when logged in as a teacher
 */

namespace App\Controller\Teacher;

use App\Entity\User\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/teacher", name="teacher_")
 */
class StudentsController extends AbstractController
{
    /** --- STUDENT PAGE
     * @Route(
     *     "/students/{studentId}/{tab}",
     *     requirements={"studentId": "\d+", "tab": "help|about|current|archived"},
     *     name="student",
     * )
     */
    public function student(Request $request, User $student, $tab = 'about'): Response
    {
        if ($student->getType() !== 'student') {
            throw new NotFoundHttpException('This student does not exist.');
        }

        if (!$student->userCanView($this->getUser())) {
            throw new AccessDeniedHttpException('You do not have permission to view this student\'s information.');
        }

        $twigs = [];
        $twigs['tab'] = $tab;
        $twigs['student'] = $student;
        return $this->render('teacher/student.html.twig', $twigs);
    }

    /** --- STUDENTS PAGE
     * @Route(
     *     "/students/{tab}",
     *     requirements={"tab": "help|current|passcodes|archived"},
     *     name="students")
     */
    public function studentsAction(Request $request, $tab = 'current'): Response
    {
        $twigs = [];
        $twigs['tab'] = $tab;
        return $this->render('teacher/students.html.twig', $twigs);
    }
}
