<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\File;
use App\Form\FileUploadType;
use App\Form\FileDownloadType;
use Symfony\Component\HttpFoundation\Request;

final class HomeController extends AbstractController
{

    #[Route('/', name: 'home')]
    public function index(Request $request, EntityManagerInterface $em): Response
    {
        $files = [
            ['id' => 1, 'filename' => 'Document1.pdf'],
            ['id' => 2, 'filename' => 'Document2.pdf'],
            ['id' => 3, 'filename' => 'Document3.pdf'],
        ];
        $file = new File();
        $uploadForm = $this->createForm(FileUploadType::class, $file);
        $uploadForm->handleRequest($request);
        $downloadForm = $this->createForm(FileDownloadType::class, $file);
        $downloadForm->handleRequest($request);

        return $this->renderHome($uploadForm, $downloadForm, $files);
    }

    public function uploadFile(
        Request $request,
        EntityManagerInterface $em,
        $uploadForm, $downloadForm, $files): Response{
        
        return $this->renderHome($uploadForm, $downloadForm, $files);
    }

    public function renderHome($uploadForm, $downloadForm, $files): Response{
        return $this->render('home/index.html.twig', [
            'controller_name' => 'HomeController',
            'uploadForm' => $uploadForm->createView(),
            'downloadForm' => $downloadForm->createView(),
            'files' => $files
        ]);
    }
}
