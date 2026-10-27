<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Services\ParentLinkService;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Illuminate\Http\Request;

/**
 * Students: create a one-time code / QR for their parent.
 *
 * POST → redirect → GET: the code travels in the flash data for one request, so it is shown
 * once and a refresh never creates a new code (which would cancel one the parent may have scanned).
 */
class ParentLinkController extends Controller
{
    public function show(Request $request)
    {
        $student = $request->user()->load('parent', 'studentProfile');
        $link    = session('parent_link');

        if ($link) {
            $link['qr_svg'] = (new Writer(new ImageRenderer(new RendererStyle(240, 1), new SvgImageBackEnd())))
                ->writeString($link['url']);
        }

        return view('student.parent-link', [
            'student' => $student,
            'parent'  => $student->parent,
            'link'    => $link,
        ]);
    }

    public function store(Request $request, ParentLinkService $links)
    {
        $student = $request->user()->load('parent', 'studentProfile');
        $result  = $links->generate($student);

        $url = $result['purpose'] === 'link'
            ? route('parent.activate', ['code' => $result['code']])
            : route('parent.recover', ['code' => $result['code']]);

        return redirect()->route('student.parent-link')
            ->with('parent_link', $result + ['url' => $url]);
    }
}
