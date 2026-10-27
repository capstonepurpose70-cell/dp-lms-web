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
 *
 * B6-4: dati page ang ibinabalik ng POST — ang refresh / "Confirm resubmission" ay gumagawa ng
 * BAGONG code at binubura ang nauna (na baka na-scan na ng magulang). Ngayon: POST → redirect → GET.
 * Ang code ay nasa flash lang (isang request) — ipinapakita nang isang beses, saka nawawala.
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
