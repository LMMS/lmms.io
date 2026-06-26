<?php

declare(strict_types=1);

namespace App\Controller\Lsp;

use App\Lsp\CategoryRepository;
use App\Lsp\Comment\CommentRepository;
use App\Lsp\Project\EditMetadata;
use App\Lsp\Project\ProjectDeletion;
use App\Lsp\Project\ProjectEdit;
use App\Lsp\Project\ProjectRepository;
use App\Lsp\Rating\RatingRepository;
use App\Lsp\Submission\DraftStorage;
use App\Lsp\Submission\FileTypeCatalog;
use App\Lsp\Submission\InvalidDraftSubmissionException;
use App\Lsp\Submission\ProjectSubmission;
use App\Lsp\Submission\SubmissionMetadata;
use App\Lsp\Submission\UnsupportedFileTypeException;
use App\Security\User;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class SubmissionController extends AbstractLspController
{
    public function __construct(
        CategoryRepository $categories,
        private readonly ProjectRepository $projects,
        private readonly CommentRepository $comments,
        private readonly RatingRepository $ratings,
        private readonly ProjectSubmission $submissions,
        private readonly ProjectDeletion $projectDeletion,
        private readonly ProjectEdit $projectEdit,
        private readonly DraftStorage $drafts,
        private readonly FileTypeCatalog $fileTypes,
    ) {
        parent::__construct($categories);
    }

    #[IsGranted('ROLE_USER')]
    public function rate(int $id, int $stars): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        $project = $this->projects->findProject($id);

        if ($project === null || $stars < 1 || $stars > 5) {
            return $this->redirectToRoute('lsp_show', ['id' => $id]);
        }

        if ($project->canBeRatedBy($user)) {
            $this->ratings->set($id, $user->getId(), $stars);
        }

        return $this->redirectToRoute('lsp_show', ['id' => $id]);
    }

    #[IsGranted('ROLE_USER')]
    public function addComment(int $id, Request $request): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        $project = $this->projects->findProject($id);

        if ($project === null) {
            return $this->errorResponse('File not found.', Response::HTTP_NOT_FOUND);
        }

        if ($request->request->get('addcomment') === 'Comment') {
            $text = html_entity_decode(trim($request->request->getString('text')));

            if ($text === '') {
                return $this->render('lsp/add_comment.twig', [
                    ...$this->categoryNav(),
                    'file_id'   => $id,
                    'file_name' => $project->filename,
                    'titles'    => ['Comment', $project->filename],
                    'error'     => 'Please type a message.',
                ]);
            }

            $this->comments->add($id, $user->getId(), $text);

            return $this->redirectToRoute('lsp_show', ['id' => $id]);
        }

        return $this->render('lsp/add_comment.twig', [
            ...$this->categoryNav(),
            'file_id'   => $id,
            'file_name' => $project->filename,
            'titles'    => ['Comment', $project->filename],
            'error'     => null,
        ]);
    }

    #[IsGranted('ROLE_USER')]
    public function deleteFile(int $id, Request $request): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        $project = $this->projects->findProject($id);

        if ($project === null) {
            return $this->errorResponse('File not found.', Response::HTTP_NOT_FOUND);
        }

        if (!$project->canBeEditedBy($user)) {
            return $this->redirectToRoute('lsp_show', ['id' => $id]);
        }

        if ($request->request->get('confirmation') === 'true') {
            $this->projectDeletion->delete($id);

            return $this->render('lsp/message.twig', [
                ...$this->categoryNav(),
                'titles'   => ['Delete'],
                'severity' => 'success',
                'icon'     => 'fa-check-circle',
                'title'    => 'Success',
                'message'  => 'File deleted successfully.',
                'redirect' => '/lsp',
                'counter'  => 3,
            ]);
        }

        return $this->render('lsp/delete_file.twig', [
            ...$this->categoryNav(),
            'file_name' => $project->filename,
            'file_id'   => $id,
        ]);
    }

    #[IsGranted('ROLE_USER')]
    public function editFile(int $id, Request $request): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        $project = $this->projects->findProject($id);

        if ($project === null) {
            return $this->errorResponse('File not found.', Response::HTTP_NOT_FOUND);
        }

        if (!$project->canBeEditedBy($user)) {
            return $this->errorResponse('You cannot edit this file.', Response::HTTP_FORBIDDEN);
        }

        if ($request->request->get('updateok') === 'OK') {
            $this->projectEdit->apply($id, EditMetadata::fromRequest($request));

            return $this->redirectToRoute('lsp_show', ['id' => $id]);
        }

        $ext = $this->fileTypes->extensionOf($project->filename);

        return $this->render('lsp/edit_file.twig', [
            ...$this->categoryNav($project->category, $project->subcategory),
            'titles'            => ['Edit', $project->filename],
            'form_action'       => '/lsp/edit/' . $id,
            'categories'        => $this->fileTypes->categoriesFor($ext),
            'selected_category' => $project->category . ' - ' . $project->subcategory,
            'licenses'          => $this->fileTypes->licenses(),
            'selected_license'  => $project->license,
            'file_id'           => $id,
            'fn'                => $project->filename,
            'tmpname'           => null,
            'fsize'             => null,
            'nocopyright'       => null,
            'description'       => $project->description ?? '',
        ]);
    }

    #[IsGranted('ROLE_USER')]
    public function addFile(Request $request): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        if ($request->request->get('addfinalok') === 'Add File') {
            $draftPath = $request->request->getString('tmpname');

            try {
                $this->drafts->assertResumable($draftPath);
            } catch (InvalidDraftSubmissionException $e) {
                return $this->errorResponse($e->getMessage(), Response::HTTP_BAD_REQUEST);
            }

            $projectId = $this->submissions->accept(
                $draftPath,
                SubmissionMetadata::fromRequest($request),
                $user->getId(),
            );

            return $this->redirectToRoute('lsp_show', ['id' => $projectId]);
        }

        // Step 2: upload received — validate type, move to tmp, show metadata form
        if ($request->request->get('ok') === 'OK') {
            if (!$request->request->get('nocopyright')) {
                return $this->errorResponse('Copyrighted content is forbidden.', Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            /** @var UploadedFile|null $uploaded */
            $uploaded = $request->files->get('filename');

            if ($uploaded === null) {
                return $this->errorResponse('No file specified.', Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            $originalName = $uploaded->getClientOriginalName();
            $fileSize = $uploaded->getSize();

            try {
                $draftPath = $this->drafts->open($uploaded);
            } catch (UnsupportedFileTypeException $e) {
                return $this->errorResponse($e->getMessage(), Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            return $this->render('lsp/edit_file.twig', [
                ...$this->categoryNav(),
                'titles'      => ['<a href="/lsp/add">Add File</a>', htmlspecialchars($originalName)],
                'form_action' => '/lsp/add',
                'categories'       => $this->fileTypes->categoriesFor($this->fileTypes->extensionOf($originalName)),
                'selected_category' => '',
                'licenses'         => $this->fileTypes->licenses(),
                'selected_license' => 'Creative Commons (by)',
                'file_id'          => null,
                'fn'               => $originalName,
                'tmpname'          => $draftPath,
                'fsize'            => $fileSize,
                'nocopyright'      => $request->request->get('nocopyright'),
                'description'      => '',
            ]);
        }

        // Step 1: show upload form
        return $this->render('lsp/add_file.twig', [
            ...$this->categoryNav(),
        ]);
    }
}
