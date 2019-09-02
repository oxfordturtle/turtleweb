<?php

/** STUDENT CONTROLLER
 *  the forum (red) section when logged in as a student
 */
namespace App\Controller\Student;

use App\Entity\File\File;
use App\Entity\File\FileManager;
use App\Entity\File\CreateFileType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/student", name="student_")
 */
class FilesController extends AbstractController
{

    /** --- DELETE FILE (pseudo-form, called from file page)
     * @Route(
     *     "/delete/file/{file}",
     *     requirements={"file": "\d+"},
     *     name="delete_file",
     *     methods={"POST"})
     */
    public function delete(Request $request, FileManager $fileManager, File $file): Response
    {
        try {
            $fileManager->delete($file, $this->getUser());
            $this->addFlash('notice', 'File "'.$file->getName().'" has been deleted.');
            return $this->redirectToRoute('student_files');
        } catch (\Exception $exception) {
            $this->addFlash('error', $exception->getMessage());
            $params = array('file' => $file->getId(), 'tab' => 'delete');
            return $this->redirectToRoute('student_file', $params);
        }
    }

    /** --- FILE PAGE
     * @Route(
     *     "/files/{file}/{tab}",
     *     requirements={"tab": "about|delete", "file": "\d+"},
     *     name="file")
     */
    public function viewFile(Request $request, File $file, $tab = 'about'): Response
    {
        if (!$file->userCanView($this->getUser())) {
            throw new AccessDeniedHttpException('You are not allowed to view the details of this file.');
        }

        $twigs = [];
        $twigs['tab'] = $tab;
        $twigs['file'] = $file;
        return $this->render('student/file.html.twig', $twigs);
    }

    /** --- FILES PAGE
     * @Route("/files/{tab}", requirements={"tab": "program"}, name="files")
     */
    public function filesAction(
        Request $request,
        FileManager $fileManager,
        $tab = 'program'
    ): Response {
        $newProgram = new File($this->getUser());
        $createProgramForm = $this->createForm(CreateFileType::class, $newProgram);
        $createProgramForm->handleRequest($request);

        if ($createProgramForm->isSubmitted()) {
            $tab = 'program';
            if ($createProgramForm->isValid()) {
                try {
                    $fileManager->create($newProgram, $this->getUser());
                    $this->addFlash('notice', 'Program "' . $newProgram->getName() . '" has been uploaded.');
                } catch (\Exception $exception) {
                    $this->addFlash('error', $exception->getMessage());
                }
            } else {
                $this->addFlash('error', 'Your form has errors. Hover over the exclamation marks for details.');
            }
        }

        $twigs = [];
        $twigs['tab'] = $tab;
        $twigs['createProgramForm'] = $createProgramForm->createView();
        return $this->render('student/files.html.twig', $twigs);
    }
}
