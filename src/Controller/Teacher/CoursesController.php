<?php

/** TEACHER CONTROLLER
 *  the forum (red) section when logged in as a teacher
 */
namespace App\Controller\Teacher;

use App\Entity\Course\Course;
use App\Entity\Course\CourseManager;
use App\Entity\Course\CreateCourseType;
use App\Entity\Course\EditCourseType;
use App\Entity\File\File;
use App\Entity\File\FileManager;
use App\Entity\File\CreateFileType;
use App\Entity\Folder\Folder;
use App\Entity\Folder\FolderManager;
use App\Entity\Folder\CreateLessonType;
use App\Entity\Folder\CreateAssignmentType;
use App\Entity\Folder\EditFolderType;
use App\Entity\User\User;
use App\Model\AddFile\AddFile;
use App\Model\AddFile\AddCurrentFileType;
use App\Model\AddFile\AddPublicFileType;
use App\Model\CloneCourse\CloneCourse;
use App\Model\CloneCourse\CloneArchivedCourseType;
use App\Model\CloneCourse\CloneCurrentCourseType;
use App\Model\CloneCourse\ClonePublicCourseType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/teacher", name="teacher_")
 */
class CoursesController extends AbstractController
{
    /** --- DELETE FOLDER (pseudo-form, called from folder page)
     * @Route(
     *     "/delete/folder/{folder}",
     *     requirements={"folder": "\d+"},
     *     name="delete_folder",
     *     methods={"POST"}
     * )
     */
    public function deleteFolder(
        Request $request,
        FolderManager $folderManager,
        Folder $folder
    ): Response {
        try {
            $folderManager->delete($folder, $this->getUser());
            $this->addFlash('notice', '"'.$folder->getName().'" has been deleted.');
            $params = [
                'course' => $folder->getCourse()->getId(),
                'tab' => $folder->getType()
            ];
            return $this->redirectToRoute('teacher_course', $params);
        } catch (\Exception $exception) {
            $this->addFlash('error', $exception->getMessage());
            $params = [
                'folder' => $folder->getId(),
                'tab' => 'delete'
            ];
            return $this->redirectToRoute('teacher_folder', $params);
        }
    }

    /** --- REMOVE FILE FROM FOLDER (pseudo-form, called from folder page)
     * @Route(
     *     "/remove/file/{folder}/{file}",
     *     requirements={"folder": "\d+", "file": "\d+"},
     *     name="remove_file_from_folder"
     * )
     */
    public function removeFileFromFolder(
        Request $request,
        FolderManager $folderManager,
        Folder $folder,
        File $file
    ): Response {
        try {
            $folderManager->removeFile($folder, $file, $this->getUser());
            $this->addFlash('notice', '"'.$file->getName().'" has been removed from this folder.');
        } catch (\Exception $exception) {
            $this->addFlash('error', $exception->getMessage());
        }
        $params = [
            'folder' => $folder->getId(),
            'tab' => 'file'
        ];
        return $this->redirectToRoute('teacher_folder', $params);
    }

