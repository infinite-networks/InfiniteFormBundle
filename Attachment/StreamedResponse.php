<?php

namespace Infinite\FormBundle\Attachment;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse as BaseStreamedResponse;

class StreamedResponse extends BaseStreamedResponse
{
    public function prepare(Request $request)
    {
        // Prevent HttpFoundation\StreamedResponse from overwriting the
        // Cache-Control header by calling Response::prepare directly
        return Response::prepare($request);
    }
}
