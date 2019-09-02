<?php

/** TEACHER CONTROLLER
 *  the forum (red) section when logged in as a teacher
 */

namespace App\Controller\Teacher;

use App\Entity\File\File;
use App\Entity\File\FileManager;
use App\Entity\File\EditFileType;
use App\Entity\Folder\Folder;
use App\Entity\User\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/teacher", name="teacher_")
 */
class FilesController extends AbstractController
{
    /** --- DELETE FILE (pseudo-form, called from file page)
     * @Route(
     *     "/delete/file/{file}",
     *     requirements={"file": "\d+"},
     *     name="delete_file",
     *     methods={"POST"}
     * )
     */
    public function deleteFile(Request $request, FileManager $fileManager, File $file): Response
    {
        try {
            $fileManager->delete($file, $this->getUser());
            $this->addFlash('notice', '"'.$file->getName().'" has been deleted.');
            return $this->redirectToRoute('teacher_files');
        } catch (\Exception $exception) {
            $this->addFlash('error', $exception->getMessage());
            $params = [
                'file' => $file->getId(),
                'tab' => 'delete'
            ];
            return $this->redirectToRoute('teacher_file', $params);
        }
    }

    /** --- FILE PAGE
     * @Route(
     *     "/files/{file}/{tab}",
     *     requirements={"file": "\d+", "tab": "help|about|edit|delete"},
     *     name="file")
     */
    public function viewFile(
        Request $request,
        FileManager $fileManager,
        File $file,
        $tab = 'about'
    ): Response {
        if (!$file->userCanView($this->getUser())) {
            throw new AccessDeniedHttpException('You do not have permission to view this file.');
        }

        $editFileForm = $this->createForm(EditFileType::class, $file);
        $editFileForm->handleRequest($request);

        if ($editFileForm->isSubmitted()) {
            $tab = 'edit';
            if ($editFileForm->isValid()) {
                try {
                    $fileManager->edit($file, $this->getUser());
                    $this->addFlash('notice', 'File details have been updated.');
                } catch (\Exception $exception) {
                    $this->addFlash('error', $exception->getMessage());
                }
            } else {
                $this->addFlash('error', 'Your form has errors. Hover over the exclamation marks for details.');
            }
        }

        $twigs = [];
        $twigs['tab'] = $tab;
        $twigs['file'] = $file;
        $twigs['editFileForm'] = $editFileForm->createView();
        return $this->render('teacher/file.html.twig', $twigs);
    }

    /** --- FILES PAGE
     * @Route(
     *     "/files/{tab}",
     *     requirements={"tab": "help|current|public|archived"},
     *     defaults={"tab": "current"},
     *     name="files")
     */
    public function viewFiles(Request $request, $tab = 'current'): Response
    {
        $public = $this->getDoctrine()->getRepository(User::class)->findOneByUsername('public');

        $twigs = [];
        $twigs['tab'] = $tab;
        $twigs['public'] = $public;
        return $this->render('teacher/files.html.twig', $twigs);
    }
}
