<?php

/** DOWLOADS CONTROLLER
 *  links for downloading files
 */
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/downloads", name="downloads_")
 */
class DownloadsController extends AbstractController
{
    /** --- DOWNLOAD ANY FILE IN THE DOWNLOADS DIRECTORY
     * @Route("/{path}", name="file")
     */
    public function other(Request $request, string $path): BinaryFileResponse
    {
        $path = $this->getParameter('downloads_directory').$path;

        if (!file_exists($path)) {
            throw new NotFoundHttpException('File not found.');
        }

        return new BinaryFileResponse($path);
    }
}
