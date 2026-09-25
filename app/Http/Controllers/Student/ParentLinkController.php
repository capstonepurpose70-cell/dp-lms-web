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
 * Estudyante: gumawa ng one-time code / QR para sa magulang.
 * Walang code na naka-store sa session — ipinapakita lang sa response na ito.
 */
class ParentLinkController extends Controller
{
    public function show(Request $request)
    {
        $student = $request->user()->load('parent', 'studentProfile');

        return view('student.parent-link', [
            'student' => $student,
            'parent'  => $student->parent,
            'link'    => null,
        ]);
    }

    public function store(Request $request, ParentLinkService $links)
    {
        $student = $request->user()->load('parent', 'studentProfile');
        $result  = $links->generate($student);

        $url = $result['purpose'] === 'link'
            ? route('parent.activate', ['code' => $result['code']])
            : route('parent.recover', ['code' => $result['code']]);

        $qrSvg = (new Writer(new ImageRenderer(new RendererStyle(240, 1), new SvgImageBackEnd())))
            ->writeString($url);

        return view('student.parent-link', [
            'student' => $student,
            'parent'  => $student->parent,
            'link'    => $result + ['url' => $url, 'qr_svg' => $qrSvg],
        ]);
    }
}
