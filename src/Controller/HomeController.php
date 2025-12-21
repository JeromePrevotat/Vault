<?php

namespace App\Controller;

use App\Entity\Category;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\File;
use App\Form\FileUploadType;
use App\Form\FileDownloadType;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

final class HomeController extends AbstractController
{
    private EntityManagerInterface $em;
    private string $BASE_PATH;

    public function __construct(
        EntityManagerInterface $em,
        #[Autowire(param: 'kernel.project_dir')] string $projectDir)
    {
        $this->em = $em;
        $this->BASE_PATH = $projectDir . '/var/file_uploads/';
    }

    #[Route('/', name: 'home', methods: ['GET', 'POST'])]
    public function index(Request $request): Response
    {
        if ($request->isMethod('POST')) {
            if ($request->request->has('download-btn')) {
                return $this->downloadFile($request);
            }
            if ($request->request->has('upload-btn')) {
                return $this->uploadFile($request);
            }
        }
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }
        $files = $this->em->getRepository(File::class)->findByOwner($user);
        $file = new File();
        $uploadForm = $this->createForm(FileUploadType::class, $file);
        $uploadForm->handleRequest($request);
        $downloadForm = $this->createForm(FileDownloadType::class, $file);
        $downloadForm->handleRequest($request);

        return $this->renderHome($uploadForm, $downloadForm, $files);
    }

    #[Route('/download/{id}', name: 'download_file', methods: ['POST'])]
    public function downloadFile(Request $request): Response {
        $fileId = $request->get('id');
        $file = $this->em->getRepository(File::class)->find($fileId);

        if (!$file) {
            return new JsonResponse([
                'success' => false,
                'message' => 'File not found.',
            ], Response::HTTP_NOT_FOUND);
        }
        if ($file->getOwner() !== $this->getUser()) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Access denied.',
            ], Response::HTTP_FORBIDDEN);
        }

        $filePath = $file->getPath();
        if (!file_exists($filePath)) {
            return new JsonResponse([
                'success' => false,
                'message' => 'File not found.',
            ], Response::HTTP_NOT_FOUND);
        }

       return new JsonResponse([
            'success' => true,
            'iv' => $file->getIv(),
            'salt' => $file->getSalt(),
            'fileName' => $file->getFilename(),
            'fileContent' => base64_encode(file_get_contents($filePath)),
        ]);
    }

    #[Route('/api/files/filter', name: 'api_filter_files', methods: ['POST'])]
    public function apiFilterFiles(Request $request): JsonResponse{
        $user = $this->getUser();
        if (!$user) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Vous devez être connecté.',
            ]);
        }
        $categoryIds = $request->getPayload()->all('categories') ?? [];
        
        if (empty($categoryIds)) {
            $files = $this->em->getRepository(File::class)->findByOwner($user);
        } else {
            $files = $this->em->getRepository(File::class)->findByOwnerAndCategory($user, $categoryIds);
        }
        
        $data = array_map(fn(File $f) => [
            'id' => $f->getId(),
            'filename' => $f->getFilename(),
            'categories' => $f->getCategory()->map(fn(Category $c) => $c->getName())->toArray(),
        ], $files);
        
        return new JsonResponse(['success' => true, 'files' => $data]);
    }

    public function uploadFile(Request $request): Response {
        // Data validation
        $uploadedFile = $request->files->get('file');
        if (!$uploadedFile || !$uploadedFile->isValid()) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Error uploading file: ' . $uploadedFile->getErrorMessage(),
            ], Response::HTTP_BAD_REQUEST);
        }
        $file = new File();
        
        // Generate a unique filename to avoid collisions
        $uniqueFileName = uniqid() . '_' . $uploadedFile->getClientOriginalName();

        // Create user directory if it doesn't exist
        /** @var \App\Entity\User $user */
        $user = $this->getUser();
        if (!$user) {
            return new JsonResponse([
                'success' => false,
                'message' => 'User not authenticated',
            ]);
        }
        $saveDir = $this->BASE_PATH . $user->getId() . '/';
        if (!is_dir($saveDir)) {
            try {
                mkdir($saveDir, 0700, true);
            } catch (\Exception $e) {
                return new JsonResponse([
                    'success' => false,
                    'message' => 'Error creating directory: ' . $e->getMessage(),
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }

        // Move the file to the user-specific directory
        try {
            $uploadedFile->move($saveDir, $uniqueFileName);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Error saving file: ' . $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }    
        
        $file->setFilename($request->get('fileName'));
        $file->setPath($saveDir . $uniqueFileName);
        $file->setOwner($user);
        $file->setIv($request->get('iv'));
        $file->setSalt($request->get('salt'));
        $categoryIds = $request->request->all('category');
        $categories = $this->em->getRepository(Category::class)->findBy(['id' => $categoryIds]);
        foreach ($categories as $cat) {
            $file->addCategory($cat);
        }

        $this->em->persist($file);
        $this->em->flush();

        return new JsonResponse([
            'success' => true,
            'message' => 'File uploaded successfully',
        ]);
    }

    public function renderHome($uploadForm, $downloadForm, $files): Response
    {
        return $this->render('home/index.html.twig', [
            'controller_name' => 'HomeController',
            'uploadForm' => $uploadForm->createView(),
            'downloadForm' => $downloadForm->createView(),
            'files' => $files
        ]);
    }
}