    /** --- FOLDER PAGE
     * @Route(
     *     "/courses/folders/{folder}/{tab}",
     *     requirements={"folder": "\d+", "tab": "help|file|submission|edit|delete"},
     *     name="folder")
     */
    public function folder(
        Request $request,
        FileManager $fileManager,
        FolderManager $folderManager,
        Folder $folder,
        $tab = 'file'
    ): Response {
        if (!$folder->userCanView($this->getUser())) {
            throw new AccessDeniedHttpException('You do not have permission to view this ' . $folder->getType() . '.');
        }

        $newFile = new File($this->getUser());
        $createFileForm = $this->createForm(CreateFileType::class, $newFile);
        $createFileForm->handleRequest($request);

        if ($createFileForm->isSubmitted()) {
            $tab = 'file';
            if ($createFileForm->isValid()) {
                try {
                    $fileManager->createInFolder($newFile, $folder, $this->getUser());
                    $this->addFlash('notice', '"'.$newFile->getName().'" has been uploaded and added to this '.$folder->getType().'.');
                } catch (\Exception $exception) {
                    $this->addFlash('error', $exception->getMessage());
                }
            } else {
                $this->addFlash('error', 'Your form has errors. Hover over the exclamation marks for details.');
            }
        }

        $addCurrentFile = new AddFile();
        $options = array('files' => $this->getUser()->getCurrentFilesArray());
        $addCurrentFileForm = $this->createForm(AddCurrentFileType::class, $addCurrentFile, $options);
        $addCurrentFileForm->handleRequest($request);

        if ($addCurrentFileForm->isSubmitted()) {
            $tab = 'file';
            if ($addCurrentFileForm->isValid()) {
                try {
                    $folderManager->addFile($folder, $addCurrentFile->getFile(), $this->getUser());
                    $this->addFlash('notice', '"'.$addCurrentFile->getFile()->getName().'" has been added to this '.$folder->getType().'.');
                } catch (\Exception $exception) {
                    $this->addFlash('error', $exception->getMessage());
                }
            } else {
                $this->addFlash('error', 'Your form has errors. Hover over the exclamation marks for details.');
            }
        }

        $public = $this->getDoctrine()->getRepository(User::class)->findOneByUsername('public');
        if ($public) {
            $addPublicFile = new AddFile();
            $options = ['files' => $public->getCurrentFilesArray()];
            $addPublicFileForm = $this->createForm(AddPublicFileType::class, $addPublicFile, $options);
            $addPublicFileForm->handleRequest($request);

            if ($addPublicFileForm->isSubmitted()) {
                $tab = 'file';
                if ($addPublicFileForm->isValid()) {
                    try {
                        $folderManager->addFile($folder, $addPublicFile->getFile(), $this->getUser());
                        $this->addFlash('notice', '"'.$addPublicFile->getFile->getName().'" has been added to this'.$folder->getType().'.');
                    } catch (\Exception $exception) {
                        $this->addFlash('error', $exception->getMessage());
                    }
                } else {
                    $this->addFlash('error', 'Your form has errors. Hover over the exclamation marks for details.');
                }
            }
        }

        $editFolderForm = $this->createForm(EditFolderType::class, $folder);
        $editFolderForm->handleRequest($request);

        if ($editFolderForm->isSubmitted()) {
            $tab = 'edit';
            if ($editFolderForm->isValid()) {
                try {
                    $folderManager->edit($folder, $this->getUser());
                    $this->addFlash('notice', ucfirst($folder->getType()).' details have been updated.');
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
        $twigs['public'] = $public;
        $twigs['createFileForm'] = $createFileForm->createView();
        $twigs['addCurrentFileForm'] = $addCurrentFileForm->createView();
        $twigs['editFolderForm'] = $editFolderForm->createView();
        if ($public) {
            $twigs['addPublicFileForm'] = $addPublicFileForm->createView();
        }
        return $this->render('teacher/folder.html.twig', $twigs);
    }

    /** --- DELETE COURSE (pseudo-form, called from course page)
     * @Route(
     *     "/delete/course/{course}",
     *     requirements={"course": "\d+"},
     *     name="delete_course",
     *     methods={"POST"}
     * )
     */
    public function deleteCourse(
        Request $request,
        CourseManager $courseManager,
        Course $course
    ): Response {
        try {
            $courseManager->delete($course, $this->getUser());
            $this->addFlash('notice', 'Course "'.$course->getName().'" has been deleted.');
            return $this->redirectToRoute('teacher_courses');
        } catch (\Exception $exception) {
            $this->addFlash('error', $exception->getMessage());
            $params = ['course' => $course->getId(), 'tab' => 'delete'];
            return $this->redirectToRoute('teacher_course', $params);
        }
    }

    /** --- COURSE PAGE
     * @Route(
     *     "/courses/{course}/{tab}",
     *     requirements={"course": "\d+", "tab": "help|lesson|assignment|student|edit|delete"},
     *     defaults={"tab": "lesson"},
     *     name="course")
     */
    public function course(
        Request $request,
        CourseManager $courseManager,
        FolderManager $folderManager,
        Course $course,
        $tab = 'lesson'
    ): Response {
        if (!$course->userCanView($this->getUser())) {
            throw new AccessDeniedHttpException('You do not have permission to view this course.');
        }

        $newLesson = new Folder($course, 'lesson');
        $createLessonForm = $this->createForm(CreateLessonType::class, $newLesson);
        $createLessonForm->handleRequest($request);

        if ($createLessonForm->isSubmitted()) {
            $tab = 'lesson';
            if ($createLessonForm->isValid()) {
                try {
                    $folderManager->create($newLesson, $this->getUser());
                    $this->addFlash('notice', 'Lesson "'.$newLesson->getName().'" has been created.');
                } catch (\Exception $exception) {
                    $this->addFlash('error', $exception->getMessage());
                }
            } else {
                $this->addFlash('Your form has errors. Hover over the exclamation marks for details.');
            }
        }

        $newAssignment = new Folder($course, 'assignment');
        $createAssignmentForm = $this->createForm(CreateAssignmentType::class, $newAssignment);
        $createAssignmentForm->handleRequest($request);

        if ($createAssignmentForm->isSubmitted()) {
            $tab = 'assignment';
            if ($createAssignmentForm->isValid()) {
                try {
                    $folderManager->create($newAssignment, $this->getUser());
                    $this->addFlash('notice', 'Assignment "'.$newAssignment->getName().'" has been created.');
                } catch (\Exception $exception) {
                    $this->addFlash('error', $exception->getMessage());
                }
            } else {
                $this->addFlash('error', 'Your form has errors. Hover over the exclamation marks for details.');
            }
        }

        $editCourseForm = $this->createForm(EditCourseType::class, $course);
        $editCourseForm->handleRequest($request);

        if ($editCourseForm->isSubmitted()) {
            $tab = 'edit';
            if ($editCourseForm->isValid()) {
                try {
                    $courseManager->edit($course, $this->getUser());
                    $this->addFlash('notice', 'Course details have been updated.');
                } catch (\Exception $exception) {
                    $this->addFlash('error', $exception->getMessage());
                }
            } else {
                $this->addFlash('error', 'Your form has errors. Hover over the exclamation marks for details.');
            }
        }

        $twigs = [];
        $twigs['tab'] = $tab;
        $twigs['course'] = $course;
        $twigs['createLessonForm'] = $createLessonForm->createView();
        $twigs['createAssignmentForm'] = $createAssignmentForm->createView();
        $twigs['editCourseForm'] = $editCourseForm->createView();
        return $this->render('teacher/course.html.twig', $twigs);
    }

    /** --- MOVE FOLDER (pseudo-form, called from courses page)
     * @Route(
     *     "/move/folder/{folder}/{direction}",
     *     requirements={"folder": "\d+", "direction": "up|down"},
     *     name="move_folder")
     */
    public function moveFolder(
        Request $request,
        FolderManager $folderManager,
        Folder $folder,
        $direction
    ): Response {
        if ($direction === 'up') {
            $difference = 1;
        } else {
            $difference = -1;
        }

        try {
            $folderManager->move($folder, $difference, $this->getUser());
        } catch (\Exception $exception) {
            $this->addFlash('error', $exception->getMessage());
        }

        $params = [
            'course' => $folder->getCourse()->getId(),
            'tab' => $folder->getType()
        ];
        return $this->redirectToRoute('teacher_course', $params);
    }

    /** --- COURSES PAGE
     * @Route(
     *     "/courses/{tab}",
     *     requirements={"tab": "help|current|public|archived"},
     *     name="courses")
     */
    public function courses(
        Request $request,
        CourseManager $courseManager,
        $tab = 'current'
    ): Response {
        $newCourse = new Course($this->getUser());
        $createCourseForm = $this->createForm(CreateCourseType::class, $newCourse);
        $createCourseForm->handleRequest($request);

        if ($createCourseForm->isSubmitted()) {
            $tab = 'current';
            if ($createCourseForm->isValid()) {
                try {
                    $courseManager->create($newCourse, $this->getUser());
                    $this->addFlash('notice', 'Course "' . $newCourse->getName() . '" has been created.');
                } catch (\Exception $exception) {
                    $this->addFlash('error', $exception->getMessage());
                }
            } else {
                $this->addFlash('notice', 'Your form has errors. Hover over the exclamation marks for details.');
            }
        }

        $cloneCurrentCourse = new CloneCourse();
        $options = ['courses' => $this->getUser()->getCurrentCourses()];
        $cloneCurrentCourseForm = $this->createForm(CloneCurrentCourseType::class, $cloneCurrentCourse, $options);
        $cloneCurrentCourseForm->handleRequest($request);

        if ($cloneCurrentCourseForm->isSubmitted()) {
            $tab = 'current';
            if ($cloneCurrentCourseForm->isValid()) {
                try {
                    $course = $cloneCurrentCourse->getCourseToClone();
                    $name = $cloneCurrentCourse->getName();
                    $startDate = $cloneCurrentCourse->getStartDate();
                    $courseManager->createClone($course, $name, $startDate, $this->getUser());
                    $this->addFlash('notice', 'Course "'.$course->getName().'" has been cloned as "'.$name.'".');
                } catch (\Exception $exception) {
                    $this->addFlash('error', $exception->getMessage());
                }
            } else {
                $this->addFlash('error', 'Your form has errors. Hover over the exclamation marks for details.');
            }
        }

        $cloneArchivedCourse = new CloneCourse();
        $options = ['courses' => $this->getUser()->getArchivedCourses()];
        $cloneArchivedCourseForm = $this->createForm(CloneArchivedCourseType::class, $cloneArchivedCourse, $options);
        $cloneArchivedCourseForm->handleRequest($request);

        if ($cloneArchivedCourseForm->isSubmitted()) {
            $tab = 'archived';
            if ($cloneArchivedCourseForm->isValid()) {
                try {
                    $course = $cloneArchivedCourse->getCourseToClone();
                    $name = $cloneArchivedCourse->getName();
                    $startDate = $cloneArchivedCourse->getStartDate();
                    $courseManager->createClone($course, $name, $startDate, $this->getUser());
                    $this->addFlash('notice', 'Course "'.$course->getName().'" has been cloned as "'.$name.'".');
                } catch (\Exception $exception) {
                    $this->addFlash('error', $exception->getMessage());
                }
            } else {
                $this->addFlash('error', 'Your form has errors. Hover over the exclamation marks for details.');
            }
        }

        $public = $this->getDoctrine()->getRepository(User::class)->findOneByUsername('public');
        if ($public) {
            $clonePublicCourse = new CloneCourse();
            $options = ['courses' => $public->getCurrentCourses()];
            $clonePublicCourseForm = $this->createForm(ClonePublicCourseType::class, $clonePublicCourse, $options);
            $clonePublicCourseForm->handleRequest($request);

            if ($clonePublicCourseForm->isSubmitted()) {
                $tab = 'public';
                if ($clonePublicCourseForm->isValid()) {
                    try {
                        $course = $clonePublicCourse->getCourseToClone();
                        $name = $clonePublicCourse->getName();
                        $startDate = $clonePublicCourse->getStartDate();
                        $courseManager->createClone($course, $name, $startDate, $this->getUser());
                        $this->addFlash('notice', 'Course "'.$course->getName().'" has been cloned as "'.$name.'".');
                    } catch (\Exception $exception) {
                        $this->addFlash('error', $exception->getMessage());
                    }
                } else {
                    $this->addFlash('error', 'Your form has errors. Hover over the exclamation marks for details.');
                }
            }
        }

        $twigs = [];
        $twigs['tab'] = $tab;
        $twigs['createCourseForm'] = $createCourseForm->createView();
        $twigs['cloneCurrentCourseForm'] = $cloneCurrentCourseForm->createView();
        $twigs['cloneArchivedCourseForm'] = $cloneArchivedCourseForm->createView();
        $twigs['public'] = $public;
        if ($public) {
            $twigs['clonePublicCourseForm'] = $clonePublicCourseForm->createView();
        }
        return $this->render('teacher/courses.html.twig', $twigs);
    }
}
