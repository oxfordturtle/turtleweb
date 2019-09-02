<?php

/** STUDENT CONTROLLER
 *  the forum (red) section when logged in as a student
 */
namespace App\Controller\Student;

use App\Entity\Course\Course;
use App\Entity\Course\CourseManager;
use App\Entity\File\CreateFileType;
use App\Entity\File\File;
use App\Entity\File\FileManager;
use App\Entity\Folder\Folder;
use App\Entity\Folder\FolderManager;
use App\Model\AddFile\AddFile;
use App\Model\AddFile\AddFileType;
use App\Model\JoinCourse\JoinCourse;
use App\Model\JoinCourse\JoinCourseType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/student/courses", name="student_")
 */
class CoursesController extends AbstractController
{
    /** --- FOLDER PAGE
     * @Route(
     *     "/folders/{folder}/{tab}",
     *     requirements={"folder": "\d+", "tab": "file|submission|about"},
     *     name="folder"
     * )
     */
    public function folder(
        Request $request,
        FileManager $fileManager,
        FolderManager $folderManager,
        Folder $folder,
        $tab = 'file'
    ): Response {
        $newSubmission = new File($this->getUser());
        $createSubmissionForm = $this->createForm(CreateFileType::class, $newSubmission);
        $createSubmissionForm->handleRequest($request);

        if ($createSubmissionForm->isSubmitted()) {
            if ($createSubmissionForm->isValid()) {
                try {
                    $fileManager->createInFolder($newSubmission, $folder, $this->getUser());
                    $this->addFlash('notice', 'Program "'.$newSubmission->getName().'" has been submitted to this assignment.');
                } catch (\Exception $exception) {
                    $this->addFlash('error', $exception->getMessage());
                }
            } else {
                $this->addFlash('error', 'Your form has errors. Hover over the exclamation marks for details.');
            }
        }

        $addSubmission = new AddFile();
        $options = array('files' => $this->getUser()->getPrograms());
        $addSubmissionForm = $this->createForm(AddFileType::class, $addSubmission, $options);
        $addSubmissionForm->handleRequest($request);

        if ($addSubmissionForm->isSubmitted()) {
            if ($addSubmissionForm->isValid()) {
                try {
                    $program = $addSubmission->getFile();
                    $folderManager->addFile($folder, $program, $this->getUser());
                    $this->addFlash('notice', 'Program "'.$program->getName().'" has been submitted to this assignment.');
                } catch (\Exception $exception) {
                    $this->addFlash('error', $exception->getMessage());
                }
            } else {
                $this->addFlash('error', 'Your form has errors. Hover over the exclamation marks for details.');
            }
        }

        $twigs = [];
        $twigs['tab'] = $tab;
        $twigs['folder'] = $folder;
        $twigs['createSubmissionForm'] = $createSubmissionForm->createView();
        $twigs['addSubmissionForm'] = $addSubmissionForm->createView();
        return $this->render('student/folder.html.twig', $twigs);
    }

    /** --- COURSE PAGE
     * @Route(
     *     "/{course}/{tab}",
     *     requirements={"course": "\d+", "tab": "lesson|assignment|about"},
     *     name="course"
     * )
     */
    public function course(Request $request, Course $course, $tab = 'lesson')
    {
        $twigs = [];
        $twigs['tab'] = $tab;
        $twigs['course'] = $course;
        return $this->render('student/course.html.twig', $twigs);
    }

    /** --- COURSES PAGE
     * @Route("/{tab}", requirements={"tab": "current|archived"}, name="courses")
     */
    public function courses(Request $request, CourseManager $courseManager, $tab = 'current')
    {
        $joinCourse = new JoinCourse();
        $joinCourseForm = $this->createForm(JoinCourseType::class, $joinCourse);
        $joinCourseForm->handleRequest($request);

        if ($joinCourseForm->isSubmitted()) {
            $tab = 'current';
            if ($joinCourseForm->isValid()) {
                $courseId = substr($joinCourse->getPasscode(), 6);
                $passcode = substr($joinCourse->getPasscode(), 0, 6);
                $course = $this->getDoctrine()->getRepository(Course::class)->find($courseId);
                if (!$course || $course->getPasscode() != $passcode) {
                    $joinCourseForm->get('passcode')->addError(new FormError('Invalid passcode.'));
                    $this->addFlash('error', 'Your form has errors. Hover over the exclamation marks for details.');
                } else {
                    try {
                        $courseManager->addStudent($course, $this->getUser());
                        $this->addFlash('notice', 'You have been added to course "'.$course->getName().'".');
                    } catch (\Exception $exception) {
                        $this->addFlash('error', $exception->getMessage());
                    }
                }
            } else {
                $this->addFlash('error', 'Your form has errors. Hover over the exclamation marks for details.');
            }
        }

        $twigs = [];
        $twigs['tab'] = $tab;
        $twigs['joinCourseForm'] = $joinCourseForm->createView();
        return $this->render('student/courses.html.twig', $twigs);
    }
}
