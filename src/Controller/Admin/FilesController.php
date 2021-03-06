<?php

/*
 * This file is part of the "Send And Get" project.
 * (c) Sergey Rybak <srybak007@gmail.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controller\Admin;

use App\Builder\AdminPageBuilderInterface;
use App\Entity\File;
use App\Form\FileFormType;
use App\Service\Files\FilesServiceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Admin site files
 * controller.
 */
class FilesController extends AbstractController
{
    private $pageBuilder;
    private $entitysService;
    private $uploadDir;

    public function __construct(
        AdminPageBuilderInterface $pageBuilder,
        FilesServiceInterface $entitysService,
        $uploadDir
    ) {
        $this->pageBuilder = $pageBuilder;
        $this->entitysService = $entitysService;
        $this->uploadDir = $uploadDir;
    }

    /**
     * admin files page.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function files(int $page): Response
    {
        return $this->render('admin/files.html.twig', [
            'page' => $this->pageBuilder->getFilesResource(),
            'files' => $this->entitysService->getAll($page, 10),
            'currentPage' => $page,
        ]);
    }

    /**
     * admin edit file page.
     *
     * @param int $id
     *
     * @return Response
     */
    public function editFile(int $id, Request $request): Response
    {
        $file = $this->entitysService->getById($id);

        if (null === $file) {
            throw new NotFoundHttpException("File #{$id} not found");
        }

        $form = $this->createForm(FileFormType::class, $file);
        $form->handleRequest($request);

        return $this->render('admin/edit_file.html.twig', [
            'page' => $this->pageBuilder->getEditFilesResource($file->getName()),
            'file' => $file,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Admin save file.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function store(int $id, Request $request): Response
    {
        $file = 0 !== $id ? $this->entitysService->getById($id) : new File();

        if (null === $file) {
            throw new NotFoundHttpException("File with is $id not found");
        }

        $form = $this->createForm(FileFormType::class, $file);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $file = $form->getData();
            $file->setUpdatedAt(new \DateTime());
            $file = $this->entitysService->save($file);
        }

        return $this->redirectToRoute('admin_edit_file', ['id' => $file->getId()], 301);
    }

    /**
     * admin remove file.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function destruct(int $id): Response
    {
        $file = 0 !== $id ? $this->entitysService->getById($id) : new File();

        if (null === $file) {
            throw new NotFoundHttpException("File with is $id not found");
        }

        $this->entitysService->remove($file);

        $path = $this->uploadDir.$file->getPath().$file->getName();

        unlink($path);

        return $this->redirectToRoute('admin_files', [], 301);
    }
}
