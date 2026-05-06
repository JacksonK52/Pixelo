<?php

namespace pixelo\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use pixelo\Services\AvatarGenerator;

class AvatarController extends Controller
{
    public function __construct(protected AvatarGenerator $generator) {}

    /**
     * Generate
     * =================================
     * GET /avatar?name=Jackson+Konjengbam&size=128&shape=circle&bg=E53935&fg=FFFFFF
     */
    public function generate(Request $request): Response
    {
        $request->validate([
            'name'   => 'nullable|string|max:100',
            'size'   => 'nullable|integer|min:16|max:1024',
            'shape'  => 'nullable|in:circle,square,rounded',
            'bg'     => 'nullable|string|regex:/^[0-9A-Fa-f]{3,6}$/',
            'fg'     => 'nullable|string|regex:/^[0-9A-Fa-f]{3,6}$/',
            'length' => 'nullable|integer|min:1|max:4', 
        ]);

        $generator = $this->generator->make($request->input('name', '?'));

        if($request->filled('size')) {
            $generator = $generator->size((int)$request->input('size'));
        }

        if($request->filled('shape')) {
            $generator = $generator->shape($request->input('shape'));
        }

        if($request->filled('bg')) {
            $generator = $generator->background($request->input('bg'));
        }

        if($request->filled('fg')) {
            $generator = $generator->color($request->input('fg'));
        }

        if($request->filled('length')) {
            $generator = $generator->length((int)$request->input('length'));
        }

        // ETag caching
        $etag = md5(implode('|', $request->only(['name', 'size', 'shape', 'bg', 'fg', 'length'])));

        if($request->header('If-None-Match') === '"'.$etag.'"') {
            return response('', 304);
        }

        return $generator->toRespose()->withHeaders([
            'ETag' => '"'.$etag.'"',
        ]);
    }
}