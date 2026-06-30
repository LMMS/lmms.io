<?php

declare(strict_types=1);

namespace App\Lsp\Submission;

final class InvalidDraftSubmissionException extends \RuntimeException
{
    public function __construct(string $message = 'Uploaded file is no longer available.')
    {
        parent::__construct($message);
    }
}
