<?php

/** TURTLE CONTROLLER
 *  links for downloading turtle files
 */
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/turtle", name="turtle_")
 */
class TurtleController extends AbstractController
{
    /** --- TURTLE SYSTEM VERSION INFORMATION
     * @Route("/versions", name="versions")
     */
    public function versions(Request $request): BinaryFileResponse
    {
        $path = $this->getParameter('turtle_directory').'versions.json';

        if (!file_exists($path)) {
            throw new NotFoundHttpException('File '.$path.' not found.');
        }

        return new BinaryFileResponse($path);
    }

    /** --- TURTLE SYSTEM DOWNLOAD COUNT
     * @Route("/downloads", name="count")
     */
    public function count(Request $request): BinaryFileResponse
    {
        $path = $this->getParameter('turtle_directory').'downloads.json';

        if (!file_exists($path)) {
            throw new NotFoundHttpException('File '.$path.' not found.');
        }

        return new BinaryFileResponse($path);
    }

    /** --- DOWNLOAD TURTLE SYSTEM D
     * @Route("/d/{version}", name="tsd")
     */
    public function tsd(Request $request, string $version): BinaryFileResponse
    {
        $turtleDir = $this->getParameter('turtle_directory');
        $versions = json_decode(file_get_contents($turtleDir.'versions.json'), true);
        $filename = 'TurtleSystemD_'.$versions['D'][$version].'.exe';

        if (!file_exists($turtleDir.$filename)) {
            throw new NotFoundHttpException('File '.$filename.' not found.');
        }

        $count = json_decode(file_get_contents($turtleDir.'downloads.json'), true);
        $month = date('Y').'.'.date('m');
        if (!array_key_exists($month, $count)) {
            $count[$month] = [];
        }
        if (!array_key_exists('D'.$version, $count[$month])) {
            $count[$month]['D'.$version] = 1;
        } else {
            $count[$month]['D'.$version] += 1;
        }
        $data = json_encode($count, JSON_PRETTY_PRINT);
        file_put_contents($turtleDir.'downloads.json', $data);

        $response = new BinaryFileResponse($turtleDir.$filename);
        $response->setContentDisposition('attachment', 'TurtleSystemD'.$version.'.exe');
        return $response;
    }

    /** --- DOWNLOAD TURTLE SYSTEM E
     * @Route("/e/{platform}", name="tse")
     */
    public function tse(Request $request, string $platform = 'browser'): BinaryFileResponse
    {
        $turtleDir = $this->getParameter('turtle_directory');
        $versions = json_decode(file_get_contents($turtleDir.'versions.json'), true);
        $exts = ['browser' => 'js', 'linux' => '???', 'macos' => 'app', 'win' => 'exe'];
        $ext = $exts[$platform];
        $filename = 'TurtleSystemE_'.$versions['E'].'.'.$ext;

        if (!file_exists($turtleDir.$filename)) {
            throw new NotFoundHttpException('File '.$filename.' not found.');
        }

        $response = new BinaryFileResponse($turtleDir.$filename);
        if ($platform == 'browser') {
            $response->setContentDisposition('inline');
        } else {
            $response->setContentDisposition('attachment', 'TurtleSystemE.'.$ext);
            $count = json_decode(file_get_contents($turtleDir.'downloads.json'), true);
            $month = date('Y').'.'.date('m');
            if (!array_key_exists($month, $count)) {
                $count[$month] = [];
            }
            if (!array_key_exists('E'.$platform, $count[$month])) {
                $count[$month]['E'.$platform] = 1;
            } else {
                $count[$month]['E'.$platform] += 1;
            }
            $data = json_encode($count, JSON_PRETTY_PRINT);
            file_put_contents($turtleDir.'downloads.json', $data);
        }
        return $response;
    }
}
